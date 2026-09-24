<?php
// includes/session.php
// Démarre la session avec des cookies sécurisés. À inclure (require_once)
// à la place d'un session_start() direct dans toutes les pages qui en ont besoin.

if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,      // inaccessible en JS, protège contre le vol de cookie via XSS
        'samesite' => 'Lax',     // limite l'envoi du cookie depuis un site tiers (CSRF)
        'secure'   => $isHttps,  // cookie envoyé uniquement en HTTPS quand le site y est servi
    ]);

    session_start();
}
