<?php
/**
 * Vue Login — app/views/auth/login.php
 * Couche : PRÉSENTATION
 * Parle uniquement au Contrôleur via HTTP POST.
 */
$pageTitle = 'Connexion';
require_once VIEW_PATH . '/layout/header.php';
?>

<div class="auth-container">
  <div class="auth-card">

    <div class="auth-brand">
      <span class="auth-logo">📚</span>
      <h1>GéMémoires</h1>
      <p>UATM GASA FORMATION</p>
    </div>

    <?php if (!empty($erreur)): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
    <?php endif; ?>

    <?php if (!empty($succes)): ?>
      <div class="alert alert-success"><?= htmlspecialchars($succes) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>/auth/login" novalidate>

      <div class="form-group">
        <label for="email">Adresse email</label>
        <input type="email" id="email" name="email"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
               placeholder="votre@email.com"
               required autofocus autocomplete="email">
      </div>

      <div class="form-group">
        <label for="mot_de_passe">
          Mot de passe
          <span class="toggle-mdp" onclick="toggleVisibilite('mot_de_passe', this)">👁</span>
        </label>
        <input type="password" id="mot_de_passe" name="mot_de_passe"
               placeholder="••••••••" required autocomplete="current-password">
      </div>

      <button type="submit" class="btn btn-primary btn-full">
        🔐 Se connecter
      </button>

    </form>

    <div class="auth-footer">
      <p>Pas encore de compte étudiant ?
        <a href="<?= BASE_URL ?>/auth/register">Créer un compte</a>
      </p>
    </div>

  </div>
</div>

<script>
function toggleVisibilite(id, btn) {
  const input = document.getElementById(id);
  input.type = input.type === 'password' ? 'text' : 'password';
  btn.textContent = input.type === 'password' ? '👁' : '🙈';
}
</script>

<?php require_once VIEW_PATH . '/layout/footer.php'; ?>
