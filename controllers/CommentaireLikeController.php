<?php
/**
 * Contrôleur CommentaireLike — controllers/CommentaireLikeController.php
 * Couche : CONTRÔLE
 * Gère les actions commentaire (ajouter, supprimer) et like (basculer)
 * Toutes les actions AJAX retournent du JSON.
 */

require_once __DIR__ . '/../config/connexion.php';
require_once __DIR__ . '/../models/Commentaire.php';
require_once __DIR__ . '/../models/Like.php';

session_start();

// — Utilitaire : réponse JSON propre —
function jsonResponse(bool $succes, array $data = [], int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge(['succes' => $succes], $data));
    exit;
}

// — Vérification session obligatoire —
if (empty($_SESSION['id_utilisateur'])) {
    jsonResponse(false, ['message' => 'Vous devez être connecté.'], 401);
}

$idUser = (int) $_SESSION['id_utilisateur'];
$role   = $_SESSION['role'] ?? '';

// — Vérification méthode POST —
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, ['message' => 'Méthode non autorisée.'], 405);
}

// — Lecture des données JSON ou POST classique —
$input    = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$action   = $input['action']     ?? '';
$idMemoire = (int)($input['id_memoire'] ?? 0);

if ($idMemoire <= 0) {
    jsonResponse(false, ['message' => 'Mémoire invalide.'], 400);
}

// ================================================================
//  AIGUILLAGE DES ACTIONS
// ================================================================
switch ($action) {

    // -------------------------------------------------------
    // ACTION : ajouter un commentaire
    // -------------------------------------------------------
    case 'ajouter_commentaire':
        $contenu = trim($input['contenu'] ?? '');
        if (empty($contenu)) {
            jsonResponse(false, ['message' => 'Le commentaire ne peut pas être vide.'], 422);
        }
        if (mb_strlen($contenu) > 1000) {
            jsonResponse(false, ['message' => 'Commentaire trop long (max 1000 caractères).'], 422);
        }

        $model = new Commentaire($connexion);
        $ok    = $model->ajouter($idMemoire, $idUser, $contenu);

        if (!$ok) {
            jsonResponse(false, ['message' => 'Erreur lors de l\'ajout.'], 500);
        }

        // Récupérer le commentaire fraîchement inséré pour l'afficher
        $commentaires = $model->getParMemoire($idMemoire);
        $nouveau      = $commentaires[0]; // le plus récent (ORDER BY DESC)

        jsonResponse(true, [
            'message'        => 'Commentaire ajouté.',
            'id_commentaire' => $nouveau->id_commentaire,
            'contenu'        => $nouveau->contenu,
            'date'           => date('d/m/Y à H:i', strtotime($nouveau->date_commentaire)),
            'auteur'         => htmlspecialchars($nouveau->prenom . ' ' . $nouveau->nom),
            'role'           => $nouveau->role,
            'total'          => $model->compter($idMemoire),
        ]);
        break;

    // -------------------------------------------------------
    // ACTION : supprimer un commentaire
    // -------------------------------------------------------
    case 'supprimer_commentaire':
        $idCommentaire = (int)($input['id_commentaire'] ?? 0);
        if ($idCommentaire <= 0) {
            jsonResponse(false, ['message' => 'Commentaire invalide.'], 400);
        }

        $model = new Commentaire($connexion);
        $ok    = $model->supprimer($idCommentaire, $idUser, $role);

        if (!$ok) {
            jsonResponse(false, ['message' => 'Suppression impossible ou non autorisée.'], 403);
        }

        jsonResponse(true, [
            'message' => 'Commentaire supprimé.',
            'total'   => $model->compter($idMemoire),
        ]);
        break;

    // -------------------------------------------------------
    // ACTION : basculer un like
    // -------------------------------------------------------
    case 'toggle_like':
        $model    = new Like($connexion);
        $resultat = $model->basculer($idMemoire, $idUser);

        jsonResponse(true, [
            'action'  => $resultat['action'],
            'total'   => $resultat['total'],
            'message' => $resultat['action'] === 'like' ? 'Mémoire liké !' : 'Like retiré.',
        ]);
        break;

    // -------------------------------------------------------
    // ACTION inconnue
    // -------------------------------------------------------
    default:
        jsonResponse(false, ['message' => 'Action non reconnue.'], 400);
}
