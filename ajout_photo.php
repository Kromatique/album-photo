<?php
// ajout_photo.php
include 'config.php'; 

// Rediriger si non connecté
if (!est_connecte()) {
    header('Location: connexion.php');
    exit;
}

$message_erreur = "";
$message_succes = "";

// --- Traitement de l'ajout de Photo ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $legende_photo = trim($_POST['legende_photo']);
    $emplacement_photo = trim($_POST['emplacement_photo']); 
    $id_page = (int)$_POST['id_page'];
    $id_utilisateur = $_SESSION['id_utilisateur'];

    if (empty($legende_photo) || empty($emplacement_photo) || empty($id_page)) {
        $message_erreur = "Veuillez remplir tous les champs.";
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

            // Redirection vers la page concernée après l'ajout
            header("Location: index.php?page=$id_page&ajout=succes");
            exit;

        } catch (PDOException $e) {
            $message_erreur = "Erreur lors de l'ajout de la photo : " . $e->getMessage();
        }
    }
}

// Récupérer la liste des pages pour le formulaire
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

        <form method="POST">
            
            <label for="emplacement">Emplacement du fichier (Chemin sur le serveur, ex: photos/evenement/image.jpg) :</label>
            <input type="text" id="emplacement" name="emplacement_photo" required>
            
            <label for="legende">Légende (courte) :</label>
            <input type="text" id="legende" name="legende_photo" required>
            
            <label for="page">Page de rattachement :</label>
            <select id="page" name="id_page" required>
                <?php if (empty($pages)): ?>
                     <option value="">Veuillez d'abord créer une page dans la base de données</option>
                <?php else: ?>
                    <?php foreach ($pages as $page): ?>
                        <option value="<?php echo $page['id_page']; ?>"><?php echo htmlspecialchars($page['intitule_page']); ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            
            <input type="submit" value="Enregistrer la Photo">
        </form>
    </div>
</body>
</html>