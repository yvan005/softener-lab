<?php
// admin-contact-messages.php — administration : messages bruts envoyés par les
// visiteurs non connectés via contact.html (archivés par contact.php, en plus de
// l'email et de la notification interne). Les commandes créées par les membres via
// le même formulaire n'apparaissent pas ici : elles sont dans /admin.php.
require_once __DIR__ . '/includes/admin.php';
require_once __DIR__ . '/includes/contact-messages.php';

$admin = require_admin($pdo);
$ready = contact_messages_ready($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        flash_set('error', 'Ta session a expiré. Réessaie.');
        redirect('/admin-contact-messages.php');
    }
    if ($ready) {
        $action = (string) ($_POST['action'] ?? '');
        if ($action === 'mark_read') {
            mark_contact_message_read($pdo, (int) ($_POST['id'] ?? 0));
        } elseif ($action === 'mark_all') {
            mark_all_contact_messages_read($pdo);
        }
    }
    redirect('/admin-contact-messages.php');
}

admin_page_start('Messages de contact', 'Messages de contact', 'Demandes envoyées par des visiteurs non connectés depuis le formulaire de contact.', 'contact');

if (!$ready) {
    echo '<div class="alert alert--info" role="status">Les messages de contact sont momentanément indisponibles. Réessaie un peu plus tard.</div>';
    member_page_end();
    exit;
}

$messages = $pdo->query(
    'SELECT id, name, email, company, service, message, is_read, created_at
     FROM contact_messages ORDER BY created_at DESC LIMIT 200'
)->fetchAll();
$unread = unread_contact_messages_count($pdo);
?>

    <?php if ($unread > 0): ?>
      <form method="post" action="/admin-contact-messages.php" style="margin-bottom:20px;">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="mark_all">
        <button type="submit" class="btn-outline btn-small"><?= (int) $unread ?> non lu<?= $unread > 1 ? 's' : '' ?> · tout marquer comme lu</button>
      </form>
    <?php endif; ?>

    <?php if (empty($messages)): ?>
      <div class="empty-state"><p>Aucun message de contact pour le moment.</p></div>
    <?php else: ?>
      <div class="order-list">
        <?php foreach ($messages as $m): ?>
          <div class="order-row notif-row<?= $m['is_read'] ? '' : ' is-unread' ?>" id="msg-<?= (int) $m['id'] ?>">
            <span class="order-row__main">
              <span class="order-row__title">
                <?= e($m['name']) ?>
                <a href="mailto:<?= e($m['email']) ?>" style="font-weight:400; text-decoration:underline;"><?= e($m['email']) ?></a>
              </span>
              <span class="order-row__meta"><?= nl2br(e($m['message'])) ?></span>
              <span class="order-row__meta">
                <?= $m['company'] ? e($m['company']) . ' · ' : '' ?><?= $m['service'] ? e($m['service']) . ' · ' : '' ?><?= e(fr_date($m['created_at'])) ?>
              </span>
            </span>
            <span class="notif-row__actions">
              <?php if (!$m['is_read']): ?>
                <form method="post" action="/admin-contact-messages.php">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="mark_read">
                  <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
                  <button type="submit" class="btn-outline btn-small">Marquer comme lu</button>
                </form>
              <?php endif; ?>
            </span>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

<?php member_page_end(); ?>
