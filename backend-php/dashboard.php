<?php
// dashboard.php — espace membre : mes formations + catalogue
require_once __DIR__ . '/includes/orders.php';

$user = require_member($pdo);
$uid  = (int) $user['id'];

/* --- Actions (POST + jeton CSRF), puis redirection pour éviter le double envoi --- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        flash_set('error', 'Ta session a expiré. Réessaie.');
        redirect('/dashboard.php');
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'request') {
        $tid = (int) ($_POST['training_id'] ?? 0);
        $stmt = $pdo->prepare('SELECT name FROM trainings WHERE id = ?');
        $stmt->execute([$tid]);
        $training = $stmt->fetch();

        if (!$training) {
            flash_set('error', "Cette formation n'existe pas.");
        } else {
            $stmt = $pdo->prepare('SELECT id FROM purchases WHERE user_id = ? AND training_id = ?');
            $stmt->execute([$uid, $tid]);
            if ($stmt->fetch()) {
                flash_set('info', 'Cette formation est déjà associée à ton compte.');
            } else {
                $pdo->prepare('INSERT INTO purchases (user_id, training_id, status) VALUES (?, ?, \'pending\')')
                    ->execute([$uid, $tid]);
                flash_set('success', 'Demande envoyée pour « ' . $training['name'] . ' ». Nous revenons vers toi rapidement.');
            }
        }
    } elseif ($action === 'cancel') {
        $pid = (int) ($_POST['purchase_id'] ?? 0);
        $del = $pdo->prepare('DELETE FROM purchases WHERE id = ? AND user_id = ? AND status = \'pending\'');
        $del->execute([$pid, $uid]);
        if ($del->rowCount() > 0) {
            flash_set('success', 'Ta demande a été annulée.');
        } else {
            flash_set('error', "Impossible d'annuler cette demande.");
        }
    }

    redirect('/dashboard.php');
}

/* --- Données --- */
$stmt = $pdo->prepare(
    'SELECT p.id, t.name, t.description, p.status, p.purchased_at
     FROM purchases p
     JOIN trainings t ON t.id = p.training_id
     WHERE p.user_id = ?
     ORDER BY p.purchased_at DESC'
);
$stmt->execute([$uid]);
$purchases = $stmt->fetchAll();

$stmt = $pdo->prepare(
    'SELECT id, name, description, price_eur
     FROM trainings
     WHERE id NOT IN (SELECT training_id FROM purchases WHERE user_id = ?)
     ORDER BY name'
);
$stmt->execute([$uid]);
$catalog = $stmt->fetchAll();

/* Commandes de services (hors formations) */
$ordersReady  = orders_ready($pdo);
$openOrders   = 0;
$recentOrders = [];
if ($ordersReady) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND status IN ('pending','in_progress')");
    $stmt->execute([$uid]);
    $openOrders = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare(
        'SELECT id, category, service, title, status, created_at
         FROM orders WHERE user_id = ? ORDER BY created_at DESC, id DESC LIMIT 3'
    );
    $stmt->execute([$uid]);
    $recentOrders = $stmt->fetchAll();
}

$active  = count(array_filter($purchases, fn($p) => $p['status'] === 'paid'));
$pending = count($purchases) - $active;
$first   = explode(' ', trim($user['full_name']))[0];

