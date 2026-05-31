<?php

session_start();

/* ── Garde : connexion obligatoire ─────────────────────────────────────── */
if (empty($_SESSION['id_utilisateur'])) {
    header("Location: ../views/auth/login.php");
    exit;
}

require_once(__DIR__ . "/../models/Memoire.php");

$action       = isset($_GET['action']) ? trim($_GET['action']) : '';
$memoireModel = new Memoire();

/* ══════════════════════════════════════════════════════════════════════════
   ACTION : consulter  →  sert le PDF inline (pas de téléchargement forcé)
══════════════════════════════════════════════════════════════════════════ */
if ($action === 'consulter') {

    $id     = isset($_GET['id'])     ? (int) $_GET['id']         : 0;
    $source = isset($_GET['source']) ? trim($_GET['source'])     : '';

    if ($source === 'memoire') {
        $doc = $memoireModel->getMemoireValide($id);
    } elseif ($source === 'ancien') {
        $doc = $memoireModel->getAncienMemoire($id);
    } else {
        $doc = null;
    }

    if (!$doc) {
        http_response_code(404);
        die("Mémoire introuvable ou non autorisé.");
    }

    /* Sécurité : basename() empêche toute traversée de répertoire */
    $fichier = basename($doc['fichier_pdf']);
    $chemin  = __DIR__ . "/../uploads/" . $fichier;

    if (!is_file($chemin)) {
        http_response_code(404);
        die("Fichier PDF introuvable sur le serveur. (chemin attendu : uploads/" . htmlspecialchars($fichier) . ")");
    }

    /* En-têtes qui forcent la LECTURE dans le navigateur */
    header("Content-Type: application/pdf");
    header("Content-Disposition: inline; filename=\"" . rawurlencode($fichier) . "\"");
    header("Content-Length: " . filesize($chemin));
    header("Cache-Control: private, max-age=3600");
    header("X-Content-Type-Options: nosniff");
    readfile($chemin);
    exit;
}

/* ══════════════════════════════════════════════════════════════════════════
   ACTION PAR DÉFAUT : recherche + affichage liste
══════════════════════════════════════════════════════════════════════════ */
$filtres = [
    'auteur'           => trim($_REQUEST['auteur']           ?? ''),
    'filiere'          => trim($_REQUEST['filiere']          ?? ''),
    'annee_academique' => trim($_REQUEST['annee_academique'] ?? ''),
    'theme'            => trim($_REQUEST['theme']            ?? ''),
];

$memoires   = $memoireModel->rechercher(
    $filtres['auteur'],
    $filtres['filiere'],
    $filtres['annee_academique'],
    $filtres['theme']
);
$filieres   = $memoireModel->getFilieres();
$anneesAcad = $memoireModel->getAnneesAcademiques();

/* Passer à la vue */
require_once(__DIR__ . "/../views/memoires/recherche_memoires.php");
