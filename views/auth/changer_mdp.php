<?php
/**
 * Vue Changer MDP — views/auth/changer_mdp.php
 * Auteur : Gaïus_Ahs (Vital-Ahs)
 */
require_once __DIR__ . '/../../config/connexion.php';
require_once __DIR__ . '/../../config/auth_config.php';
require_once __DIR__ . '/../../models/Utilisateur.php';
require_once __DIR__ . '/../../services/AuthService.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/views/auth/login.php'); exit;
}
$svc = new AuthService(); $erreurs=[]; $erreur=''; $succes='';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $r = $svc->changerMotDePasse((int)$_SESSION['user_id'], $_POST);
    if ($r['succes']) $succes=$r['message'];
    else { $erreurs=$r['erreurs']; $erreur=$r['message']; }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Changer mot de passe — Gestion Mémoires</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/auth.css">
</head>
<body>
<nav class="navbar">
  <a class="navbar-brand" href="#">📚 GéMémoires</a>
  <div class="navbar-links">
    <a href="<?= BASE_URL ?>/views/auth/profil.php">← Mon profil</a>
    <a href="<?= BASE_URL ?>/controllers/AuthController.php?action=logout" class="btn-logout">Déconnexion</a>
  </div>
</nav>
<div class="main-container">
  <div class="page-header"><h1>🔑 Changer mon mot de passe</h1></div>
  <?php if ($succes): ?>
    <div class="alert alert-success"><?= htmlspecialchars($succes) ?></div>
  <?php endif; ?>
  <?php if ($erreur): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
  <?php endif; ?>
  <div class="form-container">
    <form method="POST" action="" novalidate>
      <div class="form-group">
        <label>Ancien mot de passe <span class="req">*</span>
          <span class="toggle-mdp" onclick="toggle('a',this)">👁</span>
        </label>
        <input type="password" id="a" name="ancien_mdp" required>
        <?php if (!empty($erreurs['ancien_mdp'])): ?>
          <span class="form-err"><?= htmlspecialchars($erreurs['ancien_mdp']) ?></span>
        <?php endif; ?>
      </div>
      <div class="form-group">
        <label>Nouveau mot de passe <span class="req">*</span>
          <span class="toggle-mdp" onclick="toggle('n',this)">👁</span>
        </label>
        <input type="password" id="n" name="nouveau_mdp"
               placeholder="Min. 8 caractères dont un chiffre" required>
        <?php if (!empty($erreurs['nouveau_mdp'])): ?>
          <span class="form-err"><?= htmlspecialchars($erreurs['nouveau_mdp']) ?></span>
        <?php endif; ?>
      </div>
      <div class="form-group">
        <label>Confirmer <span class="req">*</span></label>
        <input type="password" name="confirmer_mdp" placeholder="Répéter" required>
        <?php if (!empty($erreurs['confirmer_mdp'])): ?>
          <span class="form-err"><?= htmlspecialchars($erreurs['confirmer_mdp']) ?></span>
        <?php endif; ?>
      </div>
      <div class="form-actions">
        <a href="<?= BASE_URL ?>/views/auth/profil.php" class="btn btn-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary">🔐 Changer</button>
      </div>
    </form>
  </div>
</div>
<script>
function toggle(id,btn){const i=document.getElementById(id);i.type=i.type==='password'?'text':'password';btn.textContent=i.type==='password'?'👁':'🙈';}
</script>
</body>
</html>
