<?php
$host     = 'localhost';
$dbname   = 'gestion_memoires_db';
$user     = 'root';
$password = '';  // vide par défaut sur WAMP

try {
    $connexion = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8",
        $user,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>