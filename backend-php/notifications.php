<?php
// notifications.php — espace membre : liste complète des notifications
require_once __DIR__ . '/includes/notifications.php';

$user = require_member($pdo);
$uid  = (int) $user['id'];
$ready = notifications_ready($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        flash_set('error', 'Ta session a expiré. Réessaie.');
        redirect('/notifications.php');
    }
    if ($ready) {
        $action = (string) ($_POST['action'] ?? '');
        if ($action === 'mark_read') {
            mark_notification_read($pdo, $uid, (int) ($_POST['id'] ?? 0));
        } elseif ($action === 'mark_all') {
            mark_all_notifications_read($pdo, $uid);
        }
    }
    redirect('/notifications.php');
}

$items = $ready ? get_notifications($pdo, $uid, 50) : [];
$unread = $ready ? unread_notifications_count($pdo, $uid) : 0;

member_page_start('Notifications', 'Notifications', 'Commandes, formations et annonces de Softener Lab.', 'notifications');

if (!$ready) {
    echo '<div class="alert alert--info" role="status">Les notifications sont momentanément indisponibles. Réessaie un peu plus tard.</div>';
    member_page_end();
    exit;
}
?>

    <?php if ($unread > 0): ?>
      <form method="post" action="/notifications.php" style="margin-bottom:20px;">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="mark_all">
        <button type="submit" class="btn-outline btn-small"><?= (int) $unread ?> non lue<?= $unread > 1 ? 's' : '' ?> · tout marquer comme lu</button>
      </form>
    <?php endif; ?>

    <?php if (empty($items)): ?>
      <div class="empty-state"><p>Aucune notification pour le moment.</p></div>
    <?php else: ?>
      <div class="order-list">
        <?php foreach ($items as $n): ?>
          <div class="order-row notif-row<?= $n['is_read'] ? '' : ' is-unread' ?>">
            <span class="order-row__main">
              <span class="order-row__title"><?= e($n['title']) ?></span>
              <span class="order-row__meta"><?= e($n['message']) ?></span>
              <span class="order-row__meta"><?= e(notification_type_label($n['type'])) ?> · <?= e(notif_time_ago($n['created_at'])) ?></span>
            </span>
            <span class="notif-row__actions">
              <?php if (!empty($n['link'])): ?>
                <a href="<?= e($n['link']) ?>" class="btn-outline btn-small">Voir</a>
              <?php endif; ?>
              <?php if (!$n['is_read']): ?>
                <form method="post" action="/notifications.php">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="mark_read">
                  <input type="hidden" name="id" value="<?= (int) $n['id'] ?>">
                  <button type="submit" class="btn-outline btn-small">Marquer comme lu</button>
                </form>
              <?php endif; ?>
            </span>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

<?php member_page_end(); ?>
