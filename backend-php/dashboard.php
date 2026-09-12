<?php
// dashboard.php
session_start();
require_once __DIR__ . '/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

$stmt = $pdo->prepare(
    'SELECT t.name, t.description, p.status, p.purchased_at
     FROM purchases p
     JOIN trainings t ON t.id = p.training_id
     WHERE p.user_id = ?
     ORDER BY p.purchased_at DESC'
);
$stmt->execute([$_SESSION['user_id']]);
$purchases = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mon espace — Softener Lab</title>
<link rel="stylesheet" href="/assets/main.css">
</head>
<body>

<header id="site-header"></header>

<section class="page-header">
  <div class="wrap">
    <div class="eyebrow-line"><span class="dot"></span> ESPACE MEMBRE</div>
    <h1>Bonjour <?= htmlspecialchars($_SESSION['user_name']) ?></h1>
    <p>Voici les formations associées à ton compte. <a href="/logout.php" style="text-decoration:underline; color:var(--green);">Se déconnecter</a></p>
  </div>
</section>

<section class="services">
  <div class="wrap">
    <?php if (empty($purchases)): ?>
      <p style="color:var(--muted-on-paper);">Tu n'as pas encore de formation associée à ton compte.</p>
    <?php else: ?>
      <div class="content-grid">
        <?php foreach ($purchases as $p): ?>
          <div class="content-card">
            <h3><?= htmlspecialchars($p['name']) ?></h3>
            <p><?= htmlspecialchars($p['description']) ?></p>
            <p style="margin-top:10px; font-size:0.85rem;">
              Statut : <?= $p['status'] === 'paid' ? 'Accès actif' : 'En attente de paiement' ?>
            </p>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<footer id="site-footer"></footer>
<script type="module" src="/assets/main.js"></script>
</body>
</html>
