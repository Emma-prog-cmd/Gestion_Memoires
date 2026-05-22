<?php
/**
 * ============================================================
 *  Tests PHPUnit — Module Authentification & Gestion Comptes
 *  GéMémoires — UATM GASA FORMATION
 *
 *  Exécution : ./vendor/bin/phpunit tests/AuthModuleTest.php --testdox
 * ============================================================
 */

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/app/entities/Utilisateur.php';
require_once dirname(__DIR__) . '/app/services/AuthService.php';
require_once dirname(__DIR__) . '/app/services/CompteService.php';

// ════════════════════════════════════════════════════════════
//  TESTS AUTHSERVICE
// ════════════════════════════════════════════════════════════

class AuthServiceTest extends TestCase
{
    private AuthService $service;

    protected function setUp(): void
    {
        $this->service = new AuthService();
    }

    // ── Inscription ──────────────────────────────────────────

    /** @test */
    public function inscription_avec_donnees_valides_cree_le_compte(): void
    {
        $donnees = [
            'nom'           => 'KOSSOU',
            'prenom'        => 'Yao',
            'email'         => 'test_' . uniqid() . '@uatm.bj',
            'mot_de_passe'  => 'Test1234',
            'confirmer_mdp' => 'Test1234',
            'id_filiere'    => 7,
        ];
        $r = $this->service->inscrire($donnees);
        $this->assertTrue($r['succes'], "Inscription valide devrait réussir. Message : " . $r['message']);
        $this->assertEmpty($r['erreurs']);
    }

    /** @test */
    public function inscription_nom_vide_retourne_erreur(): void
    {
        $r = $this->service->inscrire([
            'nom' => '', 'prenom' => 'Kofi',
            'email' => 'kofi@test.bj', 'mot_de_passe' => 'Test1234',
            'confirmer_mdp' => 'Test1234', 'id_filiere' => 1,
        ]);
        $this->assertFalse($r['succes']);
        $this->assertArrayHasKey('nom', $r['erreurs']);
    }

    /** @test */
    public function inscription_email_invalide_retourne_erreur(): void
    {
        $r = $this->service->inscrire([
            'nom' => 'Test', 'prenom' => 'User',
            'email' => 'pas_un_email', 'mot_de_passe' => 'Test1234',
            'confirmer_mdp' => 'Test1234', 'id_filiere' => 1,
        ]);
        $this->assertFalse($r['succes']);
        $this->assertArrayHasKey('email', $r['erreurs']);
    }

    /** @test */
    public function inscription_mdp_trop_court_retourne_erreur(): void
    {
        $r = $this->service->inscrire([
            'nom' => 'Test', 'prenom' => 'User',
            'email' => 'valid@test.bj', 'mot_de_passe' => '123',
            'confirmer_mdp' => '123', 'id_filiere' => 1,
        ]);
        $this->assertFalse($r['succes']);
        $this->assertArrayHasKey('mot_de_passe', $r['erreurs']);
    }

    /** @test */
    public function inscription_mdp_sans_chiffre_retourne_erreur(): void
    {
        $r = $this->service->inscrire([
            'nom' => 'Test', 'prenom' => 'User',
            'email' => 'valid2@test.bj', 'mot_de_passe' => 'MotDePasse',
            'confirmer_mdp' => 'MotDePasse', 'id_filiere' => 1,
        ]);
        $this->assertFalse($r['succes']);
        $this->assertArrayHasKey('mot_de_passe', $r['erreurs']);
    }

    /** @test */
    public function inscription_confirmation_mdp_differente_retourne_erreur(): void
    {
        $r = $this->service->inscrire([
            'nom' => 'Test', 'prenom' => 'User',
            'email' => 'valid3@test.bj', 'mot_de_passe' => 'Test1234',
            'confirmer_mdp' => 'Autre5678', 'id_filiere' => 1,
        ]);
        $this->assertFalse($r['succes']);
        $this->assertArrayHasKey('confirmer_mdp', $r['erreurs']);
    }

