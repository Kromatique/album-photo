<?php
include 'config.php'; 

if (!est_connecte()) {
    header('Location: connexion.php');
    exit;
}

$message_erreur = "";
$message_succes = "";

$dossier_upload = 'photos/';
$formats_autorises = ['jpg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $legende_photo = trim($_POST['legende_photo']);
    $id_page = (int)$_POST['id_page'];
    $id_utilisateur = $_SESSION['id_utilisateur'];

    if (empty($legende_photo) || empty($id_page) || empty($_FILES['fichier_photo']['name'])) {
        $message_erreur = "Veuillez remplir tous les champs et sélectionner un fichier.";
    } else {
        $fichier_info = $_FILES['fichier_photo'];
        $nom_fichier = $fichier_info['name'];
        $type_fichier = $fichier_info['type'];
        $tmp_path = $fichier_info['tmp_name'];
        
        $extension = strtolower(pathinfo($nom_fichier, PATHINFO_EXTENSION));

        if ($fichier_info['error'] !== UPLOAD_ERR_OK) {
            $message_erreur = "Erreur lors de l'upload du fichier (Code: " . $fichier_info['error'] . ").";
        } 
        // 2. Format autorisé
        elseif (!in_array($type_fichier, $formats_autorises) || !isset($formats_autorises[$extension])) {
            $message_erreur = "Seuls les formats JPG, PNG et GIF sont autorisés.";
        }
        else {
            
            // Générer un nom de fichier unique et sécurisé pour éviter les conflits et les injections
            $nouveau_nom_fichier = uniqid('photo_', true) . '.' . $extension;
            $destination = $dossier_upload . $nouveau_nom_fichier;

            if (!is_dir($dossier_upload)) {
                mkdir($dossier_upload, 0777, true);
            }

            if (move_uploaded_file($tmp_path, $destination)) {
                
                try {
                    $stmt = $connexion->prepare("
                        INSERT INTO PHOTO (emplacement_photo, legende_photo, id_page, id_utilisateur) 
                        VALUES (:emplacement_photo, :legende_photo, :id_page, :id_utilisateur)
                    ");
                    $stmt->bindParam(':emplacement_photo', $nouveau_nom_fichier); // Le nom unique
                    $stmt->bindParam(':legende_photo', $legende_photo);
                    $stmt->bindParam(':id_page', $id_page, PDO::PARAM_INT);
                    $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
                    $stmt->execute();

                    $message_succes = "Photo '" . htmlspecialchars($legende_photo) . "' ajoutée avec succès !";
                    // Rediriger vers la page de l'album
                    header('Location: index.php?page=' . $id_page);
                    exit;

                } catch (PDOException $e) {
                    if (file_exists($destination)) {
                        unlink($destination);
                    }
                    $message_erreur = "Erreur base de données: Impossible d'ajouter la photo.";
                }
            } else {
                $message_erreur = "Erreur: Le déplacement du fichier a échoué. Vérifiez les permissions du dossier 'photos/' ou la limite de taille du serveur (upload_max_filesize).";
            }
        } 
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
    <title>Ajouter une Photo</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Ajouter une Photo</h1>
        <p><a href="index.php">Retour à l'accueil</a></p>

        <?php if ($message_erreur): ?>
            <p class="message message-erreur"><?php echo $message_erreur; ?></p>
        <?php endif; ?>
        <?php if ($message_succes): ?>
            <p class="message message-succes"><?php echo $message_succes; ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            
            <label for="fichier">Fichier Photo (JPG, PNG, GIF) :</label>
            <input type="file" id="fichier" name="fichier_photo" accept="image/jpeg,image/png,image/gif" required>
            
            <label for="legende">Légende :</label>
            <input type="text" id="legende" name="legende_photo" required>
            
            <label for="page">Page de rattachement :</label>
            <select id="page" name="id_page" required>
                <?php if (empty($pages)): ?>
                     <option value="">Veuillez créer une page d'album d'abord</option>
                <?php else: ?>
                    <?php foreach ($pages as $page): ?>
                        <option value="<?php echo $page['id_page']; ?>"><?php echo htmlspecialchars($page['intitule_page']); ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            
            <input type="submit" value="Télécharger et Ajouter la Photo">
        </form>
    </div>
</body>
</html>