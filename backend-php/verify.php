<?php
// verify.php
require_once __DIR__ . '/includes/db.php';

$token = $_GET['token'] ?? '';
$message = '';

if ($token === '') {
    $message = "Lien invalide.";
} else {
    $stmt = $pdo->prepare('SELECT id FROM users WHERE verification_token = ? AND is_verified = 0');
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        $update = $pdo->prepare('UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = ?');
        $update->execute([$user['id']]);
        $message = "Ton compte est confirmé ! Tu peux maintenant te connecter.";
    } else {
        $message = "Ce lien de confirmation est invalide ou a déjà été utilisé.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Confirmation — Softener Lab</title>
<link rel="stylesheet" href="/assets/main.css">
</head>
<body>
<header id="site-header"></header>
<section class="form-section">
  <div class="wrap">
    <div class="form-box">
      <h3>Confirmation de compte</h3>
      <p><?= htmlspecialchars($message) ?></p>
      <p style="margin-top:16px;"><a href="/login.php" class="btn-primary" style="display:inline-block;">Se connecter</a></p>
    </div>
  </div>
</section>
<footer id="site-footer"></footer>
<script type="module" src="/assets/main.js"></script>
</body>
</html>