    /** @test */
    public function inscription_sans_filiere_retourne_erreur(): void
    {
        $r = $this->service->inscrire([
            'nom' => 'Test', 'prenom' => 'User',
            'email' => 'valid4@test.bj', 'mot_de_passe' => 'Test1234',
            'confirmer_mdp' => 'Test1234', 'id_filiere' => '',
        ]);
        $this->assertFalse($r['succes']);
        $this->assertArrayHasKey('id_filiere', $r['erreurs']);
    }

    // ── Connexion ────────────────────────────────────────────

    /** @test */
    public function connexion_champs_vides_retourne_erreur(): void
    {
        $r = $this->service->connecter('', '');
        $this->assertFalse($r['succes']);
        $this->assertStringContainsString('requis', $r['message']);
        $this->assertNull($r['utilisateur']);
    }

    /** @test */
    public function connexion_email_invalide_retourne_erreur(): void
    {
        $r = $this->service->connecter('pas_un_email', 'monmdp');
        $this->assertFalse($r['succes']);
        $this->assertStringContainsString('invalide', $r['message']);
    }

    /** @test */
    public function connexion_utilisateur_inexistant_retourne_erreur(): void
    {
        $r = $this->service->connecter('fantome_' . uniqid() . '@inexistant.bj', 'mdpTest1');
        $this->assertFalse($r['succes']);
        $this->assertNull($r['utilisateur']);
    }

    // ── Modifier profil ──────────────────────────────────────

    /** @test */
    public function modifier_profil_email_invalide_retourne_erreur(): void
    {
        $r = $this->service->modifierProfil(1, [
            'nom' => 'Test', 'prenom' => 'User', 'email' => 'mauvais',
        ]);
        $this->assertFalse($r['succes']);
        $this->assertArrayHasKey('email', $r['erreurs']);
    }

    /** @test */
    public function modifier_profil_nom_vide_retourne_erreur(): void
    {
        $r = $this->service->modifierProfil(1, [
            'nom' => '', 'prenom' => 'User', 'email' => 'ok@test.bj',
        ]);
        $this->assertFalse($r['succes']);
        $this->assertArrayHasKey('nom', $r['erreurs']);
    }

    // ── Changer mot de passe ─────────────────────────────────

    /** @test */
    public function changer_mdp_champs_vides_retourne_erreur(): void
    {
        $r = $this->service->changerMotDePasse(1, [
            'ancien_mdp' => '', 'nouveau_mdp' => '', 'confirmer_mdp' => '',
        ]);
        $this->assertFalse($r['succes']);
        $this->assertArrayHasKey('ancien_mdp', $r['erreurs']);
    }

    /** @test */
    public function changer_mdp_nouveau_trop_court_retourne_erreur(): void
    {
        $r = $this->service->changerMotDePasse(1, [
            'ancien_mdp' => 'OldPass1', 'nouveau_mdp' => '123', 'confirmer_mdp' => '123',
        ]);
        $this->assertFalse($r['succes']);
        $this->assertArrayHasKey('nouveau_mdp', $r['erreurs']);
    }

    /** @test */
    public function changer_mdp_confirmation_differente_retourne_erreur(): void
    {
        $r = $this->service->changerMotDePasse(1, [
            'ancien_mdp' => 'OldPass1', 'nouveau_mdp' => 'NewPass1', 'confirmer_mdp' => 'Different',
        ]);
        $this->assertFalse($r['succes']);
        $this->assertArrayHasKey('confirmer_mdp', $r['erreurs']);
    }
}

// ════════════════════════════════════════════════════════════
//  TESTS COMPTESERVICE
// ════════════════════════════════════════════════════════════

class CompteServiceTest extends TestCase
{
    private CompteService $service;

    protected function setUp(): void
    {
        $this->service = new CompteService();
    }

