<?php
include 'config.php';

if (!est_admin()) {
    header('Location: index.php');
    exit;
}

$message_erreur = "";
$message_succes = "";
$mode_edition = false;
$page_a_editer = null;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- Action AJOUTER ---
    if (isset($_POST['action']) && $_POST['action'] === 'ajouter') {
        $intitule_page = trim($_POST['intitule_page']);
        if (!empty($intitule_page)) {
            $stmt = $connexion->prepare("INSERT INTO PAGE (intitule_page) VALUES (:intitule_page)");
            $stmt->bindParam(':intitule_page', $intitule_page);
            $stmt->execute();
            $message_succes = "Page '" . htmlspecialchars($intitule_page) . "' ajoutée avec succès.";
        } else {
            $message_erreur = "Le nom de la page ne peut pas être vide.";
        }
    }

    // --- Action MODIFIER ---
    if (isset($_POST['action']) && $_POST['action'] === 'modifier') {
        $intitule_page = trim($_POST['intitule_page']);
        $id_page = (int)$_POST['id_page'];
        if (!empty($intitule_page) && $id_page > 0) {
            $stmt = $connexion->prepare("UPDATE PAGE SET intitule_page = :intitule_page WHERE id_page = :id_page");
            $stmt->bindParam(':intitule_page', $intitule_page);
            $stmt->bindParam(':id_page', $id_page, PDO::PARAM_INT);
            $stmt->execute();
            $message_succes = "Page renommée en '" . htmlspecialchars($intitule_page) . "'.";
        } else {
            $message_erreur = "Données de modification invalides.";
        }
    }
    
    // --- Action SUPPRIMER ---
    if (isset($_POST['action']) && $_POST['action'] === 'supprimer') {
        $id_page = (int)$_POST['id_page'];
        
        $req_verif = $connexion->prepare("SELECT COUNT(*) FROM PHOTO WHERE id_page = :id_page");
        $req_verif->bindParam(':id_page', $id_page, PDO::PARAM_INT);
        $req_verif->execute();
        $nb_photos = $req_verif->fetchColumn();

        if ($nb_photos > 0) {
            $message_erreur = "Suppression impossible : Cette page contient encore $nb_photos photo(s). Veuillez d'abord supprimer les photos associées.";
        } else {
            $stmt = $connexion->prepare("DELETE FROM PAGE WHERE id_page = :id_page");
            $stmt->bindParam(':id_page', $id_page, PDO::PARAM_INT);
            $stmt->execute();
            $message_succes = "La page (ID: $id_page) a été supprimée.";
        }
    }
}

// --- 3. GESTION DU MODE ÉDITION ---

if (isset($_GET['edit_id'])) {
    $mode_edition = true;
    $id_page_edit = (int)$_GET['edit_id'];
    
    $stmt = $connexion->prepare("SELECT id_page, intitule_page FROM PAGE WHERE id_page = :id_page");
    $stmt->bindParam(':id_page', $id_page_edit, PDO::PARAM_INT);
    $stmt->execute();
    $page_a_editer = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$page_a_editer) {
        $mode_edition = false;
        $message_erreur = "Cette page n'existe pas.";
    }
}

$req_pages = $connexion->query("SELECT id_page, intitule_page FROM PAGE ORDER BY id_page");
$pages = $req_pages->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Pages - Admin</title>
    <link rel="stylesheet" href="style.css">
    <style>
        
        .admin-gestion-page {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 15px;
            background-color: #f9f9f9;
            border: 1px solid #eee;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .admin-gestion-page p {
            margin: 0;
            font-weight: bold;
        }
        .admin-gestion-page .actions {
            display: flex;
            gap: 10px;
        }
        .admin-gestion-page form {
            margin: 0; padding: 0; border: none; background: none;
        }
        .btn-edit {
            display: inline-block;
            padding: 8px 12px;
            background-color: #007bff;
            color: white;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
        }
        .btn-edit:hover {
            background-color: #0056b3;
        }
        .btn-delete {
            padding: 8px 12px;
            font-size: 14px;
            background-color: var(--couleur-danger);
        }
        .btn-delete:hover {
            background-color: #c9302c;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Panneau d'Administration : Gestion des Pages</h1>
        <p><a href="index.php">Retour à l'accueil</a></p>

        <?php if ($message_erreur): ?>
            <p class="message message-erreur"><?php echo $message_erreur; ?></p>
        <?php endif; ?>
        <?php if ($message_succes): ?>
            <p class="message message-succes"><?php echo $message_succes; ?></p>
        <?php endif; ?>
        
        <hr>

        <?php if ($mode_edition && $page_a_editer): ?>
            <h2>Modifier la Page "<?php echo htmlspecialchars($page_a_editer['intitule_page']); ?>"</h2>
            <form method="POST" action="admin_pages.php">
                <input type="hidden" name="action" value="modifier">
                <input type="hidden" name="id_page" value="<?php echo $page_a_editer['id_page']; ?>">
                
                <label for="intitule_page">Nouveau nom de la page :</label>
                <input type="text" id="intitule_page" name="intitule_page" value="<?php echo htmlspecialchars($page_a_editer['intitule_page']); ?>" required>
                
                <input type="submit" value="Mettre à jour">
                <a href="admin_pages.php" style="margin-left: 10px;">Annuler l'édition</a>
            </form>
        <?php else: ?>
            <h2>Ajouter une nouvelle Page Album</h2>
            <form method="POST" action="admin_pages.php">
                <input type="hidden" name="action" value="ajouter">
                
                <label for="intitule_page">Nom de la nouvelle page :</label>
                <input type="text" id="intitule_page" name="intitule_page" placeholder="Ex: Événements 2024" required>
                
                <input type="submit" value="Ajouter la Page">
            </form>
        <?php endif; ?>

        <hr>

        <h2>Pages Existantes</h2>
        <?php if (empty($pages)): ?>
            <p>Aucune page n'a été créée pour le moment.</p>
        <?php else: ?>
            <?php foreach ($pages as $page): ?>
                <div class="admin-gestion-page">
                    <p><?php echo htmlspecialchars($page['intitule_page']); ?> (ID: <?php echo $page['id_page']; ?>)</p>
                    <div class="actions">
                        <a href="?edit_id=<?php echo $page['id_page']; ?>" class="btn-edit">Modifier</a>
                        
                        <form method="POST" action="admin_pages.php" onsubmit="return confirm('Voulez-vous vraiment supprimer cette page ? Cette action est irréversible.');">
                            <input type="hidden" name="action" value="supprimer">
                            <input type="hidden" name="id_page" value="<?php echo $page['id_page']; ?>">
                            <input type="submit" value="Supprimer" class="btn-delete">
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

    </div>
</body>
</html>