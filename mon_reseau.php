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
$query_user = "SELECT pseudo FROM utilisateurs WHERE id = ?";
$stmt_user = $conn->prepare($query_user);
$stmt_user->bind_param("i", $utilisateur_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$utilisateur = $result_user->fetch_assoc();

// Récupérer les amis de l'utilisateur
$query = "SELECT u.id, u.pseudo FROM utilisateurs u 
          INNER JOIN amis a ON (u.id = a.user_id OR u.id = a.friend_id) 
          WHERE (a.user_id = ? OR a.friend_id = ?) AND u.id != ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("iii", $utilisateur_id, $utilisateur_id, $utilisateur_id);
$stmt->execute();
$friends_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Réseau - ECE In</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="index.php">ECE In</a>
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
        <h1>Mon Réseau</h1>
        <a href="search_friend.php" class="btn btn-primary">Ajouter des amis</a>
        <a href="friend_requests.php" class="btn btn-secondary">Demandes d'amis reçues</a>
        <ul class="list-group mt-3">
            <?php while ($row = $friends_result->fetch_assoc()): ?>
                <li class="list-group-item"><?= htmlspecialchars($row['pseudo']) ?></li>
            <?php endwhile; ?>
        </ul>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
