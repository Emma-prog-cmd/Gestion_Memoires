<?php
/**
 * models/Memoire.php
 * Gestion de la recherche et consultation des mémoires.
 * Tables utilisées : memoire, anciens_memoires  (lecture seule)
 */
require_once(__DIR__ . "/../config/connexion.php");

class Memoire
{
    /**
     * Recherche dans les deux tables selon les filtres.
     * Chaque filtre est optionnel : ignoré s'il est vide.
     */
    public function rechercher(
        string $auteur           = '',
        string $filiere          = '',
        string $annee_academique = '',
        string $theme            = ''
    ): array {
        global $connexion;

        $params      = [];
        $whereM      = ["m.statut = 'valide'"];
        $whereAM     = [];

        if ($auteur !== '') {
            $whereM[]    = "m.auteur  LIKE ?";
            $whereAM[]   = "am.auteur LIKE ?";
            $params[]    = '%' . $auteur . '%';
            $params[]    = '%' . $auteur . '%';
        }
        if ($filiere !== '') {
            $whereM[]    = "m.filiere  = ?";
            $whereAM[]   = "am.filiere = ?";
            $params[]    = $filiere;
            $params[]    = $filiere;
        }
        if ($annee_academique !== '') {
            $whereM[]    = "m.annee_academique  = ?";
            $whereAM[]   = "am.annee_academique = ?";
            $params[]    = $annee_academique;
            $params[]    = $annee_academique;
        }
        if ($theme !== '') {
            $whereM[]    = "m.theme  LIKE ?";
            $whereAM[]   = "am.theme LIKE ?";
            $params[]    = '%' . $theme . '%';
            $params[]    = '%' . $theme . '%';
        }

        $sqlWhereM  = 'WHERE ' . implode(' AND ', $whereM);
        $sqlWhereAM = !empty($whereAM) ? 'WHERE ' . implode(' AND ', $whereAM) : '';

        $sql = "
            SELECT
                m.id_memoire        AS id,
                m.theme,
                m.auteur,
                m.filiere,
                m.annee_academique,
                m.resume,
                m.fichier_pdf,
                m.date_soumission   AS date_ref,
                'memoire'           AS source
            FROM memoire m
            {$sqlWhereM}

            UNION ALL

            SELECT
                am.id_ancien        AS id,
                am.theme,
                am.auteur,
                am.filiere,
                am.annee_academique,
                NULL                AS resume,
                am.fichier_pdf,
                am.date_import      AS date_ref,
                'ancien'            AS source
            FROM anciens_memoires am
            {$sqlWhereAM}

            ORDER BY date_ref DESC
        ";

        $stmt = $connexion->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Liste des filières pour le <select> */
    public function getFilieres(): array
    {
        global $connexion;
        $sql = "
            SELECT DISTINCT filiere FROM memoire
            WHERE filiere IS NOT NULL AND filiere <> '' AND statut = 'valide'
            UNION
            SELECT DISTINCT filiere FROM anciens_memoires
            WHERE filiere IS NOT NULL AND filiere <> ''
            ORDER BY filiere ASC
        ";
        return $connexion->query($sql)->fetchAll(PDO::FETCH_COLUMN);
    }

    /** Liste des années académiques pour le <select> */
    public function getAnneesAcademiques(): array
    {
        global $connexion;
        $sql = "
            SELECT DISTINCT annee_academique FROM memoire
            WHERE annee_academique IS NOT NULL AND annee_academique <> '' AND statut = 'valide'
            UNION
            SELECT DISTINCT annee_academique FROM anciens_memoires
            WHERE annee_academique IS NOT NULL AND annee_academique <> ''
            ORDER BY annee_academique DESC
        ";
        return $connexion->query($sql)->fetchAll(PDO::FETCH_COLUMN);
    }

    /** Récupère un mémoire validé par son id (table memoire) */
    public function getMemoireValide(int $id): ?array
    {
        global $connexion;
        $stmt = $connexion->prepare(
            "SELECT id_memoire AS id, theme, auteur, filiere,
                    annee_academique, resume, fichier_pdf, 'memoire' AS source
             FROM memoire
             WHERE id_memoire = ? AND statut = 'valide'"
        );
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /** Récupère un ancien mémoire par son id (table anciens_memoires) */
    public function getAncienMemoire(int $id): ?array
    {
        global $connexion;
        $stmt = $connexion->prepare(
            "SELECT id_ancien AS id, theme, auteur, filiere,
                    annee_academique, NULL AS resume, fichier_pdf, 'ancien' AS source
             FROM anciens_memoires
             WHERE id_ancien = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
