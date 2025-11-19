<?php
// ajout_photo.php (Version simple, sans upload)
include 'config.php'; 

// Rediriger si non connecté (Seul un utilisateur connecté pourra ajouter une photo)
if (!est_connecte()) {
    header('Location: connexion.php');
    exit;
}

$message = "";

// --- Traitement de l'ajout de Photo ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $legende_photo = trim($_POST['legende_photo']);
    $emplacement_photo = trim($_POST['emplacement_photo']); // <--- Saisie du chemin strict
    $id_page = (int)$_POST['id_page'];
    $id_utilisateur = $_SESSION['id_utilisateur'];

    if (empty($legende_photo) || empty($emplacement_photo) || empty($id_page)) {
        $message = "Veuillez remplir tous les champs.";
    } else {
        try {
            // Insertion dans la base de données
            $stmt = $connexion->prepare("
                INSERT INTO PHOTO (emplacement_photo, legende_photo, id_page, id_utilisateur) 
                VALUES (:emplacement_photo, :legende_photo, :id_page, :id_utilisateur)
            ");
            $stmt->bindParam(':emplacement_photo', $emplacement_photo);
            $stmt->bindParam(':legende_photo', $legende_photo);
            $stmt->bindParam(':id_page', $id_page, PDO::PARAM_INT);
            $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
            $stmt->execute();

            $message = "Photo ajoutée avec succès !";
            // Redirection vers la page concernée après l'ajout
            header("Location: index.php?page=$id_page");
            exit;

        } catch (PDOException $e) {
            $message = "Erreur lors de l'ajout de la photo.";
        }
    }
}

// Récupérer la liste des pages pour le formulaire
$req_pages = $connexion->query("SELECT id_page, intitule_page FROM PAGE ORDER BY id_page");
$pages = $req_pages->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Ajouter une Photo</title>
</head>
<body>
    <h1>Ajouter une Photo (Chemin Strict)</h1>
    <p><a href="index.php">Retour à l'accueil</a></p>

    <?php if ($message): ?>
        <p style="color:<?php echo (strpos($message, 'succès') !== false) ? 'green' : 'red'; ?>;"><?php echo $message; ?></p>
    <?php endif; ?>

    <form method="POST">
        
        <label for="emplacement">Emplacement du fichier (Chemin sur le serveur, ex: photos/evenement/image.jpg) :</label><br>
        <input type="text" id="emplacement" name="emplacement_photo" required><br><br>
        
        <label for="legende">Légende (courte) :</label><br>
        <input type="text" id="legende" name="legende_photo" required><br><br>
        
        <label for="page">Page de rattachement :</label><br>
        <select id="page" name="id_page" required>
            <?php if (empty($pages)): ?>
                 <option value="">Veuillez créer une page dans la base de données</option>
            <?php else: ?>
                <?php foreach ($pages as $page): ?>
                    <option value="<?php echo $page['id_page']; ?>"><?php echo htmlspecialchars($page['intitule_page']); ?></option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select><br><br>
        
        <input type="submit" value="Enregistrer la Photo">
    </form>
</body>
</html>