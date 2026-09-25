<?php
// order.php — espace membre : détail d'une commande (suivi + annulation)
require_once __DIR__ . '/includes/orders.php';

$user = require_member($pdo);
$uid  = (int) $user['id'];

if (!orders_ready($pdo)) {
    flash_set('error', 'Le suivi des commandes est momentanément indisponible.');
    redirect('/orders.php');
}

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $uid]);
$order = $stmt->fetch();

if (!$order) {
    flash_set('error', 'Commande introuvable.');
    redirect('/orders.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        flash_set('error', 'Ta session a expiré. Réessaie.');
    } elseif (($_POST['action'] ?? '') === 'cancel') {
        $upd = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ? AND user_id = ? AND status = 'pending'");
        $upd->execute([$id, $uid]);
        if ($upd->rowCount() > 0) {
            log_order_event($pdo, $id, 'cancelled', 'Annulée par le membre.');
            flash_set('success', 'Ta commande a été annulée.');
        } else {
            flash_set('error', "Cette commande ne peut plus être annulée : elle est déjà prise en charge.");
        }
    }
    redirect('/order.php?id=' . $id);
}

$ref = order_ref($order['id']);
$status = $order['status'];
$steps = ['pending' => 'Reçue', 'in_progress' => 'En cours', 'delivered' => 'Livrée'];
$stepKeys = array_keys($steps);
$currentIndex = array_search($status, $stepKeys, true);
$events = get_order_events($pdo, $id);

member_page_start($ref, $order['title'], 'Commande ' . $ref . ' · ' . order_category_label($order['category']), 'orders');
?>

    <p class="back-link"><a href="/orders.php">← Toutes mes commandes</a></p>

    <?php if ($status === 'cancelled'): ?>
      <div class="alert alert--info" role="status">Cette commande a été annulée.</div>
    <?php else: ?>
      <ol class="order-steps" aria-label="Avancement de la commande">
        <?php foreach ($stepKeys as $i => $key): ?>
          <li class="<?= $i < $currentIndex ? 'is-done' : ($i === $currentIndex ? 'is-current' : '') ?>"><?= e($steps[$key]) ?></li>
        <?php endforeach; ?>
      </ol>
    <?php endif; ?>

    <?php if (!empty($order['admin_note'])): ?>
      <div class="admin-note">
        <strong>Message de Softener Lab</strong>
        <p><?= nl2br(e($order['admin_note'])) ?></p>
      </div>
    <?php endif; ?>

    <div class="order-detail">
      <div class="content-card">
        <h3>Ton besoin</h3>
        <p class="order-brief"><?= nl2br(e($order['brief'])) ?></p>
      </div>

      <aside class="content-card">
        <h3>Détails</h3>
        <dl class="order-facts">
          <dt>Statut</dt><dd><?= order_badge($status) ?></dd>
          <dt>Service</dt><dd><?= e($order['service']) ?></dd>
          <dt>Reçue le</dt><dd><?= e(fr_date($order['created_at'])) ?></dd>
          <?php if (!empty($order['deadline'])): ?>
            <dt>Échéance souhaitée</dt><dd><?= e(fr_date($order['deadline'])) ?></dd>
          <?php endif; ?>
          <?php if (!empty($order['updated_at']) && $order['updated_at'] !== $order['created_at']): ?>
            <dt>Dernière mise à jour</dt><dd><?= e(fr_date($order['updated_at'])) ?></dd>
          <?php endif; ?>
        </dl>

        <?php if ($status === 'pending'): ?>
          <form method="post" action="/order.php?id=<?= (int) $order['id'] ?>" class="order-actions">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="cancel">
            <button type="submit" class="btn-outline btn-small">Annuler la commande</button>
          </form>
        <?php elseif ($status === 'in_progress'): ?>
          <p class="form-hint">Nous travaillons sur ta commande. Une précision à ajouter ? <a href="/contact.html">Écris-nous</a>.</p>
        <?php elseif (in_array($status, ['delivered', 'cancelled'], true)): ?>
          <div class="order-actions">
            <a class="btn-outline btn-small" href="/orders.php?new=1&amp;reorder=<?= (int) $order['id'] ?>">Recommander ce service</a>
          </div>
        <?php endif; ?>
      </aside>
    </div>

    <?php if (!empty($events)): ?>
      <div class="content-card" style="margin-top:24px;">
        <h3>Historique</h3>
        <ul class="order-history">
          <?php foreach (array_reverse($events) as $ev): ?>
            <li>
              <span class="order-history__status"><?= e(order_statuses()[$ev['status']]['label'] ?? $ev['status']) ?></span>
              <span class="order-history__date"><?= e(fr_date($ev['created_at'])) ?> à <?= e(date('H:i', strtotime($ev['created_at']))) ?></span>
              <?php if (!empty($ev['note'])): ?><p class="order-history__note"><?= nl2br(e($ev['note'])) ?></p><?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

<?php member_page_end(); ?>
