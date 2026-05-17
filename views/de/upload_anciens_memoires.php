<link rel="stylesheet" href="../../assets/css/upload.css">
<script src="../../assets/js/upload.js"></script>
<h2>Ajouter anciens mémoires</h2>

<form 
       action="../../controllers/AncienMemoireController.php"
       method="POST"
       enctype="multipart/form-data"
       onsubmit="return verifierFormulaire()">
     <label>Thème:</label> 
    <input type="text" name="theme" id="theme">

     <label>Auteur:</label> 
   <input type="text" name="auteur" id="auteur">

    <label>Fichier:</label> 
   <input type="file" name="fichier" id="fichier">

    <label>Filière:</label> 
    <input type="text" name="filiere">

    <label>Promotion:</label> 
    <input type="text" name="promotion">

    <button type="submit">
        Uploader
    </button>
</form>