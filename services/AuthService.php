<?php
/**
 * Service AuthService — Gestion Mémoires UATM GASA FORMATION
 * Couche : MÉTIER — Toute la logique d'authentification.
 * Pas de SQL ici. Délègue à l'entité Utilisateur.
 * Auteur : Gaïus_Ahs (Vital-Ahs)
 */
require_once __DIR__ . '/../config/connexion.php';
require_once __DIR__ . '/../config/auth_config.php';
require_once __DIR__ . '/../models/Utilisateur.php';

class AuthService
{
    /* ── CONNEXION ──────────────────────────────────────── */
    public function connecter(string $email, string $mdp): array {
        if (empty(trim($email)) || empty(trim($mdp)))
            return ['succes'=>false,'message'=>'Email et mot de passe requis.'];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            return ['succes'=>false,'message'=>"Format d'email invalide."];

        $u = Utilisateur::findByEmail(strtolower(trim($email)));
        if (!$u)
            return ['succes'=>false,'message'=>'Aucun compte associé à cet email.'];
        if (!$u->estActif())
            return ['succes'=>false,'message'=>'Compte désactivé. Contactez l\'administration.'];
        if (!password_verify($mdp, $u->mot_de_passe))
            return ['succes'=>false,'message'=>'Mot de passe incorrect.'];

        $u->mettreAJourConnexion();
        $this->creerSession($u);
        return ['succes'=>true,'message'=>'Connexion réussie.','utilisateur'=>$u];
    }

    /* ── INSCRIPTION ────────────────────────────────────── */
    public function inscrire(array $d): array {
        $erreurs = $this->validerInscription($d);
        if (!empty($erreurs))
            return ['succes'=>false,'message'=>'Veuillez corriger les erreurs.','erreurs'=>$erreurs];

        if (Utilisateur::emailExiste(strtolower(trim($d['email']))))
            return ['succes'=>false,'message'=>'Cet email est déjà utilisé.','erreurs'=>[]];

        $u = new Utilisateur([
            'nom'          => trim($d['nom']),
            'prenom'       => trim($d['prenom']),
            'email'        => strtolower(trim($d['email'])),
            'mot_de_passe' => password_hash($d['mot_de_passe'], PASSWORD_BCRYPT, ['cost'=>12]),
            'role'         => ROLE_ETUDIANT_DIPLOME,
            'id_filiere'   => (int)$d['id_filiere'],
            'actif'        => 1,
        ]);

        if (!$u->insert())
            return ['succes'=>false,'message'=>'Erreur lors de la création du compte.','erreurs'=>[]];

        return ['succes'=>true,'message'=>'Compte créé ! Vous pouvez vous connecter.','erreurs'=>[]];
    }

    /* ── DÉCONNEXION ────────────────────────────────────── */
    public function deconnecter(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(),'',time()-42000,
                $p['path'],$p['domain'],$p['secure'],$p['httponly']);
        }
        session_destroy();
    }

    /* ── MODIFIER PROFIL ────────────────────────────────── */
    public function modifierProfil(int $id, array $d): array {
        $erreurs = [];
        if (empty(trim($d['nom']??'')))    $erreurs['nom']    = 'Nom requis.';
        if (empty(trim($d['prenom']??''))) $erreurs['prenom'] = 'Prénom requis.';
        $email = strtolower(trim($d['email']??''));
        if (empty($email))
            $erreurs['email'] = 'Email requis.';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
            $erreurs['email'] = 'Format invalide.';
        elseif (Utilisateur::emailExiste($email, $id))
            $erreurs['email'] = 'Email déjà utilisé.';

        if (!empty($erreurs))
            return ['succes'=>false,'message'=>'Erreurs de validation.','erreurs'=>$erreurs];

        $u = Utilisateur::findById($id);
        if (!$u) return ['succes'=>false,'message'=>'Utilisateur introuvable.','erreurs'=>[]];

        $u->nom      = trim($d['nom']);
        $u->prenom   = trim($d['prenom']);
        $u->email    = $email;
        if (!empty($d['id_filiere'])) $u->id_filiere = (int)$d['id_filiere'];

        if (!$u->update())
            return ['succes'=>false,'message'=>'Erreur lors de la mise à jour.','erreurs'=>[]];

        $_SESSION['user_nom']   = $u->getNomComplet();
        $_SESSION['user_email'] = $u->email;
        return ['succes'=>true,'message'=>'Profil mis à jour avec succès.','erreurs'=>[]];
    }

    /* ── CHANGER MOT DE PASSE ───────────────────────────── */
    public function changerMotDePasse(int $id, array $d): array {
        $erreurs = [];
        $ancien  = $d['ancien_mdp']   ?? '';
        $nouveau = $d['nouveau_mdp']  ?? '';
        $conf    = $d['confirmer_mdp']?? '';

        if (empty($ancien))           $erreurs['ancien_mdp']   = 'Ancien mot de passe requis.';
        if (strlen($nouveau) < 8)     $erreurs['nouveau_mdp']  = 'Minimum 8 caractères.';
        elseif (!preg_match('/[0-9]/',$nouveau))
                                      $erreurs['nouveau_mdp']  = 'Au moins un chiffre requis.';
        if ($nouveau !== $conf)       $erreurs['confirmer_mdp']= 'Les mots de passe ne correspondent pas.';

        if (!empty($erreurs))
            return ['succes'=>false,'message'=>'Erreurs de validation.','erreurs'=>$erreurs];

        $u = Utilisateur::findById($id);
        if (!$u || !password_verify($ancien, $u->mot_de_passe))
            return ['succes'=>false,'message'=>'Ancien mot de passe incorrect.',
                    'erreurs'=>['ancien_mdp'=>'Incorrect.']];

        $u->updateMotDePasse(password_hash($nouveau, PASSWORD_BCRYPT, ['cost'=>12]));
        return ['succes'=>true,'message'=>'Mot de passe changé avec succès.','erreurs'=>[]];
    }

    /* ── PRIVÉS ─────────────────────────────────────────── */
    private function creerSession(Utilisateur $u): void {
        session_regenerate_id(true);
        $_SESSION['user_id']    = $u->id_utilisateur;
        $_SESSION['user_nom']   = $u->getNomComplet();
        $_SESSION['user_role']  = $u->role;
        $_SESSION['user_email'] = $u->email;
    }

    private function validerInscription(array $d): array {
        $e = [];
        if (empty(trim($d['nom']??'')))    $e['nom']    = 'Nom requis.';
        if (empty(trim($d['prenom']??''))) $e['prenom'] = 'Prénom requis.';
        $em = trim($d['email']??'');
        if (empty($em))                          $e['email'] = 'Email requis.';
        elseif (!filter_var($em,FILTER_VALIDATE_EMAIL)) $e['email'] = 'Format invalide.';
        $mdp = $d['mot_de_passe']??'';
        if (strlen($mdp)<8)                  $e['mot_de_passe']  = 'Min. 8 caractères.';
        elseif (!preg_match('/[0-9]/',$mdp)) $e['mot_de_passe']  = 'Au moins un chiffre.';
        if (($d['confirmer_mdp']??'')!==$mdp) $e['confirmer_mdp']= 'Confirmation incorrecte.';
        if (empty($d['id_filiere']))           $e['id_filiere']   = 'Filière requise.';
        return $e;
    }
}
