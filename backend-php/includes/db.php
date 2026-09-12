<?php
// includes/db.php
// Remplis ces 4 valeurs avec celles données par InfinityFree
// (panneau > MySQL Databases). L'hôte est presque toujours "sqlXXX.infinityfree.com".

$DB_HOST = 'sqlXXX.infinityfree.com';
$DB_NAME = 'epiz_XXXXXXXX_softenerlab';
$DB_USER = 'epiz_XXXXXXXX';
$DB_PASS = 'change-moi';

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
