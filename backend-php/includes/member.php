<?php
// includes/member.php
// Briques communes à toutes les pages de l'espace membre (dashboard, profil).

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/csrf.php';

const MEMBER_LOCK_THRESHOLD = 5;   // mêmes seuils que login.php
const MEMBER_LOCK_MINUTES   = 15;

function e($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $to): void {
    header('Location: ' . $to);
    exit;
}

/** Exige une session ouverte et renvoie la ligne `users` à jour. */
function require_member(PDO $pdo): array {
    if (!isset($_SESSION['user_id'])) {
        redirect('/login.php');
    }
    $stmt = $pdo->prepare(
        'SELECT id, full_name, email, password_hash, failed_attempts, locked_until, created_at
         FROM users WHERE id = ?'
    );
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if (!$user) {
        // Compte supprimé entre-temps : on ferme la session.
        session_destroy();
        redirect('/login.php');
    }
    return $user;
}

/**
 * Vérifie le mot de passe actuel pour une action sensible (changement de mot de
 * passe, suppression du compte). Les échecs comptent dans le même compteur que
 * la connexion : 5 erreurs = compte verrouillé 15 minutes.
 * Renvoie un message d'erreur, ou null si le mot de passe est correct.
 */
function verify_current_password(PDO $pdo, array $user, string $password): ?string {
    if ($user['locked_until'] !== null && strtotime($user['locked_until']) > time()) {
        $min = (int) ceil((strtotime($user['locked_until']) - time()) / 60);
        return "Trop de tentatives échouées. Réessaie dans {$min} minute" . ($min > 1 ? 's' : '') . '.';
    }

    if (!password_verify($password, $user['password_hash'])) {
        $attempts = (int) $user['failed_attempts'] + 1;
        if ($attempts >= MEMBER_LOCK_THRESHOLD) {
            $lockUntil = date('Y-m-d H:i:s', time() + MEMBER_LOCK_MINUTES * 60);
            $pdo->prepare('UPDATE users SET failed_attempts = 0, locked_until = ? WHERE id = ?')
                ->execute([$lockUntil, $user['id']]);
            return 'Trop de tentatives échouées. Réessaie dans ' . MEMBER_LOCK_MINUTES . ' minutes.';
        }
        $pdo->prepare('UPDATE users SET failed_attempts = ? WHERE id = ?')
            ->execute([$attempts, $user['id']]);
        return 'Mot de passe actuel incorrect.';
    }

    if ((int) $user['failed_attempts'] > 0) {
        $pdo->prepare('UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE id = ?')
            ->execute([$user['id']]);
    }
    return null;
}

/* --- Messages flash (affichés une seule fois après une redirection) --- */

function flash_set(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function flash_render(): string {
    if (empty($_SESSION['flash'])) return '';
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return '<div class="alert alert--' . e($f['type']) . '" role="status">' . e($f['message']) . '</div>';
}

/* --- Aides d'affichage --- */

function fr_date(?string $datetime): string {
    if (!$datetime) return '';
    $months = ['janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
    $t = strtotime($datetime);
    return date('j', $t) . ' ' . $months[(int) date('n', $t) - 1] . ' ' . date('Y', $t);
}

function plural(int $n, string $one, string $many): string {
    return $n > 1 ? $many : $one;
}

function price_label($price): string {
    $p = (float) $price;
    return $p <= 0 ? 'Gratuit' : number_format($p, 2, ',', ' ') . ' €';
}

/* --- Gabarit de page --- */

function member_page_start(string $title, string $h1, string $lead, string $active): void {
    $tabs = [
        'dashboard' => ['/dashboard.php', 'Tableau de bord'],
        'orders'    => ['/orders.php',    'Mes commandes'],
        'profile'   => ['/profile.php',   'Mon profil'],
    ];
    ?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex">
<title><?= e($title) ?> — Softener Lab</title>
<link rel="stylesheet" href="/assets/main.css">
</head>
<body>

<header id="site-header"></header>

<section class="page-header">
  <div class="wrap">
    <div class="eyebrow-line"><span class="dot"></span> ESPACE MEMBRE</div>
    <h1><?= e($h1) ?></h1>
    <p><?= e($lead) ?></p>
  </div>
</section>

<section class="services is-compact member-area">
  <div class="wrap">
    <nav class="member-tabs" aria-label="Espace membre">
      <?php foreach ($tabs as $key => [$href, $label]): ?>
        <a href="<?= e($href) ?>" class="member-tab<?= $key === $active ? ' is-active' : '' ?>"<?= $key === $active ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
      <?php endforeach; ?>
      <a href="/logout.php" class="member-tab member-tab--logout">Se déconnecter</a>
    </nav>

    <?= flash_render() ?>
<?php
}

function member_page_end(): void {
    ?>
  </div>
</section>

<footer id="site-footer"></footer>
<script type="module" src="/assets/main.js"></script>
</body>
</html>
<?php
}
