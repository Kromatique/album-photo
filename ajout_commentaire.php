<?php
// ajout_commentaire.php
include 'config.php'; 

if (!est_connecte()) {
    header('Location: connexion.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_photo'], $_POST['texte_commentaire'])) {
    $id_photo = (int)$_POST['id_photo'];
    $texte_commentaire = trim($_POST['texte_commentaire']);
    $id_utilisateur = $_SESSION['id_utilisateur'];

    if (!empty($texte_commentaire)) {
        try {
            $stmt = $connexion->prepare("
                INSERT INTO COMMENTAIRE (texte_commentaire, id_photo, id_utilisateur) 
                VALUES (:texte_commentaire, :id_photo, :id_utilisateur)
            ");
            $stmt->bindParam(':texte_commentaire', $texte_commentaire);
            $stmt->bindParam(':id_photo', $id_photo);
            $stmt->bindParam(':id_utilisateur', $id_utilisateur);
            $stmt->execute();

            // Redirection vers la page de la photo après l'ajout
            header('Location: index.php?page=' . $_GET['page'] . '#photo_' . $id_photo);
            exit;

        } catch (PDOException $e) {
            echo "Erreur lors de l'ajout du commentaire.";
        }
    }
}
// Retour à la page d'accueil si l'accès n'est pas correct
header('Location: index.php');
exit;
?>