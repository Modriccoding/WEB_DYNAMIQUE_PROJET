<?php
session_start();
include 'connexion.php';

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['utilisateur_id'])) {
    header("Location: login.html");
    exit();
}

// Récupérer les informations de l'utilisateur connecté
$utilisateur_id = $_SESSION['utilisateur_id'];
$query_user = "SELECT pseudo, email, nom, bio, photo_profil FROM utilisateurs WHERE id = ?";
$stmt_user = $conn->prepare($query_user);
$stmt_user->bind_param("i", $utilisateur_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$utilisateur = $result_user->fetch_assoc();

// Gérer la mise à jour des informations et le téléchargement de la photo de profil
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pseudo = $_POST['pseudo'];
    $email = $_POST['email'];
    $nom = $_POST['nom'];
    $bio = $_POST['bio'];

    // Mettre à jour les informations de l'utilisateur
    $query_update = "UPDATE utilisateurs SET pseudo = ?, email = ?, nom = ?, bio = ? WHERE id = ?";
    $stmt_update = $conn->prepare($query_update);
    $stmt_update->bind_param("ssssi", $pseudo, $email, $nom, $bio, $utilisateur_id);
    $stmt_update->execute();

    // Gérer le téléchargement de la photo de profil
    if (isset($_FILES['photo_profil']) && $_FILES['photo_profil']['error'] == 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["photo_profil"]["name"]);
        move_uploaded_file($_FILES["photo_profil"]["tmp_name"], $target_file);

        // Mettre à jour le chemin de la photo de profil dans la base de données
        $query_update_photo = "UPDATE utilisateurs SET photo_profil = ? WHERE id = ?";
        $stmt_update_photo = $conn->prepare($query_update_photo);
        $stmt_update_photo->bind_param("si", $target_file, $utilisateur_id);
        $stmt_update_photo->execute();
    }

    // Rediriger pour éviter la resoumission du formulaire
    header("Location: vous.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre Profil - ECE In</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #3b5998; /* Couleur de fond similaire au logo */
            color: #fff; /* Texte blanc pour contraste */
        }
        .navbar {
            background-color: #3b5998; /* Couleur de la barre de navigation */
        }
        .navbar-brand img {
            height: 40px;
        }
        .navbar-nav .nav-link {
            color: #3b5998 !important; /* Couleur du texte de navigation */
        }
        .navbar-text {
            color: #3b5998; /* Couleur du texte "Connecté en tant que" */
        }
        .profile-card {
            background-color: #fff;
            color: #3b5998;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        .profile-img {
            max-width: 150px;
            border-radius: 50%;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="index.php">
            <img src="LOGOfecebook.jpg" alt="Logo FECEBOOK">
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="index.php">Accueil</a></li>
                <li class="nav-item"><a class="nav-link" href="mon_reseau.php">Mon Réseau</a></li>
                <li class="nav-item"><a class="nav-link" href="vous.php">Vous</a></li>
                <li class="nav-item"><a class="nav-link" href="notifications.php">Notifications</a></li>
                <li class="nav-item"><a class="nav-link" href="messagerie.php">Messagerie</a></li>
                <li class="nav-item"><a class="nav-link" href="emplois.php">Emplois</a></li>
            </ul>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <span class="navbar-text">
                        Connecté en tant que <?= htmlspecialchars($utilisateur['pseudo']) ?>
                    </span>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Se déconnecter</a>
                </li>
            </ul>
        </div>
    </nav>
    <div class="container">
        <div class="profile-card">
            <h1>Votre Profil</h1>
            <form action="vous.php" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="pseudo">Pseudo :</label>
                    <input type="text" class="form-control" id="pseudo" name="pseudo" value="<?= htmlspecialchars($utilisateur['pseudo']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email :</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($utilisateur['email']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="nom">Nom :</label>
                    <input type="text" class="form-control" id="nom" name="nom" value="<?= htmlspecialchars($utilisateur['nom']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="bio">Bio :</label>
                    <textarea class="form-control" id="bio" name="bio" required><?= htmlspecialchars($utilisateur['bio']) ?></textarea>
                </div>
                <div class="form-group">
                    <label for="photo_profil">Photo de Profil :</label>
                    <?php if ($utilisateur['photo_profil']): ?>
                        <img src="<?= htmlspecialchars($utilisateur['photo_profil']) ?>" alt="Photo de profil" class="profile-img">
                    <?php endif; ?>
                    <input type="file" class="form-control-file" id="photo_profil" name="photo_profil">
                </div>
                <button type="submit" class="btn btn-primary">Mettre à jour</button>
            </form>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
