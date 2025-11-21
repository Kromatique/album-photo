<?php
// ajout_photo.php (Version avec UPLOAD de fichier SANS LIMITE DE TAILLE PHP)
include 'config.php'; 

// Rediriger si non connecté
if (!est_connecte()) {
    header('Location: connexion.php');
    exit;
}

$message_erreur = "";
$message_succes = "";

// --- PARAMÈTRES D'UPLOAD ---
$dossier_upload = 'photos/';
// La taille maximale est maintenant gérée uniquement par le fichier php.ini du serveur.
$formats_autorises = ['jpg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif'];

// --- Traitement de l'ajout de Photo ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $legende_photo = trim($_POST['legende_photo']);
    $id_page = (int)$_POST['id_page'];
    $id_utilisateur = $_SESSION['id_utilisateur'];

    // Vérification de base
    if (empty($legende_photo) || empty($id_page) || empty($_FILES['fichier_photo']['name'])) {
        $message_erreur = "Veuillez remplir tous les champs et sélectionner un fichier.";
    } else {
        // VÉRIFICATIONS DU FICHIER UPLOADÉ
        $fichier_info = $_FILES['fichier_photo'];
        $nom_fichier = $fichier_info['name'];
        $type_fichier = $fichier_info['type'];
        $tmp_path = $fichier_info['tmp_name'];
        
        $extension = strtolower(pathinfo($nom_fichier, PATHINFO_EXTENSION));

        // 1. Erreur d'Upload (PHP)
        if ($fichier_info['error'] !== UPLOAD_ERR_OK) {
            $message_erreur = "Erreur lors de l'upload du fichier (Code: " . $fichier_info['error'] . ").";
        } 
        // 2. Format autorisé
        elseif (!in_array($type_fichier, $formats_autorises) || !isset($formats_autorises[$extension])) {
            $message_erreur = "Seuls les formats JPG, PNG et GIF sont autorisés.";
        }
        else {
            // TOUT EST OK : GESTION DU NOM ET DÉPLACEMENT
            
            // Générer un nom de fichier unique et sécurisé pour éviter les conflits et les injections
            $nouveau_nom_fichier = uniqid('photo_', true) . '.' . $extension;
            $destination = $dossier_upload . $nouveau_nom_fichier;

            // Créer le dossier 'photos/' s'il n'existe pas
            if (!is_dir($dossier_upload)) {
                mkdir($dossier_upload, 0777, true);
            }

            // Déplacement du fichier temporaire vers la destination finale
            if (move_uploaded_file($tmp_path, $destination)) {
                
                // INSÉRER EN BASE DE DONNÉES
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
                    // En cas d'échec d'insertion en BDD, supprimer le fichier du serveur
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

// Récupérer les pages pour le menu déroulant
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