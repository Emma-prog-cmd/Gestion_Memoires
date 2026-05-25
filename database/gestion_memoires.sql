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

-- ===============================================================
-- DONNÉES DE TEST
-- ===============================================================

INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, role) VALUES
('Admin',     'Systeme',   'admin@gemoires.com',   SHA2('password', 256), 'administrateur'),
('Kone',      'Aminata',   'aminata@etudiant.com', SHA2('password', 256), 'etudiant_diplome'),
('Traore',    'Moussa',    'moussa@etudiant.com',  SHA2('password', 256), 'etudiant_diplome'),
('Coulibaly', 'Fatoumata', 'fatou@etudiant.com',   SHA2('password', 256), 'etudiant_diplome'),
('Diallo',    'Ibrahim',   'ibrahim@etudiant.com', SHA2('password', 256), 'etudiant_diplome'),
('Prof',      'Test',      'prof@gemoires.com',    SHA2('password', 256), 'professeur'),
('Directeur', 'Etudes',    'de@gemoires.com',      SHA2('password', 256), 'directeur_etude');

INSERT INTO memoire (theme, filiere, auteur, promotion, annee_academique, resume, fichier_pdf, statut, id_etudiant) VALUES
(
    'Intelligence Artificielle et Diagnostic Medical',
    'Informatique',
    'Kone Aminata',
    'Licence 3',
    '2023-2024',
    'Ce memoire explore l application des algorithmes de machine learning pour ameliorer la precision des diagnostics medicaux dans les hopitaux d Afrique de l Ouest.',
    'UML PROJET (1).pdf',
    'valide',
    2
),
(
    'Developpement d une Application Mobile de Gestion Agricole',
    'Informatique',
    'Traore Moussa',
    'Licence 3',
    '2023-2024',
    'Etude et conception d une application mobile Android permettant aux agriculteurs de gerer leurs cultures et suivre les previsions meteo.',
    'UML PROJET (1).pdf',
    'valide',
    3
),
(
    'Securite des Systemes d Information dans les PME',
    'Reseaux et Telecommunications',
    'Coulibaly Fatoumata',
    'Licence 3',
    '2022-2023',
    'Analyse des vulnerabilites courantes dans les PME et proposition d un framework de securite adapte aux ressources limitees.',
    'UML PROJET (1).pdf',
    'valide',
    4
),
(
    'Blockchain et Tracabilite dans la Chaine d Approvisionnement',
    'Informatique',
    'Diallo Ibrahim',
    'Master 1',
    '2022-2023',
    'Implementation d une solution de tracabilite basee sur Ethereum pour securiser la chaine logistique du cacao en Cote d Ivoire.',
    'UML PROJET (1).pdf',
    'valide',
    5
),
(
    'Systeme de Vote Electronique Securise',
    'Genie Logiciel',
    'Kone Aminata',
    'Master 1',
    '2021-2022',
    'Conception d un systeme de vote electronique utilisant la cryptographie asymetrique et la technologie blockchain.',
    'UML PROJET (1).pdf',
    'valide',
    2
);

INSERT INTO anciens_memoires (theme, auteur, filiere, promotion, annee_academique, fichier_pdf, id_directeur) VALUES
(
    'Reseaux de Neurones Artificiels Appliques a la Reconnaissance Vocale',
    'Bamba Seydou',
    'Informatique',
    'Licence 3',
    '2019-2020',
    'UML PROJET (1).pdf',
    7
),
(
    'Conception d un Systeme de Gestion Electronique des Documents',
    'Ouedraogo Marie',
    'Genie Logiciel',
    'Licence 3',
    '2019-2020',
    'UML PROJET (1).pdf',
    7
),
(
    'Analyse des Performances des Protocoles de Routage dans les Reseaux Ad Hoc',
    'Sanogo Paul',
    'Reseaux et Telecommunications',
    'Master 1',
    '2020-2021',
    'UML PROJET (1).pdf',
    7
),
(
    'Developpement d un ERP Open Source pour les Cooperatives Agricoles',
    'Toure Aissatou',
    'Informatique',
    'Master 1',
    '2020-2021',
    'UML PROJET (1).pdf',
    7
),
(
    'Impact des Technologies Mobiles sur l Education en Milieu Rural',
    'Diabate Cheick',
    'Informatique',
    'Licence 3',
    '2018-2019',
    'UML PROJET (1).pdf',
    7
);

-- ============================================================
-- Module Auth (Gaïus_Ahs) : colonnes complémentaires
-- ============================================================
ALTER TABLE utilisateur
    ADD COLUMN IF NOT EXISTS actif              TINYINT(1)   NOT NULL DEFAULT 1,
    ADD COLUMN IF NOT EXISTS id_filiere        INT          NULL,
    ADD COLUMN IF NOT EXISTS derniere_connexion TIMESTAMP   NULL;

-- Compte admin bcrypt (mot de passe : Admin1234)
INSERT IGNORE INTO utilisateur (nom, prenom, email, mot_de_passe, role, actif) VALUES
('GASA', 'Admin', 'admin@uatm.bj',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'administrateur', 1);
