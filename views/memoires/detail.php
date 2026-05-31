<?php
/**
 * views/memoire/detail.php
 * Page autonome — pas de VIEW_PATH, BASE_URL, CTRL_PATH
 * Accès direct : localhost/Gestion_Memoires/views/memoire/detail.php?id=1
 */

session_start();

// Connexion BDD — chemin relatif depuis views/memoire/
require_once __DIR__ . '/../../config/connexion.php';
require_once __DIR__ . '/../../models/Commentaire.php';
require_once __DIR__ . '/../../models/Like.php';

// Vérification session
if (empty($_SESSION['id_utilisateur'])) {
    header('Location: ../auth/login.php');
    exit;
}

$idUser = (int)$_SESSION['id_utilisateur'];
$role   = $_SESSION['role']   ?? '';
$prenom = $_SESSION['prenom'] ?? 'U';
$nom    = $_SESSION['nom']    ?? '';

// Récupération du mémoire
$idMemoire = (int)($_GET['id'] ?? 0);
if ($idMemoire <= 0) {
    die('<p style="color:red;padding:20px">ID mémoire manquant. <a href="liste.php">Retour</a></p>');
}

$stmt = $connexion->prepare("SELECT * FROM memoire WHERE id_memoire = :id");
$stmt->execute([':id' => $idMemoire]);
$memoire = $stmt->fetch(PDO::FETCH_OBJ);

if (!$memoire) {
    die('<p style="color:red;padding:20px">Mémoire introuvable. <a href="liste.php">Retour</a></p>');
}

// Commentaires et likes
$modelCommentaire = new Commentaire($connexion);
$modelLike        = new Like($connexion);

$commentaires = $modelCommentaire->getParMemoire($idMemoire);
$nbLikes      = $modelLike->compter($idMemoire);
$dejaLike     = $modelLike->aDejaLike($idMemoire, $idUser);

// Helpers
function getRoleLabel(string $r): string {
    return match($r) {
        'etudiant_diplome', 'etudiant_consultant' => 'Étudiant',
        'professeur'      => 'Professeur',
        'directeur_etude' => 'Dir. études',
        'administrateur'  => 'Admin',
        default           => $r,
    };
}
function getRoleBadge(string $r): string {
    return match($r) {
        'professeur'                       => 'badge-green',
        'directeur_etude','administrateur' => 'badge-red',
        default                            => 'badge-blue',
    };
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($memoire->theme) ?> — GéMémoires</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/commentaire_like.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <a class="navbar-brand" href="liste.php">
        📚 GéMémoires <span class="brand-sub">UATM GASA FORMATION</span>
    </a>
    <div class="navbar-links">
        <a href="liste.php">Bibliothèque</a>
        <span class="nav-user">
            <?= htmlspecialchars($prenom . ' ' . $nom) ?>
        </span>
        <a href="../auth/logout.php" class="btn-logout">Déconnexion</a>
    </div>
</nav>

<!-- CONTENU -->
<div class="container" style="max-width:860px">

    <!-- CARTE MÉMOIRE -->
    <div class="memoire-card">

        <div class="memoire-header">
            <span class="badge badge-blue"><?= htmlspecialchars($memoire->filiere) ?></span>
            <span class="badge badge-gray"><?= htmlspecialchars($memoire->annee_academique) ?></span>
            <?php if ($memoire->statut === 'valide'): ?>
                <span class="badge badge-green">Publié</span>
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

        <!-- LIKE + COMPTEURS -->
        <div class="social-bar">
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

            <span class="social-stat">
                💬 <strong id="comment-count"><?= count($commentaires) ?></strong>
                commentaire<?= count($commentaires) > 1 ? 's' : '' ?>
            </span>
        </div>

    </div><!-- /memoire-card -->

    <!-- SECTION COMMENTAIRES -->
    <div class="comments-section">

        <h2 class="comments-title">
            💬 Commentaires
            <span class="badge badge-blue" id="badge-count"><?= count($commentaires) ?></span>
        </h2>

        <!-- Formulaire ajout -->
        <div class="comment-form-wrap">
            <div class="comment-avatar">
                <?= strtoupper(mb_substr($prenom, 0, 1)) ?>
            </div>
            <div class="comment-form-inner">
                <textarea
                    id="comment-input"
                    placeholder="Écrire un commentaire… (Ctrl+Entrée pour publier)"
                    maxlength="1000"
                    rows="3"
                ></textarea>
                <div class="comment-form-footer">
                    <span class="char-count" id="char-count">0 / 1000</span>
                    <button class="btn-success" id="btn-submit-comment">
                        Publier
                    </button>
                </div>
                <div id="comment-error" class="alert alert-error" style="display:none"></div>
            </div>
        </div>

        <!-- Liste commentaires -->
        <div id="comments-list">
            <?php if (empty($commentaires)): ?>
                <p class="no-comments" id="no-comments-msg">
                    Aucun commentaire pour l'instant. Soyez le premier !
                </p>
            <?php else: ?>
                <?php foreach ($commentaires as $c): ?>
                    <?php
                    $peutSupprimer = (
                        (int)$c->id_utilisateur === $idUser ||
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
                                <span class="badge <?= getRoleBadge($c->role) ?>">
                                    <?= getRoleLabel($c->role) ?>
                                </span>
                                <span class="comment-date">
                                    <?= date('d/m/Y à H:i', strtotime($c->date_commentaire)) ?>
                                </span>
                            </div>
                            <p class="comment-text">
                                <?= nl2br(htmlspecialchars($c->contenu)) ?>
                            </p>
                            <?php if ($peutSupprimer): ?>
                                <button
                                    class="btn-delete-comment"
                                    data-id="<?= $c->id_commentaire ?>"
                                    data-memoire="<?= $idMemoire ?>"
                                >🗑️ Supprimer</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div><!-- /comments-list -->

    </div><!-- /comments-section -->

</div><!-- /container -->

<!-- FOOTER -->
<footer class="footer">
    © <?= date('Y') ?> GéMémoires — UATM GASA FORMATION
</footer>

<!-- Variables JS — chemin relatif vers le contrôleur -->
<script>
    const MEMOIRE_ID = <?= $idMemoire ?>;
    const CTRL_URL   = '../../controllers/CommentaireLikeController.php';
</script>

<script src="../../assets/js/commentaire_like.js"></script>

</body>
</html>
