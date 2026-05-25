<?php
/**
 * Vue Créer un compte — views/admin/creer_compte.php
 * Auteur : Gaïus_Ahs (Vital-Ahs)
 */
require_once __DIR__ . '/../../config/connexion.php';
require_once __DIR__ . '/../../config/auth_config.php';
require_once __DIR__ . '/../../models/Utilisateur.php';
require_once __DIR__ . '/../../models/Filiere.php';
require_once __DIR__ . '/../../services/CompteService.php';

if (!isset($_SESSION['user_id'])||$_SESSION['user_role']!=='administrateur') {
    header('Location: '.BASE_URL.'/views/auth/login.php'); exit;
}
$svc     = new CompteService();
$erreurs = []; $erreur = ''; $succes = '';
$filieres = Filiere::findAll();
$parUFR  = [];
foreach ($filieres as $f) $parUFR[$f->nom_ufr ?? 'UFR'][] = $f;

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $r = $svc->creerCompte($_POST);
    if ($r['succes']) {
        $_SESSION['flash'] = $r;
        header('Location: '.BASE_URL.'/views/admin/utilisateurs.php'); exit;
    }
    $erreurs=$r['erreurs']; $erreur=$r['message'];
}

$roles = [
    ROLE_ETUDIANT_DIPLOME         => 'Étudiant',
    ROLE_PROFESSEUR       => 'Professeur / Encadreur',
    ROLE_DIRECTEUR_ETUDE => 'Directeur des études',
    'administrateur'   => 'Administrateur',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Créer un compte — UATM</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/auth.css">
</head>
<body>
<nav class="navbar">
  <a class="navbar-brand" href="#">📚 GéMémoires — Admin</a>
  <div class="navbar-links">
    <a href="<?= BASE_URL ?>/views/admin/utilisateurs.php">← Retour</a>
    <a href="<?= BASE_URL ?>/controllers/AuthController.php?action=logout" class="btn-logout">Déconnexion</a>
  </div>
</nav>
<div class="main-container">
  <div class="page-header"><h1>➕ Créer un compte</h1></div>
  <?php if ($erreur): ?><div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div><?php endif; ?>

  <div class="form-container">
    <form method="POST" action="" novalidate>
      <div class="form-row">
        <div class="form-group">
          <label>Nom <span class="req">*</span></label>
          <input type="text" name="nom" value="<?= htmlspecialchars($_POST['nom']??'') ?>" required>
          <?php if(!empty($erreurs['nom'])): ?><span class="form-err"><?= htmlspecialchars($erreurs['nom']) ?></span><?php endif; ?>
        </div>
        <div class="form-group">
          <label>Prénom <span class="req">*</span></label>
          <input type="text" name="prenom" value="<?= htmlspecialchars($_POST['prenom']??'') ?>" required>
        </div>
      </div>
      <div class="form-group">
        <label>Email <span class="req">*</span></label>
        <input type="email" name="email" value="<?= htmlspecialchars($_POST['email']??'') ?>" required>
        <?php if(!empty($erreurs['email'])): ?><span class="form-err"><?= htmlspecialchars($erreurs['email']) ?></span><?php endif; ?>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Rôle <span class="req">*</span></label>
          <select name="role" required onchange="toggleFiliere(this.value)">
            <option value="">-- Choisir --</option>
            <?php foreach($roles as $val=>$lbl): ?>
              <option value="<?= $val ?>" <?= (($_POST['role']??'')===$val)?'selected':'' ?>><?= $lbl ?></option>
            <?php endforeach; ?>
          </select>
          <?php if(!empty($erreurs['role'])): ?><span class="form-err"><?= htmlspecialchars($erreurs['role']) ?></span><?php endif; ?>
        </div>
        <div class="form-group" id="gFiliere">
          <label>Filière (si étudiant)</label>
          <select name="id_filiere">
            <option value="">-- Aucune --</option>
            <?php foreach($parUFR as $ufr=>$liste): ?>
              <optgroup label="<?= htmlspecialchars($ufr) ?>">
                <?php foreach($liste as $f): ?>
                  <option value="<?= $f->id_filiere ?>" <?= (($_POST['id_filiere']??'')==$f->id_filiere)?'selected':'' ?>>
                    <?= htmlspecialchars($f->nom_filiere) ?> (<?= $f->niveau ?>)
                  </option>
                <?php endforeach; ?>
              </optgroup>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Mot de passe <span class="req">*</span>
            <span class="toggle-mdp" onclick="toggle('p1',this)">👁</span>
          </label>
          <input type="password" id="p1" name="mot_de_passe" placeholder="Min. 8 car." required>
          <?php if(!empty($erreurs['mot_de_passe'])): ?><span class="form-err"><?= htmlspecialchars($erreurs['mot_de_passe']) ?></span><?php endif; ?>
        </div>
        <div class="form-group">
          <label>Confirmer <span class="req">*</span></label>
          <input type="password" name="confirmer_mdp" placeholder="Répéter" required>
          <?php if(!empty($erreurs['confirmer_mdp'])): ?><span class="form-err"><?= htmlspecialchars($erreurs['confirmer_mdp']) ?></span><?php endif; ?>
        </div>
      </div>
      <div class="form-actions">
        <a href="<?= BASE_URL ?>/views/admin/utilisateurs.php" class="btn btn-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary">✅ Créer le compte</button>
      </div>
    </form>
  </div>
</div>
<script>
function toggle(id,btn){const i=document.getElementById(id);i.type=i.type==='password'?'text':'password';btn.textContent=i.type==='password'?'👁':'🙈';}
function toggleFiliere(role){document.getElementById('gFiliere').style.opacity=role==='<?= ROLE_ETUDIANT_DIPLOME ?>'?'1':'0.4';}
toggleFiliere(document.querySelector('[name=role]')?.value||'');
</script>
</body>
</html>
