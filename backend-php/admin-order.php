<?php
// admin-order.php — administration : détail d'une commande, changement de statut + email au membre
require_once __DIR__ . '/includes/admin.php';
require_once __DIR__ . '/includes/mailer.php';
require_once __DIR__ . '/includes/notifications.php';

$admin = require_admin($pdo);

if (!orders_ready($pdo)) {
    flash_set('error', 'Le suivi des commandes est momentanément indisponible.');
    redirect('/admin.php');
}

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare(
    'SELECT o.*, u.full_name, u.email, u.created_at AS member_since
     FROM orders o JOIN users u ON u.id = o.user_id WHERE o.id = ?'
);
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) {
    flash_set('error', 'Commande introuvable.');
    redirect('/admin.php');
}

$ref = order_ref($order['id']);
$statuses = order_statuses();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        flash_set('error', 'Ta session a expiré. Réessaie.');
        redirect('/admin-order.php?id=' . $id);
    }

    $newStatus = (string) ($_POST['status'] ?? '');
    $note      = trim((string) ($_POST['note'] ?? ''));
    $notify    = !empty($_POST['notify']);

    if (!isset($statuses[$newStatus])) $errors[] = 'Statut invalide.';
    if (mb_strlen($note) > 2000) $errors[] = 'Le message ne doit pas dépasser 2000 caractères.';

    if (empty($errors)) {
        $changed = $newStatus !== $order['status'];

        if (!$changed && $note === '') {
            flash_set('info', 'Aucun changement à enregistrer.');
            redirect('/admin-order.php?id=' . $id);
        }

        if ($note !== '') {
            $pdo->prepare('UPDATE orders SET status = ?, admin_note = ? WHERE id = ?')->execute([$newStatus, $note, $id]);
        } else {
            $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute([$newStatus, $id]);
        }

        $msg = $changed
            ? 'Statut mis à jour : ' . $statuses[$newStatus]['label'] . '.'
            : 'Message enregistré.';

        $notifTitle = 'Commande ' . $ref;
        $notifBody  = $changed ? 'Nouveau statut : ' . $statuses[$newStatus]['label'] . '.' : 'Nouveau message reçu.';
        if ($note !== '') $notifBody .= ' « ' . mb_substr($note, 0, 200) . ' »';
        notify_user($pdo, (int) $order['user_id'], 'order_status', $notifTitle, $notifBody, '/order.php?id=' . $id);

        if ($notify) {
            $sent = send_order_status_email(
                $order['email'], $order['full_name'], (int) $order['id'], $ref,
                $order['title'], $order['service'], $newStatus, $note
            );
            if ($sent) {
                flash_set('success', $msg . ' Email envoyé à ' . $order['email'] . '.');
            } else {
                flash_set('error', $msg . " Mais l'email n'a pas pu être envoyé (vérifie la configuration SMTP).");
            }
        } else {
            flash_set('success', $msg . ' Aucun email envoyé.');
        }
        redirect('/admin-order.php?id=' . $id);
    }
}

admin_page_start($ref, $order['title'], 'Commande ' . $ref . ' · ' . $order['full_name'], 'orders');
?>

    <p class="back-link"><a href="/admin.php">← Toutes les commandes</a></p>

    <?php foreach ($errors as $err): ?>
      <div class="alert alert--error" role="alert"><?= e($err) ?></div>
    <?php endforeach; ?>

    <div class="order-detail">
      <div>
        <div class="content-card">
          <h3>Besoin du membre</h3>
          <p class="order-brief"><?= nl2br(e($order['brief'])) ?></p>
        </div>

        <?php if (!empty($order['admin_note'])): ?>
          <div class="admin-note">
            <strong>Dernier message envoyé au membre</strong>
            <p><?= nl2br(e($order['admin_note'])) ?></p>
          </div>
        <?php endif; ?>

        <form class="form-box member-box" method="post" action="/admin-order.php?id=<?= (int) $order['id'] ?>" style="margin-top:24px;">
          <h2 class="member-h2 member-h2--box">Mettre à jour</h2>
          <?= csrf_field() ?>
          <div class="form-row">
            <label for="status">Statut</label>
            <select id="status" name="status">
              <?php foreach ($statuses as $key => $s): ?>
                <option value="<?= e($key) ?>"<?= $key === $order['status'] ? ' selected' : '' ?>><?= e($s['label']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-row">
            <label for="note">Message au membre <span class="form-hint-inline">(facultatif — visible sur sa commande et joint à l'email)</span></label>
            <textarea id="note" name="note" maxlength="2000" placeholder="Ex : Première maquette prête, on t'envoie les fichiers demain."></textarea>
          </div>
          <label class="check-row check-row--ok">
            <input type="checkbox" name="notify" value="1" checked>
            <span>Prévenir le membre par email (<?= e($order['email']) ?>)</span>
          </label>
          <button type="submit" class="btn-primary">Enregistrer</button>
        </form>
      </div>

      <aside class="content-card">
        <h3>Détails</h3>
        <dl class="order-facts">
          <dt>Statut</dt><dd><?= order_badge($order['status']) ?></dd>
          <dt>Membre</dt><dd><?= e($order['full_name']) ?></dd>
          <dt>Email</dt><dd><a href="mailto:<?= e($order['email']) ?>" style="text-decoration:underline;"><?= e($order['email']) ?></a></dd>
          <dt>Service</dt><dd><?= e(order_category_label($order['category'])) ?> · <?= e($order['service']) ?></dd>
          <dt>Reçue le</dt><dd><?= e(fr_date($order['created_at'])) ?></dd>
          <?php if (!empty($order['deadline'])): ?>
            <dt>Échéance</dt><dd><?= e(fr_date($order['deadline'])) ?></dd>
          <?php endif; ?>
          <?php if (!empty($order['updated_at']) && $order['updated_at'] !== $order['created_at']): ?>
            <dt>Mise à jour</dt><dd><?= e(fr_date($order['updated_at'])) ?></dd>
          <?php endif; ?>
          <dt>Membre depuis</dt><dd><?= e(fr_date($order['member_since'])) ?></dd>
        </dl>
      </aside>
    </div>

<?php member_page_end(); ?>
