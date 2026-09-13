<?php
// includes/db.php
// Remplis ces 4 valeurs avec celles données par InfinityFree
// (panneau > MySQL Databases). L'hôte est presque toujours "sqlXXX.infinityfree.com".

$DB_HOST = 'sql109.infinityfree.com';
$DB_NAME = 'if0_42901352_db_softener';
$DB_USER = 'if0_42901352';
$DB_PASS = 'TON_MOT_DE_PASSE_VPANEL'; // remplace par ton mot de passe vPanel InfinityFree

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
