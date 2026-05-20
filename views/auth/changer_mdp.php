<?php
/**
 * Vue Changer Mot de Passe — app/views/auth/changer_mdp.php
 * Couche : PRÉSENTATION
 */
$pageTitle = 'Changer mon mot de passe';
require_once VIEW_PATH . '/layout/header.php';
?>

<div class="page-header">
  <h1>🔑 Changer mon mot de passe</h1>
  <a href="<?= BASE_URL ?>/auth/profil" class="btn btn-secondary">← Retour au profil</a>
</div>

<div class="form-container">

  <?php if (!empty($succes)): ?>
    <div class="alert alert-success">
      <?= htmlspecialchars($succes) ?>
      — <a href="<?= BASE_URL ?>/auth/profil">Retour au profil</a>
    </div>
  <?php endif; ?>

  <?php if (!empty($erreur)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
  <?php endif; ?>

  <form method="POST" action="<?= BASE_URL ?>/auth/changer_mdp" novalidate>

    <div class="form-group">
      <label for="ancien_mdp">
        Ancien mot de passe <span class="req">*</span>
        <span class="toggle-mdp" onclick="toggleVisibilite('ancien_mdp', this)">👁</span>
      </label>
      <input type="password" id="ancien_mdp" name="ancien_mdp"
             placeholder="Votre mot de passe actuel" required autocomplete="current-password">
      <?php if (!empty($erreurs['ancien_mdp'])): ?>
        <span class="form-err"><?= htmlspecialchars($erreurs['ancien_mdp']) ?></span>
      <?php endif; ?>
    </div>

    <div class="form-group">
      <label for="nouveau_mdp">
        Nouveau mot de passe <span class="req">*</span>
        <span class="toggle-mdp" onclick="toggleVisibilite('nouveau_mdp', this)">👁</span>
      </label>
      <input type="password" id="nouveau_mdp" name="nouveau_mdp"
             placeholder="Min. 8 caractères, dont un chiffre" required
             autocomplete="new-password">
      <div class="mdp-force" id="mdpForce" style="display:none">
        <div class="force-bar"><div id="forceBar"></div></div>
        <span id="forceLabel"></span>
      </div>
      <?php if (!empty($erreurs['nouveau_mdp'])): ?>
        <span class="form-err"><?= htmlspecialchars($erreurs['nouveau_mdp']) ?></span>
      <?php endif; ?>
    </div>

    <div class="form-group">
      <label for="confirmer_mdp">
        Confirmer le nouveau mot de passe <span class="req">*</span>
      </label>
      <input type="password" id="confirmer_mdp" name="confirmer_mdp"
             placeholder="Répéter le nouveau mot de passe" required
             autocomplete="new-password">
      <span id="matchMsg" style="font-size:12px"></span>
      <?php if (!empty($erreurs['confirmer_mdp'])): ?>
        <span class="form-err"><?= htmlspecialchars($erreurs['confirmer_mdp']) ?></span>
      <?php endif; ?>
    </div>

    <div class="securite-info">
      <h4>Règles de sécurité</h4>
      <ul>
        <li id="rule-len"  class="rule">Au moins 8 caractères</li>
        <li id="rule-num"  class="rule">Au moins un chiffre (0-9)</li>
        <li id="rule-let"  class="rule">Au moins une lettre</li>
        <li id="rule-conf" class="rule">Confirmation identique</li>
      </ul>
    </div>

    <div class="form-actions">
      <a href="<?= BASE_URL ?>/auth/profil" class="btn btn-secondary">Annuler</a>
      <button type="submit" class="btn btn-primary" id="btnSoumettre" disabled>
        🔐 Changer le mot de passe
      </button>
    </div>

  </form>
</div>

<script>
function toggleVisibilite(id, btn) {
  const i = document.getElementById(id);
  i.type = i.type === 'password' ? 'text' : 'password';
  btn.textContent = i.type === 'password' ? '👁' : '🙈';
}

const nouveauInput   = document.getElementById('nouveau_mdp');
const confirmerInput = document.getElementById('confirmer_mdp');
const btnSoumettre   = document.getElementById('btnSoumettre');

function verifier() {
  const v = nouveauInput.value;
  const c = confirmerInput.value;

  const rules = {
    'rule-len' : v.length >= 8,
    'rule-num' : /[0-9]/.test(v),
    'rule-let' : /[A-Za-z]/.test(v),
    'rule-conf': v === c && c.length > 0,
  };

  for (const [id, ok] of Object.entries(rules)) {
    const el = document.getElementById(id);
    el.className = 'rule ' + (ok ? 'rule-ok' : 'rule-ko');
    el.querySelector ? null : null;
  }

  // Match message
  const matchMsg = document.getElementById('matchMsg');
  if (c.length > 0) {
    matchMsg.textContent  = v === c ? '✅ Identiques' : '❌ Différents';
    matchMsg.style.color  = v === c ? '#16a34a' : '#dc2626';
  } else {
    matchMsg.textContent = '';
  }

  // Indicateur de force
  const wrap = document.getElementById('mdpForce');
  const bar  = document.getElementById('forceBar');
  const lbl  = document.getElementById('forceLabel');
  if (v) {
    wrap.style.display = 'flex';
    let score = 0;
    if (v.length >= 8)             score++;
    if (/[0-9]/.test(v))           score++;
    if (/[A-Z]/.test(v))           score++;
    if (/[^A-Za-z0-9]/.test(v))   score++;
    const colors = ['#ef4444','#f59e0b','#3b82f6','#10b981'];
    const labels = ['Très faible','Moyen','Fort','Très fort'];
    bar.style.width      = (score * 25) + '%';
    bar.style.background = colors[score-1] || '#ef4444';
    lbl.textContent      = labels[score-1] || 'Trop court';
    lbl.style.color      = colors[score-1] || '#ef4444';
  } else {
    wrap.style.display = 'none';
  }

  // Activer le bouton seulement si tout est valide
  btnSoumettre.disabled = !Object.values(rules).every(Boolean);
}

nouveauInput.addEventListener('input', verifier);
confirmerInput.addEventListener('input', verifier);
</script>

<?php require_once VIEW_PATH . '/layout/footer.php'; ?>
