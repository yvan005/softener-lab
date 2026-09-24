<?php
// orders.php — espace membre : mes commandes de services + nouvelle commande
require_once __DIR__ . '/includes/orders.php';
require_once __DIR__ . '/includes/mailer.php';

$user = require_member($pdo);
$uid  = (int) $user['id'];
$ready = orders_ready($pdo);

$errors = [];
$form = ['service' => '', 'title' => '', 'brief' => '', 'deadline' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        flash_set('error', 'Ta session a expiré. Réessaie.');
        redirect('/orders.php');
    }
    if (!$ready) {
        flash_set('error', 'Le suivi des commandes est momentanément indisponible.');
        redirect('/orders.php');
    }

    $form = [
        'service'  => (string) ($_POST['service'] ?? ''),
        'title'    => trim((string) ($_POST['title'] ?? '')),
        'brief'    => trim((string) ($_POST['brief'] ?? '')),
        'deadline' => trim((string) ($_POST['deadline'] ?? '')),
    ];

    $found = order_find_service($form['service']);
    if (!$found) $errors[] = 'Choisis un service dans la liste.';

    $tl = mb_strlen($form['title']);
    if ($tl < 3 || $tl > 150) $errors[] = 'Le titre doit contenir entre 3 et 150 caractères.';

    $bl = mb_strlen($form['brief']);
    if ($bl < 10 || $bl > 3000) $errors[] = 'La description doit contenir entre 10 et 3000 caractères.';

    $deadline = null;
    if ($form['deadline'] !== '') {
        $ok = preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $form['deadline'], $m)
              && checkdate((int) $m[2], (int) $m[3], (int) $m[1])
              && $form['deadline'] >= date('Y-m-d');
        if (!$ok) $errors[] = "L'échéance souhaitée doit être une date valide, aujourd'hui ou plus tard.";
        else $deadline = $form['deadline'];
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND status IN ('pending','in_progress')");
        $stmt->execute([$uid]);
        if ((int) $stmt->fetchColumn() >= OPEN_ORDER_LIMIT) {
            $errors[] = 'Tu as déjà ' . OPEN_ORDER_LIMIT . ' commandes ouvertes. Attends leur avancement avant d\'en créer une nouvelle, ou contacte-nous.';
        }
    }

    if (empty($errors)) {
        [$category, $service] = $found;
        $pdo->prepare(
            "INSERT INTO orders (user_id, category, service, title, brief, deadline, status)
             VALUES (?, ?, ?, ?, ?, ?, 'pending')"
        )->execute([$uid, $category, $service, $form['title'], $form['brief'], $deadline]);
        $id = (int) $pdo->lastInsertId();
        $ref = order_ref($id);
        // Prévient l'équipe ; un échec d'envoi ne doit jamais empêcher l'enregistrement de la commande.
        notify_admin_new_order($user['full_name'], $user['email'], $id, $ref, $service, $form['title'], $form['brief'], $deadline);
        flash_set('success', 'Commande ' . $ref . ' enregistrée. Nous revenons vers toi rapidement.');
        redirect('/order.php?id=' . $id);
    }
}

/* --- Liste + filtre --- */
$allOrders = [];
if ($ready) {
    $stmt = $pdo->prepare(
        'SELECT id, category, service, title, status, created_at
         FROM orders WHERE user_id = ? ORDER BY created_at DESC, id DESC'
    );
    $stmt->execute([$uid]);
    $allOrders = $stmt->fetchAll();
}

$filters = ['all' => 'Toutes', 'open' => 'En cours', 'delivered' => 'Livrées', 'cancelled' => 'Annulées'];
$filter = $_GET['status'] ?? 'all';
if (!isset($filters[$filter])) $filter = 'all';

$orders = array_values(array_filter($allOrders, function ($o) use ($filter) {
    if ($filter === 'open') return in_array($o['status'], ['pending', 'in_progress'], true);
    if ($filter === 'delivered') return $o['status'] === 'delivered';
    if ($filter === 'cancelled') return $o['status'] === 'cancelled';
    return true;
}));

member_page_start('Mes commandes', 'Mes commandes', 'Passe une commande de service et suis son avancement.', 'orders');

if (!$ready) {
    echo orders_unavailable_notice();
    member_page_end();
    exit;
}
?>

    <details class="order-new"<?= (!empty($errors) || isset($_GET['new']) || empty($allOrders)) ? ' open' : '' ?>>
      <summary class="btn-primary">+ Nouvelle commande</summary>

      <form class="form-box member-box" method="post" action="/orders.php">
        <?php foreach ($errors as $err): ?>
          <div class="alert alert--error" role="alert"><?= e($err) ?></div>
        <?php endforeach; ?>
        <?= csrf_field() ?>

        <div class="form-row">
          <label for="service">Service</label>
          <select id="service" name="service" required>
            <option value="">— Choisir un service —</option>
            <?php foreach (order_catalog() as $catKey => $cat): ?>
              <optgroup label="<?= e($cat['label']) ?>">
                <?php foreach ($cat['services'] as $srv): $val = $catKey . '|' . $srv; ?>
                  <option value="<?= e($val) ?>"<?= $form['service'] === $val ? ' selected' : '' ?>><?= e($srv) ?></option>
                <?php endforeach; ?>
              </optgroup>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-row">
          <label for="title">Titre de la commande</label>
          <input type="text" id="title" name="title" required minlength="3" maxlength="150" placeholder="Ex : Logo et charte graphique pour mon restaurant" value="<?= e($form['title']) ?>">
        </div>
        <div class="form-row">
          <label for="brief">Décris ton besoin</label>
          <textarea id="brief" name="brief" required minlength="10" maxlength="3000" placeholder="Contexte, objectifs, public visé, références, formats souhaités…"><?= e($form['brief']) ?></textarea>
        </div>
        <div class="form-row">
          <label for="deadline">Échéance souhaitée <span class="form-hint-inline">(facultatif)</span></label>
          <input type="date" id="deadline" name="deadline" min="<?= e(date('Y-m-d')) ?>" value="<?= e($form['deadline']) ?>">
        </div>
        <button type="submit" class="btn-primary">Envoyer la commande</button>
      </form>
    </details>

    <?php if (!empty($allOrders)): ?>
      <div class="filter-chips" role="navigation" aria-label="Filtrer les commandes">
        <?php foreach ($filters as $key => $label): ?>
          <a href="/orders.php<?= $key === 'all' ? '' : '?status=' . e($key) ?>" class="chip<?= $key === $filter ? ' is-active' : '' ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
      </div>

      <?php if (empty($orders)): ?>
        <div class="empty-state"><p>Aucune commande dans cette catégorie.</p></div>
      <?php else: ?>
        <div class="order-list">
          <?php foreach ($orders as $o): ?>
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
      <?php endif; ?>
    <?php endif; ?>

<?php member_page_end(); ?>
