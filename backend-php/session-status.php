<?php
// session-status.php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/roles.php';
header('Content-Type: application/json');

if (isset($_SESSION['user_id'])) {
    echo json_encode([
        'loggedIn' => true,
        'name' => $_SESSION['user_name'] ?? '',
        'email' => $_SESSION['user_email'] ?? '',
        'isAdmin' => is_admin_email($_SESSION['user_email'] ?? ''),
    ]);
} else {
    echo json_encode(['loggedIn' => false]);
}
