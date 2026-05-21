<?php
require_once("../models/AncienMemoire.php");

if(isset($_POST['theme'])){

    $theme = $_POST['theme'];
    $auteur = $_POST['auteur'];
    $filiere = $_POST['filiere'];
    $promotion = $_POST['promotion'];

    $fichier = $_FILES['fichier']['name'];
    $tmp = $_FILES['fichier']['tmp_name'];

    $extension = strtolower(pathinfo($fichier, PATHINFO_EXTENSION));
    $extensions_ok = ['pdf', 'doc', 'docx'];

    if (!in_array($extension, $extensions_ok)) {
    die("Erreur : seulement les fichiers PDF et Word sont acceptés.");
    }
    move_uploaded_file(
        $tmp,
        "../uploads/".$fichier
    );

    $ancien = new AncienMemoire();

    $ancien->ajouterAncienMemoire(
        $theme,
        $auteur,
        $filiere,
        $promotion,
        $fichier
    );

    header("Location: ../views/de/upload_anciens_memoires.php");
}
?>