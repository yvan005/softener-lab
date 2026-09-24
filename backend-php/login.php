<?php
// login.php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/db.php';

$errors = [];
$LOCK_THRESHOLD = 5;      // tentatives échouées avant verrouillage
$LOCK_MINUTES   = 15;     // durée du verrouillage

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    $isLocked = $user && $user['locked_until'] !== null && strtotime($user['locked_until']) > time();

    if ($isLocked) {
        $minutesLeft = (int) ceil((strtotime($user['locked_until']) - time()) / 60);
        $errors[] = "Trop de tentatives échouées. Réessaie dans {$minutesLeft} minute" . ($minutesLeft > 1 ? 's' : '') . ".";
    } elseif (!$user || !password_verify($password, $user['password_hash'])) {
        if ($user) {
            $attempts = (int) $user['failed_attempts'] + 1;
            if ($attempts >= $LOCK_THRESHOLD) {
                $lockUntil = date('Y-m-d H:i:s', time() + $LOCK_MINUTES * 60);
                $upd = $pdo->prepare('UPDATE users SET failed_attempts = 0, locked_until = ? WHERE id = ?');
                $upd->execute([$lockUntil, $user['id']]);
                $errors[] = "Trop de tentatives échouées. Réessaie dans {$LOCK_MINUTES} minutes.";
            } else {
                $upd = $pdo->prepare('UPDATE users SET failed_attempts = ? WHERE id = ?');
                $upd->execute([$attempts, $user['id']]);
                $errors[] = "Email ou mot de passe incorrect.";
            }
        } else {
            $errors[] = "Email ou mot de passe incorrect.";
        }
    } elseif (!$user['is_verified']) {
        $errors[] = "Confirme d'abord ton adresse email (vérifie ta boîte de réception).";
    } else {
        $reset = $pdo->prepare('UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE id = ?');
        $reset->execute([$user['id']]);

        // Régénère l'identifiant de session après authentification pour éviter
        // toute fixation de session (un ID de session obtenu avant connexion
        // ne doit jamais devenir un ID de session authentifié).
        session_regenerate_id(true);

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
