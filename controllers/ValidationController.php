<?php
require_once("../models/Validation.php");

if(isset($_POST['id_memoire'])){

    $idMemoire = $_POST['id_memoire'];
    $decision = $_POST['decision'];
    $commentaire = $_POST['commentaire'];

    $validation = new Validation();
    $validation->validerMemoire(
        $idMemoire,
        3, // id professeur connecté
        $decision,
        $commentaire
    );

    header("Location: /Gestion_Memoires/views/professeur/validation_memoire.php");
   exit();

}
?>