<?php
// index.php
include 'config.php'; 

// --- 1. Gérer la déconnexion ---
if (isset($_GET['deconnexion'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// --- 2. Récupérer la page sélectionnée (ou la première par défaut) ---
$id_page_courante = isset($_GET['page']) ? (int)$_GET['page'] : null;

// Requête pour les pages (pour le menu)
$req_pages = $connexion->query("SELECT id_page, intitule_page FROM PAGE ORDER BY id_page");
$pages = $req_pages->fetchAll(PDO::FETCH_ASSOC);

if ($id_page_courante === null && !empty($pages)) {
    $id_page_courante = $pages[0]['id_page'];
}

// --- 3. Afficher le HTML et le Menu ---
echo '<html><head><title>Album Photo M2L</title></head><body>';
echo '<h1>Site Web Album Photo</h1>';

// Affichage du statut de connexion
if (est_connecte()) {
    echo '<p>Connecté en tant que **' . htmlspecialchars($_SESSION['nom']) . '** (Admin: ' . (est_admin() ? 'Oui' : 'Non') . ') - <a href="?deconnexion=1">Déconnexion</a></p>';
    echo '<p><a href="ajout_photo.php">**Ajouter une Photo**</a></p>';
} else {
    echo '<p><a href="connexion.php">Connexion / Inscription</a></p>';
}

echo '<h2>Pages d\'Album</h2><ul>';
foreach ($pages as $page) {
    echo '<li><a href="?page=' . $page['id_page'] . '">**' . htmlspecialchars($page['intitule_page']) . '**</a></li>';
}
echo '</ul><hr>';

// --- 4. Affichage de la Page Courante et des Photos ---
if ($id_page_courante !== null) {
    
    // Requête des photos (exclure les photos archivées, sauf pour l'administrateur)
    $statut_photo_filtre = est_admin() ? "'ACTIF', 'ARCHIVE'" : "'ACTIF'";
    
    $req_photos = $connexion->prepare("
        SELECT id_photo, emplacement_photo, legende_photo, statut_suppression, id_utilisateur
        FROM PHOTO 
        WHERE id_page = :id_page 
        AND statut_suppression IN ($statut_photo_filtre)
        ORDER BY date_ajout DESC
    ");
    $req_photos->bindParam(':id_page', $id_page_courante, PDO::PARAM_INT);
    $req_photos->execute();
    $photos = $req_photos->fetchAll(PDO::FETCH_ASSOC);

    echo '<h2>Photos de la Page ' . htmlspecialchars($pages[array_search($id_page_courante, array_column($pages, 'id_page'))]['intitule_page']) . '</h2>';

    foreach ($photos as $photo) {
        echo '<div>';
        echo '<h3>Photo ID: ' . $photo['id_photo'] . '</h3>';
        
        // Mention "En cours de suppression" [cite: 29]
        if ($photo['statut_suppression'] == 'ARCHIVE') {
            echo '<p style="color:red;">**[EN COURS DE SUPPRESSION (Archivé)]**</p>';
        }

        // Affichage de la photo (simulée ici par le chemin) [cite: 17]
        echo '<p><img src="photos/' . htmlspecialchars($photo['emplacement_photo']) . '" alt="Photo" width="300"></p>'; 
        echo '<p>Légende: **' . htmlspecialchars($photo['legende_photo']) . '**</p>'; 
        
        // Bouton de suppression (si connecté et auteur OU administrateur) [cite: 26]
        if (est_connecte() && (est_admin() || $_SESSION['id_utilisateur'] == $photo['id_utilisateur'])) {
            echo '<form method="POST" action="gestion_suppression.php">';
            echo '<input type="hidden" name="id_photo" value="' . $photo['id_photo'] . '">';
            echo '<input type="submit" name="supprimer_photo" value="Supprimer cette Photo">';
            echo '</form>';
        }

        // --- Affichage des Commentaires ---
        echo '<h4>Commentaires (Publics)</h4>';
        
        // Requête des commentaires (exclure les commentaires archivés, sauf pour l'administrateur)
        $statut_com_filtre = est_admin() ? "'ACTIF', 'ARCHIVE'" : "'ACTIF'";
        
        $req_commentaires = $connexion->prepare("
            SELECT C.id_commentaire, C.texte_commentaire, C.date_commentaire, C.id_utilisateur, C.statut_suppression, U.nom
            FROM COMMENTAIRE C
            JOIN UTILISATEUR U ON C.id_utilisateur = U.id_utilisateur
            WHERE C.id_photo = :id_photo 
            AND C.statut_suppression IN ($statut_com_filtre)
            ORDER BY C.date_commentaire DESC
        ");
        $req_commentaires->bindParam(':id_photo', $photo['id_photo'], PDO::PARAM_INT);
        $req_commentaires->execute();
        $commentaires = $req_commentaires->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($commentaires)) {
            echo '<p>Aucun commentaire.</p>';
        } else {
            echo '<ul>';
            foreach ($commentaires as $commentaire) {
                echo '<li>';
                echo '**' . htmlspecialchars($commentaire['nom']) . '** (' . $commentaire['date_commentaire'] . '): ' . htmlspecialchars($commentaire['texte_commentaire']);
                
                // Mention "En cours de suppression" [cite: 29]
                if ($commentaire['statut_suppression'] == 'ARCHIVE') {
                    echo ' <span style="color:red;">**[EN COURS DE SUPPRESSION]**</span>';
                }
                
                // Bouton de suppression (si connecté et auteur OU administrateur) [cite: 26]
                if (est_connecte() && (est_admin() || $_SESSION['id_utilisateur'] == $commentaire['id_utilisateur'])) {
                    echo ' <form method="POST" action="gestion_suppression.php" style="display:inline;">';
                    echo '<input type="hidden" name="id_commentaire" value="' . $commentaire['id_commentaire'] . '">';
                    echo '<input type="submit" name="supprimer_commentaire" value="X">';
                    echo '</form>';
                }
                echo '</li>';
            }
            echo '</ul>';
        }
        
        // Formulaire d'ajout de commentaire (si connecté) 
        if (est_connecte()) {
            echo '<form method="POST" action="ajout_commentaire.php">';
            echo '<input type="hidden" name="id_photo" value="' . $photo['id_photo'] . '">';
            echo 'Votre commentaire : <textarea name="texte_commentaire" required></textarea>';
            echo '<input type="submit" value="Ajouter Commentaire">';
            echo '</form>';
        } else {
            echo '<p>Connectez-vous pour laisser un commentaire.</p>';
        }
        
        echo '<hr>';
        echo '</div>';
    }
} else {
    echo '<p>Veuillez créer et sélectionner une page.</p>';
}

echo '</body></html>';
?>