<?php
// includes/roles.php
// Qui est administrateur ? Les adresses email listées dans ADMIN_EMAILS (config.php).
// Comme la connexion exige un email vérifié et que l'email n'est pas modifiable depuis
// l'espace membre, seul le propriétaire de la boîte mail peut obtenir ce rôle.

require_once __DIR__ . '/config.php';

function admin_emails(): array {
    if (!defined('ADMIN_EMAILS')) return [];
    $list = ADMIN_EMAILS;
    if (!is_array($list)) $list = [$list];
    $list = array_map(fn($e) => strtolower(trim((string) $e)), $list);
    return array_values(array_filter($list, fn($e) => $e !== ''));
}

function is_admin_email(?string $email): bool {
    $email = strtolower(trim((string) $email));
    return $email !== '' && in_array($email, admin_emails(), true);
}
