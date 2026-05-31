<?php
/**
 * controllers/CommentaireLikeController.php
 * Reçoit les requêtes AJAX et retourne du JSON
 * Pas de VIEW_PATH, BASE_URL ou autre constante externe
 */

session_start();

require_once __DIR__ . '/../config/connexion.php';
require_once __DIR__ . '/../models/Commentaire.php';
require_once __DIR__ . '/../models/Like.php';

function jsonReponse(bool $succes, array $data = [], int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge(['succes' => $succes], $data));
    exit;
}

if (empty($_SESSION['id_utilisateur'])) {
    jsonReponse(false, ['message' => 'Vous devez être connecté.'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonReponse(false, ['message' => 'Méthode non autorisée.'], 405);
}

$input     = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$action    = $input['action']    ?? '';
$idMemoire = (int)($input['id_memoire'] ?? 0);
$idUser    = (int)$_SESSION['id_utilisateur'];
$role      = $_SESSION['role'] ?? '';

if ($idMemoire <= 0) {
    jsonReponse(false, ['message' => 'Mémoire invalide.'], 400);
}

switch ($action) {

    case 'ajouter_commentaire':
        $contenu = trim($input['contenu'] ?? '');
        if (empty($contenu)) {
            jsonReponse(false, ['message' => 'Le commentaire ne peut pas être vide.'], 422);
        }
        if (mb_strlen($contenu) > 1000) {
            jsonReponse(false, ['message' => 'Maximum 1000 caractères.'], 422);
        }
        $model = new Commentaire($connexion);
        $ok    = $model->ajouter($idMemoire, $idUser, $contenu);
        if (!$ok) {
            jsonReponse(false, ['message' => 'Erreur lors de l\'ajout.'], 500);
        }
        $liste   = $model->getParMemoire($idMemoire);
        $nouveau = $liste[0];
        jsonReponse(true, [
            'message'        => 'Commentaire ajouté.',
            'id_commentaire' => $nouveau->id_commentaire,
            'contenu'        => $nouveau->contenu,
            'date'           => date('d/m/Y à H:i', strtotime($nouveau->date_commentaire)),
            'auteur'         => htmlspecialchars($nouveau->prenom . ' ' . $nouveau->nom),
            'role'           => $nouveau->role,
            'total'          => $model->compter($idMemoire),
        ]);
        break;

    case 'supprimer_commentaire':
        $idCommentaire = (int)($input['id_commentaire'] ?? 0);
        if ($idCommentaire <= 0) {
            jsonReponse(false, ['message' => 'Commentaire invalide.'], 400);
        }
        $model = new Commentaire($connexion);
        $ok    = $model->supprimer($idCommentaire, $idUser, $role);
        if (!$ok) {
            jsonReponse(false, ['message' => 'Suppression impossible ou non autorisée.'], 403);
        }
        jsonReponse(true, [
            'message' => 'Commentaire supprimé.',
            'total'   => $model->compter($idMemoire),
        ]);
        break;

    case 'toggle_like':
        $model    = new Like($connexion);
        $resultat = $model->basculer($idMemoire, $idUser);
        jsonReponse(true, [
            'action'  => $resultat['action'],
            'total'   => $resultat['total'],
            'message' => $resultat['action'] === 'like' ? 'Mémoire liké !' : 'Like retiré.',
        ]);
        break;

    default:
        jsonReponse(false, ['message' => 'Action inconnue.'], 400);
}
