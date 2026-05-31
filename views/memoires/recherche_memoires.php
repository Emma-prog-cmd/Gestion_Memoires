<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche de mémoires – GéMémoires</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        /* ── Barre de navigation ──────────────────────────────────── */
        .navbar {
            background-color: #1a3a5f;
            height: 56px;
            padding: 0 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 8px rgba(0,0,0,0.25);
        }
        .navbar-brand {
            color: white;
            font-size: 17px;
            font-weight: 700;
            display: flex;
            align-items: baseline;
            gap: 8px;
            text-decoration: none;
        }
        .brand-sub { font-size: 11px; color: #94a3b8; font-weight: 400; }
        .navbar-links { display: flex; align-items: center; gap: 16px; }
        .navbar-links a { color: #cbd5e1; font-size: 14px; text-decoration: none; }
        .navbar-links a:hover { color: white; }
        .nav-user { color: #94a3b8; font-size: 14px; }
        .btn-logout {
            background: rgba(255,255,255,0.12);
            color: white !important;
            padding: 5px 12px;
            border-radius: 5px;
            font-size: 13px;
        }
        .btn-logout:hover { background: rgba(255,255,255,0.22) !important; }

        /* ── Formulaire de recherche ──────────────────────────────── */
        .search-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            padding: 24px 28px;
            margin-bottom: 28px;
        }
        .search-card h2 {
            font-size: 17px;
            color: #1a3a5f;
            margin-bottom: 18px;
        }
        .search-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            align-items: end;
        }
        .fg label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 5px;
        }
        .fg input, .fg select {
            margin: 0;
            width: 100%;
        }
        .btn-reset {
            background: #f1f5f9;
            color: #374151;
            border: none;
            padding: 10px 16px;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: 0.2s;
        }
        .btn-reset:hover { background: #e2e8f0; }

        /* ── Résultats ────────────────────────────────────────────── */
        .results-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 14px;
        }
        .results-bar h2 { font-size: 16px; color: #1a3a5f; }
        .results-count { font-size: 13px; color: #64748b; }
        .results-count strong { color: #1a3a5f; }

        .table-wrap {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow-x: auto;
        }
        table { margin-top: 0; border-radius: 10px; overflow: hidden; }
        table th { background: #1a3a5f; }
        table tbody tr:hover { background: #f8fafc; }

        .resume-cell {
            max-width: 260px;
            font-size: 13px;
            color: #4b5563;
            line-height: 1.5;
        }
        .resume-trunc {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* ── Bouton Consulter ─────────────────────────────────────── */
        .btn-consulter {
            background: #1a3a5f;
            color: white;
            border: none;
            padding: 7px 14px;
            border-radius: 5px;
            font-size: 13px;
            cursor: pointer;
            white-space: nowrap;
            transition: background 0.2s;
        }
        .btn-consulter:hover { background: #2c5282; }

        /* ── Badges ───────────────────────────────────────────────── */
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-blue   { background: #dbeafe; color: #1d4ed8; }
        .badge-orange { background: #fef3c7; color: #92400e; }

        /* ── État vide ────────────────────────────────────────────── */
        .empty-state {
            text-align: center;
            padding: 56px 24px;
            color: #64748b;
        }
        .empty-state .ico { font-size: 48px; margin-bottom: 12px; }

        /* ── Modale PDF ───────────────────────────────────────────── */
        #overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.65);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }
        #overlay.on { display: flex; }
        #pdf-box {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            width: 92vw;
            height: 90vh;
            max-width: 1100px;
            box-shadow: 0 8px 40px rgba(0,0,0,0.4);
        }
        #pdf-header {
            background: #1a3a5f;
            color: white;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
        }
        #pdf-titre {
            font-size: 14px;
            font-weight: 600;
            max-width: 80%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        #btn-close {
            background: rgba(255,255,255,0.18);
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
            border-radius: 5px;
            padding: 2px 10px;
        }
        #btn-close:hover { background: rgba(255,255,255,0.32); }
        #pdf-frame { flex: 1; border: none; width: 100%; }

        /* ── Footer ───────────────────────────────────────────────── */
        .footer {
            background: #1a3a5f;
            color: #94a3b8;
            text-align: center;
            padding: 14px;
            font-size: 13px;
            margin-top: 48px;
        }

        @media (max-width: 768px) {
            .search-grid { grid-template-columns: 1fr; }
            #pdf-box { width: 100vw; height: 100vh; border-radius: 0; }
        }
    </style>
</head>
<body>

<!-- ══ NAVBAR ═══════════════════════════════════════════════════════════════ -->
<nav class="navbar">
    <a class="navbar-brand" href="#">
        📚 GéMémoires
        <span class="brand-sub">UATM GASA FORMATION</span>
    </a>
    <div class="navbar-links">
        <span class="nav-user">
            👤 <?= htmlspecialchars(($_SESSION['prenom'] ?? '') . ' ' . ($_SESSION['nom'] ?? '')) ?>
            &nbsp;|&nbsp;
            <?= htmlspecialchars(str_replace('_', ' ', $_SESSION['role'] ?? '')) ?>
        </span>
        <a class="btn-logout" href="../auth/login.php">Déconnexion</a>
    </div>
</nav>

<!-- ══ MODALE PDF ════════════════════════════════════════════════════════════ -->
<div id="overlay">
    <div id="pdf-box">
        <div id="pdf-header">
            <span id="pdf-titre">Lecture du mémoire</span>
            <button id="btn-close" onclick="fermer()" title="Fermer (Échap)">✕</button>
        </div>
        <iframe id="pdf-frame" src="" title="Visionneuse PDF"></iframe>
    </div>
</div>

<!-- ══ CONTENU ═══════════════════════════════════════════════════════════════ -->
<div class="container">

    <h1 style="margin-top:28px;font-size:22px;color:#1a3a5f;">🔍 Recherche de mémoires</h1>
    <p style="color:#64748b;font-size:14px;margin-bottom:20px;">
        Consultez les mémoires validés et les mémoires archivés.
    </p>

    <!-- Formulaire de recherche -->
    <div class="search-card">
        <h2>🎯 Critères de recherche</h2>
        <form method="GET" action="../../controllers/RechercheController.php">

            <div class="search-grid">

                <div class="fg">
                    <label for="auteur">👤 Nom de l'auteur</label>
                    <input type="text" id="auteur" name="auteur"
                           placeholder="Ex : Koné, Traoré…"
                           value="<?= htmlspecialchars($filtres['auteur']) ?>">
                </div>

                <div class="fg">
                    <label for="filiere">📂 Filière</label>
                    <select id="filiere" name="filiere">
                        <option value="">— Toutes —</option>
                        <?php foreach ($filieres as $f): ?>
                            <option value="<?= htmlspecialchars($f) ?>"
                                <?= ($filtres['filiere'] === $f) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($f) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="fg">
                    <label for="annee_academique">🗓 Année académique</label>
                    <select id="annee_academique" name="annee_academique">
                        <option value="">— Toutes —</option>
                        <?php foreach ($anneesAcad as $a): ?>
                            <option value="<?= htmlspecialchars($a) ?>"
                                <?= ($filtres['annee_academique'] === $a) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($a) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="fg">
                    <label for="theme">📝 Thème (mot-clé)</label>
                    <input type="text" id="theme" name="theme"
                           placeholder="Ex : intelligence artificielle…"
                           value="<?= htmlspecialchars($filtres['theme']) ?>">
                </div>

                <div class="fg" style="display:flex;gap:10px;align-items:flex-end;">
                    <button type="submit">🔍 Rechercher</button>
                    <a class="btn-reset" href="../../controllers/RechercheController.php">↺ Réinitialiser</a>
                </div>

            </div>
        </form>
    </div>

    <!-- Résultats -->
    <div class="results-bar">
        <h2>📋 Résultats</h2>
        <span class="results-count">
            <strong><?= count($memoires) ?></strong>
            mémoire<?= count($memoires) > 1 ? 's' : '' ?> trouvé<?= count($memoires) > 1 ? 's' : '' ?>
        </span>
    </div>

    <?php if (empty($memoires)): ?>

        <div class="table-wrap">
            <div class="empty-state">
                <div class="ico">📭</div>
                <p>Aucun mémoire ne correspond à vos critères.</p>
                <p style="margin-top:8px;font-size:13px;">
                    Essayez d'élargir votre recherche ou de supprimer certains filtres.
                </p>
            </div>
        </div>

    <?php else: ?>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>📝 Thème</th>
                        <th>👤 Auteur</th>
                        <th>📂 Filière</th>
                        <th>🗓 Année</th>
                        <th>📄 Résumé</th>
                        <th>Type</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($memoires as $m): ?>
                    <tr>
                        <td style="font-weight:600;color:#1a3a5f;min-width:160px;">
                            <?= htmlspecialchars($m['theme']) ?>
                        </td>
                        <td><?= htmlspecialchars($m['auteur']) ?></td>
                        <td><?= htmlspecialchars($m['filiere'] ?? '—') ?></td>
                        <td style="white-space:nowrap;">
                            <?= htmlspecialchars($m['annee_academique'] ?? '—') ?>
                        </td>
                        <td class="resume-cell">
                            <?php if (!empty($m['resume'])): ?>
                                <span class="resume-trunc"><?= htmlspecialchars($m['resume']) ?></span>
                            <?php else: ?>
                                <em style="color:#9ca3af;">Non renseigné</em>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($m['source'] === 'ancien'): ?>
                                <span class="badge badge-orange">Archivé</span>
                            <?php else: ?>
                                <span class="badge badge-blue">Récent</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn-consulter"
                                data-titre="<?= htmlspecialchars($m['theme'], ENT_QUOTES) ?>"
                                data-url="../../controllers/RechercheController.php?action=consulter&amp;id=<?= (int)$m['id'] ?>&amp;source=<?= $m['source'] ?>"
                                onclick="ouvrir(this)">
                                👁 Consulter
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php endif; ?>

</div>

<!-- ══ FOOTER ════════════════════════════════════════════════════════════════ -->
<footer class="footer">
    © <?= date('Y') ?> GéMémoires – UATM GASA FORMATION
</footer>

<!-- ══ JS ════════════════════════════════════════════════════════════════════ -->
<script>
function ouvrir(btn) {
    var titre = btn.getAttribute('data-titre');
    var url   = btn.getAttribute('data-url');
    document.getElementById('pdf-titre').textContent = titre;
    document.getElementById('pdf-frame').src = url;
    document.getElementById('overlay').classList.add('on');
    document.body.style.overflow = 'hidden';
}
function fermer() {
    document.getElementById('overlay').classList.remove('on');
    document.getElementById('pdf-frame').src = '';
    document.body.style.overflow = '';
}
// Clic sur le fond sombre
document.getElementById('overlay').addEventListener('click', function(e){
    if (e.target === this) fermer();
});
// Touche Échap
document.addEventListener('keydown', function(e){
    if (e.key === 'Escape') fermer();
});
</script>

</body>
</html>
