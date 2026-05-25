<?php
/**
 * Configuration module Auth — Gestion Mémoires UATM GASA FORMATION
 * Auteur : Gaïus_Ahs (Vital-Ahs)
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('BASE_URL',   '/Gestion_Memoires');

// Rôles — conformes à la table utilisateur du projet
define('ROLE_ETUDIANT_DIPLOME',   'etudiant_diplome');
define('ROLE_ETUDIANT_CONSULTANT','etudiant_consultant');
define('ROLE_PROFESSEUR',         'professeur');
define('ROLE_DIRECTEUR_ETUDE',    'directeur_etude');
define('ROLE_ADMINISTRATEUR',     'administrateur');
