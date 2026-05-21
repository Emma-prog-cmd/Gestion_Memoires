<?php
require_once("../../config/connexion.php");


$sql = "SELECT * FROM memoire WHERE statut='en_attente'";
$result = $connexion->query($sql);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validation des mémoires</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<h2>Validation des mémoires</h2>

<?php if ($result->rowCount() == 0): ?>
    <p>Aucun mémoire en attente de validation.</p>
<?php else: ?>
<table border="1">
    <tr>
        <th>Thème</th>
        <th>Auteur</th>
        <th>Fichier</th>
        <th>Action</th>
    </tr>
    <?php while($memoire = $result->fetch(PDO::FETCH_ASSOC)): ?>
        <tr>
            <td><?= htmlspecialchars($memoire['theme']) ?></td>
            <td><?= htmlspecialchars($memoire['auteur']) ?></td>
            <td>
                <a href="../../uploads/<?= htmlspecialchars($memoire['fichier_pdf']) ?>" 
                   target="_blank">
                    Voir fichier
                </a>
            </td>
            <td>
                <form action="../../controllers/ValidationController.php" method="POST">
                    <input type="hidden" 
                           name="id_memoire" 
                           value="<?= $memoire['id_memoire'] ?>">
                    <textarea name="commentaire" 
                              placeholder="Commentaire (obligatoire pour rejet)"></textarea>
                    <button type="submit"
                            name="decision" 
                            value="valide"
                            onclick="return confirmerValidation()">
                        ✅ Valider
                    </button>
                    <button type="submit"
                            name="decision" 
                            value="rejete"
                            onclick="return confirmerRejet()">
                        ❌ Rejeter
                    </button>
                </form>
            </td>
        </tr>
    <?php endwhile; ?>
</table>
<?php endif; ?>

<script src="../../assets/js/validation.js"></script>
</body>
</html>