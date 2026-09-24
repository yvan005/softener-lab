<?php
// admin.php — administration : toutes les commandes de services
require_once __DIR__ . '/includes/admin.php';

$admin = require_admin($pdo);

admin_page_start('Administration', 'Commandes', 'Toutes les commandes de services des membres.', 'orders');

if (!orders_ready($pdo)) {
    echo orders_unavailable_notice();
    member_page_end();
    exit;
}

/* --- Compteurs par statut --- */
$counts = ['pending' => 0, 'in_progress' => 0, 'delivered' => 0, 'cancelled' => 0];
foreach ($pdo->query('SELECT status, COUNT(*) AS n FROM orders GROUP BY status')->fetchAll() as $r) {
    if (isset($counts[$r['status']])) $counts[$r['status']] = (int) $r['n'];
}

$filters = [
    'open'        => ['Ouvertes', ['pending', 'in_progress'], $counts['pending'] + $counts['in_progress']],
    'pending'     => ['Reçues',   ['pending'],                $counts['pending']],
    'in_progress' => ['En cours', ['in_progress'],            $counts['in_progress']],
    'delivered'   => ['Livrées',  ['delivered'],              $counts['delivered']],
    'cancelled'   => ['Annulées', ['cancelled'],              $counts['cancelled']],
    'all'         => ['Toutes',   null,                       array_sum($counts)],
];
$filter = $_GET['status'] ?? 'open';
if (!isset($filters[$filter])) $filter = 'open';
$q = trim((string) ($_GET['q'] ?? ''));

/* --- Requête --- */
$where = [];
$params = [];
if ($filters[$filter][1] !== null) {
    $where[] = 'o.status IN (' . implode(',', array_fill(0, count($filters[$filter][1]), '?')) . ')';
    $params = array_merge($params, $filters[$filter][1]);
}
if ($q !== '') {
    if (preg_match('/^cmd-?0*(\d+)$/i', $q, $m)) {
        $where[] = 'o.id = ?';
        $params[] = (int) $m[1];
    } else {
        $like = '%' . $q . '%';
        $where[] = '(o.title LIKE ? OR o.service LIKE ? OR u.full_name LIKE ? OR u.email LIKE ?)';
        array_push($params, $like, $like, $like, $like);
    }
}

$sql = 'SELECT o.id, o.category, o.service, o.title, o.status, o.created_at, u.full_name, u.email
        FROM orders o JOIN users u ON u.id = o.user_id'
     . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
     . " ORDER BY CASE o.status WHEN 'pending' THEN 0 WHEN 'in_progress' THEN 1 ELSE 2 END, o.created_at DESC, o.id DESC
        LIMIT 200";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();
?>

    <div class="member-stats">
      <div class="stat-card"><span class="stat-value"><?= $counts['pending'] ?></span><span class="stat-label"><?= plural($counts['pending'], 'Commande reçue', 'Commandes reçues') ?></span></div>
      <div class="stat-card"><span class="stat-value"><?= $counts['in_progress'] ?></span><span class="stat-label">En cours</span></div>
      <div class="stat-card"><span class="stat-value"><?= $counts['delivered'] ?></span><span class="stat-label"><?= plural($counts['delivered'], 'Livrée', 'Livrées') ?></span></div>
    </div>

    <div class="filter-chips" role="navigation" aria-label="Filtrer les commandes">
      <?php foreach ($filters as $key => [$label, , $n]): ?>
        <a href="/admin.php?status=<?= e($key) ?><?= $q !== '' ? '&amp;q=' . e(urlencode($q)) : '' ?>" class="chip<?= $key === $filter ? ' is-active' : '' ?>"><?= e($label) ?> (<?= (int) $n ?>)</a>
      <?php endforeach; ?>
    </div>

    <form class="admin-search" method="get" action="/admin.php">
      <input type="hidden" name="status" value="<?= e($filter) ?>">
      <input type="search" name="q" value="<?= e($q) ?>" placeholder="Rechercher : membre, email, titre, service, CMD-0001…" aria-label="Rechercher une commande">
      <button type="submit" class="btn-outline btn-small">Rechercher</button>
    </form>

    <?php if (empty($rows)): ?>
      <div class="empty-state"><p>Aucune commande ne correspond.</p></div>
    <?php else: ?>
      <div class="order-list">
        <?php foreach ($rows as $o): ?>
          <a class="order-row" href="/admin-order.php?id=<?= (int) $o['id'] ?>">
            <span class="order-row__ref"><?= e(order_ref($o['id'])) ?></span>
            <span class="order-row__main">
              <span class="order-row__title"><?= e($o['title']) ?></span>
              <span class="order-row__meta"><?= e($o['full_name']) ?> · <?= e(order_category_label($o['category'])) ?> · <?= e($o['service']) ?> · <?= e(fr_date($o['created_at'])) ?></span>
            </span>
            <?= order_badge($o['status']) ?>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

<?php member_page_end(); ?>
