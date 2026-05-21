<?php
/**
 * Vue Modifier un compte — app/views/admin/modifier_compte.php
 * Couche : PRÉSENTATION
 */
$pageTitle = 'Modifier un compte';
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
  <h1>✏️ Modifier le compte</h1>
  <a href="<?= BASE_URL ?>/compte/index" class="btn btn-secondary">← Retour à la liste</a>
</div>

<!-- Résumé du compte -->
<div class="compte-resume">
  <div class="compte-avatar">
    <?= mb_strtoupper(mb_substr($utilisateur->prenom,0,1).mb_substr($utilisateur->nom,0,1)) ?>
  </div>
  <div>
    <strong><?= htmlspecialchars($utilisateur->getNomComplet()) ?></strong>
    <span class="text-muted">ID #<?= $utilisateur->id_user ?></span>
    <span class="badge <?= $utilisateur->actif ? 'badge-green' : 'badge-red' ?>">
      <?= $utilisateur->actif ? 'Actif' : 'Inactif' ?>
    </span>
  </div>
</div>

<?php if (!empty($erreur)): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
<?php endif; ?>

<div class="form-container">
  <form method="POST" action="<?= BASE_URL ?>/compte/modifier/<?= $utilisateur->id_user ?>" novalidate>

    <div class="form-row">
      <div class="form-group">
        <label for="nom">Nom <span class="req">*</span></label>
        <input type="text" id="nom" name="nom" required
               value="<?= htmlspecialchars($_POST['nom'] ?? $utilisateur->nom) ?>">
        <?php if (!empty($erreurs['nom'])): ?>
          <span class="form-err"><?= htmlspecialchars($erreurs['nom']) ?></span>
        <?php endif; ?>
      </div>
      <div class="form-group">
        <label for="prenom">Prénom <span class="req">*</span></label>
        <input type="text" id="prenom" name="prenom" required
               value="<?= htmlspecialchars($_POST['prenom'] ?? $utilisateur->prenom) ?>">
        <?php if (!empty($erreurs['prenom'])): ?>
          <span class="form-err"><?= htmlspecialchars($erreurs['prenom']) ?></span>
        <?php endif; ?>
      </div>
    </div>

    <div class="form-group">
      <label for="email">Email <span class="req">*</span></label>
      <input type="email" id="email" name="email" required
             value="<?= htmlspecialchars($_POST['email'] ?? $utilisateur->email) ?>">
      <?php if (!empty($erreurs['email'])): ?>
        <span class="form-err"><?= htmlspecialchars($erreurs['email']) ?></span>
      <?php endif; ?>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="role">Rôle</label>
        <select id="role" name="role" onchange="toggleFiliere(this.value)">
          <?php foreach ($rolesDisponibles as $val => $lbl): ?>
            <option value="<?= $val ?>"
              <?= (($_POST['role'] ?? $utilisateur->role) === $val) ? 'selected' : '' ?>>
              <?= $lbl ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group" id="groupeFiliere">
        <label for="id_filiere">Filière (si étudiant)</label>
        <select id="id_filiere" name="id_filiere">
          <option value="">-- Aucune --</option>
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
    </div>

    <!-- Réinitialiser le mot de passe (optionnel) -->
    <details class="section-details">
      <summary>🔑 Réinitialiser le mot de passe (optionnel)</summary>
      <div class="form-row" style="margin-top:14px">
        <div class="form-group">
          <label for="nouveau_mdp">
            Nouveau MDP
            <span class="toggle-mdp" onclick="toggleVisibilite('nouveau_mdp', this)">👁</span>
          </label>
          <input type="password" id="nouveau_mdp" name="nouveau_mdp"
                 placeholder="Laisser vide = pas de changement">
          <span class="form-hint">Min. 8 caractères si renseigné.</span>
        </div>
        <div class="form-group">
          <label for="confirmer_mdp_reset">Confirmer</label>
          <input type="password" id="confirmer_mdp_reset" name="confirmer_mdp"
                 placeholder="Répéter">
        </div>
      </div>
    </details>

    <div class="form-actions">
      <a href="<?= BASE_URL ?>/compte/index" class="btn btn-secondary">Annuler</a>
      <button type="submit" class="btn btn-primary">💾 Enregistrer les modifications</button>
    </div>
  </form>
</div>

<script>
function toggleFiliere(role) {
  const g = document.getElementById('groupeFiliere');
  g.style.opacity = role === '<?= ROLE_ETUDIANT ?>' ? '1' : '.5';
}
function toggleVisibilite(id, btn) {
  const i = document.getElementById(id);
  i.type = i.type === 'password' ? 'text' : 'password';
  btn.textContent = i.type === 'password' ? '👁' : '🙈';
}
toggleFiliere(document.getElementById('role').value);
</script>

<?php require_once VIEW_PATH . '/layout/footer.php'; ?>
