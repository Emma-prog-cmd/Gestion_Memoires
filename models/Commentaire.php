<?php
/**
 * Modèle Commentaire — models/Commentaire.php
 * Couche : DONNÉES
 * Gère toutes les opérations sur la table `commentaire`
 */
class Commentaire {

    private PDO $db;

    public function __construct(PDO $connexion) {
        $this->db = $connexion;
    }

    /**
     * Ajouter un commentaire
     */
    public function ajouter(int $idMemoire, int $idUtilisateur, string $contenu): bool {
        $contenu = trim($contenu);
        if (empty($contenu)) return false;

        $stmt = $this->db->prepare("
            INSERT INTO commentaire (contenu, id_utilisateur, id_memoire)
            VALUES (:contenu, :id_utilisateur, :id_memoire)
        ");
        return $stmt->execute([
            ':contenu'        => htmlspecialchars($contenu, ENT_QUOTES, 'UTF-8'),
            ':id_utilisateur' => $idUtilisateur,
            ':id_memoire'     => $idMemoire,
        ]);
    }

    /**
     * Supprimer un commentaire (uniquement par son auteur ou un admin)
     */
    public function supprimer(int $idCommentaire, int $idUtilisateur, string $role): bool {
        // Un admin peut supprimer n'importe quel commentaire
        if (in_array($role, ['administrateur', 'directeur_etude'])) {
            $stmt = $this->db->prepare("DELETE FROM commentaire WHERE id_commentaire = :id");
            return $stmt->execute([':id' => $idCommentaire]);
        }
        // Un utilisateur normal supprime uniquement le sien
        $stmt = $this->db->prepare("
            DELETE FROM commentaire
            WHERE id_commentaire = :id AND id_utilisateur = :id_user
        ");
        return $stmt->execute([
            ':id'      => $idCommentaire,
            ':id_user' => $idUtilisateur,
        ]);
    }

    /**
     * Récupérer tous les commentaires d'un mémoire avec les infos auteur
     */
    public function getParMemoire(int $idMemoire): array {
        $stmt = $this->db->prepare("
            SELECT
                c.id_commentaire,
                c.contenu,
                c.date_commentaire,
                c.id_utilisateur,
                u.nom,
                u.prenom,
                u.role
            FROM commentaire c
            INNER JOIN utilisateur u ON c.id_utilisateur = u.id_utilisateur
            WHERE c.id_memoire = :id_memoire
            ORDER BY c.date_commentaire DESC
        ");
        $stmt->execute([':id_memoire' => $idMemoire]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Compter les commentaires d'un mémoire
     */
    public function compter(int $idMemoire): int {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM commentaire WHERE id_memoire = :id
        ");
        $stmt->execute([':id' => $idMemoire]);
        return (int) $stmt->fetchColumn();
    }
}
