<?php
require_once("../config/connexion.php");

class Validation{

    public function validerMemoire(
        $idMemoire,
        $idProfesseur,
        $decision,
        $commentaire
    ){

        global $connexion;

        // insertion validation
        $sql = "INSERT INTO validation(
                id_memoire,
                id_professeur,
                decision,
                commentaire
            )
            VALUES(?,?,?,?)";

        $stmt = $connexion->prepare($sql);
        $stmt->execute([
            $idMemoire,
            $idProfesseur,
            $decision,
            $commentaire
        ]);

        // mise à jour statut mémoire
        $sql2 = "UPDATE memoire
                 SET statut=?
                 WHERE id_memoire=?";

        $stmt2 = $connexion->prepare($sql2);
        $stmt2->execute([
            $decision,
            $idMemoire
        ]);
    }
}
?>