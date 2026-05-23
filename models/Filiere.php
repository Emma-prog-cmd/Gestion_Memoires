<?php
/**
 * Modèle Filiere — Gestion Mémoires UATM GASA FORMATION
 * Couche : PERSISTANCE (Active Record)
 * Auteur : Gaïus_Ahs (Vital-Ahs)
 */
require_once __DIR__ . '/../config/connexion.php';

class Filiere
{
    public ?int    $id_filiere  = null;
    public ?int    $id_ufr      = null;
    public string  $nom_filiere = '';
    public string  $niveau      = 'Licence';
    public ?string $nom_ufr     = null;
    public ?string $code_ufr    = null;

    public function __construct(array $data = []) {
        foreach ($data as $k => $v)
            if (property_exists($this, $k)) $this->$k = $v;
    }

    /* ── FIND ALL ───────────────────────────────────────── */
    public static function findAll(): array {
        global $connexion;
        $stmt = $connexion->query("
            SELECT f.*, u.nom_ufr, u.code_ufr
            FROM filiere f
            LEFT JOIN ufr u ON f.id_ufr = u.id_ufr
            ORDER BY u.code_ufr, f.nom_filiere, f.niveau
        ");
        return array_map(fn($r)=>new static($r), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /* ── FIND BY ID ─────────────────────────────────────── */
    public static function findById(int $id): ?static {
        global $connexion;
        $stmt = $connexion->prepare(
            "SELECT f.*,u.nom_ufr FROM filiere f
             LEFT JOIN ufr u ON f.id_ufr=u.id_ufr
             WHERE f.id_filiere=?"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new static($row) : null;
    }
}
