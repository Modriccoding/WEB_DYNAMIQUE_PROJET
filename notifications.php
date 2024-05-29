<?php
session_start();
include 'connexion.php';

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['utilisateur_id'])) {
    header("Location: login.html");
    exit();
}

// Récupérer les notifications de l'utilisateur
$sql = "SELECT * FROM notifications WHERE utilisateur_id = " . $_SESSION['utilisateur_id'];
$result = $conn->query($sql);
$notifications = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
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
            </div>
        </nav>
    </header>
    <main class="container">
        <h2>Notifications</h2>
        <ul class="list-group">
            <?php foreach ($notifications as $notification): ?>
                <li class="list-group-item">
                    <p><?php echo htmlspecialchars($notification['contenu']); ?></p>
                    <p><small><?php echo htmlspecialchars($notification['date_notification']); ?></small></p>
                </li>
            <?php endforeach; ?>
        </ul>
    </main>
    <footer class="bg-light text-center py-3">
        <p>&copy; 2024 ECE In - Tous droits réservés</p>
    </footer>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
