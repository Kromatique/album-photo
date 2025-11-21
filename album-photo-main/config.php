<?php
// config.php
$serveur = "localhost";
$utilisateur = "root"; // À remplacer
$mot_de_passe = ""; // À remplacer
$base_de_donnees = "album_photo";

// Connexion à la base de données
try {
    $connexion = new PDO("mysql:host=$serveur;dbname=$base_de_donnees;charset=utf8", $utilisateur, $mot_de_passe);
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Démarrer la session pour la gestion de l'utilisateur connecté
session_start();

// Fonction simple pour simuler la connexion (à améliorer pour la sécurité)
function est_connecte() {
    return isset($_SESSION['id_utilisateur']);
}
function est_admin() {
    return est_connecte() && $_SESSION['est_administrateur'];
}
?>