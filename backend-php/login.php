<?php
// login.php
session_start();
require_once __DIR__ . '/includes/db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        $errors[] = "Email ou mot de passe incorrect.";
    } elseif (!$user['is_verified']) {
        $errors[] = "Confirme d'abord ton adresse email (vérifie ta boîte de réception).";
    } else {
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['user_name']  = $user['full_name'];
        $_SESSION['user_email'] = $user['email'];
        header('Location: /dashboard.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Connexion — Softener Lab</title>
<link rel="stylesheet" href="/assets/main.css">
</head>
<body>

<header id="site-header"></header>

<section class="page-header">
  <div class="wrap">
    <div class="eyebrow-line"><span class="dot"></span> ESPACE MEMBRE</div>
    <h1>Se connecter</h1>
    <p>Accède à tes formations achetées.</p>
  </div>
</section>

<section class="form-section">
  <div class="wrap">
    <?php if (!empty($errors)): ?>
      <div class="form-box" style="margin-bottom:20px; border-color:#c0392b;">
        <?php foreach ($errors as $e): ?>
          <p style="color:#c0392b;"><?= htmlspecialchars($e) ?></p>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form class="form-box" method="post" action="login.php">
      <div class="form-row">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div class="form-row">
        <label for="password">Mot de passe</label>
        <input type="password" id="password" name="password" required>
      </div>
      <button type="submit" class="btn-primary">Se connecter</button>
    </form>
    <p style="margin-top:16px; font-size:0.9rem; color:var(--muted-on-paper);">
      Pas encore de compte ? <a href="/register.php" style="text-decoration:underline;">S'inscrire</a>
    </p>
  </div>
</section>

<footer id="site-footer"></footer>
<script type="module" src="/assets/main.js"></script>
</body>
</html>
