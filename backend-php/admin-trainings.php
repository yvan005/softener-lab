<?php
// admin-trainings.php — administration : demandes d'accès aux formations
require_once __DIR__ . '/includes/admin.php';
require_once __DIR__ . '/includes/mailer.php';

$admin = require_admin($pdo);

/* --- Actions --- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $back = '/admin-trainings.php' . (isset($_GET['status']) ? '?status=' . urlencode((string) $_GET['status']) : '');

    if (!csrf_valid()) {
        flash_set('error', 'Ta session a expiré. Réessaie.');
        redirect($back);
    }

    $pid    = (int) ($_POST['purchase_id'] ?? 0);
    $action = (string) ($_POST['action'] ?? '');

    $stmt = $pdo->prepare(
        'SELECT p.id, p.status, t.name AS training, u.full_name, u.email
         FROM purchases p
         JOIN trainings t ON t.id = p.training_id
         JOIN users u ON u.id = p.user_id
         WHERE p.id = ?'
    );
    $stmt->execute([$pid]);
    $p = $stmt->fetch();

    if (!$p) {
        flash_set('error', 'Demande introuvable.');
    } elseif ($action === 'activate' && $p['status'] === 'pending') {
        $pdo->prepare("UPDATE purchases SET status = 'paid' WHERE id = ?")->execute([$pid]);
        $sent = send_training_status_email($p['email'], $p['full_name'], $p['training'], 'paid');
        flash_set($sent ? 'success' : 'error',
            'Accès activé pour ' . $p['full_name'] . ' (« ' . $p['training'] . ' »).'
            . ($sent ? ' Email envoyé.' : " Mais l'email n'a pas pu être envoyé (vérifie la configuration SMTP)."));
    } elseif ($action === 'reject' && $p['status'] === 'pending') {
        $pdo->prepare("DELETE FROM purchases WHERE id = ? AND status = 'pending'")->execute([$pid]);
        $sent = send_training_status_email($p['email'], $p['full_name'], $p['training'], 'rejected');
        flash_set($sent ? 'success' : 'error',
            'Demande refusée pour ' . $p['full_name'] . ' (« ' . $p['training'] . ' »).'
            . ($sent ? ' Email envoyé.' : " Mais l'email n'a pas pu être envoyé (vérifie la configuration SMTP)."));
    } elseif ($action === 'revert' && $p['status'] === 'paid') {
        $pdo->prepare("UPDATE purchases SET status = 'pending' WHERE id = ?")->execute([$pid]);
        flash_set('success', 'Accès remis en attente pour ' . $p['full_name'] . '. Aucun email envoyé.');
    } else {
        flash_set('error', 'Action impossible sur cette demande.');
    }
    redirect($back);
}

/* --- Liste --- */
$filters = ['pending' => 'En attente', 'paid' => 'Actives', 'all' => 'Toutes'];
$filter = $_GET['status'] ?? 'pending';
if (!isset($filters[$filter])) $filter = 'pending';

$sql = 'SELECT p.id, p.status, p.purchased_at, t.name AS training, t.price_eur, u.full_name, u.email
        FROM purchases p
        JOIN trainings t ON t.id = p.training_id
        JOIN users u ON u.id = p.user_id'
     . ($filter === 'all' ? '' : ' WHERE p.status = ?')
     . ' ORDER BY p.purchased_at DESC, p.id DESC LIMIT 200';
$stmt = $pdo->prepare($sql);
$stmt->execute($filter === 'all' ? [] : [$filter]);
$rows = $stmt->fetchAll();

$pendingCount = (int) $pdo->query("SELECT COUNT(*) FROM purchases WHERE status = 'pending'")->fetchColumn();

admin_page_start('Demandes de formation', 'Demandes de formation', 'Active ou refuse les demandes d\'accès des membres.', 'trainings');
?>

    <div class="filter-chips" role="navigation" aria-label="Filtrer les demandes">
      <?php foreach ($filters as $key => $label): ?>
        <a href="/admin-trainings.php?status=<?= e($key) ?>" class="chip<?= $key === $filter ? ' is-active' : '' ?>"><?= e($label) ?><?= $key === 'pending' ? ' (' . $pendingCount . ')' : '' ?></a>
      <?php endforeach; ?>
    </div>

    <?php if (empty($rows)): ?>
      <div class="empty-state"><p>Aucune demande dans cette catégorie.</p></div>
    <?php else: ?>
      <div class="order-list">
        <?php foreach ($rows as $r): $isPaid = $r['status'] === 'paid'; ?>
          <div class="order-row admin-request">
            <span class="order-row__main">
              <span class="order-row__title"><?= e($r['training']) ?></span>
              <span class="order-row__meta"><?= e($r['full_name']) ?> · <?= e($r['email']) ?> · <?= e(price_label($r['price_eur'])) ?> · <?= e(fr_date($r['purchased_at'])) ?></span>
            </span>
            <span class="badge <?= $isPaid ? 'badge--ok' : 'badge--pending' ?>"><?= $isPaid ? 'Accès actif' : 'En attente' ?></span>
            <form class="admin-request__actions" method="post" action="/admin-trainings.php?status=<?= e($filter) ?>">
              <?= csrf_field() ?>
              <input type="hidden" name="purchase_id" value="<?= (int) $r['id'] ?>">
              <?php if ($isPaid): ?>
                <button type="submit" name="action" value="revert" class="btn-outline btn-small">Remettre en attente</button>
              <?php else: ?>
                <button type="submit" name="action" value="activate" class="btn-primary btn-small">Activer</button>
                <button type="submit" name="action" value="reject" class="btn-outline btn-small">Refuser</button>
              <?php endif; ?>
            </form>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

<?php member_page_end(); ?>
