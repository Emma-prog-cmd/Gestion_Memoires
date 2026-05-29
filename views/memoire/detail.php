<?php
/**
 * Vue détail mémoire — views/memoire/detail.php
 * Couche : PRÉSENTATION
 * Affiche les infos du mémoire + section commentaires + bouton like
 * Variables attendues : $memoire (objet), $commentaires (array), $nbLikes (int), $dejaLike (bool)
 */
$pageTitle = htmlspecialchars($memoire->theme ?? 'Détail du mémoire');
require_once "../config/connexion.php";

$idUser     = $_SESSION['id_utilisateur'] ?? null;
$role       = $_SESSION['role'] ?? '';
$idMemoire  = (int) $memoire->id_memoire;
?>

<div class="container" style="max-width:860px">

  <!-- ===== EN-TÊTE MÉMOIRE ===== -->
  <div class="memoire-card">
    <div class="memoire-header">
      <span class="badge badge-blue"><?= htmlspecialchars($memoire->filiere) ?></span>
      <?php if (!empty($memoire->annee_academique)): ?>
        <span class="badge badge-gray"><?= htmlspecialchars($memoire->annee_academique) ?></span>
      <?php endif; ?>
    </div>

    <h1 class="memoire-title"><?= htmlspecialchars($memoire->theme) ?></h1>

    <div class="memoire-meta">
      <span>✍️ <?= htmlspecialchars($memoire->auteur) ?></span>
      <span>📅 <?= date('d/m/Y', strtotime($memoire->date_soumission)) ?></span>
      <?php if (!empty($memoire->promotion)): ?>
        <span>🎓 <?= htmlspecialchars($memoire->promotion) ?></span>
      <?php endif; ?>
    </div>

    <?php if (!empty($memoire->resume)): ?>
      <p class="memoire-resume"><?= nl2br(htmlspecialchars($memoire->resume)) ?></p>
    <?php endif; ?>

    <!-- ===== BARRE LIKE + COMPTEURS ===== -->
    <div class="social-bar">

      <?php if ($idUser): ?>
        <!-- Bouton Like AJAX -->
        <button
          class="btn-like <?= $dejaLike ? 'liked' : '' ?>"
          id="btn-like"
          data-id="<?= $idMemoire ?>"
          title="<?= $dejaLike ? 'Retirer mon like' : 'Liker ce mémoire' ?>"
        >
          <span class="like-icon">❤️</span>
          <span class="like-count" id="like-count"><?= $nbLikes ?></span>
          <span class="like-label"><?= $nbLikes > 1 ? 'likes' : 'like' ?></span>
        </button>
      <?php else: ?>
        <span class="social-stat">❤️ <strong><?= $nbLikes ?></strong> like<?= $nbLikes > 1 ? 's' : '' ?></span>
      <?php endif; ?>

      <span class="social-stat">
        💬 <strong id="comment-count"><?= count($commentaires) ?></strong>
        commentaire<?= count($commentaires) > 1 ? 's' : '' ?>
      </span>

    </div>
  </div>

  <!-- ===== SECTION COMMENTAIRES ===== -->
  <div class="comments-section" id="comments-section">

    <h2 class="comments-title">
      💬 Commentaires
      <span class="badge badge-blue" id="badge-count"><?= count($commentaires) ?></span>
    </h2>

    <!-- Formulaire d'ajout (uniquement si connecté) -->
    <?php if ($idUser): ?>
      <div class="comment-form-wrap" id="comment-form-wrap">
        <div class="comment-avatar">
          <?= strtoupper(mb_substr($_SESSION['prenom'] ?? 'U', 0, 1)) ?>
        </div>
        <div class="comment-form-inner">
          <textarea
            id="comment-input"
            placeholder="Écrire un commentaire…"
            maxlength="1000"
            rows="3"
          ></textarea>
          <div class="comment-form-footer">
            <span class="char-count" id="char-count">0 / 1000</span>
            <button class="btn btn-primary btn-sm" id="btn-submit-comment">
              Publier
            </button>
          </div>
          <div id="comment-error" class="alert alert-error" style="display:none"></div>
        </div>
      </div>
    <?php else: ?>
      <p class="text-muted" style="margin-bottom:16px">
        <a href="<?= BASE_URL ?>/auth/login">Connectez-vous</a> pour laisser un commentaire.
      </p>
    <?php endif; ?>

    <!-- Liste des commentaires -->
    <div id="comments-list">
      <?php if (empty($commentaires)): ?>
        <p class="no-comments" id="no-comments-msg">Aucun commentaire pour l'instant. Soyez le premier !</p>
      <?php else: ?>
        <?php foreach ($commentaires as $c): ?>
          <?php
            $peutSupprimer = $idUser && (
              (int)$c->id_utilisateur === (int)$idUser ||
              in_array($role, ['administrateur', 'directeur_etude'])
            );
          ?>
          <div class="comment-item" id="comment-<?= $c->id_commentaire ?>">
            <div class="comment-avatar-sm">
              <?= strtoupper(mb_substr($c->prenom, 0, 1)) ?>
            </div>
            <div class="comment-body">
              <div class="comment-header">
                <strong class="comment-author">
                  <?= htmlspecialchars($c->prenom . ' ' . $c->nom) ?>
                </strong>
                <span class="comment-role badge <?= getRoleBadge($c->role) ?>">
                  <?= getRoleLabel($c->role) ?>
                </span>
                <span class="comment-date">
                  <?= date('d/m/Y à H:i', strtotime($c->date_commentaire)) ?>
                </span>
              </div>
              <p class="comment-text"><?= nl2br(htmlspecialchars($c->contenu)) ?></p>
              <?php if ($peutSupprimer): ?>
                <button
                  class="btn-delete-comment"
                  data-id="<?= $c->id_commentaire ?>"
                  data-memoire="<?= $idMemoire ?>"
                  title="Supprimer ce commentaire"
                >🗑️ Supprimer</button>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

  </div><!-- /comments-section -->

</div><!-- /container -->

<?php
// ── Helpers d'affichage ──────────────────────────────────────────
function getRoleLabel(string $role): string {
    return match($role) {
        'etudiant_diplome'   => 'Étudiant',
        'etudiant_consultant'=> 'Étudiant',
        'professeur'         => 'Professeur',
        'directeur_etude'    => 'Dir. études',
        'administrateur'     => 'Admin',
        default              => $role,
    };
}
function getRoleBadge(string $role): string {
    return match($role) {
        'professeur'                         => 'badge-green',
        'directeur_etude', 'administrateur'  => 'badge-red',
        default                              => 'badge-blue',
    };
}
?>

<!-- Données pour le JS -->
<script>
  const MEMOIRE_ID  = <?= $idMemoire ?>;
  const USER_LOGGED = <?= $idUser ? 'true' : 'false' ?>;
  const CTRL_URL    = '<?= BASE_URL ?>/controllers/CommentaireLikeController.php';
</script>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/commentaire_like.css">
<script src="<?= BASE_URL ?>/assets/js/commentaire_like.js" defer></script>

<?php require_once VIEW_PATH . '/layout/footer.php'; ?>
