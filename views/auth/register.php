<?php
/**
 * Vue Inscription — views/auth/register.php
 * Couche : PRÉSENTATION
 * Auteur : Gaïus_Ahs (Vital-Ahs)
 */
require_once __DIR__ . '/../../config/connexion.php';
require_once __DIR__ . '/../../config/auth_config.php';
require_once __DIR__ . '/../../models/Utilisateur.php';
require_once __DIR__ . '/../../models/Filiere.php';
require_once __DIR__ . '/../../services/AuthService.php';

$svc     = new AuthService();
$erreurs = [];
$erreur  = '';
$succes  = '';
$filieres = Filiere::findAll();

// Grouper par UFR
$parUFR = [];
foreach ($filieres as $f) {
    $cle = ($f->code_ufr ?? '') . ' — ' . ($f->nom_ufr ?? 'UFR');
    $parUFR[$cle][] = $f;
}
ksort($parUFR);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $r = $svc->inscrire($_POST);
    if ($r['succes']) {
        $_SESSION['flash_ok'] = $r['message'];
        header('Location: ' . BASE_URL . '/views/auth/login.php'); exit;
    }
    $erreurs = $r['erreurs'];
    $erreur  = $r['message'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Inscription — Gestion Mémoires UATM</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/auth.css">
</head>
<body>
<div class="auth-container">
  <div class="auth-card auth-card-wide">
    <div class="auth-brand">
      <h2>Créer un compte étudiant</h2>
      <p>UATM GASA FORMATION</p>
    </div>

    <?php if ($erreur): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
    <?php endif; ?>

    <form method="POST" action="" novalidate>
      <div class="form-row">
        <div class="form-group">
          <label>Nom <span class="req">*</span></label>
          <input type="text" name="nom"
                 value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" required>
          <?php if (!empty($erreurs['nom'])): ?>
            <span class="form-err"><?= htmlspecialchars($erreurs['nom']) ?></span>
          <?php endif; ?>
        </div>
        <div class="form-group">
          <label>Prénom <span class="req">*</span></label>
          <input type="text" name="prenom"
                 value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>" required>
          <?php if (!empty($erreurs['prenom'])): ?>
            <span class="form-err"><?= htmlspecialchars($erreurs['prenom']) ?></span>
          <?php endif; ?>
        </div>
      </div>

      <div class="form-group">
        <label>Email <span class="req">*</span></label>
        <input type="email" name="email"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        <?php if (!empty($erreurs['email'])): ?>
          <span class="form-err"><?= htmlspecialchars($erreurs['email']) ?></span>
        <?php endif; ?>
      </div>

      <div class="form-group">
        <label>Filière <span class="req">*</span></label>
        <select name="id_filiere" required>
          <option value="">-- Choisir votre filière --</option>
          <?php foreach ($parUFR as $groupe => $liste): ?>
            <optgroup label="<?= htmlspecialchars($groupe) ?>">
              <?php foreach ($liste as $f): ?>
                <option value="<?= $f->id_filiere ?>"
                  <?= (($_POST['id_filiere'] ?? '') == $f->id_filiere) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($f->nom_filiere) ?> (<?= $f->niveau ?>)
                </option>
              <?php endforeach; ?>
            </optgroup>
          <?php endforeach; ?>
        </select>
        <?php if (!empty($erreurs['id_filiere'])): ?>
          <span class="form-err"><?= htmlspecialchars($erreurs['id_filiere']) ?></span>
        <?php endif; ?>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Mot de passe <span class="req">*</span>
            <span class="toggle-mdp" onclick="toggle('mdp',this)">👁</span>
          </label>
          <input type="password" id="mdp" name="mot_de_passe"
                 placeholder="Min. 8 car. dont un chiffre" required>
          <?php if (!empty($erreurs['mot_de_passe'])): ?>
            <span class="form-err"><?= htmlspecialchars($erreurs['mot_de_passe']) ?></span>
          <?php endif; ?>
        </div>
        <div class="form-group">
          <label>Confirmer <span class="req">*</span></label>
          <input type="password" name="confirmer_mdp" placeholder="Répéter" required>
          <?php if (!empty($erreurs['confirmer_mdp'])): ?>
            <span class="form-err"><?= htmlspecialchars($erreurs['confirmer_mdp']) ?></span>
          <?php endif; ?>
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-full">✅ Créer mon compte</button>
    </form>
    <div class="auth-footer">
      Déjà inscrit ? <a href="<?= BASE_URL ?>/views/auth/login.php">Se connecter</a>
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
