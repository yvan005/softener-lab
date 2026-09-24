<?php
// reset-password.php
require_once __DIR__ . '/includes/db.php';

$token   = $_GET['token'] ?? $_POST['token'] ?? '';
$errors  = [];
$success = false;
$user    = null;

if ($token !== '') {
    $stmt = $pdo->prepare('SELECT id, reset_token_expires FROM users WHERE reset_token = ?');
    $stmt->execute([$token]);
    $user = $stmt->fetch();
}

$isExpired = $user && $user['reset_token_expires'] !== null && strtotime($user['reset_token_expires']) < time();

if ($token === '' || !$user || $isExpired) {
    $invalidLink = true;
} else {
    $invalidLink = false;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['password_confirm'] ?? '';

        if (strlen($password) < 8) $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
        if ($password !== $confirm) $errors[] = "Les mots de passe ne correspondent pas.";

        if (empty($errors)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $upd = $pdo->prepare(
                'UPDATE users SET password_hash = ?, reset_token = NULL, reset_token_expires = NULL,
                 failed_attempts = 0, locked_until = NULL WHERE id = ?'
            );
            $upd->execute([$hash, $user['id']]);
            $success = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Réinitialiser le mot de passe — Softener Lab</title>
<link rel="stylesheet" href="/assets/main.css">
</head>
<body>

<header id="site-header"></header>

<section class="page-header">
  <div class="wrap">
    <div class="eyebrow-line"><span class="dot"></span> ESPACE MEMBRE</div>
    <h1>Nouveau mot de passe</h1>
    <p>Choisis un mot de passe que tu n'utilises nulle part ailleurs.</p>
  </div>
</section>

<section class="form-section">
  <div class="wrap">
    <?php if ($invalidLink): ?>
      <div class="form-box">
        <h3>Lien invalide ou expiré</h3>
        <p>Ce lien de réinitialisation n'est plus valable (il expire au bout d'1 heure ou a déjà été utilisé).</p>
        <p style="margin-top:16px;"><a href="/forgot-password.php" class="btn-primary" style="display:inline-block;">Demander un nouveau lien</a></p>
      </div>
    <?php elseif ($success): ?>
      <div class="form-box">
        <h3>Mot de passe mis à jour</h3>
        <p>Tu peux maintenant te connecter avec ton nouveau mot de passe.</p>
        <p style="margin-top:16px;"><a href="/login.php" class="btn-primary" style="display:inline-block;">Se connecter</a></p>
      </div>
    <?php else: ?>
      <?php if (!empty($errors)): ?>
        <div class="form-box" style="margin-bottom:20px; border-color:#c0392b;">
          <?php foreach ($errors as $e): ?>
            <p style="color:#c0392b;"><?= htmlspecialchars($e) ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form class="form-box" method="post" action="reset-password.php">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
        <div class="form-row">
          <label for="password">Nouveau mot de passe</label>
          <input type="password" id="password" name="password" required minlength="8">
        </div>
        <div class="form-row">
          <label for="password_confirm">Confirmer le mot de passe</label>
          <input type="password" id="password_confirm" name="password_confirm" required minlength="8">
        </div>
        <button type="submit" class="btn-primary">Réinitialiser le mot de passe</button>
      </form>
    <?php endif; ?>
  </div>
</section>

<footer id="site-footer"></footer>
<script type="module" src="/assets/main.js"></script>
</body>
</html>
