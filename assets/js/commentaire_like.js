/**
 * commentaire_like.js
 * Gère toutes les interactions AJAX : like toggle + ajout/suppression commentaires
 * Dépend des variables globales définies dans la vue : MEMOIRE_ID, USER_LOGGED, CTRL_URL
 */

document.addEventListener('DOMContentLoaded', () => {

  const btnLike       = document.getElementById('btn-like');
  const likeCount     = document.getElementById('like-count');
  const commentInput  = document.getElementById('comment-input');
  const btnSubmit     = document.getElementById('btn-submit-comment');
  const commentList   = document.getElementById('comments-list');
  const commentCount  = document.getElementById('comment-count');
  const badgeCount    = document.getElementById('badge-count');
  const charCount     = document.getElementById('char-count');
  const commentError  = document.getElementById('comment-error');
  const noCommentsMsg = document.getElementById('no-comments-msg');

  const toast = creerToast();

  // ================================================================
  //  LIKE — toggle
  // ================================================================
  if (btnLike) {
    btnLike.addEventListener('click', async () => {
      btnLike.disabled = true;
      try {
        const data = await postJSON({ action: 'toggle_like', id_memoire: MEMOIRE_ID });
        if (data.succes) {
          const estLike = data.action === 'like';
          likeCount.textContent = data.total;
          btnLike.querySelector('.like-label').textContent =
            data.total > 1 ? 'likes' : 'like';
          btnLike.classList.toggle('liked', estLike);
          btnLike.title = estLike ? 'Retirer mon like' : 'Liker ce mémoire';
          const icon = btnLike.querySelector('.like-icon');
          icon.style.animation = 'none';
          void icon.offsetWidth;
          icon.style.animation = '';
          afficherToast(data.message, 'success');
        } else {
          afficherToast(data.message || 'Erreur.', 'error');
        }
      } catch (e) {
        afficherToast('Erreur réseau. Réessayez.', 'error');
      } finally {
        btnLike.disabled = false;
      }
    });
  }

  // ================================================================
  //  COMMENTAIRE — compteur de caractères
  // ================================================================
  if (commentInput) {
    commentInput.addEventListener('input', () => {
      const len = commentInput.value.length;
      charCount.textContent = `${len} / 1000`;
      charCount.className = 'char-count';
      if (len >= 900)  charCount.classList.add('near-limit');
      if (len >= 1000) charCount.classList.add('at-limit');
    });

    commentInput.addEventListener('keydown', (e) => {
      if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
        btnSubmit?.click();
      }
    });
  }

  // ================================================================
  //  COMMENTAIRE — ajouter
  // ================================================================
  if (btnSubmit) {
    btnSubmit.addEventListener('click', async () => {
      const contenu = commentInput.value.trim();

      if (!contenu) {
        afficherErreur('Le commentaire ne peut pas être vide.');
        commentInput.focus();
        return;
      }
      if (contenu.length > 1000) {
        afficherErreur('Trop long (max 1000 caractères).');
        return;
      }

      cacherErreur();
      btnSubmit.disabled    = true;
      btnSubmit.textContent = '⏳ Publication…';

      try {
        const data = await postJSON({
          action:     'ajouter_commentaire',
          id_memoire: MEMOIRE_ID,
          contenu:    contenu,
        });

        if (data.succes) {
          commentInput.value    = '';
          charCount.textContent = '0 / 1000';
          charCount.className   = 'char-count';

          if (noCommentsMsg) noCommentsMsg.remove();

          const html = creerHtmlCommentaire(data);
          commentList.insertAdjacentHTML('afterbegin', html);

          const newItem = commentList.firstElementChild;
          activerSuppressionCommentaire(newItem);

          mettreAJourCompteur(data.total);
          afficherToast('Commentaire publié !', 'success');
        } else {
          afficherErreur(data.message || 'Erreur lors de l\'ajout.');
        }
      } catch (e) {
        afficherErreur('Erreur réseau. Réessayez.');
      } finally {
        btnSubmit.disabled    = false;
        btnSubmit.textContent = 'Publier';
      }
    });
  }

  // ================================================================
  //  COMMENTAIRE — supprimer (boutons existants au chargement)
  // ================================================================
  if (commentList) {
    commentList.querySelectorAll('.btn-delete-comment').forEach(btn => {
      activerSuppressionCommentaire(btn.closest('.comment-item'));
    });
  }

  // ================================================================
  //  FONCTIONS UTILITAIRES
  // ================================================================

  async function postJSON(payload) {
    const response = await fetch(CTRL_URL, {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify(payload),
    });
    if (!response.ok) {
      const err = await response.json().catch(() => ({}));
      throw new Error(err.message || `HTTP ${response.status}`);
    }
    return response.json();
  }

  function creerHtmlCommentaire(data) {
    const roleBadge = getRoleBadge(data.role ?? '');
    const roleLabel = getRoleLabel(data.role ?? '');
    const initiale  = (data.auteur ?? 'U')[0].toUpperCase();

    return `
      <div class="comment-item" id="comment-${data.id_commentaire}">
        <div class="comment-avatar-sm">${initiale}</div>
        <div class="comment-body">
          <div class="comment-header">
            <strong class="comment-author">${escHtml(data.auteur)}</strong>
            <span class="comment-role badge ${roleBadge}">${roleLabel}</span>
            <span class="comment-date">${escHtml(data.date)}</span>
          </div>
          <p class="comment-text">${nl2brJs(escHtml(data.contenu))}</p>
          <button
            class="btn-delete-comment"
            data-id="${data.id_commentaire}"
            data-memoire="${MEMOIRE_ID}"
            title="Supprimer ce commentaire"
          >🗑️ Supprimer</button>
        </div>
      </div>`;
  }

  function activerSuppressionCommentaire(item) {
    if (!item) return;
    const btn = item.querySelector('.btn-delete-comment');
    if (!btn) return;

    btn.addEventListener('click', async () => {
      if (!confirm('Supprimer ce commentaire ?')) return;

      const idCommentaire = parseInt(btn.dataset.id);
      btn.disabled = true;

      try {
        const data = await postJSON({
          action:         'supprimer_commentaire',
          id_memoire:     MEMOIRE_ID,
          id_commentaire: idCommentaire,
        });

        if (data.succes) {
          item.classList.add('removing');
          setTimeout(() => {
            item.remove();
            mettreAJourCompteur(data.total);
            if (data.total === 0) {
              commentList.innerHTML =
                '<p class="no-comments" id="no-comments-msg">Aucun commentaire pour l\'instant. Soyez le premier !</p>';
            }
          }, 280);
          afficherToast('Commentaire supprimé.', 'success');
        } else {
          afficherToast(data.message || 'Suppression impossible.', 'error');
          btn.disabled = false;
        }
      } catch (e) {
        afficherToast('Erreur réseau.', 'error');
        btn.disabled = false;
      }
    });
  }

  function mettreAJourCompteur(total) {
    if (commentCount) commentCount.textContent = total;
    if (badgeCount)   badgeCount.textContent   = total;
  }

  function afficherErreur(msg) {
    if (!commentError) return;
    commentError.textContent  = msg;
    commentError.style.display = 'block';
  }

  function cacherErreur() {
    if (!commentError) return;
    commentError.style.display = 'none';
  }

  function creerToast() {
    const el = document.createElement('div');
    el.id = 'toast';
    document.body.appendChild(el);
    return el;
  }

  let toastTimer;
  function afficherToast(msg, type = 'success') {
    toast.textContent = msg;
    toast.className   = `show toast-${type}`;
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => { toast.className = ''; }, 3000);
  }

  function escHtml(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function nl2brJs(str) {
    return str.replace(/\n/g, '<br>');
  }

  function getRoleLabel(role) {
    const map = {
      etudiant_diplome:     'Étudiant',
      etudiant_consultant:  'Étudiant',
      professeur:           'Professeur',
      directeur_etude:      'Dir. études',
      administrateur:       'Admin',
    };
    return map[role] ?? role;
  }

  function getRoleBadge(role) {
    if (role === 'professeur') return 'badge-green';
    if (['directeur_etude', 'administrateur'].includes(role)) return 'badge-red';
    return 'badge-blue';
  }

});