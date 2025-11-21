<?php
include 'config.php';

// Délai de suppression des archives
$delai_jours = 2 / (60 * 24); 

$tables = ['PHOTO', 'COMMENTAIRE'];

foreach ($tables as $table) {
    // Supprimer les entrées archivées il y a plus que le délai
    $stmt = $connexion->prepare("
        DELETE FROM $table 
        WHERE statut_suppression = 'ARCHIVE' 
        AND date_archivage < DATE_SUB(NOW(), INTERVAL :delai_jours DAY)
    ");
    $stmt->bindParam(':delai_jours', $delai_jours, PDO::PARAM_STR); 
    $stmt->execute();
}

echo "Purge des archives terminée pour les tables PHOTO et COMMENTAIRE.";
?>