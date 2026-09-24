<?php
// includes/csrf.php
// Protection CSRF : un jeton aléatoire est stocké en session, injecté dans chaque
// formulaire POST de l'espace membre, puis comparé à la réception.

require_once __DIR__ . '/session.php';

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function csrf_valid(): bool {
    $sent = $_POST['csrf_token'] ?? '';
    $known = $_SESSION['csrf_token'] ?? '';
    return is_string($sent) && $sent !== '' && $known !== '' && hash_equals($known, $sent);
}
