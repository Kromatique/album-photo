<?php
// connexion.php
include 'config.php'; 

// Rediriger si déjà connecté
if (est_connecte()) {
    header('Location: index.php');
    exit;
}

$message_erreur = "";
$message_succes = "";

// --- Traitement de la CONNEXION ---
if (isset($_POST['action']) && $_POST['action'] === 'connexion') {
    $email = trim($_POST['email']);
    $mot_de_passe = $_POST['mot_de_passe'];

    try {
        $stmt = $connexion->prepare("
            SELECT id_utilisateur, nom, mot_de_passe, est_administrateur 
            FROM UTILISATEUR 
            WHERE email = :email
        ");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

        // NOTE: Vérification avec MD5 pour correspondre au script SQL fourni
        if ($utilisateur && md5($mot_de_passe) === $utilisateur['mot_de_passe']) {
            $_SESSION['id_utilisateur'] = $utilisateur['id_utilisateur'];
            $_SESSION['nom'] = $utilisateur['nom'];
            $_SESSION['est_administrateur'] = $utilisateur['est_administrateur'];
            header('Location: index.php');
            exit;
        } else {
            $message_erreur = "Email ou mot de passe incorrect.";
        }
    } catch (PDOException $e) {
        $message_erreur = "Erreur de connexion à la base de données.";
    }
}

// --- Traitement de l'INSCRIPTION ---
if (isset($_POST['action']) && $_POST['action'] === 'inscription') {
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $mot_de_passe = $_POST['mot_de_passe'];

    if (empty($nom) || empty($email) || empty($mot_de_passe)) {
        $message_erreur = "Veuillez remplir tous les champs.";
    } else {
        try {
            $stmt = $connexion->prepare("
                INSERT INTO UTILISATEUR (nom, email, mot_de_passe) 
                VALUES (:nom, :email, MD5(:mot_de_passe))
            ");
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':mot_de_passe', $mot_de_passe);
            $stmt->execute();
            $message_succes = "Inscription réussie. Vous pouvez maintenant vous connecter.";
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                 $message_erreur = "Cet email est déjà utilisé.";
            } else {
                 $message_erreur = "Erreur lors de l'inscription.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion / Inscription</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Connexion et Inscription</h1>
        <p><a href="index.php">Retour à l'accueil</a></p>

        <?php if ($message_erreur): ?>
            <p class="message message-erreur"><?php echo $message_erreur; ?></p>
        <?php endif; ?>
        <?php if ($message_succes): ?>
            <p class="message message-succes"><?php echo $message_succes; ?></p>
        <?php endif; ?>

        <div class="connexion-container">
            <div class="connexion-col">
                <h2>Connexion</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="connexion">
                    <label for="email_c">Email :</label>
                    <input type="email" id="email_c" name="email" required>
                    
                    <label for="mdp_c">Mot de passe :</label>
                    <input type="password" id="mdp_c" name="mot_de_passe" required>
                    
                    <input type="submit" value="Se connecter">
                </form>
            </div>

            <div class="connexion-col">
                <h2>Inscription</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="inscription">
                    <label for="nom_i">Nom :</label>
                    <input type="text" id="nom_i" name="nom" required>

                    <label for="email_i">Email :</label>
                    <input type="email" id="email_i" name="email" required>
                    
                    <label for="mdp_i">Mot de passe :</label>
                    <input type="password" id="mdp_i" name="mot_de_passe" required>
                    
                    <input type="submit" value="S'inscrire">
                </form>
            </div>
        </div>
    </div>
</body>
</html>