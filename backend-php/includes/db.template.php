<?php
// includes/db.php
// Ce fichier est généré automatiquement à chaque déploiement à partir de
// includes/db.template.php — ne modifie pas directement ce fichier sur le
// serveur, tes changements seraient écrasés au prochain push.

$DB_HOST = 'sql109.infinityfree.com';
$DB_NAME = 'if0_42901352_db_softener';
$DB_USER = 'if0_42901352';
$DB_PASS = '__DB_PASSWORD__';

try {
    $pdo = new PDO(
        "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    die('Connexion à la base de données impossible. Vérifie includes/db.php.');
}
