<?php
// register.php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/mailer.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['password_confirm'] ?? '';

    if ($fullName === '') $errors[] = "Le nom est requis.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Adresse email invalide.";
    if (strlen($password) < 8) $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
    if ($password !== $confirm) $errors[] = "Les mots de passe ne correspondent pas.";

    $existingUnverifiedId = null;
    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id, is_verified FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $existing = $stmt->fetch();
        if ($existing && (int) $existing['is_verified'] === 1) {
            $errors[] = "Un compte existe déjà avec cette adresse email.";
        } elseif ($existing) {
            // Compte jamais confirmé (ex: lien expiré) : on relance l'inscription
            // avec un nouveau mot de passe et un nouveau lien plutôt que de bloquer.
            $existingUnverifiedId = $existing['id'];
        }
    }

    if (empty($errors)) {
        $hash    = password_hash($password, PASSWORD_DEFAULT);
        $token   = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + 24 * 60 * 60); // valable 24h

        if ($existingUnverifiedId) {
            $stmt = $pdo->prepare(
                'UPDATE users SET full_name = ?, password_hash = ?, verification_token = ?, verification_token_expires = ? WHERE id = ?'
            );
            $stmt->execute([$fullName, $hash, $token, $expires, $existingUnverifiedId]);
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO users (full_name, email, password_hash, verification_token, verification_token_expires) VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([$fullName, $email, $hash, $token, $expires]);
        }

        $sent = send_verification_email($email, $fullName, $token);
        if (!$sent) {
            $errors[] = "Ton compte a été créé, mais l'email de confirmation n'a pas pu être envoyé. Vérifie la configuration SMTP dans includes/config.php.";
        } else {
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
<title>Créer un compte — Softener Lab</title>
<link rel="stylesheet" href="/assets/main.css">
</head>
<body>

<header id="site-header"></header>

<section class="page-header">
  <div class="wrap">
    <div class="eyebrow-line"><span class="dot"></span> ESPACE MEMBRE</div>
    <h1>Créer un compte</h1>
    <p>Accède à tes formations une fois ton compte confirmé par email.</p>
  </div>
</section>

<section class="form-section">
  <div class="wrap">
    <?php if ($success): ?>
      <div class="form-box">
        <h3>Compte créé !</h3>
        <p>Un email de confirmation vient d'être envoyé à ton adresse. Clique sur le lien reçu pour activer ton compte.</p>
      </div>
    <?php else: ?>
      <?php if (!empty($errors)): ?>
        <div class="form-box" style="margin-bottom:20px; border-color:#c0392b;">
          <?php foreach ($errors as $e): ?>
            <p style="color:#c0392b;"><?= htmlspecialchars($e) ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form class="form-box" method="post" action="register.php">
        <div class="form-row">
          <label for="full_name">Nom complet</label>
          <input type="text" id="full_name" name="full_name" required value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
        </div>
        <div class="form-row">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="form-row">
          <label for="password">Mot de passe</label>
          <input type="password" id="password" name="password" required minlength="8">
        </div>
        <div class="form-row">
          <label for="password_confirm">Confirmer le mot de passe</label>
          <input type="password" id="password_confirm" name="password_confirm" required minlength="8">
        </div>
        <button type="submit" class="btn-primary">Créer mon compte</button>
      </form>
      <p style="margin-top:16px; font-size:0.9rem; color:var(--muted-on-paper);">
        Déjà inscrit ? <a href="/login.php" style="text-decoration:underline;">Se connecter</a>
      </p>
    <?php endif; ?>
  </div>
</section>

<footer id="site-footer"></footer>
<script type="module" src="/assets/main.js"></script>
</body>
</html>
