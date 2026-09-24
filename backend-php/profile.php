<?php
// profile.php — espace membre : informations, mot de passe, suppression du compte
require_once __DIR__ . '/includes/member.php';

$user = require_member($pdo);
$uid  = (int) $user['id'];

$errors = ['name' => [], 'password' => [], 'delete' => []];
$nameValue = $user['full_name'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        flash_set('error', 'Ta session a expiré. Réessaie.');
        redirect('/profile.php');
    }

    $action = $_POST['action'] ?? '';

    /* --- Modifier le nom --- */
    if ($action === 'update_name') {
        $nameValue = trim($_POST['full_name'] ?? '');
        $len = mb_strlen($nameValue);
        if ($len < 2 || $len > 150) {
            $errors['name'][] = 'Le nom doit contenir entre 2 et 150 caractères.';
        } elseif (preg_match('/[<>]/', $nameValue)) {
            $errors['name'][] = 'Le nom contient des caractères non autorisés.';
        }

        if (empty($errors['name'])) {
            $pdo->prepare('UPDATE users SET full_name = ? WHERE id = ?')->execute([$nameValue, $uid]);
            $_SESSION['user_name'] = $nameValue;
            flash_set('success', 'Ton nom a été mis à jour.');
            redirect('/profile.php');
        }
    }

    /* --- Changer le mot de passe --- */
    elseif ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['new_password_confirm'] ?? '';

        if (strlen($new) < 8) $errors['password'][] = 'Le nouveau mot de passe doit contenir au moins 8 caractères.';
        if ($new !== $confirm) $errors['password'][] = 'Les deux mots de passe ne correspondent pas.';
        if (empty($errors['password']) && $new === $current) {
            $errors['password'][] = "Le nouveau mot de passe doit être différent de l'actuel.";
        }

        if (empty($errors['password'])) {
            $err = verify_current_password($pdo, $user, $current);
            if ($err !== null) {
                $errors['password'][] = $err;
            } else {
                $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
                    ->execute([password_hash($new, PASSWORD_DEFAULT), $uid]);
                session_regenerate_id(true);
                flash_set('success', 'Ton mot de passe a été modifié.');
                redirect('/profile.php');
            }
        }
    }

    /* --- Supprimer le compte --- */
    elseif ($action === 'delete_account') {
        if (empty($_POST['confirm_delete'])) {
            $errors['delete'][] = 'Coche la case pour confirmer la suppression.';
        } else {
            $err = verify_current_password($pdo, $user, $_POST['password'] ?? '');
            if ($err !== null) {
                $errors['delete'][] = $err;
            } else {
                // Les achats sont supprimés en cascade (clé étrangère ON DELETE CASCADE).
                $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$uid]);
                $_SESSION = [];
                session_destroy();
                redirect('/');
            }
        }
    }
}

function render_errors(array $list): void {
    foreach ($list as $msg) {
        echo '<div class="alert alert--error" role="alert">' . e($msg) . '</div>';
    }
}

member_page_start('Mon profil', 'Mon profil', 'Gère tes informations personnelles et la sécurité de ton compte.', 'profile');
?>

    <div class="profile-grid">

      <form class="form-box member-box" method="post" action="/profile.php">
        <h2 class="member-h2 member-h2--box">Informations</h2>
        <?php render_errors($errors['name']); ?>
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="update_name">
        <div class="form-row">
          <label for="full_name">Nom complet</label>
          <input type="text" id="full_name" name="full_name" required maxlength="150" value="<?= e($nameValue) ?>">
        </div>
        <div class="form-row">
          <label for="email">Adresse email</label>
          <input type="email" id="email" value="<?= e($user['email']) ?>" disabled>
          <p class="form-hint">Pour changer d'adresse, <a href="/contact.html">contacte-nous</a>.</p>
        </div>
        <p class="form-hint form-hint--spaced">Membre depuis le <?= e(fr_date($user['created_at'])) ?>.</p>
        <button type="submit" class="btn-primary">Enregistrer</button>
      </form>

      <form class="form-box member-box" method="post" action="/profile.php">
        <h2 class="member-h2 member-h2--box">Mot de passe</h2>
        <?php render_errors($errors['password']); ?>
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="change_password">
        <div class="form-row">
          <label for="current_password">Mot de passe actuel</label>
          <input type="password" id="current_password" name="current_password" required autocomplete="current-password">
        </div>
        <div class="form-row">
          <label for="new_password">Nouveau mot de passe</label>
          <input type="password" id="new_password" name="new_password" required minlength="8" autocomplete="new-password">
          <p class="form-hint">8 caractères minimum.</p>
        </div>
        <div class="form-row">
          <label for="new_password_confirm">Confirmer le nouveau mot de passe</label>
          <input type="password" id="new_password_confirm" name="new_password_confirm" required minlength="8" autocomplete="new-password">
        </div>
        <button type="submit" class="btn-primary">Changer le mot de passe</button>
      </form>

    </div>

    <form class="form-box member-box danger-zone" method="post" action="/profile.php">
      <h2 class="member-h2 member-h2--box">Supprimer mon compte</h2>
      <p class="form-hint form-hint--spaced">Cette action est définitive : ton compte, tes formations et tes commandes seront effacés.</p>
      <?php render_errors($errors['delete']); ?>
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="delete_account">
      <div class="form-row">
        <label for="delete_password">Mot de passe</label>
        <input type="password" id="delete_password" name="password" required autocomplete="current-password">
      </div>
      <label class="check-row">
        <input type="checkbox" name="confirm_delete" value="1">
        <span>Je comprends que la suppression est irréversible.</span>
      </label>
      <button type="submit" class="btn-danger">Supprimer définitivement mon compte</button>
    </form>

<?php member_page_end(); ?>
