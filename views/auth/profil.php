<?php
/**
 * Vue Modifier Profil — app/views/auth/profil.php
 * Couche : PRÉSENTATION
 */
$pageTitle = 'Mon profil';
require_once VIEW_PATH . '/layout/header.php';

$parUFR = [];
foreach ($filieres as $f) {
    $key = ($f['code_ufr'] ?? '') . ' — ' . ($f['nom_ufr'] ?? 'UFR');
    $parUFR[$key][] = $f;
}
ksort($parUFR);
?>

<div class="page-header">
  <h1>👤 Mon profil</h1>
</div>

<div class="profil-layout">

  <!-- Carte infos actuelles -->
  <div class="profil-sidebar">
    <div class="profil-avatar">
      <?= mb_strtoupper(mb_substr($utilisateur->prenom, 0, 1) . mb_substr($utilisateur->nom, 0, 1)) ?>
    </div>
    <h3><?= htmlspecialchars($utilisateur->getNomComplet()) ?></h3>
    <span class="badge badge-<?= match($utilisateur->role) {
      ROLE_ETUDIANT         => 'blue',
      ROLE_PROFESSEUR       => 'green',
      ROLE_DIRECTEUR_ETUDES => 'orange',
      ROLE_ADMINISTRATEUR   => 'red',
      default               => 'gray',
    } ?>">
      <?= ucfirst(str_replace('_', ' ', $utilisateur->role)) ?>
    </span>
    <div class="profil-meta">
      <p>📧 <?= htmlspecialchars($utilisateur->email) ?></p>
      <?php if (!empty($utilisateur->nom_filiere)): ?>
        <p>🎓 <?= htmlspecialchars($utilisateur->nom_filiere) ?> (<?= $utilisateur->niveau ?>)</p>
      <?php endif; ?>
      <p>📅 Inscrit le <?= date('d/m/Y', strtotime($utilisateur->date_inscription)) ?></p>
    </div>
    <div class="profil-links">
      <a href="<?= BASE_URL ?>/auth/changer_mdp" class="btn btn-secondary btn-full">🔑 Changer MDP</a>
    </div>
  </div>

  <!-- Formulaire de modification -->
  <div class="profil-main">
    <h2>Modifier mes informations</h2>

    <?php if (!empty($succes)): ?>
      <div class="alert alert-success"><?= htmlspecialchars($succes) ?></div>
    <?php endif; ?>
    <?php if (!empty($erreur)): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>/auth/profil" novalidate>

      <div class="form-row">
        <div class="form-group">
          <label for="nom">Nom <span class="req">*</span></label>
          <input type="text" id="nom" name="nom"
                 value="<?= htmlspecialchars($_POST['nom'] ?? $utilisateur->nom) ?>" required>
          <?php if (!empty($erreurs['nom'])): ?>
            <span class="form-err"><?= htmlspecialchars($erreurs['nom']) ?></span>
          <?php endif; ?>
        </div>
        <div class="form-group">
          <label for="prenom">Prénom <span class="req">*</span></label>
          <input type="text" id="prenom" name="prenom"
                 value="<?= htmlspecialchars($_POST['prenom'] ?? $utilisateur->prenom) ?>" required>
          <?php if (!empty($erreurs['prenom'])): ?>
            <span class="form-err"><?= htmlspecialchars($erreurs['prenom']) ?></span>
          <?php endif; ?>
        </div>
      </div>

      <div class="form-group">
        <label for="email">Adresse email <span class="req">*</span></label>
        <input type="email" id="email" name="email"
               value="<?= htmlspecialchars($_POST['email'] ?? $utilisateur->email) ?>" required>
        <?php if (!empty($erreurs['email'])): ?>
          <span class="form-err"><?= htmlspecialchars($erreurs['email']) ?></span>
        <?php endif; ?>
      </div>

      <?php if ($utilisateur->role === ROLE_ETUDIANT): ?>
        <div class="form-group">
          <label for="id_filiere">Filière</label>
          <select id="id_filiere" name="id_filiere">
            <option value="">-- Sélectionner --</option>
            <?php foreach ($parUFR as $groupe => $liste): ?>
              <optgroup label="<?= htmlspecialchars($groupe) ?>">
                <?php foreach ($liste as $f): ?>
                  <option value="<?= $f['id_filiere'] ?>"
                    <?= (($utilisateur->id_filiere == $f['id_filiere'])) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($f['nom_filiere']) ?> (<?= $f['niveau'] ?>)
                  </option>
                <?php endforeach; ?>
              </optgroup>
            <?php endforeach; ?>
          </select>
        </div>
      <?php endif; ?>

      <div class="form-actions">
        <button type="submit" class="btn btn-primary">💾 Enregistrer les modifications</button>
      </div>

    </form>
  </div>

</div>

<?php require_once VIEW_PATH . '/layout/footer.php'; ?>
