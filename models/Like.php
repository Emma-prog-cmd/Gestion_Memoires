<?php
/**
 * models/Like.php
 */
class Like {

    private PDO $db;

    public function __construct(PDO $connexion) {
        $this->db = $connexion;
    }

    public function basculer(int $idMemoire, int $idUtilisateur): array {
        if ($this->aDejaLike($idMemoire, $idUtilisateur)) {
            $stmt = $this->db->prepare("
                DELETE FROM likes
                WHERE id_memoire = :id_memoire AND id_utilisateur = :id_utilisateur
            ");
            $stmt->execute([
                ':id_memoire'     => $idMemoire,
                ':id_utilisateur' => $idUtilisateur,
            ]);
            $action = 'unlike';
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO likes (id_utilisateur, id_memoire)
                VALUES (:id_utilisateur, :id_memoire)
            ");
            $stmt->execute([
                ':id_utilisateur' => $idUtilisateur,
                ':id_memoire'     => $idMemoire,
            ]);
            $action = 'like';
        }
        return [
            'action' => $action,
            'total'  => $this->compter($idMemoire),
        ];
    }

    public function aDejaLike(int $idMemoire, int $idUtilisateur): bool {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM likes
            WHERE id_memoire = :id_memoire AND id_utilisateur = :id_utilisateur
        ");
        $stmt->execute([
            ':id_memoire'     => $idMemoire,
            ':id_utilisateur' => $idUtilisateur,
        ]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function compter(int $idMemoire): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM likes WHERE id_memoire = :id");
        $stmt->execute([':id' => $idMemoire]);
        return (int) $stmt->fetchColumn();
    }
}
