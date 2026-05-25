<?php
/**
 * Vue Liste des comptes — views/admin/utilisateurs.php
 * Couche : PRÉSENTATION
 * Auteur : Gaïus_Ahs (Vital-Ahs)
 */
require_once __DIR__ . '/../../config/connexion.php';
require_once __DIR__ . '/../../config/auth_config.php';
require_once __DIR__ . '/../../models/Utilisateur.php';
require_once __DIR__ . '/../../services/CompteService.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== ROLE_ADMINISTRATEUR) {
    header('Location: ' . BASE_URL . '/views/auth/login.php'); exit;
}

// Flash message
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$svc          = new CompteService();
$data         = $svc->listerTous();
$utilisateurs = $data['utilisateurs'];
$stats        = $data['stats'];
$total        = $data['total'];

$roleLabels = [
    ROLE_ETUDIANT         => ['label'=>'Étudiant',        'badge'=>'badge-blue'],
    ROLE_PROFESSEUR       => ['label'=>'Professeur',       'badge'=>'badge-green'],
    ROLE_DIRECTEUR_ETUDES => ['label'=>'Dir. études',      'badge'=>'badge-orange'],
    ROLE_ADMINISTRATEUR   => ['label'=>'Administrateur',   'badge'=>'badge-red'],
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Gestion des comptes — UATM</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/auth.css">
</head>
<body>
<nav class="navbar">
  <a class="navbar-brand" href="#">📚 GéMémoires — Admin</a>
  <div class="navbar-links">
    <span>👤 <?= htmlspecialchars($_SESSION['user_nom']) ?></span>
    <a href="<?= BASE_URL ?>/controllers/AuthController.php?action=logout" class="btn-logout">Déconnexion</a>
  </div>
</nav>
<div class="main-container">

  <div class="page-header">
    <h1>👥 Gestion des comptes</h1>
    <a href="<?= BASE_URL ?>/controllers/CompteController.php?action=creer"
       class="btn btn-primary">+ Créer un compte</a>
  </div>

  <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['succes']?'success':'danger' ?>">
      <?= htmlspecialchars($flash['message']) ?>
    </div>
  <?php endif; ?>

  <!-- Statistiques -->
  <div class="stats-grid">
    <?php foreach ($roleLabels as $role => $info): ?>
      <div class="stat-card">
        <div class="stat-number"><?= $stats[$role] ?? 0 ?></div>
        <div class="stat-label"><?= $info['label'] ?>s</div>
      </div>
    <?php endforeach; ?>
    <div class="stat-card">
      <div class="stat-number"><?= $total ?></div>
      <div class="stat-label">Total</div>
    </div>
  </div>

  <!-- Filtre -->
  <div class="filter-bar">
    <input type="text" id="searchInput" placeholder="🔍 Rechercher..."
           onkeyup="filtrer()">
    <select id="roleFilter" onchange="filtrer()">
      <option value="">Tous les rôles</option>
      <?php foreach ($roleLabels as $role => $info): ?>
        <option value="<?= $role ?>"><?= $info['label'] ?></option>
      <?php endforeach; ?>
    </select>
    <select id="etatFilter" onchange="filtrer()">
      <option value="">Tous états</option>
      <option value="1">Actifs</option>
      <option value="0">Inactifs</option>
    </select>
  </div>

  <!-- Tableau -->
  <div class="table-wrap">
    <table class="table" id="tbl">
      <thead>
        <tr><th>#</th><th>Nom complet</th><th>Email</th><th>Rôle</th>
            <th>Filière</th><th>Inscription</th><th>État</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($utilisateurs as $u): ?>
          <?php $r = $roleLabels[$u->role] ?? ['label'=>$u->role,'badge'=>'badge-gray']; ?>
          <tr data-role="<?= $u->role ?>" data-actif="<?= (int)$u->actif ?>">
            <td><?= $u->id_user ?></td>
            <td><strong><?= htmlspecialchars($u->getNomComplet()) ?></strong></td>
            <td><?= htmlspecialchars($u->email) ?></td>
            <td><span class="badge <?= $r['badge'] ?>"><?= $r['label'] ?></span></td>
            <td><?= htmlspecialchars($u->nom_filiere ?? '—') ?></td>
            <td><?= date('d/m/Y', strtotime($u->date_inscription)) ?></td>
            <td>
              <span class="badge <?= $u->actif ? 'badge-green':'badge-red' ?>">
                <?= $u->actif ? '✅ Actif':'⛔ Inactif' ?>
              </span>
            </td>
            <td>
              <a href="<?= BASE_URL ?>/controllers/CompteController.php?action=modifier&id=<?= $u->id_user ?>"
                 class="btn btn-sm btn-secondary">✏️</a>
              <?php if ($u->id_user !== (int)$_SESSION['user_id']): ?>
                <form method="POST"
                      action="<?= BASE_URL ?>/controllers/CompteController.php"
                      style="display:inline"
                      onsubmit="return confirm('<?= $u->actif?'Désactiver ?':'Activer ?' ?>')">
                  <input type="hidden" name="action" value="toggleActif">
                  <input type="hidden" name="id"     value="<?= $u->id_user ?>">
                  <button type="submit"
                          class="btn btn-sm <?= $u->actif?'btn-danger':'btn-success' ?>">
                    <?= $u->actif ? '⛔':'✅' ?>
                  </button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <p style="font-size:13px;color:#64748b;margin-top:8px">
    Affichés : <span id="cnt"><?= count($utilisateurs) ?></span> compte(s)
  </p>
</div>

<script>
function filtrer(){
  const q=document.getElementById('searchInput').value.toLowerCase();
  const role=document.getElementById('roleFilter').value;
  const etat=document.getElementById('etatFilter').value;
  let n=0;
  document.querySelectorAll('#tbl tbody tr').forEach(tr=>{
    const ok=(!q||tr.textContent.toLowerCase().includes(q))
           &&(!role||tr.dataset.role===role)
           &&(!etat||tr.dataset.actif===etat);
    tr.style.display=ok?'':'none';
    if(ok)n++;
  });
  document.getElementById('cnt').textContent=n;
}
</script>
</body>
</html>
