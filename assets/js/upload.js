function verifierFormulaire() {

    let theme = document.getElementById("theme").value;
    let auteur = document.getElementById("auteur").value;
    let fichier = document.getElementById("fichier").value;

    if(theme === "" || auteur === "" || fichier === ""){
        alert("Veuillez remplir tous les champs");
        return false;
    }

    let extension = fichier.split('.').pop().toLowerCase();

    if(extension !== "pdf"){
        alert("Seuls les fichiers PDF sont autorisés");
        return false;
    }

    alert("Upload en cours...");
    return true;
}