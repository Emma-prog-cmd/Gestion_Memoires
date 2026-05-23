<?php
/**
 * Modèle Utilisateur — Gestion Mémoires UATM GASA FORMATION
 * Couche : PERSISTANCE (Active Record)
 * Contient TOUT le SQL. Convention : global $connexion
 * Auteur : Gaïus_Ahs (Vital-Ahs)
 */
require_once __DIR__ . '/../config/connexion.php';

class Utilisateur
{
    public ?int    $id_user            = null;
    public ?int    $id_filiere         = null;
    public string  $nom                = '';
    public string  $prenom             = '';
    public string  $email              = '';
    public string  $mot_de_passe       = '';
    public string  $role               = 'etudiant';
    public int     $actif              = 1;
    public ?string $date_inscription   = null;
    public ?string $derniere_connexion = null;
    public ?string $nom_filiere        = null;

    public function __construct(array $data = []) {
        foreach ($data as $k => $v)
            if (property_exists($this, $k)) $this->$k = $v;
    }

    /* ── INSERT ─────────────────────────────────────────── */
    public function insert(): bool {
        global $connexion;
        $stmt = $connexion->prepare("
            INSERT INTO utilisateur
                (id_filiere,nom,prenom,email,mot_de_passe,role,actif)
            VALUES (?,?,?,?,?,?,?)
        ");
        $ok = $stmt->execute([
            $this->id_filiere, $this->nom, $this->prenom,
            $this->email, $this->mot_de_passe, $this->role, $this->actif
        ]);
        if ($ok) $this->id_user = (int) $connexion->lastInsertId();
        return $ok;
    }

    /* ── UPDATE PROFIL ──────────────────────────────────── */
    public function update(): bool {
        global $connexion;
        $stmt = $connexion->prepare("
            UPDATE utilisateur SET nom=?,prenom=?,email=?,id_filiere=?
            WHERE id_user=?
        ");
        return $stmt->execute([
            $this->nom, $this->prenom, $this->email,
            $this->id_filiere, $this->id_user
        ]);
    }

    /* ── UPDATE MOT DE PASSE ────────────────────────────── */
    public function updateMotDePasse(string $hash): bool {
        global $connexion;
        $stmt = $connexion->prepare(
            "UPDATE utilisateur SET mot_de_passe=? WHERE id_user=?"
        );
        $ok = $stmt->execute([$hash, $this->id_user]);
        if ($ok) $this->mot_de_passe = $hash;
        return $ok;
    }

    /* ── UPDATE ROLE ────────────────────────────────────── */
    public function updateRole(string $role): bool {
        global $connexion;
        $roles = ['etudiant','professeur','directeur_etudes','administrateur'];
        if (!in_array($role, $roles)) return false;
        $stmt = $connexion->prepare(
            "UPDATE utilisateur SET role=? WHERE id_user=?"
        );
        $ok = $stmt->execute([$role, $this->id_user]);
        if ($ok) $this->role = $role;
        return $ok;
    }

    /* ── TOGGLE ACTIF ───────────────────────────────────── */
    public function toggleActif(): bool {
        global $connexion;
        $nouvel = $this->actif ? 0 : 1;
        $stmt   = $connexion->prepare(
            "UPDATE utilisateur SET actif=? WHERE id_user=?"
        );
        $ok = $stmt->execute([$nouvel, $this->id_user]);
        if ($ok) $this->actif = $nouvel;
        return $ok;
    }

    /* ── DELETE (logique) ───────────────────────────────── */
    public function delete(): bool {
        global $connexion;
        return $connexion->prepare(
            "UPDATE utilisateur SET actif=0 WHERE id_user=?"
        )->execute([$this->id_user]);
    }

    /* ── MAJ CONNEXION ──────────────────────────────────── */
    public function mettreAJourConnexion(): void {
        global $connexion;
        $connexion->prepare(
            "UPDATE utilisateur SET derniere_connexion=NOW() WHERE id_user=?"
        )->execute([$this->id_user]);
    }

    /* ── FIND BY ID ─────────────────────────────────────── */
    public static function findById(int $id): ?static {
        global $connexion;
        $stmt = $connexion->prepare("
            SELECT u.*,f.nom_filiere FROM utilisateur u
            LEFT JOIN filiere f ON u.id_filiere=f.id_filiere
            WHERE u.id_user=?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new static($row) : null;
    }

    /* ── FIND BY EMAIL ──────────────────────────────────── */
    public static function findByEmail(string $email): ?static {
        global $connexion;
        $stmt = $connexion->prepare("
            SELECT u.*,f.nom_filiere FROM utilisateur u
            LEFT JOIN filiere f ON u.id_filiere=f.id_filiere
            WHERE u.email=?
        ");
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new static($row) : null;
    }

    /* ── FIND ALL ───────────────────────────────────────── */
    public static function findAll(): array {
        global $connexion;
        $stmt = $connexion->query("
            SELECT u.*,f.nom_filiere FROM utilisateur u
            LEFT JOIN filiere f ON u.id_filiere=f.id_filiere
            ORDER BY u.nom,u.prenom
        ");
        return array_map(fn($r)=>new static($r), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /* ── FIND BY ROLE ───────────────────────────────────── */
    public static function findByRole(string $role): array {
        global $connexion;
        $stmt = $connexion->prepare("
            SELECT u.*,f.nom_filiere FROM utilisateur u
            LEFT JOIN filiere f ON u.id_filiere=f.id_filiere
            WHERE u.role=? AND u.actif=1 ORDER BY u.nom
        ");
        $stmt->execute([$role]);
        return array_map(fn($r)=>new static($r), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /* ── EMAIL EXISTE ───────────────────────────────────── */
    public static function emailExiste(string $email, int $exclu=0): bool {
        global $connexion;
        $stmt = $connexion->prepare(
            "SELECT COUNT(*) FROM utilisateur WHERE email=? AND id_user!=?"
        );
        $stmt->execute([$email, $exclu]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /* ── COMPTER PAR ROLE ───────────────────────────────── */
    public static function compterParRole(): array {
        global $connexion;
        $rows = $connexion->query(
            "SELECT role,COUNT(*) AS n FROM utilisateur GROUP BY role"
        )->fetchAll(PDO::FETCH_ASSOC);
        $r=[];
        foreach($rows as $row) $r[$row['role']]=(int)$row['n'];
        return $r;
    }

    /* ── HELPERS ────────────────────────────────────────── */
    public function getNomComplet(): string { return trim($this->prenom.' '.$this->nom); }
    public function estActif(): bool        { return (bool)$this->actif; }
    public function estAdmin(): bool        { return $this->role==='administrateur'; }
}
