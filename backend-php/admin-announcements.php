<?php
// admin-announcements.php — administration : annonces diffusées à tous les membres
require_once __DIR__ . '/includes/admin.php';
require_once __DIR__ . '/includes/notifications.php';

$admin = require_admin($pdo);
$errors = [];

if (!notifications_ready($pdo)) {
    admin_page_start('Annonces', 'Annonces', 'Diffuse un message à tous les membres.', 'announcements');
    echo '<div class="alert alert--info" role="status">Les notifications sont momentanément indisponibles. Réessaie un peu plus tard.</div>';
    member_page_end();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        flash_set('error', 'Ta session a expiré. Réessaie.');
        redirect('/admin-announcements.php');
    }

    $title = trim((string) ($_POST['title'] ?? ''));
    $message = trim((string) ($_POST['message'] ?? ''));

    if ($title === '' || mb_strlen($title) > 150) $errors[] = 'Le titre doit faire entre 1 et 150 caractères.';
    if ($message === '' || mb_strlen($message) > 500) $errors[] = 'Le message doit faire entre 1 et 500 caractères.';

    if (empty($errors)) {
        $pdo->prepare('INSERT INTO announcements (title, message) VALUES (?, ?)')->execute([$title, $message]);
        $count = notify_all_users($pdo, 'announcement', $title, $message, '/notifications.php');
        flash_set('success', 'Annonce envoyée à ' . $count . ' membre' . ($count > 1 ? 's' : '') . '.');
        redirect('/admin-announcements.php');
    }
}

$history = $pdo->query('SELECT id, title, message, created_at FROM announcements ORDER BY created_at DESC LIMIT 30')->fetchAll();

admin_page_start('Annonces', 'Annonces', 'Diffuse un message à tous les membres — il apparaîtra dans leur cloche de notifications.', 'announcements');
?>

    <?php foreach ($errors as $err): ?>
      <div class="alert alert--error" role="alert"><?= e($err) ?></div>
    <?php endforeach; ?>

    <form class="form-box member-box" method="post" action="/admin-announcements.php">
      <h2 class="member-h2 member-h2--box">Nouvelle annonce</h2>
      <?= csrf_field() ?>
      <div class="form-row">
        <label for="title">Titre</label>
        <input type="text" id="title" name="title" maxlength="150" required value="<?= e($_POST['title'] ?? '') ?>">
      </div>
      <div class="form-row">
        <label for="message">Message <span class="form-hint-inline">(500 caractères max, visible dans la notification)</span></label>
        <textarea id="message" name="message" maxlength="500" required><?= e($_POST['message'] ?? '') ?></textarea>
      </div>
      <button type="submit" class="btn-primary">Envoyer à tous les membres</button>
    </form>

    <div class="section-head">
      <h2 class="member-h2">Historique</h2>
    </div>

    <?php if (empty($history)): ?>
      <div class="empty-state"><p>Aucune annonce envoyée pour le moment.</p></div>
    <?php else: ?>
      <div class="order-list">
        <?php foreach ($history as $a): ?>
          <div class="order-row admin-request">
            <span class="order-row__main">
              <span class="order-row__title"><?= e($a['title']) ?></span>
              <span class="order-row__meta"><?= e($a['message']) ?></span>
            </span>
            <span class="order-row__meta"><?= e(fr_date($a['created_at'])) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

<?php member_page_end(); ?>
