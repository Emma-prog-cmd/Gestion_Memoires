<?php
require_once("../config/connexion.php");

class AncienMemoire{

    public function ajouterAncienMemoire(
        $theme,
        $auteur,
        $filiere,
        $promotion,
        $fichier
    ){

        global $connexion;

        $sql = "INSERT INTO anciens_memoires(
                theme,
                auteur,
                filiere,
                promotion,
                fichier_pdf
            )
            VALUES(?,?,?,?,?)";

        $stmt = $connexion->prepare($sql);

        $stmt->execute([
            $theme,
            $auteur,
            $filiere,
            $promotion,
            $fichier
        ]);
    }
}
?>