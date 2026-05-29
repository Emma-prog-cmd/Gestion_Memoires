<?php
/**
 * models/DepotMemoire.php
 * Gestion du dépôt de mémoire par un étudiant diplômé.
 * Table utilisée : memoire
 */
require_once("../config/connexion.php");

class DepotMemoire {

    /**
     * Insère un nouveau mémoire en base avec le statut 'en_attente'.
     */
    public function deposerMemoire(
        $theme,
        $filiere,
        $auteur,
        $promotion,
        $annee_academique,
        $resume,
        $fichier_pdf,
        $id_etudiant
    ) {
        global $connexion;

        $sql = "INSERT INTO memoire (
                    theme,
                    filiere,
                    auteur,
                    promotion,
                    annee_academique,
                    resume,
                    fichier_pdf,
                    statut,
                    id_etudiant
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, 'en_attente', ?)";

        $stmt = $connexion->prepare($sql);

        $stmt->execute([
            $theme,
            $filiere,
            $auteur,
            $promotion,
            $annee_academique,
            $resume,
            $fichier_pdf,
            $id_etudiant
        ]);
    }

    /**
     * Récupère tous les mémoires déposés par un étudiant donné.
     */
    public function getMemoiresParEtudiant(int $id_etudiant): array {

        global $connexion;

        $sql = "SELECT id_memoire, theme, filiere, annee_academique,
                       statut, date_soumission
                FROM memoire
                WHERE id_etudiant = ?
                ORDER BY date_soumission DESC";

        $stmt = $connexion->prepare($sql);
        $stmt->execute([$id_etudiant]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
