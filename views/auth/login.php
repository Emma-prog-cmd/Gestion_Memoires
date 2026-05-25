<?php
/**
 * Vue Connexion — views/auth/login.php
 * Couche : PRÉSENTATION
 * Auteur : Gaïus_Ahs (Vital-Ahs)
 */
require_once __DIR__ . '/../../config/connexion.php';
require_once __DIR__ . '/../../config/auth_config.php';
require_once __DIR__ . '/../../models/Utilisateur.php';
require_once __DIR__ . '/../../services/AuthService.php';

$svc    = new AuthService();
$erreur = '';
$succes = $_SESSION['flash_ok'] ?? '';
unset($_SESSION['flash_ok']);

// Déjà connecté → rediriger
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['user_role'];
    if ($role === ROLE_PROFESSEUR)
        header('Location: ' . BASE_URL . '/views/professeur/validation_memoire.php');
    elseif ($role === ROLE_DIRECTEUR_ETUDE)
        header('Location: ' . BASE_URL . '/views/de/upload_anciens_memoires.php');
    elseif ($role === ROLE_ADMINISTRATEUR)
        header('Location: ' . BASE_URL . '/views/admin/utilisateurs.php');
    else
        header('Location: ' . BASE_URL . '/views/auth/profil.php');
    exit;
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $r = $svc->connecter($_POST['email'] ?? '', $_POST['mot_de_passe'] ?? '');
    if ($r['succes']) {
        $role = $_SESSION['user_role'];
        if ($role === ROLE_PROFESSEUR)
            header('Location: ' . BASE_URL . '/views/professeur/validation_memoire.php');
        elseif ($role === ROLE_DIRECTEUR_ETUDE)
            header('Location: ' . BASE_URL . '/views/de/upload_anciens_memoires.php');
        elseif ($role === ROLE_ADMINISTRATEUR)
            header('Location: ' . BASE_URL . '/views/admin/utilisateurs.php');
        else
            header('Location: ' . BASE_URL . '/views/auth/profil.php');
        exit;
    }
    $erreur = $r['message'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Connexion — Gestion Mémoires UATM</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/auth.css">
</head>
<body>
<div class="auth-container">
  <div class="auth-card">
    <div class="auth-brand">
      <span class="auth-logo">📚</span>
      <h1>GéMémoires</h1>
      <p>UATM GASA FORMATION</p>
    </div>

    <?php if ($erreur): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
    <?php endif; ?>
    <?php if ($succes): ?>
      <div class="alert alert-success"><?= htmlspecialchars($succes) ?></div>
    <?php endif; ?>

    <form method="POST" action="" novalidate>
      <div class="form-group">
        <label for="email">Adresse email</label>
        <input type="email" id="email" name="email"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
               placeholder="votre@email.com" required autofocus>
      </div>
      <div class="form-group">
        <label for="mot_de_passe">
          Mot de passe
          <span class="toggle-mdp" onclick="toggle('mot_de_passe',this)">👁</span>
        </label>
        <input type="password" id="mot_de_passe" name="mot_de_passe"
               placeholder="••••••••" required>
      </div>
      <button type="submit" class="btn btn-primary btn-full">🔐 Se connecter</button>
    </form>

    <div class="auth-footer">
      <p>Pas encore de compte ?
        <a href="<?= BASE_URL ?>/views/auth/register.php">Créer un compte étudiant</a>
      </p>
    </div>
  </div>
</div>
<script>
function toggle(id,btn){
  const i=document.getElementById(id);
  i.type=i.type==='password'?'text':'password';
  btn.textContent=i.type==='password'?'👁':'🙈';
}
</script>
</body>
</html>
