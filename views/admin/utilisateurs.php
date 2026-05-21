<?php
/**
 * Vue Liste des comptes — app/views/admin/utilisateurs.php
 * Couche : PRÉSENTATION
 */
$pageTitle = 'Gestion des comptes';
require_once VIEW_PATH . '/layout/header.php';

$roleLabels = [
    ROLE_ETUDIANT         => ['label' => 'Étudiant',        'badge' => 'badge-blue'],
    ROLE_PROFESSEUR       => ['label' => 'Professeur',       'badge' => 'badge-green'],
    ROLE_DIRECTEUR_ETUDES => ['label' => 'Dir. des études',  'badge' => 'badge-orange'],
    ROLE_ADMINISTRATEUR   => ['label' => 'Administrateur',   'badge' => 'badge-red'],
];
?>

<div class="page-header">
  <h1>👥 Gestion des comptes</h1>
  <a href="<?= BASE_URL ?>/compte/creer" class="btn btn-primary">+ Créer un compte</a>
</div>

<!-- Statistiques par rôle -->
<div class="stats-grid">
  <?php foreach ($roleLabels as $role => $info): ?>
    <div class="stat-card">
      <div class="stat-number"><?= $stats[$role] ?? 0 ?></div>
      <div class="stat-label"><?= $info['label'] ?>s</div>
    </div>
  <?php endforeach; ?>
  <div class="stat-card">
    <div class="stat-number"><?= $total ?></div>
    <div class="stat-label">Total comptes</div>
  </div>
</div>

<!-- Barre de recherche + filtre rôle -->
<div class="filter-bar">
  <input type="text" id="searchInput" placeholder="🔍 Rechercher (nom, email…)"
         onkeyup="filtrerTable()">
  <select id="roleFilter" onchange="filtrerTable()">
    <option value="">Tous les rôles</option>
    <?php foreach ($roleLabels as $role => $info): ?>
      <option value="<?= $role ?>"><?= $info['label'] ?></option>
    <?php endforeach; ?>
  </select>
  <select id="etatFilter" onchange="filtrerTable()">
    <option value="">Tous les états</option>
    <option value="1">Actifs</option>
    <option value="0">Inactifs</option>
  </select>
</div>

<!-- Tableau des comptes -->
<div class="table-wrap">
  <table class="table" id="tableComptes">
    <thead>
      <tr>
        <th>#</th>
        <th>Nom complet</th>
        <th>Email</th>
        <th>Rôle</th>
        <th>Filière</th>
        <th>Inscription</th>
        <th>État</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($utilisateurs as $u): ?>
        <?php $r = $roleLabels[$u->role] ?? ['label' => $u->role, 'badge' => 'badge-gray']; ?>
        <tr data-role="<?= $u->role ?>" data-actif="<?= (int)$u->actif ?>">
          <td class="td-id"><?= $u->id_user ?></td>
          <td class="td-nom">
            <strong><?= htmlspecialchars($u->getNomComplet()) ?></strong>
          </td>
          <td><?= htmlspecialchars($u->email) ?></td>
          <td>
            <span class="badge <?= $r['badge'] ?>"><?= $r['label'] ?></span>
          </td>
          <td class="td-filiere">
            <?= htmlspecialchars($u->nom_filiere ?? '—') ?>
            <?php if (!empty($u->niveau)): ?>
              <small>(<?= $u->niveau ?>)</small>
            <?php endif; ?>
          </td>
          <td class="td-date">
            <?= date('d/m/Y', strtotime($u->date_inscription)) ?>
          </td>
          <td>
            <span class="badge <?= $u->actif ? 'badge-green' : 'badge-red' ?>">
              <?= $u->actif ? '✅ Actif' : '⛔ Inactif' ?>
            </span>
          </td>
          <td class="td-actions">
            <!-- Modifier -->
            <a href="<?= BASE_URL ?>/compte/modifier/<?= $u->id_user ?>"
               class="btn btn-sm btn-secondary" title="Modifier">✏️</a>

            <!-- Activer / Désactiver -->
            <?php if ($u->id_user !== (int)$_SESSION['user_id']): ?>
              <form method="POST"
                    action="<?= BASE_URL ?>/compte/toggleActif/<?= $u->id_user ?>"
                    style="display:inline"
                    onsubmit="return confirm('<?= $u->actif
                      ? 'Désactiver ce compte ?'
                      : 'Réactiver ce compte ?' ?>')">
                <button type="submit"
                        class="btn btn-sm <?= $u->actif ? 'btn-danger' : 'btn-success' ?>"
                        title="<?= $u->actif ? 'Désactiver' : 'Activer' ?>">
                  <?= $u->actif ? '⛔' : '✅' ?>
                </button>
              </form>
            <?php else: ?>
              <span class="text-muted" title="Votre propre compte">—</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<p class="table-footer">
  Total affiché : <span id="countVisible"><?= count($utilisateurs) ?></span> compte(s)
</p>

<script>
function filtrerTable() {
  const q     = document.getElementById('searchInput').value.toLowerCase();
  const role  = document.getElementById('roleFilter').value;
  const etat  = document.getElementById('etatFilter').value;
  const rows  = document.querySelectorAll('#tableComptes tbody tr');
  let visible = 0;

  rows.forEach(row => {
    const text   = row.textContent.toLowerCase();
    const rData  = row.dataset.role;
    const aData  = row.dataset.actif;

    const okQ    = !q    || text.includes(q);
    const okRole = !role || rData === role;
    const okEtat = !etat || aData === etat;

    const show = okQ && okRole && okEtat;
    row.style.display = show ? '' : 'none';
    if (show) visible++;
  });
  document.getElementById('countVisible').textContent = visible;
}
</script>

<?php require_once VIEW_PATH . '/layout/footer.php'; ?>
