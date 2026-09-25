<?php
// export-data.php — espace membre : téléchargement des données personnelles
// (profil, commandes, formations) au format JSON, avant une éventuelle
// suppression de compte depuis /profile.php.
require_once __DIR__ . '/includes/orders.php';

$user = require_member($pdo);
$uid  = (int) $user['id'];

$data = [
    'exporte_le' => date('c'),
    'profil' => [
        'nom'             => $user['full_name'],
        'email'           => $user['email'],
        'membre_depuis'   => $user['created_at'],
    ],
    'formations' => [],
    'commandes'  => [],
];

$stmt = $pdo->prepare(
    'SELECT t.name, t.description, p.status, p.purchased_at
     FROM purchases p JOIN trainings t ON t.id = p.training_id
     WHERE p.user_id = ? ORDER BY p.purchased_at DESC'
);
$stmt->execute([$uid]);
$data['formations'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (orders_ready($pdo)) {
    $stmt = $pdo->prepare(
        'SELECT category, service, title, brief, deadline, status, created_at
         FROM orders WHERE user_id = ? ORDER BY created_at DESC'
    );
    $stmt->execute([$uid]);
    $data['commandes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

header('Content-Type: application/json; charset=utf-8');
header('Content-Disposition: attachment; filename="softener-lab-mes-donnees.json"');
header('Content-Length: ' . strlen($json));
echo $json;
