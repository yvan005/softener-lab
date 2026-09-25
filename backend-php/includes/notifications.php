<?php
// includes/notifications.php
// Notifications internes des membres (cloche du header + page /notifications.php).
// Déclenchées par : changement de statut de commande, réponse à une demande de
// formation, annonce diffusée par l'admin. Table auto-créée si absente, même
// principe que includes/orders.php (la base est gérée à la main via phpMyAdmin).

require_once __DIR__ . '/member.php';

/** Vrai si les tables `notifications` et `announcements` sont utilisables. */
function notifications_ready(PDO $pdo): bool {
    static $ready = null;
    if ($ready !== null) return $ready;

    try {
        $pdo->query('SELECT 1 FROM notifications LIMIT 1');
    } catch (Throwable $e) {
        try {
            $pdo->exec(
                "CREATE TABLE IF NOT EXISTS notifications (
                  id INT AUTO_INCREMENT PRIMARY KEY,
                  user_id INT NOT NULL,
                  type VARCHAR(30) NOT NULL,
                  title VARCHAR(150) NOT NULL,
                  message VARCHAR(500) NOT NULL,
                  link VARCHAR(190) DEFAULT NULL,
                  is_read TINYINT(1) NOT NULL DEFAULT 0,
                  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                  INDEX idx_notifications_user (user_id, is_read, created_at)
                )"
            );
            $pdo->query('SELECT 1 FROM notifications LIMIT 1');
        } catch (Throwable $e2) {
            error_log('Softener Lab : table `notifications` introuvable et création impossible — importe sql/schema.sql. ' . $e2->getMessage());
            return $ready = false;
        }
    }

    try {
        $pdo->query('SELECT 1 FROM announcements LIMIT 1');
    } catch (Throwable $e) {
        try {
            $pdo->exec(
                "CREATE TABLE IF NOT EXISTS announcements (
                  id INT AUTO_INCREMENT PRIMARY KEY,
                  title VARCHAR(150) NOT NULL,
                  message VARCHAR(500) NOT NULL,
                  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                )"
            );
            $pdo->query('SELECT 1 FROM announcements LIMIT 1');
        } catch (Throwable $e2) {
            error_log('Softener Lab : table `announcements` introuvable et création impossible — importe sql/schema.sql. ' . $e2->getMessage());
            return $ready = false;
        }
    }

    return $ready = true;
}

/** Envoie une notification à un seul membre. */
function notify_user(PDO $pdo, int $userId, string $type, string $title, string $message, ?string $link = null): void {
    if (!notifications_ready($pdo)) return;
    $pdo->prepare('INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, ?, ?, ?, ?)')
        ->execute([$userId, $type, mb_substr($title, 0, 150), mb_substr($message, 0, 500), $link]);
}

/** Envoie la même notification à tous les membres (annonces admin). */
function notify_all_users(PDO $pdo, string $type, string $title, string $message, ?string $link = null): int {
    if (!notifications_ready($pdo)) return 0;
    $ids = $pdo->query('SELECT id FROM users')->fetchAll(PDO::FETCH_COLUMN);
    if (!$ids) return 0;

    $stmt = $pdo->prepare('INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, ?, ?, ?, ?)');
    $title = mb_substr($title, 0, 150);
    $message = mb_substr($message, 0, 500);
    foreach ($ids as $uid) {
        $stmt->execute([(int) $uid, $type, $title, $message, $link]);
    }
    return count($ids);
}

/**
 * Notifie en interne (cloche) les comptes administrateurs — ceux dont l'email
 * figure dans ADMIN_EMAILS (includes/roles.php). Comme pour le tableau "Administration",
 * le compte doit exister et avoir confirmé son email ; sinon la notification est
 * simplement ignorée pour cette adresse (l'email à l'équipe reste envoyé en parallèle).
 */
function notify_admins(PDO $pdo, string $type, string $title, string $message, ?string $link = null): int {
    if (!notifications_ready($pdo)) return 0;
    $emails = admin_emails();
    if (!$emails) return 0;

    $placeholders = implode(',', array_fill(0, count($emails), '?'));
    $stmt = $pdo->prepare("SELECT id FROM users WHERE LOWER(email) IN ({$placeholders})");
    $stmt->execute($emails);
    $ids = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    if (!$ids) return 0;

    $stmt = $pdo->prepare('INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, ?, ?, ?, ?)');
    $title = mb_substr($title, 0, 150);
    $message = mb_substr($message, 0, 500);
    foreach ($ids as $uid) {
        $stmt->execute([$uid, $type, $title, $message, $link]);
    }
    return count($ids);
}

function unread_notifications_count(PDO $pdo, int $userId): int {
    if (!notifications_ready($pdo)) return 0;
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
    $stmt->execute([$userId]);
    return (int) $stmt->fetchColumn();
}

/** @return array<int, array<string, mixed>> */
function get_notifications(PDO $pdo, int $userId, int $limit = 50, int $offset = 0): array {
    if (!notifications_ready($pdo)) return [];
    $limit = max(1, min(100, $limit));
    $offset = max(0, $offset);
    $stmt = $pdo->prepare(
        "SELECT id, type, title, message, link, is_read, created_at
         FROM notifications WHERE user_id = ? ORDER BY created_at DESC, id DESC LIMIT {$limit} OFFSET {$offset}"
    );
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function total_notifications_count(PDO $pdo, int $userId): int {
    if (!notifications_ready($pdo)) return 0;
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ?');
    $stmt->execute([$userId]);
    return (int) $stmt->fetchColumn();
}

function mark_notification_read(PDO $pdo, int $userId, int $id): void {
    if (!notifications_ready($pdo)) return;
    $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?')->execute([$id, $userId]);
}

function mark_all_notifications_read(PDO $pdo, int $userId): void {
    if (!notifications_ready($pdo)) return;
    $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0')->execute([$userId]);
}

/** "à l'instant" / "il y a 5 min" / "il y a 3 h" / "il y a 2 j" / date complète au-delà. */
function notif_time_ago(string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return "à l'instant";
    if ($diff < 3600) { $n = (int) floor($diff / 60); return 'il y a ' . $n . ' min'; }
    if ($diff < 86400) { $n = (int) floor($diff / 3600); return 'il y a ' . $n . ' h'; }
    if ($diff < 7 * 86400) { $n = (int) floor($diff / 86400); return 'il y a ' . $n . ' j'; }
    return fr_date($datetime);
}

function notification_type_label(string $type): string {
    return match ($type) {
        'order_status'    => 'Commande',
        'training_status' => 'Formation',
        'announcement'    => 'Annonce',
        'contact_visitor' => 'Contact',
        'admin_order'     => 'Commande',
        default           => 'Notification',
    };
}
