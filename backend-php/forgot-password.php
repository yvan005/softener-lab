<?php
// forgot-password.php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/mailer.php';

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Adresse email invalide.";
    } else {
        $stmt = $pdo->prepare('SELECT id, full_name FROM users WHERE email = ? AND is_verified = 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // On envoie l'email seulement si le compte existe, mais on affiche le
        // même message de succès dans tous les cas : ça évite de révéler à un
        // visiteur si une adresse email a un compte chez nous ou non.
        if ($user) {
            $token   = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 60 * 60); // valable 1h

            $upd = $pdo->prepare('UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?');
            $upd->execute([$token, $expires, $user['id']]);

            send_password_reset_email($email, $user['full_name'], $token);
        }

        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mot de passe oublié — Softener Lab</title>
<link rel="stylesheet" href="/assets/main.css">
</head>
<body>

<header id="site-header"></header>

<section class="page-header">
  <div class="wrap">
    <div class="eyebrow-line"><span class="dot"></span> ESPACE MEMBRE</div>
    <h1>Mot de passe oublié</h1>
    <p>Indique ton adresse email, on t'envoie un lien pour en choisir un nouveau.</p>
  </div>
</section>

<section class="form-section">
  <div class="wrap">
    <?php if ($success): ?>
      <div class="form-box">
        <h3>Email envoyé</h3>
        <p>Si un compte existe avec cette adresse, un lien de réinitialisation vient d'être envoyé. Il est valable 1 heure.</p>
        <p style="margin-top:16px;"><a href="/login.php" style="text-decoration:underline;">Retour à la connexion</a></p>
      </div>
    <?php else: ?>
      <?php if (!empty($errors)): ?>
        <div class="form-box" style="margin-bottom:20px; border-color:#c0392b;">
          <?php foreach ($errors as $e): ?>
            <p style="color:#c0392b;"><?= htmlspecialchars($e) ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form class="form-box" method="post" action="forgot-password.php">
        <div class="form-row">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <button type="submit" class="btn-primary">Envoyer le lien</button>
      </form>
      <p style="margin-top:16px; font-size:0.9rem; color:var(--muted-on-paper);">
        <a href="/login.php" style="text-decoration:underline;">Retour à la connexion</a>
      </p>
    <?php endif; ?>
  </div>
</section>

<footer id="site-footer"></footer>
<script type="module" src="/assets/main.js"></script>
</body>
</html>
