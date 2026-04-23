<?php
$serveur = "localhost";
$utilisateur = "root";
$mot_de_passe = "";
$base_de_donnees = "album_photo";

// Connexion à la base de données
try {
    $connexion = new PDO("mysql:host=$serveur;dbname=$base_de_donnees;charset=utf8", $utilisateur, $mot_de_passe);
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

session_start();

function est_connecte() {
    return isset($_SESSION['id_utilisateur']);
}
function est_admin() {
    return est_connecte() && $_SESSION['est_administrateur'];
}
?>