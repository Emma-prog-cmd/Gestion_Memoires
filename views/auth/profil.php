<?php
/**
 * Vue Profil — views/auth/profil.php
 * Couche : PRÉSENTATION
 * Auteur : Gaïus_Ahs (Vital-Ahs)
 */
require_once __DIR__ . '/../../config/connexion.php';
require_once __DIR__ . '/../../config/auth_config.php';
require_once __DIR__ . '/../../models/Utilisateur.php';
require_once __DIR__ . '/../../models/Filiere.php';
require_once __DIR__ . '/../../services/AuthService.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/views/auth/login.php'); exit;
}
$svc         = new AuthService();
$erreurs     = [];
$erreur      = '';
$succes      = '';
$utilisateur = Utilisateur::findById((int)$_SESSION['user_id']);
$filieres    = Filiere::findAll();
$parUFR      = [];
foreach ($filieres as $f) {
    $parUFR[$f->nom_ufr ?? 'UFR'][] = $f;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $r = $svc->modifierProfil((int)$_SESSION['user_id'], $_POST);
    if ($r['succes']) {
        $succes      = $r['message'];
        $utilisateur = Utilisateur::findById((int)$_SESSION['user_id']);
    } else {
        $erreurs = $r['erreurs']; $erreur = $r['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Mon profil — Gestion Mémoires UATM</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/auth.css">
</head>
<body>
<nav class="navbar">
  <a class="navbar-brand" href="<?= BASE_URL ?>/views/auth/login.php">📚 GéMémoires</a>
  <div class="navbar-links">
    <span>👤 <?= htmlspecialchars($_SESSION['user_nom']) ?></span>
    <a href="<?= BASE_URL ?>/views/auth/changer_mdp.php">🔑 Changer MDP</a>
    <a href="<?= BASE_URL ?>/controllers/AuthController.php?action=logout" class="btn-logout">Déconnexion</a>
  </div>
</nav>
<div class="main-container">
  <div class="page-header"><h1>👤 Mon profil</h1></div>

  <?php if ($succes): ?>
    <div class="alert alert-success"><?= htmlspecialchars($succes) ?></div>
  <?php endif; ?>
  <?php if ($erreur): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
  <?php endif; ?>

  <div class="form-container">
    <form method="POST" action="" novalidate>
      <div class="form-row">
        <div class="form-group">
          <label>Nom <span class="req">*</span></label>
          <input type="text" name="nom"
                 value="<?= htmlspecialchars($_POST['nom'] ?? $utilisateur->nom) ?>" required>
          <?php if (!empty($erreurs['nom'])): ?>
            <span class="form-err"><?= htmlspecialchars($erreurs['nom']) ?></span>
          <?php endif; ?>
        </div>
        <div class="form-group">
          <label>Prénom <span class="req">*</span></label>
          <input type="text" name="prenom"
                 value="<?= htmlspecialchars($_POST['prenom'] ?? $utilisateur->prenom) ?>" required>
        </div>
      </div>
      <div class="form-group">
        <label>Email <span class="req">*</span></label>
        <input type="email" name="email"
               value="<?= htmlspecialchars($_POST['email'] ?? $utilisateur->email) ?>" required>
        <?php if (!empty($erreurs['email'])): ?>
          <span class="form-err"><?= htmlspecialchars($erreurs['email']) ?></span>
        <?php endif; ?>
      </div>
      <?php if ($utilisateur->role === ROLE_ETUDIANT): ?>
      <div class="form-group">
        <label>Filière</label>
        <select name="id_filiere">
          <option value="">-- Choisir --</option>
          <?php foreach ($parUFR as $ufr => $liste): ?>
            <optgroup label="<?= htmlspecialchars($ufr) ?>">
              <?php foreach ($liste as $f): ?>
                <option value="<?= $f->id_filiere ?>"
                  <?= ($utilisateur->id_filiere == $f->id_filiere) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($f->nom_filiere) ?> (<?= $f->niveau ?>)
                </option>
              <?php endforeach; ?>
            </optgroup>
          <?php endforeach; ?>
        </select>
      </div>
      <?php endif; ?>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
      </div>
    </form>
  </div>
</div>
</body>
</html>
