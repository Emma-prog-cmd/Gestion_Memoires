<?php
/**
 * Contrôleur AuthController — Gestion Mémoires UATM GASA FORMATION
 * Couche : CONTRÔLEUR — Aucun SQL, aucune logique métier.
 * Auteur : Gaïus_Ahs (Vital-Ahs)
 */
require_once __DIR__ . '/../config/connexion.php';
require_once __DIR__ . '/../config/auth_config.php';
require_once __DIR__ . '/../models/Utilisateur.php';
require_once __DIR__ . '/../models/Filiere.php';
require_once __DIR__ . '/../services/AuthService.php';

class AuthController
{
    private AuthService $svc;

    public function __construct() {
        $this->svc = new AuthService();
    }

    public function login(): void {
        if (isset($_SESSION['user_id'])) { $this->redirectParRole(); return; }
        $erreur = '';
        $succes = $_SESSION['flash_ok'] ?? '';
        unset($_SESSION['flash_ok']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $r = $this->svc->connecter(
                $_POST['email'] ?? '',
                $_POST['mot_de_passe'] ?? ''
            );
            if ($r['succes']) { $this->redirectParRole(); return; }
            $erreur = $r['message'];
        }
        require_once __DIR__ . '/../views/auth/login.php';
    }

    public function register(): void {
        if (isset($_SESSION['user_id'])) { $this->redirectParRole(); return; }
        $erreurs = []; $erreur = ''; $succes = '';
        $filieres = Filiere::findAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $r = $this->svc->inscrire($_POST);
            if ($r['succes']) {
                $_SESSION['flash_ok'] = $r['message'];
                header('Location: ' . BASE_URL . '/views/auth/login.php'); exit;
            }
            $erreurs = $r['erreurs']; $erreur = $r['message'];
        }
        require_once __DIR__ . '/../views/auth/register.php';
    }

    public function logout(): void {
        $this->svc->deconnecter();
        header('Location: ' . BASE_URL . '/views/auth/login.php'); exit;
    }

    public function profil(): void {
        $this->auth();
        $erreurs = []; $erreur = ''; $succes = '';
        $utilisateur = Utilisateur::findById((int)$_SESSION['user_id']);
        $filieres    = Filiere::findAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $r = $this->svc->modifierProfil((int)$_SESSION['user_id'], $_POST);
            if ($r['succes']) {
                $succes = $r['message'];
                $utilisateur = Utilisateur::findById((int)$_SESSION['user_id']);
            } else {
                $erreurs = $r['erreurs']; $erreur = $r['message'];
            }
        }
        require_once __DIR__ . '/../views/auth/profil.php';
    }

    public function changerMdp(): void {
        $this->auth();
        $erreurs = []; $erreur = ''; $succes = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $r = $this->svc->changerMotDePasse((int)$_SESSION['user_id'], $_POST);
            if ($r['succes']) $succes = $r['message'];
            else { $erreurs = $r['erreurs']; $erreur = $r['message']; }
        }
        require_once __DIR__ . '/../views/auth/changer_mdp.php';
    }

    private function auth(): void {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/views/auth/login.php'); exit;
        }
    }

    private function redirectParRole(): void {
        $dest = match ($_SESSION['user_role'] ?? '') {
            ROLE_PROFESSEUR      => BASE_URL . '/views/professeur/validation_memoire.php',
            ROLE_DIRECTEUR_ETUDE => BASE_URL . '/views/de/upload_anciens_memoires.php',
            ROLE_ADMINISTRATEUR  => BASE_URL . '/views/admin/utilisateurs.php',
            default              => BASE_URL . '/views/auth/profil.php',
        };
        header('Location: ' . $dest); exit;
    }
}

// Exécution directe via URL
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    $ctrl   = new AuthController();
    $action = $_GET['action'] ?? $_POST['action'] ?? 'login';
    match ($action) {
        'register'    => $ctrl->register(),
        'logout'      => $ctrl->logout(),
        'profil'      => $ctrl->profil(),
        'changer_mdp' => $ctrl->changerMdp(),
        default       => $ctrl->login(),
    };
}
