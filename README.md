##Projet Album Photo M2L

### 1. Présentation du projet
Le projet **Album Photo M2L** répond au besoin de la Maison des Ligues de Lorraine (M2L) de disposer d'une application interne permettant aux différents adhérents et clubs sportifs de partager et d'archiver visuellement leurs événements. L'application permet aux utilisateurs de publier des photos et de réagir en y ajoutant des commentaires sous forme de fils de discussion. Elle propose également une gestion de suppression sécurisée et temporaire (archivage) afin d'éviter les pertes de données accidentelles.

### 2. Architecture et Technologies
Ce projet est développé en technologies web classiques :
*   **Langages et Outils** : **HTML5** pour la structure des pages, **CSS3** pour la mise en page (via des variables et un style responsive), **PHP 8** pour le traitement dynamique côté serveur, et **MySQL** pour le système de gestion de base de données.
*   **Architecture** : L'application adopte une structure de type **Web Classique (scripts autonomes)**. Bien que les fichiers PHP intègrent à la fois le traitement et le rendu HTML (architecture dite "monolithique" simple), la logique métier est en partie isolée :
    *   La gestion de l'accès aux données est centralisée dans un fichier de configuration globale qui crée l'instance de connexion.
    *   Les actions de traitement de données (comme la suppression ou l'ajout de commentaires) sont séparées dans des scripts dédiés qui jouent le rôle de contrôleurs.

### 3. Gestion et structure des données
La circulation des données se fait de manière dynamique : l'utilisateur soumet des requêtes HTTP (via des formulaires ou des paramètres d'URL), qui sont lues par le serveur PHP puis exécutées en base de données SQL via des requêtes préparées avec l'interface PDO.

Le modèle relationnel (MySQL) est structuré autour de 4 tables pour organiser les informations :
*   **UTILISATEUR** : Sert à **stocker** les comptes des membres (nom, email, mot de passe hashé en MD5, et un booléen pour savoir s'il est administrateur).
*   **PAGE** : Sert à **stocker** les différentes catégories ou sections d'albums (ex: Athlétisme, Football, Basket).
*   **PHOTO** : Sert à **stocker** le nom physique du fichier image correspondant, sa légende, la date d'ajout, l'identifiant de la page associée et son statut de suppression.
*   **COMMENTAIRE** : Sert à **stocker** le texte du commentaire rédigé, sa date de création, l'auteur et la photo concernée.

Les fichiers images physiques sont directement envoyés par l'utilisateur et sont enregistrés pour les **stocker** dans le dossier `/photos` du serveur web, tandis que la base de données ne fait que **stocker** le chemin d'accès textuel (le nom du fichier généré de manière unique).

Le mécanisme de suppression utilise un système d'archivage temporaire : au lieu de supprimer immédiatement, un utilisateur simple va passer le statut de la photo ou du commentaire à `'ARCHIVE'` et **stocker** la date d'archivage en base. Un événement planifié dans MySQL (**Event Scheduler**) vient automatiquement lire ces dates toutes les 2 minutes pour supprimer définitivement les lignes obsolètes.

### 4. Fonctionnalités principales (Cas d'utilisation)
*   **Utilisateur Anonyme** :
    *   Naviguer dans les albums et consulter les différentes photos publiées.
    *   Consulter les commentaires des autres utilisateurs sous chaque photo.
    *   S'inscrire sur la plateforme ou s'authentifier.
*   **Utilisateur Connecté** :
    *   Ajouter une nouvelle photo dans l'album de son choix en téléchargeant un fichier image et en saisissant une légende.
    *   Rédiger et publier un commentaire sous n'importe quelle photo.
    *   Demander la suppression de ses propres photos ou de ses propres commentaires (ce qui va les archiver de manière temporaire).
*   **Administrateur** :
    *   Accéder à l'interface de gestion des pages pour créer, renommer ou supprimer des catégories d'albums.
    *   Supprimer immédiatement et définitivement (sans passer par l'archivage temporaire) n'importe quelle photo ou commentaire du site.

### 5. Cartographie des fichiers clés

| Nom du fichier | Emplacement | Rôle précis dans la logique du code |
| :--- | :--- | :--- |
| **`config.php`** | `/config.php` | Initialise la connexion PDO à la base de données, démarre la session PHP et définit les fonctions globales pour vérifier si un utilisateur est authentifié ou s'il est administrateur. |
| **`index.php`** | `/index.php` | Point d'entrée principal qui récupère les catégories dans la base de données, gère la pagination des photos avec une clause SQL `LIMIT/OFFSET`, et affiche les images et leurs commentaires. |
| **`gestion_suppression.php`** | `/gestion_suppression.php` | Contrôleur qui valide l'identité de l'appelant et applique la règle métier : suppression physique pour un admin, ou passage en archivage temporaire (`UPDATE` à `'ARCHIVE'`) pour un utilisateur simple. |
| **`ajout_photo.php`** | `/ajout_photo.php` | Gère la réception du fichier image transféré par formulaire, vérifie son format, lui attribue un nom unique et insère les informations de la photo dans la table SQL. |
| **`album_photo.sql`** | `/album_photo.sql` | Contient le script de création des tables de la base de données et la définition de l'événement MySQL `purge_2m` qui s'exécute en arrière-plan pour nettoyer les données archivées. |
