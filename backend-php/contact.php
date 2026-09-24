<?php
// contact.php — reçoit le formulaire de contact.html en AJAX (fetch) et
// répond en JSON. N'affiche aucune page : la mise en forme du message
// de succès/erreur est gérée côté client par src/main.js.

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

$sent = send_contact_email($name, $email, $company, $service, $message);

if (!$sent) {
    http_response_code(500);
    echo json_encode(['success' => false, 'errors' => ["L'envoi a échoué. Réessaie dans un instant ou écris-nous directement par email."]]);
    exit;
}

echo json_encode(['success' => true]);
