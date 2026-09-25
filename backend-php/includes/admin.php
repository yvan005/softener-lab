<?php
// includes/admin.php — briques communes des pages d'administration
require_once __DIR__ . '/orders.php';
require_once __DIR__ . '/roles.php';

/** Exige un membre connecté ET administrateur. */
function require_admin(PDO $pdo): array {
    $user = require_member($pdo);
    if (!is_admin_email($user['email'])) {
        flash_set('error', "Cette section est réservée à l'administration.");
        redirect('/dashboard.php');
    }
    return $user;
}

function admin_page_start(string $title, string $h1, string $lead, string $active): void {
    $tabs = [
        'orders'        => ['/admin.php',              'Commandes'],
        'trainings'     => ['/admin-trainings.php',    'Demandes de formation'],
        'contact'       => ['/admin-contact-messages.php', 'Messages de contact'],
        'announcements' => ['/admin-announcements.php', 'Annonces'],
        'member'        => ['/dashboard.php',          '← Espace membre'],
    ];
    page_start($title, $h1, $lead, $tabs, $active, 'ADMINISTRATION');
}