member_page_start('Mon espace', 'Bonjour ' . $first, 'Retrouve tes commandes, tes formations et le catalogue.', 'dashboard');
?>

    <div class="member-stats">
      <div class="stat-card">
        <span class="stat-value"><?= $active ?></span>
        <span class="stat-label"><?= plural($active, 'Formation active', 'Formations actives') ?></span>
      </div>
      <div class="stat-card">
        <span class="stat-value"><?= $pending ?></span>
        <span class="stat-label"><?= plural($pending, 'Demande de formation', 'Demandes de formation') ?></span>
      </div>
      <?php if ($ordersReady): ?>
        <div class="stat-card">
          <span class="stat-value"><?= $openOrders ?></span>
          <span class="stat-label"><?= plural($openOrders, 'Commande en cours', 'Commandes en cours') ?></span>
        </div>
      <?php endif; ?>
      <div class="stat-card">
        <span class="stat-value stat-value--text"><?= e(fr_date($user['created_at'])) ?></span>
        <span class="stat-label">Membre depuis</span>
      </div>
    </div>

    <?php if ($ordersReady): ?>
      <div class="section-head">
        <h2 class="member-h2">Mes commandes</h2>
        <?php if (!empty($recentOrders)): ?><a class="link-more" href="/orders.php">Tout voir →</a><?php endif; ?>
      </div>
      <?php if (empty($recentOrders)): ?>
        <div class="empty-state">
          <p>Tu n'as pas encore passé de commande de service (design, développement, cybersécurité, flyers…).</p>
          <p><a href="/orders.php?new=1">Passer une commande</a></p>
        </div>
      <?php else: ?>
        <div class="order-list">
          <?php foreach ($recentOrders as $o): ?>
            <a class="order-row" href="/order.php?id=<?= (int) $o['id'] ?>">
              <span class="order-row__ref"><?= e(order_ref($o['id'])) ?></span>
              <span class="order-row__main">
                <span class="order-row__title"><?= e($o['title']) ?></span>
                <span class="order-row__meta"><?= e(order_category_label($o['category'])) ?> · <?= e($o['service']) ?> · <?= e(fr_date($o['created_at'])) ?></span>
              </span>
              <?= order_badge($o['status']) ?>
            </a>
          <?php endforeach; ?>
        </div>
        <p style="margin-top:16px;"><a class="link-more" href="/orders.php?new=1">+ Nouvelle commande</a></p>
      <?php endif; ?>
    <?php endif; ?>

    <h2 class="member-h2">Mes formations</h2>
    <?php if (empty($purchases)): ?>
      <div class="empty-state">
        <p>Tu n'as pas encore de formation associée à ton compte.</p>
        <?php if (!empty($catalog)): ?>
          <p>Choisis-en une dans le catalogue ci-dessous, ou <a href="/formations.html">découvre nos parcours</a>.</p>
        <?php else: ?>
          <p><a href="/formations.html">Découvre nos parcours</a>.</p>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <div class="member-grid">
        <?php foreach ($purchases as $p): $isPaid = $p['status'] === 'paid'; ?>
          <article class="content-card training-card">
            <span class="badge <?= $isPaid ? 'badge--ok' : 'badge--pending' ?>">
              <?= $isPaid ? 'Accès actif' : 'Demande en cours' ?>
            </span>
            <h3><?= e($p['name']) ?></h3>
            <p><?= e($p['description']) ?></p>
            <div class="training-card__foot">
              <span class="training-card__date"><?= $isPaid ? 'Depuis' : 'Demandée' ?> le <?= e(fr_date($p['purchased_at'])) ?></span>
              <?php if (!$isPaid): ?>
                <form method="post" action="/dashboard.php">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="cancel">
                  <input type="hidden" name="purchase_id" value="<?= (int) $p['id'] ?>">
                  <button type="submit" class="btn-outline btn-small">Annuler</button>
                </form>
              <?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($catalog)): ?>
      <h2 class="member-h2">Catalogue</h2>
      <div class="member-grid">
        <?php foreach ($catalog as $t): ?>
          <article class="content-card training-card">
            <span class="badge badge--neutral"><?= e(price_label($t['price_eur'])) ?></span>
            <h3><?= e($t['name']) ?></h3>
            <p><?= e($t['description']) ?></p>
            <div class="training-card__foot">
              <form method="post" action="/dashboard.php">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="request">
                <input type="hidden" name="training_id" value="<?= (int) $t['id'] ?>">
                <button type="submit" class="btn-primary btn-small">Demander l'accès</button>
              </form>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

<?php member_page_end(); ?>
