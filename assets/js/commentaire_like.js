/**
 * assets/js/commentaire_like.js
 * Gère : like toggle + ajout/suppression commentaires en AJAX
 * Dépend de : MEMOIRE_ID et CTRL_URL définis dans la vue detail.php
 */

document.addEventListener('DOMContentLoaded', function () {

    var btnLike       = document.getElementById('btn-like');
    var likeCount     = document.getElementById('like-count');
    var commentInput  = document.getElementById('comment-input');
    var btnSubmit     = document.getElementById('btn-submit-comment');
    var commentList   = document.getElementById('comments-list');
    var commentCount  = document.getElementById('comment-count');
    var badgeCount    = document.getElementById('badge-count');
    var charCount     = document.getElementById('char-count');
    var commentError  = document.getElementById('comment-error');

    // Créer le toast
    var toast = document.createElement('div');
    toast.id  = 'toast';
    document.body.appendChild(toast);
    var toastTimer;

    // ============================================================
    //  LIKE
    // ============================================================
    if (btnLike) {
        btnLike.addEventListener('click', function () {
            btnLike.disabled = true;

            postJSON({ action: 'toggle_like', id_memoire: MEMOIRE_ID })
                .then(function (data) {
                    if (data.succes) {
                        var estLike = data.action === 'like';
                        likeCount.textContent = data.total;
                        btnLike.querySelector('.like-label').textContent =
                            data.total > 1 ? 'likes' : 'like';
                        if (estLike) {
                            btnLike.classList.add('liked');
                            btnLike.title = 'Retirer mon like';
                        } else {
                            btnLike.classList.remove('liked');
                            btnLike.title = 'Liker ce mémoire';
                        }
                        afficherToast(data.message, 'success');
                    } else {
                        afficherToast(data.message || 'Erreur.', 'error');
                    }
                })
                .catch(function () {
                    afficherToast('Erreur réseau. Réessayez.', 'error');
                })
                .finally(function () {
                    btnLike.disabled = false;
                });
        });
    }

    // ============================================================
    //  COMPTEUR CARACTÈRES
    // ============================================================
    if (commentInput) {
        commentInput.addEventListener('input', function () {
            var len = commentInput.value.length;
            charCount.textContent = len + ' / 1000';
            charCount.className = 'char-count';
            if (len >= 900)  charCount.classList.add('near-limit');
            if (len >= 1000) charCount.classList.add('at-limit');
        });

        // Ctrl+Entrée pour publier
        commentInput.addEventListener('keydown', function (e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                if (btnSubmit) btnSubmit.click();
            }
        });
    }

    // ============================================================
    //  AJOUTER COMMENTAIRE
    // ============================================================
    if (btnSubmit) {
        btnSubmit.addEventListener('click', function () {
            var contenu = commentInput.value.trim();

            if (!contenu) {
                afficherErreur('Le commentaire ne peut pas être vide.');
                commentInput.focus();
                return;
            }
            if (contenu.length > 1000) {
                afficherErreur('Maximum 1000 caractères.');
                return;
            }

            cacherErreur();
            btnSubmit.disabled    = true;
            btnSubmit.textContent = '⏳ Publication…';

            postJSON({ action: 'ajouter_commentaire', id_memoire: MEMOIRE_ID, contenu: contenu })
                .then(function (data) {
                    if (data.succes) {
                        commentInput.value    = '';
                        charCount.textContent = '0 / 1000';
                        charCount.className   = 'char-count';

                        // Supprimer le message "aucun commentaire"
                        var msg = document.getElementById('no-comments-msg');
                        if (msg) msg.remove();

                        // Injecter le nouveau commentaire
                        var html = creerHtmlCommentaire(data);
                        commentList.insertAdjacentHTML('afterbegin', html);

                        // Activer suppression sur le nouveau commentaire
                        var newItem = commentList.firstElementChild;
                        activerSuppression(newItem);

                        majCompteurs(data.total);
                        afficherToast('Commentaire publié !', 'success');
                    } else {
                        afficherErreur(data.message || 'Erreur lors de l\'ajout.');
                    }
                })
                .catch(function () {
                    afficherErreur('Erreur réseau. Réessayez.');
                })
                .finally(function () {
                    btnSubmit.disabled    = false;
                    btnSubmit.textContent = 'Publier';
                });
        });
    }

    // ============================================================
    //  SUPPRIMER — boutons déjà présents au chargement
    // ============================================================
    if (commentList) {
        var boutons = commentList.querySelectorAll('.btn-delete-comment');
        boutons.forEach(function (btn) {
            activerSuppression(btn.closest('.comment-item'));
        });
    }

    // ============================================================
    //  FONCTIONS
    // ============================================================

    function postJSON(payload) {
        return fetch(CTRL_URL, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify(payload),
        }).then(function (response) {
            if (!response.ok) {
                return response.json().then(function (err) {
                    throw new Error(err.message || 'HTTP ' + response.status);
                });
            }
            return response.json();
        });
    }

    function creerHtmlCommentaire(data) {
        var initiale  = (data.auteur || 'U').charAt(0).toUpperCase();
        var roleBadge = getRoleBadge(data.role || '');
        var roleLabel = getRoleLabel(data.role || '');

        return '<div class="comment-item" id="comment-' + data.id_commentaire + '">'
            + '<div class="comment-avatar-sm">' + initiale + '</div>'
            + '<div class="comment-body">'
            + '<div class="comment-header">'
            + '<strong class="comment-author">' + escHtml(data.auteur) + '</strong>'
            + '<span class="badge ' + roleBadge + '">' + roleLabel + '</span>'
            + '<span class="comment-date">' + escHtml(data.date) + '</span>'
            + '</div>'
            + '<p class="comment-text">' + nl2br(escHtml(data.contenu)) + '</p>'
            + '<button class="btn-delete-comment" data-id="' + data.id_commentaire
            + '" data-memoire="' + MEMOIRE_ID + '">🗑️ Supprimer</button>'
            + '</div></div>';
    }

    function activerSuppression(item) {
        if (!item) return;
        var btn = item.querySelector('.btn-delete-comment');
        if (!btn) return;

        btn.addEventListener('click', function () {
            if (!confirm('Supprimer ce commentaire ?')) return;

            var idCommentaire = parseInt(btn.getAttribute('data-id'));
            btn.disabled = true;

            postJSON({
                action:         'supprimer_commentaire',
                id_memoire:     MEMOIRE_ID,
                id_commentaire: idCommentaire,
            })
            .then(function (data) {
                if (data.succes) {
                    item.classList.add('removing');
                    setTimeout(function () {
                        item.remove();
                        majCompteurs(data.total);
                        if (data.total === 0) {
                            commentList.innerHTML =
                                '<p class="no-comments" id="no-comments-msg">'
                                + 'Aucun commentaire pour l\'instant. Soyez le premier !</p>';
                        }
                    }, 280);
                    afficherToast('Commentaire supprimé.', 'success');
                } else {
                    afficherToast(data.message || 'Suppression impossible.', 'error');
                    btn.disabled = false;
                }
            })
            .catch(function () {
                afficherToast('Erreur réseau.', 'error');
                btn.disabled = false;
            });
        });
    }

    function majCompteurs(total) {
        if (commentCount) commentCount.textContent = total;
        if (badgeCount)   badgeCount.textContent   = total;
    }

    function afficherErreur(msg) {
        if (!commentError) return;
        commentError.textContent   = msg;
        commentError.style.display = 'block';
    }
    function cacherErreur() {
        if (!commentError) return;
        commentError.style.display = 'none';
    }

    function afficherToast(msg, type) {
        toast.textContent = msg;
        toast.className   = 'show toast-' + type;
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () { toast.className = ''; }, 3000);
    }

    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }
    function nl2br(str) {
        return str.replace(/\n/g, '<br>');
    }
    function getRoleLabel(role) {
        var map = {
            etudiant_diplome:    'Étudiant',
            etudiant_consultant: 'Étudiant',
            professeur:          'Professeur',
            directeur_etude:     'Dir. études',
            administrateur:      'Admin',
        };
        return map[role] || role;
    }
    function getRoleBadge(role) {
        if (role === 'professeur') return 'badge-green';
        if (role === 'directeur_etude' || role === 'administrateur') return 'badge-red';
        return 'badge-blue';
    }

});
