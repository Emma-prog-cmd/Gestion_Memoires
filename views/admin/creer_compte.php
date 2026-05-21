<?php
/**
 * Vue Créer un compte — app/views/admin/creer_compte.php
 * Couche : PRÉSENTATION
 */
$pageTitle = 'Créer un compte';
require_once VIEW_PATH . '/layout/header.php';

$parUFR = [];
foreach ($filieres as $f) {
    $key = ($f['code_ufr'] ?? '') . ' — ' . ($f['nom_ufr'] ?? 'UFR');
    $parUFR[$key][] = $f;
}
ksort($parUFR);

$rolesDisponibles = [
    ROLE_ETUDIANT         => 'Étudiant',
    ROLE_PROFESSEUR       => 'Professeur / Encadreur',
    ROLE_DIRECTEUR_ETUDES => 'Directeur des études',
    ROLE_ADMINISTRATEUR   => 'Administrateur',
];
?>

<div class="page-header">
  <h1>➕ Créer un compte</h1>
  <a href="<?= BASE_URL ?>/compte/index" class="btn btn-secondary">← Retour à la liste</a>
</div>

<?php if (!empty($erreur)): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
<?php endif; ?>

<div class="form-container">
  <form method="POST" action="<?= BASE_URL ?>/compte/creer" novalidate>

    <div class="form-row">
      <div class="form-group">
        <label for="nom">Nom <span class="req">*</span></label>
        <input type="text" id="nom" name="nom"
               value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" required
               placeholder="AGOSSOU">
        <?php if (!empty($erreurs['nom'])): ?>
          <span class="form-err"><?= htmlspecialchars($erreurs['nom']) ?></span>
        <?php endif; ?>
      </div>
      <div class="form-group">
        <label for="prenom">Prénom <span class="req">*</span></label>
        <input type="text" id="prenom" name="prenom"
               value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>" required
               placeholder="Kofi">
        <?php if (!empty($erreurs['prenom'])): ?>
          <span class="form-err"><?= htmlspecialchars($erreurs['prenom']) ?></span>
        <?php endif; ?>
      </div>
    </div>

    <div class="form-group">
      <label for="email">Adresse email <span class="req">*</span></label>
      <input type="email" id="email" name="email"
             value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required
             placeholder="kofi@uatm.bj">
      <?php if (!empty($erreurs['email'])): ?>
        <span class="form-err"><?= htmlspecialchars($erreurs['email']) ?></span>
      <?php endif; ?>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="role">Rôle <span class="req">*</span></label>
        <select id="role" name="role" required onchange="toggleFiliere(this.value)">
          <option value="">-- Choisir un rôle --</option>
          <?php foreach ($rolesDisponibles as $val => $label): ?>
            <option value="<?= $val ?>"
              <?= (($_POST['role'] ?? '') === $val) ? 'selected' : '' ?>>
              <?= $label ?>
            </option>
          <?php endforeach; ?>
        </select>
        <?php if (!empty($erreurs['role'])): ?>
          <span class="form-err"><?= htmlspecialchars($erreurs['role']) ?></span>
        <?php endif; ?>
      </div>

      <div class="form-group" id="groupeFiliere"
           style="<?= (($_POST['role'] ?? '') !== ROLE_ETUDIANT) ? 'opacity:.4' : '' ?>">
        <label for="id_filiere">Filière (étudiant)</label>
        <select id="id_filiere" name="id_filiere">
          <option value="">-- Facultatif --</option>
          <?php foreach ($parUFR as $groupe => $liste): ?>
            <optgroup label="<?= htmlspecialchars($groupe) ?>">
              <?php foreach ($liste as $f): ?>
                <option value="<?= $f['id_filiere'] ?>"
                  <?= (($_POST['id_filiere'] ?? '') == $f['id_filiere']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($f['nom_filiere']) ?> (<?= $f['niveau'] ?>)
                </option>
              <?php endforeach; ?>
            </optgroup>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="mot_de_passe">
          Mot de passe <span class="req">*</span>
          <span class="toggle-mdp" onclick="toggleVisibilite('mot_de_passe', this)">👁</span>
        </label>
        <input type="password" id="mot_de_passe" name="mot_de_passe"
               placeholder="Min. 8 caractères" required>
        <?php if (!empty($erreurs['mot_de_passe'])): ?>
          <span class="form-err"><?= htmlspecialchars($erreurs['mot_de_passe']) ?></span>
        <?php endif; ?>
      </div>
      <div class="form-group">
        <label for="confirmer_mdp">Confirmer <span class="req">*</span></label>
        <input type="password" id="confirmer_mdp" name="confirmer_mdp"
               placeholder="Répéter" required>
        <?php if (!empty($erreurs['confirmer_mdp'])): ?>
          <span class="form-err"><?= htmlspecialchars($erreurs['confirmer_mdp']) ?></span>
        <?php endif; ?>
      </div>
    </div>

    <div class="form-actions">
      <a href="<?= BASE_URL ?>/compte/index" class="btn btn-secondary">Annuler</a>
      <button type="submit" class="btn btn-primary">✅ Créer le compte</button>
    </div>
  </form>
</div>

<script>
function toggleFiliere(role) {
  const g = document.getElementById('groupeFiliere');
  g.style.opacity = role === '<?= ROLE_ETUDIANT ?>' ? '1' : '.4';
  document.getElementById('id_filiere').disabled = role !== '<?= ROLE_ETUDIANT ?>';
}
function toggleVisibilite(id, btn) {
  const i = document.getElementById(id);
  i.type = i.type === 'password' ? 'text' : 'password';
  btn.textContent = i.type === 'password' ? '👁' : '🙈';
}
// Init
toggleFiliere(document.getElementById('role').value);
</script>

<?php require_once VIEW_PATH . '/layout/footer.php'; ?>