    /** @test */
    public function creer_compte_avec_donnees_valides_reussit(): void
    {
        $r = $this->service->creerCompte([
            'nom'           => 'PROF',
            'prenom'        => 'Nouveau',
            'email'         => 'prof_' . uniqid() . '@uatm.bj',
            'mot_de_passe'  => 'Prof1234',
            'confirmer_mdp' => 'Prof1234',
            'role'          => ROLE_PROFESSEUR,
            'id_filiere'    => '',
        ]);
        $this->assertTrue($r['succes'], $r['message']);
    }

    /** @test */
    public function creer_compte_role_invalide_retourne_erreur(): void
    {
        $r = $this->service->creerCompte([
            'nom' => 'Test', 'prenom' => 'User',
            'email' => 'test@test.bj', 'mot_de_passe' => 'Test1234',
            'confirmer_mdp' => 'Test1234', 'role' => 'super_admin',
        ]);
        $this->assertFalse($r['succes']);
        $this->assertArrayHasKey('role', $r['erreurs']);
    }

    /** @test */
    public function toggle_actif_propre_compte_retourne_erreur(): void
    {
        $r = $this->service->toggleActif(1, 1); // L'admin essaie de se désactiver lui-même
        $this->assertFalse($r['succes']);
        $this->assertStringContainsString('propre compte', $r['message']);
    }

    /** @test */
    public function toggle_actif_utilisateur_inexistant_retourne_erreur(): void
    {
        $r = $this->service->toggleActif(99999, 1);
        $this->assertFalse($r['succes']);
        $this->assertStringContainsString('introuvable', $r['message']);
    }
}

// ════════════════════════════════════════════════════════════
//  TESTS ENTITÉ UTILISATEUR
// ════════════════════════════════════════════════════════════

class UtilisateurEntiteTest extends TestCase
{
    /** @test */
    public function constructeur_assigne_les_attributs_correctement(): void
    {
        $u = new Utilisateur([
            'nom'    => 'GBEDEVI',
            'prenom' => 'Adjovi',
            'email'  => 'adjovi@uatm.bj',
            'role'   => ROLE_ETUDIANT,
        ]);
        $this->assertEquals('GBEDEVI', $u->nom);
        $this->assertEquals('Adjovi', $u->prenom);
        $this->assertEquals('adjovi@uatm.bj', $u->email);
        $this->assertEquals(ROLE_ETUDIANT, $u->role);
    }

    /** @test */
    public function get_nom_complet_retourne_prenom_et_nom(): void
    {
        $u = new Utilisateur(['nom' => 'DOSSOU', 'prenom' => 'Brice']);
        $this->assertEquals('Brice DOSSOU', $u->getNomComplet());
    }

    /** @test */
    public function est_actif_est_vrai_par_defaut(): void
    {
        $u = new Utilisateur();
        $this->assertTrue($u->estActif());
    }

    /** @test */
    public function role_par_defaut_est_etudiant(): void
    {
        $u = new Utilisateur();
        $this->assertEquals(ROLE_ETUDIANT, $u->role);
    }

    /** @test */
    public function est_admin_retourne_faux_pour_etudiant(): void
    {
        $u = new Utilisateur(['role' => ROLE_ETUDIANT]);
        $this->assertFalse($u->estAdmin());
    }

    /** @test */
    public function est_admin_retourne_vrai_pour_administrateur(): void
    {
        $u = new Utilisateur(['role' => ROLE_ADMINISTRATEUR]);
        $this->assertTrue($u->estAdmin());
    }

    /** @test */
    public function attribut_inconnu_nest_pas_assigne(): void
    {
        $u = new Utilisateur(['champ_inconnu' => 'valeur', 'nom' => 'TEST']);
        $this->assertEquals('TEST', $u->nom);
        $this->assertFalse(property_exists($u, 'champ_inconnu'));
    }
}
