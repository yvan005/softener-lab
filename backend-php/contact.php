<?php
// contact.php — reçoit le formulaire de contact.html en AJAX (fetch) et
// répond en JSON. N'affiche aucune page : la mise en forme du message
// de succès/erreur est gérée côté client par src/main.js.
//
// Membre connecté  → la demande devient une commande de suivi (table `orders`),
//                    comme si elle venait de orders.php. Le visiteur suit ensuite
//                    son avancement depuis /orders.php, comme n'importe quelle commande.
// Visiteur anonyme → comportement inchangé : simple email à l'équipe.

require_once __DIR__ . '/includes/orders.php';
require_once __DIR__ . '/includes/notifications.php';
require_once __DIR__ . '/includes/contact-messages.php';
require_once __DIR__ . '/includes/mailer.php';

header('Content-Type: application/json; charset=utf-8');

$allowedServices = [
    "Formations (vue d'ensemble)",
    'Formation Bureautique',
    'Formation Cybersécurité',
    'Formation Graphique & Design',
    'Formation Programmation Python',
    'Formation SEO International',
    "Design & infographie (vue d'ensemble)",
    'Design graphique',
    'Design web / UX-UI',
    'Design réseaux sociaux',
    'Motion design',
    'Illustration',
    'Design de packaging',
    "Design d'espace / intérieur",
    "Développement d'applications (vue d'ensemble)",
    'Applications web',
    'Applications mobiles',
    'Logiciels métier',
    "Cybersécurité (vue d'ensemble)",
    'Audit de sécurité',
    "Test d'intrusion",
    'Conseil et stratégie',
    'Formation et sensibilisation',
    'Sécurité réseau',
    'Réponse à incident',
    'Sécurité cloud',
    'Sécurité des applications',
    "Flyers & templates (vue d'ensemble)",
    'Modèle Événementiel',
    'Modèle Promotion',
    'Modèle Annonces',
    'Modèle Recrutement',
    'Modèle Associatif',
    'Modèle Restaurant / Menu',
    'Modèle Informatif / Éducatif',
    'Modèle Politique',
    'Modèle Invitation',
    'Modèle Coupon / Réduction',
    'Autre / plusieurs services',
];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'errors' => ["Méthode non autorisée."]]);
    exit;
}

$name    = trim($_POST['name'] ?? '');
$company = trim($_POST['company'] ?? '');
$email   = trim($_POST['email'] ?? '');
$service = trim($_POST['service'] ?? '');
$message = trim($_POST['message'] ?? '');

$errors = [];
if ($name === '') $errors[] = "Le nom est requis.";
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Adresse email invalide.";
if ($message === '') $errors[] = "Le message est requis.";
if ($service !== '' && !in_array($service, $allowedServices, true)) $errors[] = "Service concerné invalide.";

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

$uid = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;

if ($uid !== null && orders_ready($pdo)) {
    // Membre connecté : la demande devient une commande, pas un simple email.
    $stmt = $pdo->prepare('SELECT id, full_name, email FROM users WHERE id = ?');
    $stmt->execute([$uid]);
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(401);
        echo json_encode(['success' => false, 'errors' => ["Ta session a expiré. Recharge la page et réessaie."]]);
        exit;
    }

    $match = order_match_label($service);
    [$category, $orderService] = $match ?? ['autre', $service !== '' ? $service : 'Autre / plusieurs services'];

    $title = $service !== '' ? mb_substr($service, 0, 150) : 'Nouvelle demande de contact';

    $brief = "Demande envoyée depuis le formulaire de contact du site.\n\n"
           . ($company !== '' ? "Entreprise : {$company}\n\n" : '')
           . $message;
    $brief = mb_substr($brief, 0, 3000);

    $pdo->prepare(
        "INSERT INTO orders (user_id, category, service, title, brief, status)
         VALUES (?, ?, ?, ?, ?, 'pending')"
    )->execute([$uid, $category, $orderService, $title, $brief]);
    $id = (int) $pdo->lastInsertId();
    $ref = order_ref($id);

    // Prévient l'équipe : email + notification interne (cloche) aux comptes admin.
    notify_admin_new_order($user['full_name'], $user['email'], $id, $ref, $orderService, $title, $brief, null);
    notify_admins(
        $pdo,
        'admin_order',
        'Nouvelle commande',
        $ref . ' — ' . $title . ' (' . $orderService . ') par ' . $user['full_name'],
        '/admin-order.php?id=' . $id
    );

    echo json_encode([
        'success' => true,
        'message' => "Merci ! Ta demande a été enregistrée comme commande {$ref}. Tu peux suivre son avancement depuis « Mes commandes ».",
    ]);
    exit;
}

// Visiteur non connecté : email à l'équipe (comme avant) + archivage du message
// (page /admin-contact-messages.php) + notification interne (cloche) aux comptes
// admin. L'archivage et la notification sont indépendants du succès de l'email.
$msgId = save_contact_message($pdo, $name, $email, $company, $service, $message);

notify_admins(
    $pdo,
    'contact_visitor',
    'Nouveau message de contact',
    $name . ' (' . $email . ')' . ($service !== '' ? ' — ' . $service : '') . " : \n" . mb_substr($message, 0, 300),
    $msgId !== null ? '/admin-contact-messages.php#msg-' . $msgId : '/admin-contact-messages.php'
);

$sent = send_contact_email($name, $email, $company, $service, $message);

if (!$sent) {
    http_response_code(500);
    echo json_encode(['success' => false, 'errors' => ["L'envoi a échoué. Réessaie dans un instant ou écris-nous directement par email."]]);
    exit;
}

echo json_encode(['success' => true]);
