<?php
/**
 * Vue Modifier un compte — views/admin/modifier_compte.php
 * Auteur : Gaïus_Ahs (Vital-Ahs)
 */
require_once __DIR__ . '/../../config/connexion.php';
require_once __DIR__ . '/../../config/auth_config.php';
require_once __DIR__ . '/../../models/Utilisateur.php';
require_once __DIR__ . '/../../models/Filiere.php';
require_once __DIR__ . '/../../services/CompteService.php';

if (!isset($_SESSION['user_id'])||$_SESSION['user_role']!==ROLE_ADMINISTRATEUR) {
    header('Location: '.BASE_URL.'/views/auth/login.php'); exit;
}

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$utilisateur = Utilisateur::findById($id);
if (!$utilisateur) {
    $_SESSION['flash']=['succes'=>false,'message'=>'Utilisateur introuvable.'];
    header('Location: '.BASE_URL.'/views/admin/utilisateurs.php'); exit;
}

$svc     = new CompteService();
$erreurs = []; $erreur = ''; $succes = '';
$filieres = Filiere::findAll();
$parUFR  = [];
foreach ($filieres as $f) $parUFR[$f->nom_ufr ?? 'UFR'][] = $f;

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $r = $svc->modifierCompte($id, $_POST);
    if ($r['succes']) {
        $_SESSION['flash']=$r;
        header('Location: '.BASE_URL.'/views/admin/utilisateurs.php'); exit;
    }
    $erreurs=$r['erreurs']; $erreur=$r['message'];
    $utilisateur = Utilisateur::findById($id);
}

$roles=[ROLE_ETUDIANT_DIPLOME=>'Étudiant diplômé',ROLE_ETUDIANT_CONSULTANT=>'Étudiant consultant',
        ROLE_PROFESSEUR=>'Professeur',ROLE_DIRECTEUR_ETUDE=>'Directeur études',ROLE_ADMINISTRATEUR=>'Administrateur'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Modifier le compte — UATM</title>
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
  <div class="page-header">
    <h1>✏️ Modifier le compte de <?= htmlspecialchars($utilisateur->getNomComplet()) ?></h1>
  </div>
  <?php if($erreur): ?><div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div><?php endif; ?>

  <div class="form-container">
    <form method="POST" action="" novalidate>
      <input type="hidden" name="id" value="<?= $id ?>">
      <div class="form-row">
        <div class="form-group">
          <label>Nom <span class="req">*</span></label>
          <input type="text" name="nom" value="<?= htmlspecialchars($_POST['nom']??$utilisateur->nom) ?>" required>
          <?php if(!empty($erreurs['nom'])): ?><span class="form-err"><?= htmlspecialchars($erreurs['nom']) ?></span><?php endif; ?>
        </div>
        <div class="form-group">
          <label>Prénom <span class="req">*</span></label>
          <input type="text" name="prenom" value="<?= htmlspecialchars($_POST['prenom']??$utilisateur->prenom) ?>" required>
        </div>
      </div>
      <div class="form-group">
        <label>Email <span class="req">*</span></label>
        <input type="email" name="email" value="<?= htmlspecialchars($_POST['email']??$utilisateur->email) ?>" required>
        <?php if(!empty($erreurs['email'])): ?><span class="form-err"><?= htmlspecialchars($erreurs['email']) ?></span><?php endif; ?>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Rôle</label>
          <select name="role">
            <?php foreach($roles as $val=>$lbl): ?>
              <option value="<?= $val ?>" <?= (($_POST['role']??$utilisateur->role)===$val)?'selected':'' ?>><?= $lbl ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Filière</label>
          <select name="id_filiere">
            <option value="">-- Aucune --</option>
            <?php foreach($parUFR as $ufr=>$liste): ?>
              <optgroup label="<?= htmlspecialchars($ufr) ?>">
                <?php foreach($liste as $f): ?>
                  <option value="<?= $f->id_filiere ?>" <?= ($utilisateur->id_filiere==$f->id_filiere)?'selected':'' ?>>
                    <?= htmlspecialchars($f->nom_filiere) ?> (<?= $f->niveau ?>)
                  </option>
                <?php endforeach; ?>
              </optgroup>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <details class="section-details">
        <summary>🔑 Réinitialiser le mot de passe (optionnel)</summary>
        <div class="form-row" style="margin-top:12px">
          <div class="form-group">
            <label>Nouveau MDP <span class="toggle-mdp" onclick="toggle('np',this)">👁</span></label>
            <input type="password" id="np" name="nouveau_mdp" placeholder="Laisser vide = inchangé">
          </div>
          <div class="form-group">
            <label>Confirmer</label>
            <input type="password" name="confirmer_mdp" placeholder="Répéter">
          </div>
        </div>
      </details>
      <div class="form-actions">
        <a href="<?= BASE_URL ?>/views/admin/utilisateurs.php" class="btn btn-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
      </div>
    </form>
  </div>
</div>
<script>
function toggle(id,btn){const i=document.getElementById(id);i.type=i.type==='password'?'text':'password';btn.textContent=i.type==='password'?'👁':'🙈';}
</script>
</body>
</html>
