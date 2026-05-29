
function verifierDepot() {

    let theme            = document.getElementById("theme").value.trim();
    let auteur           = document.getElementById("auteur").value.trim();
    let filiere          = document.getElementById("filiere").value.trim();
    let annee_academique = document.getElementById("annee_academique").value.trim();
    let fichier          = document.getElementById("fichier_pdf").value;

    if (theme === "" || auteur === "" || filiere === "" || annee_academique === "" || fichier === "") {
        alert("Veuillez remplir tous les champs obligatoires.");
        return false;
    }

    let extension = fichier.split('.').pop().toLowerCase();

    if (extension !== "pdf") {
        alert("Seuls les fichiers PDF sont autorisés.");
        return false;
    }

    let formatAnnee = /^\d{4}-\d{4}$/.test(annee_academique);

    if (!formatAnnee) {
        alert("L'année académique doit être au format 2023-2024.");
        return false;
    }

    alert("Dépôt en cours...");
    return true;
}
