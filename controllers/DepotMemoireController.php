<?php

session_start();

/* ── Garde : connexion obligatoire ───────────────────────────────────── */
if (empty($_SESSION['id_utilisateur'])) {
    header("Location: ../views/auth/login.php");
    exit;
}

/* ── Garde : rôle étudiant_diplome uniquement ────────────────────────── */
if ($_SESSION['role'] !== 'etudiant_diplome') {
    die("Accès refusé : seuls les étudiants diplômés peuvent déposer un mémoire.");
}

require_once("../models/DepotMemoire.php");

if (isset($_POST['theme'])) {

    /* ── Récupération des champs texte ──────────────────────────────── */
    $theme            = trim($_POST['theme']);
    $filiere          = trim($_POST['filiere']);
    $auteur           = trim($_POST['auteur']);
    $promotion        = trim($_POST['promotion']);
    $annee_academique = trim($_POST['annee_academique']);
    $resume           = trim($_POST['resume']);
    $id_etudiant      = (int) $_SESSION['id_utilisateur'];

    /* ── Validation du fichier PDF ──────────────────────────────────── */
    $fichier    = $_FILES['fichier_pdf']['name'];
    $tmp        = $_FILES['fichier_pdf']['tmp_name'];
    $extension  = strtolower(pathinfo($fichier, PATHINFO_EXTENSION));

    if ($extension !== 'pdf') {
        die("Erreur : seuls les fichiers PDF sont acceptés.");
    }

    /* ── Nom unique pour éviter les collisions ──────────────────────── */
    $nom_fichier = time() . '_' . basename($fichier);

    move_uploaded_file($tmp, "../uploads/" . $nom_fichier);

    /* ── Insertion en base ──────────────────────────────────────────── */
    $depot = new DepotMemoire();

    $depot->deposerMemoire(
        $theme,
        $filiere,
        $auteur,
        $promotion,
        $annee_academique,
        $resume,
        $nom_fichier,
        $id_etudiant
    );

    header("Location: ../views/etudiant/depot_memoire.php?success=1");
    exit;
}
?>
