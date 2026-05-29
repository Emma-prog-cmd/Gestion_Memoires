<?php
/**
 * views/etudiant/depot_memoire.php
 * Formulaire de dépôt de mémoire pour l'étudiant diplômé connecté.
 */
session_start();

/* ── Garde : connexion obligatoire ───────────────────────────────────── */
if (empty($_SESSION['id_utilisateur'])) {
    header("Location: ../auth/login.php");
    exit;
}

/* ── Garde : rôle étudiant_diplome uniquement ────────────────────────── */
if ($_SESSION['role'] !== 'etudiant_diplome') {
    die("Accès refusé.");
}

require_once("../../models/DepotMemoire.php");

$id_etudiant  = (int) $_SESSION['id_utilisateur'];
$depotModel   = new DepotMemoire();
$mes_memoires = $depotModel->getMemoiresParEtudiant($id_etudiant);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dépôt de mémoire</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<div class="container">

    <h2>📄 Déposer mon mémoire</h2>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            ✅ Votre mémoire a été soumis avec succès. Il est en attente de validation.
        </div>
    <?php endif; ?>

    <!-- ═══════════════════════════════════════════════
         FORMULAIRE DE DÉPÔT
    ═══════════════════════════════════════════════ -->
    <form
        action="../../controllers/DepotMemoireController.php"
        method="POST"
        enctype="multipart/form-data"
        onsubmit="return verifierDepot()">

        <label>Thème <span style="color:red">*</span></label>
        <input type="text" name="theme" id="theme"
               placeholder="Ex : Intelligence Artificielle et Santé" required>

        <label>Auteur <span style="color:red">*</span></label>
        <input type="text" name="auteur" id="auteur"
               placeholder="Ex : KONE Aminata" required>

        <label>Filière <span style="color:red">*</span></label>
        <input type="text" name="filiere" id="filiere"
               placeholder="Ex : Informatique" required>

        <label>Promotion</label>
        <input type="text" name="promotion"
               placeholder="Ex : Licence 3">

        <label>Année académique <span style="color:red">*</span></label>
        <input type="text" name="annee_academique" id="annee_academique"
               placeholder="Ex : 2023-2024"
               pattern="\d{4}-\d{4}"
               title="Format attendu : 2023-2024" required>

        <label>Résumé</label>
        <textarea name="resume" rows="5"
                  placeholder="Résumé de votre mémoire (optionnel)..."></textarea>

        <label>Fichier PDF <span style="color:red">*</span></label>
        <input type="file" name="fichier_pdf" id="fichier_pdf"
               accept=".pdf" required>

        <button type="submit">📤 Soumettre le mémoire</button>

    </form>

    <!-- ═══════════════════════════════════════════════
         HISTORIQUE DES DÉPÔTS DE L'ÉTUDIANT
    ═══════════════════════════════════════════════ -->
    <h2>📋 Mes mémoires déposés</h2>

    <?php if (empty($mes_memoires)): ?>
        <p>Vous n'avez encore déposé aucun mémoire.</p>
    <?php else: ?>
    <table border="1">
        <tr>
            <th>Thème</th>
            <th>Filière</th>
            <th>Année académique</th>
            <th>Statut</th>
            <th>Date de dépôt</th>
        </tr>
        <?php foreach ($mes_memoires as $m): ?>
        <tr>
            <td><?= htmlspecialchars($m['theme']) ?></td>
            <td><?= htmlspecialchars($m['filiere']) ?></td>
            <td><?= htmlspecialchars($m['annee_academique']) ?></td>
            <td>
                <?php
                $badges = [
                    'en_attente' => '🕐 En attente',
                    'valide'     => '✅ Validé',
                    'rejete'     => '❌ Rejeté',
                ];
                echo $badges[$m['statut']] ?? htmlspecialchars($m['statut']);
                ?>
            </td>
            <td><?= htmlspecialchars($m['date_soumission']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>

</div>

<script src="../../assets/js/depot.js"></script>
</body>
</html>
