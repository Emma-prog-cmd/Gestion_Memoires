-- -----------------------------------------------
-- TABLE UTILISATEUR
-- -----------------------------------------------
CREATE TABLE utilisateur (
    id_utilisateur  INT          AUTO_INCREMENT PRIMARY KEY,
    nom             VARCHAR(100) NOT NULL,
    prenom          VARCHAR(100) NOT NULL,
    email           VARCHAR(150) UNIQUE NOT NULL,
    mot_de_passe    VARCHAR(255) NOT NULL,
    role            ENUM(
                        'etudiant_diplome',
                        'etudiant_consultant',
                        'professeur',
                        'directeur_etude',
                        'administrateur'
                    ) NOT NULL,
    date_creation   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- -----------------------------------------------
-- TABLE MEMOIRE
-- -----------------------------------------------
CREATE TABLE memoire (
    id_memoire       INT          AUTO_INCREMENT PRIMARY KEY,
    theme            VARCHAR(255) NOT NULL,
    filiere          VARCHAR(100) NOT NULL,
    auteur           VARCHAR(150) NOT NULL,
    promotion        VARCHAR(100),
    annee_academique VARCHAR(9)   NOT NULL,
    resume           TEXT,
    fichier_pdf      VARCHAR(255) NOT NULL,
    statut           ENUM('en_attente','valide','rejete') DEFAULT 'en_attente',
    date_soumission  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_etudiant      INT NOT NULL,
    FOREIGN KEY (id_etudiant)
        REFERENCES utilisateur(id_utilisateur)
        ON DELETE CASCADE
);

-- -----------------------------------------------
-- TABLE VALIDATION
-- -----------------------------------------------
CREATE TABLE validation (
    id_validation   INT  AUTO_INCREMENT PRIMARY KEY,
    id_memoire      INT  NOT NULL,
    id_professeur   INT  NOT NULL,
    decision        ENUM('valide','rejete') NOT NULL,
    commentaire     TEXT,
    date_validation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_validation (id_memoire, id_professeur),
    FOREIGN KEY (id_memoire)
        REFERENCES memoire(id_memoire)
        ON DELETE CASCADE,
    FOREIGN KEY (id_professeur)
        REFERENCES utilisateur(id_utilisateur)
        ON DELETE CASCADE
);

-- -----------------------------------------------
-- TABLE COMMENTAIRE
-- -----------------------------------------------
CREATE TABLE commentaire (
    id_commentaire   INT  AUTO_INCREMENT PRIMARY KEY,
    contenu          TEXT NOT NULL,
    date_commentaire TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_utilisateur   INT  NOT NULL,
    id_memoire       INT  NOT NULL,
    FOREIGN KEY (id_utilisateur)
        REFERENCES utilisateur(id_utilisateur)
        ON DELETE CASCADE,
    FOREIGN KEY (id_memoire)
        REFERENCES memoire(id_memoire)
        ON DELETE CASCADE
);

-- -----------------------------------------------
-- TABLE LIKES
-- -----------------------------------------------
CREATE TABLE likes (
    id_like        INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT NOT NULL,
    id_memoire     INT NOT NULL,
    date_like      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_like (id_utilisateur, id_memoire),
    FOREIGN KEY (id_utilisateur)
        REFERENCES utilisateur(id_utilisateur)
        ON DELETE CASCADE,
    FOREIGN KEY (id_memoire)
        REFERENCES memoire(id_memoire)
        ON DELETE CASCADE
);

-- -----------------------------------------------
-- TABLE ANCIENS MEMOIRES
-- -----------------------------------------------
CREATE TABLE anciens_memoires (
    id_ancien        INT          AUTO_INCREMENT PRIMARY KEY,
    theme            VARCHAR(255) NOT NULL,
    auteur           VARCHAR(150) NOT NULL,
    filiere          VARCHAR(100),
    promotion        VARCHAR(100),
    annee_academique VARCHAR(9),
    fichier_pdf      VARCHAR(255) NOT NULL,
    date_import      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_directeur     INT NOT NULL,
    FOREIGN KEY (id_directeur)
        REFERENCES utilisateur(id_utilisateur)
        ON DELETE RESTRICT
);

-- -----------------------------------------------
-- TABLE GESTION COMPTE
-- -----------------------------------------------
CREATE TABLE gestion_compte (
    id_gestion       INT          AUTO_INCREMENT PRIMARY KEY,
    action_effectuee VARCHAR(255) NOT NULL,
    date_action      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_admin         INT NOT NULL,
    id_cible         INT NOT NULL,
    FOREIGN KEY (id_admin)
        REFERENCES utilisateur(id_utilisateur)
        ON DELETE RESTRICT,
    FOREIGN KEY (id_cible)
        REFERENCES utilisateur(id_utilisateur)
        ON DELETE SET NULL
);