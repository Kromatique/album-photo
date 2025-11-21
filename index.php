<?php
// index.php
include 'config.php'; 

// --- 1. Gérer la déconnexion ---
if (isset($_GET['deconnexion'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// --- 2. Récupérer la page d'ALBUM sélectionnée ---
$id_page_courante = isset($_GET['page']) ? (int)$_GET['page'] : null;

// Requête pour les pages (pour le menu)
$req_pages = $connexion->query("SELECT id_page, intitule_page FROM PAGE ORDER BY id_page");
$pages = $req_pages->fetchAll(PDO::FETCH_ASSOC);

if ($id_page_courante === null && !empty($pages)) {
    $id_page_courante = $pages[0]['id_page'];
}

// --- 3. GESTION DE LA PAGINATION DES PHOTOS ---
$photos_par_page = 6; // Nombre de photos à afficher par page
$page_pagination_courante = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if ($page_pagination_courante < 1) {
    $page_pagination_courante = 1;
}
$offset = ($page_pagination_courante - 1) * $photos_par_page;

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Album Photo M2L</title>
    <link rel="stylesheet" href="style.css"> 
</head>
<body>
    <div class="container">
        <h1>Site Web Album Photo</h1>

        <div class="header-info">
            <?php if (est_connecte()): ?>
                <p>Connecté : <strong><?php echo htmlspecialchars($_SESSION['nom']); ?></strong> (Admin: <?php echo (est_admin() ? 'Oui' : 'Non'); ?>)</p>
                <div>
                    <a href="ajout_photo.php">Ajouter une Photo</a> |
                    <?php if (est_admin()): ?>
                        <a href="admin_pages.php" style="color:var(--couleur-danger); font-weight:bold;">Gérer les Pages</a> |
                    <?php endif; ?>
                    <a href="?deconnexion=1">Déconnexion</a>
                </div>
            <?php else: ?>
                <p><a href="connexion.php">Connexion / Inscription</a></p>
            <?php endif; ?>
        </div>

        <nav class="page-menu">
            <h2>Pages d'Album</h2>
            <ul>
                <?php foreach ($pages as $page): ?>
                    <li><a href="?page=<?php echo $page['id_page']; ?>"><?php echo htmlspecialchars($page['intitule_page']); ?></a></li>
                <?php endforeach; ?>
            </ul>
        </nav>
        <hr>

        <?php if ($id_page_courante !== null): ?>
            
            <?php // Récupérer le nom de la page actuelle
            $nom_page_actuelle = "Page Inconnue";
            foreach ($pages as $page) {
                if ($page['id_page'] == $id_page_courante) {
                    $nom_page_actuelle = $page['intitule_page'];
                    break;
                }
            }
            ?>
            <h2>Photos de la Page <?php echo htmlspecialchars($nom_page_actuelle); ?></h2>

            <?php
            // Filtre de statut
            $statut_photo_filtre = est_admin() ? "'ACTIF', 'ARCHIVE'" : "'ACTIF'";
            
            // REQUÊTE POUR COMPTER LE TOTAL DES PHOTOS
            $req_total = $connexion->prepare("
                SELECT COUNT(*) 
                FROM PHOTO 
                WHERE id_page = :id_page 
                AND statut_suppression IN ($statut_photo_filtre)
            ");
            $req_total->bindParam(':id_page', $id_page_courante, PDO::PARAM_INT);
            $req_total->execute();
            $total_photos = $req_total->fetchColumn();
            $total_pages_pagination = ceil($total_photos / $photos_par_page);

            // REQUÊTE POUR RÉCUPÉRER LES PHOTOS DE LA PAGE ACTUELLE (avec LIMIT et OFFSET)
            $req_photos = $connexion->prepare("
                SELECT id_photo, emplacement_photo, legende_photo, statut_suppression, id_utilisateur
                FROM PHOTO 
                WHERE id_page = :id_page 
                AND statut_suppression IN ($statut_photo_filtre)
                ORDER BY date_ajout DESC
                LIMIT :limit OFFSET :offset
            ");
            $req_photos->bindParam(':id_page', $id_page_courante, PDO::PARAM_INT);
            $req_photos->bindParam(':limit', $photos_par_page, PDO::PARAM_INT);
            $req_photos->bindParam(':offset', $offset, PDO::PARAM_INT);
            $req_photos->execute();
            $photos = $req_photos->fetchAll(PDO::FETCH_ASSOC);

            if (empty($photos)) {
                echo "<p>Aucune photo sur cette page pour le moment.</p>";
            }

            foreach ($photos as $photo):
            ?>
                <div class="photo-container" id="photo_<?php echo $photo['id_photo']; ?>">
                    <?php if ($photo['statut_suppression'] == 'ARCHIVE'): ?>
                        <p class="statut-archive">[EN COURS DE SUPPRESSION (Archivé)]</p>
                    <?php endif; ?>
                    <p><img src="photos/<?php echo htmlspecialchars($photo['emplacement_photo']); ?>" alt="<?php echo htmlspecialchars($photo['legende_photo']); ?>"></p> 
                    <p><strong>Légende:</strong> <?php echo htmlspecialchars($photo['legende_photo']); ?></p> 
                    
                    <?php if (est_connecte() && (est_admin() || $_SESSION['id_utilisateur'] == $photo['id_utilisateur'])): ?>
                        <form method="POST" action="gestion_suppression.php" onsubmit="return confirm('Voulez-vous vraiment supprimer cette photo ?');">
                            <input type="hidden" name="id_photo" value="<?php echo $photo['id_photo']; ?>">
                            <input type="submit" name="supprimer_photo" value="Supprimer cette Photo">
                        </form>
                    <?php endif; ?>

                    <h4>Commentaires</h4>
                    
                    <?php
                    // Requête des commentaires
                    $statut_com_filtre = est_admin() ? "'ACTIF', 'ARCHIVE'" : "'ACTIF'";
                    $req_commentaires = $connexion->prepare("
                        SELECT C.id_commentaire, C.texte_commentaire, C.date_commentaire, C.id_utilisateur, C.statut_suppression, U.nom
                        FROM COMMENTAIRE C JOIN UTILISATEUR U ON C.id_utilisateur = U.id_utilisateur
                        WHERE C.id_photo = :id_photo AND C.statut_suppression IN ($statut_com_filtre)
                        ORDER BY C.date_commentaire DESC
                    ");
                    $req_commentaires->bindParam(':id_photo', $photo['id_photo'], PDO::PARAM_INT);
                    $req_commentaires->execute();
                    $commentaires = $req_commentaires->fetchAll(PDO::FETCH_ASSOC);
                    ?>

                    <ul class="commentaires-liste">
                        <?php if (empty($commentaires)): ?>
                            <li><p>Aucun commentaire.</p></li>
                        <?php else: ?>
                            <?php foreach ($commentaires as $commentaire): ?>
                                <li class="commentaire-item">
                                    <strong><?php echo htmlspecialchars($commentaire['nom']); ?></strong>
                                    <span class="date">(<?php echo date('d/m/Y H:i', strtotime($commentaire['date_commentaire'])); ?>)</span>
                                    
                                    <?php if (est_connecte() && (est_admin() || $_SESSION['id_utilisateur'] == $commentaire['id_utilisateur'])): ?>
                                        <form method="POST" action="gestion_suppression.php" style="display:inline; float:right;" onsubmit="return confirm('Voulez-vous vraiment supprimer ce commentaire ?');">
                                            <input type="hidden" name="id_commentaire" value="<?php echo $commentaire['id_commentaire']; ?>">
                                            <input type="submit" name="supprimer_commentaire" value="X">
                                        </form>
                                    <?php endif; ?>
                                    
                                    <p><?php echo nl2br(htmlspecialchars($commentaire['texte_commentaire'])); ?></p>
                                    
                                    <?php if ($commentaire['statut_suppression'] == 'ARCHIVE'): ?>
                                        <span class="statut-archive-com">[EN COURS DE SUPPRESSION]</span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                    
                    <?php if (est_connecte()): ?>
                        <form method="POST" action="ajout_commentaire.php?page=<?php echo $id_page_courante; ?>" class="form-ajout-commentaire">
                            <input type="hidden" name="id_photo" value="<?php echo $photo['id_photo']; ?>">
                            <label for="texte_com_<?php echo $photo['id_photo']; ?>">Votre commentaire :</label>
                            <textarea id="texte_com_<?php echo $photo['id_photo']; ?>" name="texte_commentaire" required></textarea>
                            <input type="submit" value="Ajouter Commentaire">
                        </form>
                    <?php else: ?>
                        <p><i><a href="connexion.php">Connectez-vous</a> pour laisser un commentaire.</i></p>
                    <?php endif; ?>
                    
                </div>
            <?php endforeach; ?>
            
            <?php if ($total_pages_pagination > 1): ?>
                <nav class="pagination-nav">
                    <ul>
                        <?php for ($i = 1; $i <= $total_pages_pagination; $i++): ?>
                            <li>
                                <a href="?page=<?php echo $id_page_courante; ?>&p=<?php echo $i; ?>" 
                                   class="<?php echo ($i == $page_pagination_courante) ? 'actif' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>

        <?php else: ?>
            <p>Veuillez sélectionner une page dans le menu, ou en <a href="admin_pages.php">créer une</a> si vous êtes administrateur.</p>
        <?php endif; ?>

    </div>
</body>
</html>