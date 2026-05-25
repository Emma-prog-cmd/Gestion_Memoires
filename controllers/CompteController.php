<?php
/**
 * Contrôleur CompteController — Gestion Mémoires UATM GASA FORMATION
 * Couche : CONTRÔLEUR — Gestion des comptes (admin uniquement).
 * Auteur : Gaïus_Ahs (Vital-Ahs)
 */
require_once __DIR__ . '/../config/connexion.php';
require_once __DIR__ . '/../config/auth_config.php';
require_once __DIR__ . '/../models/Utilisateur.php';
require_once __DIR__ . '/../models/Filiere.php';
require_once __DIR__ . '/../services/CompteService.php';

class CompteController
{
    private CompteService $svc;

    public function __construct() {
        $this->svc = new CompteService();
        if (!isset($_SESSION['user_id']) ||
            $_SESSION['user_role'] !== 'administrateur') {
            header('Location: ' . BASE_URL . '/views/auth/login.php'); exit;
        }
    }

    /* ── INDEX — liste tous les comptes ─────────────────── */
    public function index(): void {
        $data         = $this->svc->listerTous();
        $utilisateurs = $data['utilisateurs'];
        $stats        = $data['stats'];
        $total        = $data['total'];
        require_once __DIR__ . '/../views/admin/utilisateurs.php';
    }

    /* ── CRÉER ──────────────────────────────────────────── */
    public function creer(): void {
        $erreurs = []; $erreur = ''; $succes = '';
        $filieres = Filiere::findAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $r = $this->svc->creerCompte($_POST);
            if ($r['succes']) {
                $_SESSION['flash'] = $r;
                header('Location: ' . BASE_URL . '/views/admin/utilisateurs.php'); exit;
            }
            $erreurs = $r['erreurs']; $erreur = $r['message'];
        }
        require_once __DIR__ . '/../views/admin/creer_compte.php';
    }

    /* ── MODIFIER ───────────────────────────────────────── */
    public function modifier(int $id): void {
        $utilisateur = Utilisateur::findById($id);
        if (!$utilisateur) {
            $_SESSION['flash'] = ['succes'=>false,'message'=>'Utilisateur introuvable.'];
            header('Location: ' . BASE_URL . '/views/admin/utilisateurs.php'); exit;
        }
        $erreurs = []; $erreur = ''; $succes = '';
        $filieres = Filiere::findAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $r = $this->svc->modifierCompte($id, $_POST);
            if ($r['succes']) {
                $_SESSION['flash'] = $r;
                header('Location: ' . BASE_URL . '/views/admin/utilisateurs.php'); exit;
            }
            $erreurs = $r['erreurs']; $erreur = $r['message'];
            $utilisateur = Utilisateur::findById($id);
        }
        require_once __DIR__ . '/../views/admin/modifier_compte.php';
    }

    /* ── TOGGLE ACTIF ───────────────────────────────────── */
    public function toggleActif(int $id): void {
        $_SESSION['flash'] = $this->svc->toggleActif($id, (int)$_SESSION['user_id']);
        header('Location: ' . BASE_URL . '/views/admin/utilisateurs.php'); exit;
    }
}

/* ── EXÉCUTION DIRECTE ─────────────────────────────────────── */
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    $ctrl   = new CompteController();
    $action = $_GET['action'] ?? $_POST['action'] ?? 'index';
    $id     = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
    match ($action) {
        'creer'       => $ctrl->creer(),
        'modifier'    => $ctrl->modifier($id),
        'toggleActif' => $ctrl->toggleActif($id),
        default       => $ctrl->index(),
    };
}
