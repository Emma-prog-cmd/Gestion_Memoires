<?php
/**
 * Service CompteService — Gestion Mémoires UATM GASA FORMATION
 * Couche : MÉTIER — Gestion des comptes par l'administrateur.
 * Auteur : Gaïus_Ahs (Vital-Ahs)
 */
require_once __DIR__ . '/../config/connexion.php';
require_once __DIR__ . '/../config/auth_config.php';
require_once __DIR__ . '/../models/Utilisateur.php';

class CompteService
{
    private const ROLES = [
        'etudiant_diplome','etudiant_consultant','professeur','directeur_etude','administrateur'
    ];

    /* ── LISTER TOUS ────────────────────────────────────── */
    public function listerTous(): array {
        $utilisateurs = Utilisateur::findAll();
        return [
            'utilisateurs' => $utilisateurs,
            'stats'        => Utilisateur::compterParRole(),
            'total'        => count($utilisateurs),
        ];
    }

    /* ── CRÉER COMPTE (admin) ───────────────────────────── */
    public function creerCompte(array $d): array {
        $erreurs = $this->validerCreation($d);
        if (!empty($erreurs))
            return ['succes'=>false,'message'=>'Données invalides.','erreurs'=>$erreurs];

        if (Utilisateur::emailExiste(strtolower(trim($d['email']))))
            return ['succes'=>false,'message'=>'Email déjà utilisé.','erreurs'=>[]];

        $u = new Utilisateur([
            'nom'          => trim($d['nom']),
            'prenom'       => trim($d['prenom']),
            'email'        => strtolower(trim($d['email'])),
            'mot_de_passe' => password_hash($d['mot_de_passe'], PASSWORD_BCRYPT, ['cost'=>12]),
            'role'         => $d['role'],
            'id_filiere'   => !empty($d['id_filiere']) ? (int)$d['id_filiere'] : null,
            'actif'        => 1,
        ]);

        if (!$u->insert())
            return ['succes'=>false,'message'=>'Erreur lors de la création.','erreurs'=>[]];

        return ['succes'=>true,'message'=>"Compte de {$u->getNomComplet()} créé.",'erreurs'=>[]];
    }

    /* ── MODIFIER COMPTE (admin) ────────────────────────── */
    public function modifierCompte(int $id, array $d): array {
        $erreurs = $this->validerModification($d, $id);
        if (!empty($erreurs))
            return ['succes'=>false,'message'=>'Données invalides.','erreurs'=>$erreurs];

        $u = Utilisateur::findById($id);
        if (!$u) return ['succes'=>false,'message'=>'Utilisateur introuvable.','erreurs'=>[]];

        $u->nom        = trim($d['nom']);
        $u->prenom     = trim($d['prenom']);
        $u->email      = strtolower(trim($d['email']));
        $u->id_filiere = !empty($d['id_filiere']) ? (int)$d['id_filiere'] : null;

        if (!$u->update())
            return ['succes'=>false,'message'=>'Erreur mise à jour.','erreurs'=>[]];

        if (!empty($d['role']) && $d['role'] !== $u->role)
            $u->updateRole($d['role']);

        if (!empty($d['nouveau_mdp'])) {
            if (strlen($d['nouveau_mdp']) < 8)
                return ['succes'=>false,'message'=>'Nouveau MDP trop court (min. 8 car.).','erreurs'=>[]];
            $u->updateMotDePasse(
                password_hash($d['nouveau_mdp'], PASSWORD_BCRYPT, ['cost'=>12])
            );
        }
        return ['succes'=>true,'message'=>"Compte de {$u->getNomComplet()} mis à jour.",'erreurs'=>[]];
    }

    /* ── ACTIVER / DÉSACTIVER ───────────────────────────── */
    public function toggleActif(int $id, int $adminId): array {
        if ($id === $adminId)
            return ['succes'=>false,'message'=>'Vous ne pouvez pas désactiver votre propre compte.'];

        $u = Utilisateur::findById($id);
        if (!$u) return ['succes'=>false,'message'=>'Utilisateur introuvable.'];

        $u->toggleActif();
        $etat = $u->estActif() ? 'activé' : 'désactivé';
        return ['succes'=>true,'message'=>"Compte de {$u->getNomComplet()} {$etat}."];
    }

    /* ── VALIDATIONS PRIVÉES ────────────────────────────── */
    private function validerCreation(array $d): array {
        $e = [];
        if (empty(trim($d['nom']??'')))    $e['nom']    = 'Nom requis.';
        if (empty(trim($d['prenom']??''))) $e['prenom'] = 'Prénom requis.';
        $em = trim($d['email']??'');
        if (empty($em))                          $e['email'] = 'Email requis.';
        elseif (!filter_var($em,FILTER_VALIDATE_EMAIL)) $e['email'] = 'Format invalide.';
        if (!in_array($d['role']??'', self::ROLES)) $e['role'] = 'Rôle invalide.';
        $mdp = $d['mot_de_passe']??'';
        if (strlen($mdp)<8) $e['mot_de_passe']='Min. 8 caractères.';
        if (($d['confirmer_mdp']??'')!==$mdp) $e['confirmer_mdp']='Confirmation incorrecte.';
        return $e;
    }

    private function validerModification(array $d, int $exclu): array {
        $e = [];
        if (empty(trim($d['nom']??'')))    $e['nom']    = 'Nom requis.';
        if (empty(trim($d['prenom']??''))) $e['prenom'] = 'Prénom requis.';
        $em = trim($d['email']??'');
        if (empty($em))                               $e['email']='Email requis.';
        elseif (!filter_var($em,FILTER_VALIDATE_EMAIL))    $e['email']='Format invalide.';
        elseif (Utilisateur::emailExiste($em,$exclu))      $e['email']='Email déjà utilisé.';
        if (!empty($d['role']) && !in_array($d['role'],self::ROLES)) $e['role']='Rôle invalide.';
        return $e;
    }
}
