<?php
// notifications-status.php — API JSON pour la cloche de notifications du header.
// Appelée en fetch() depuis n'importe quelle page (assets/main.js). Les mutations
// (marquer comme lu) sont protégées par le cookie de session SameSite=Lax : un
// site tiers ne peut pas déclencher cet appel avec les identifiants du membre.
require_once __DIR__ . '/includes/notifications.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['loggedIn' => false]);
    exit;
}

$uid = (int) $_SESSION['user_id'];

if (!notifications_ready($pdo)) {
    echo json_encode(['loggedIn' => true, 'count' => 0, 'items' => []]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');
    if ($action === 'mark_read') {
        mark_notification_read($pdo, $uid, (int) ($_POST['id'] ?? 0));
    } elseif ($action === 'mark_all') {
        mark_all_notifications_read($pdo, $uid);
    }
}

$items = array_map(function (array $n): array {
    return [
        'id'      => (int) $n['id'],
        'type'    => $n['type'],
        'title'   => $n['title'],
        'message' => $n['message'],
        'link'    => $n['link'],
        'isRead'  => (bool) $n['is_read'],
        'timeAgo' => notif_time_ago($n['created_at']),
    ];
}, get_notifications($pdo, $uid, 8));

echo json_encode([
    'loggedIn' => true,
    'count'    => unread_notifications_count($pdo, $uid),
    'items'    => $items,
]);
