<?php
/**
 * Vue Inscription — app/views/auth/register.php
 * Couche : PRÉSENTATION
 */
$pageTitle = 'Créer un compte';
require_once VIEW_PATH . '/layout/header.php';

// Grouper les filières par UFR pour l'affichage
$parUFR = [];
foreach ($filieres as $f) {
    $key = ($f['code_ufr'] ?? '') . ' — ' . ($f['nom_ufr'] ?? 'UFR');
    $parUFR[$key][] = $f;
}
ksort($parUFR);
?>

<div class="auth-container">
  <div class="auth-card auth-card-wide">

    <div class="auth-brand">
      <h2>Créer un compte étudiant</h2>
      <p>UATM GASA FORMATION</p>
    </div>

    <?php if (!empty($erreur)): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>/auth/register" novalidate>

      <!-- Nom & Prénom -->
      <div class="form-row">
        <div class="form-group">
          <label for="nom">Nom <span class="req">*</span></label>
          <input type="text" id="nom" name="nom"
                 value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>"
                 placeholder="AGOSSOU" required>
          <?php if (!empty($erreurs['nom'])): ?>
            <span class="form-err"><?= htmlspecialchars($erreurs['nom']) ?></span>
          <?php endif; ?>
        </div>
        <div class="form-group">
          <label for="prenom">Prénom <span class="req">*</span></label>
          <input type="text" id="prenom" name="prenom"
                 value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>"
                 placeholder="Kofi" required>
          <?php if (!empty($erreurs['prenom'])): ?>
            <span class="form-err"><?= htmlspecialchars($erreurs['prenom']) ?></span>
          <?php endif; ?>
        </div>
      </div>

      <!-- Email -->
      <div class="form-group">
        <label for="email">Adresse email <span class="req">*</span></label>
        <input type="email" id="email" name="email"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
               placeholder="kofi@uatm.bj" required>
        <?php if (!empty($erreurs['email'])): ?>
          <span class="form-err"><?= htmlspecialchars($erreurs['email']) ?></span>
        <?php endif; ?>
      </div>

      <!-- Filière -->
      <div class="form-group">
        <label for="id_filiere">Filière <span class="req">*</span></label>
        <select id="id_filiere" name="id_filiere" required>
          <option value="">-- Sélectionner votre filière --</option>
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
        <?php if (!empty($erreurs['id_filiere'])): ?>
          <span class="form-err"><?= htmlspecialchars($erreurs['id_filiere']) ?></span>
        <?php endif; ?>
      </div>

      <!-- Mots de passe -->
      <div class="form-row">
        <div class="form-group">
          <label for="mot_de_passe">
            Mot de passe <span class="req">*</span>
            <span class="toggle-mdp" onclick="toggleVisibilite('mot_de_passe', this)">👁</span>
          </label>
          <input type="password" id="mot_de_passe" name="mot_de_passe"
                 placeholder="Min. 8 car. avec un chiffre" required>
          <span class="form-hint">Au moins 8 caractères, dont un chiffre</span>
          <?php if (!empty($erreurs['mot_de_passe'])): ?>
            <span class="form-err"><?= htmlspecialchars($erreurs['mot_de_passe']) ?></span>
          <?php endif; ?>
        </div>
        <div class="form-group">
          <label for="confirmer_mdp">Confirmer <span class="req">*</span></label>
          <input type="password" id="confirmer_mdp" name="confirmer_mdp"
                 placeholder="Répéter le mot de passe" required>
          <?php if (!empty($erreurs['confirmer_mdp'])): ?>
            <span class="form-err"><?= htmlspecialchars($erreurs['confirmer_mdp']) ?></span>
          <?php endif; ?>
        </div>
      </div>

      <!-- Indicateur force MDP -->
      <div class="mdp-force" id="mdpForce" style="display:none">
        <div class="force-bar"><div id="forceBar"></div></div>
        <span id="forceLabel"></span>
      </div>

      <button type="submit" class="btn btn-primary btn-full">
        ✅ Créer mon compte
      </button>
    </form>

    <div class="auth-footer">
      <p>Déjà inscrit ? <a href="<?= BASE_URL ?>/auth/login">Se connecter</a></p>
    </div>
  </div>
</div>

<script>
function toggleVisibilite(id, btn) {
  const i = document.getElementById(id);
  i.type = i.type === 'password' ? 'text' : 'password';
  btn.textContent = i.type === 'password' ? '👁' : '🙈';
}

// Indicateur de force du mot de passe
document.getElementById('mot_de_passe').addEventListener('input', function() {
  const v = this.value;
  const wrap = document.getElementById('mdpForce');
  const bar  = document.getElementById('forceBar');
  const lbl  = document.getElementById('forceLabel');

  if (!v) { wrap.style.display = 'none'; return; }
  wrap.style.display = 'flex';

  let score = 0;
  if (v.length >= 8)  score++;
  if (/[0-9]/.test(v)) score++;
  if (/[A-Z]/.test(v)) score++;
  if (/[^A-Za-z0-9]/.test(v)) score++;

  const colors = ['#ef4444','#f59e0b','#3b82f6','#10b981'];
  const labels = ['Très faible','Moyen','Fort','Très fort'];
  bar.style.width = (score * 25) + '%';
  bar.style.background = colors[score - 1] || '#ef4444';
  lbl.textContent = labels[score - 1] || 'Trop court';
  lbl.style.color = colors[score - 1] || '#ef4444';
});
</script>

<?php require_once VIEW_PATH . '/layout/footer.php'; ?>
