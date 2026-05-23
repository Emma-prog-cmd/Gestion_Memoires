<?php
/**
 * Configuration module Auth — Gestion Mémoires UATM GASA FORMATION
 * Auteur : Gaïus_Ahs (Vital-Ahs)
 * Définit les constantes et démarre la session.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('BASE_URL',   '/Gestion_Memoires');
define('VIEWS_PATH', dirname(__DIR__) . '/views');

define('ROLE_ETUDIANT',          'etudiant');
define('ROLE_PROFESSEUR',        'professeur');
define('ROLE_DIRECTEUR_ETUDES',  'directeur_etudes');
define('ROLE_ADMINISTRATEUR',    'administrateur');
