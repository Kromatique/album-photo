<?php
// gestion_suppression.php
include 'config.php';

if (!est_connecte()) {
    header('Location: connexion.php');
    exit;
}

$id_utilisateur_connecte = $_SESSION['id_utilisateur'];
$est_admin = est_admin();
$delai_archivage = 2 / (60 * 24); // 2 minutes en jours (pour le test [cite: 30]), passer à 15 jours (15) pour la prod.

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = '';
    $id = 0;
    
    // Déterminer si c'est une photo ou un commentaire
    if (isset($_POST['supprimer_photo'], $_POST['id_photo'])) {
        $type = 'PHOTO';
        $id = (int)$_POST['id_photo'];
    } elseif (isset($_POST['supprimer_commentaire'], $_POST['id_commentaire'])) {
        $type = 'COMMENTAIRE';
        $id = (int)$_POST['id_commentaire'];
    }

    if ($type) {
        // 1. Vérifier si l'utilisateur est l'auteur ou un administrateur
        $table = ($type == 'PHOTO') ? 'PHOTO' : 'COMMENTAIRE';
        $col_id = ($type == 'PHOTO') ? 'id_photo' : 'id_commentaire';
        
        $req_verif = $connexion->prepare("SELECT id_utilisateur FROM $table WHERE $col_id = :id");
        $req_verif->bindParam(':id', $id, PDO::PARAM_INT);
        $req_verif->execute();
        $item = $req_verif->fetch(PDO::FETCH_ASSOC);

        if ($item && ($est_admin || $item['id_utilisateur'] == $id_utilisateur_connecte)) {
            
            if ($est_admin) {
                // 2. Suppression immédiate par un administrateur [cite: 27]
                $stmt_del = $connexion->prepare("DELETE FROM $table WHERE $col_id = :id");
                $stmt_del->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt_del->execute();
                
            } else {
                // 3. Archivage par un utilisateur non-admin (pour 15 jours/2 minutes) 
                $stmt_arch = $connexion->prepare("
                    UPDATE $table 
                    SET statut_suppression = 'ARCHIVE', date_archivage = NOW() 
                    WHERE $col_id = :id
                ");
                $stmt_arch->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt_arch->execute();
            }
        }
    }
}

// Redirection vers la page d'accueil ou la page précédente si possible
header('Location: index.php');
exit;
?>