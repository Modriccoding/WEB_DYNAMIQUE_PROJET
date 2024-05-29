<?php
session_start();
include 'connexion.php';

// Activer les messages d'erreur pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['utilisateur_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['utilisateur_id'];

// Récupérer les noms d'utilisateur
$usernames = [];
$user_query = "SELECT id, pseudo FROM utilisateurs";
$user_result = $conn->query($user_query);
while ($row = $user_result->fetch_assoc()) {
    $usernames[$row['id']] = $row['pseudo'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['send_message'])) {
        if (isset($_POST['friend_id']) && isset($_POST['message'])) {
            $friend_id = $_POST['friend_id'];
            $msg_content = $_POST['message'];

            // Vérifier si les utilisateurs sont amis
            $check_friendship = "SELECT * FROM amis WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)";
            $stmt = $conn->prepare($check_friendship);
            if ($stmt === false) {
                die("Erreur de préparation de la requête: " . $conn->error);
            }
            $stmt->bind_param("iiii", $user_id, $friend_id, $friend_id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Insérer le message dans la base de données
                $query = "INSERT INTO messages (utilisateur_id, destinataire_id, contenu) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($query);
                if ($stmt === false) {
                    die("Erreur de préparation de la requête: " . $conn->error);
                }
                $stmt->bind_param("iis", $user_id, $friend_id, $msg_content);

                if ($stmt->execute()) {
                    // Rediriger pour éviter la soumission multiple du formulaire
                    header("Location: messagerie.php?friend_id=$friend_id");
                    exit();
                } else {
                    die("Erreur lors de l'envoi du message : " . $stmt->error);
                }
            } else {
                die("Erreur: Vous ne pouvez envoyer des messages qu'à vos amis.");
            }
        } else {
            die("Erreur: Informations manquantes.");
        }
    }
}

// Récupérer la liste des amis
$query = "SELECT u.id, u.pseudo FROM utilisateurs u 
          INNER JOIN amis a ON (u.id = a.user_id OR u.id = a.friend_id) 
          WHERE (a.user_id = ? OR a.friend_id = ?) AND u.id != ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("iii", $user_id, $user_id, $user_id);
$stmt->execute();
$friends_result = $stmt->get_result();

// Récupérer les messages
$friend_id = isset($_GET['friend_id']) ? $_GET['friend_id'] : null;
$messages_result = null;
if ($friend_id) {
    $query = "SELECT * FROM messages 
              WHERE (utilisateur_id = ? AND destinataire_id = ?) OR (utilisateur_id = ? AND destinataire_id = ?) 
              ORDER BY date_envoi ASC"; // Modifier l'ordre de tri pour ascendant
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiii", $user_id, $friend_id, $friend_id, $user_id);
    $stmt->execute();
    $messages_result = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messagerie</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        .messages {
            border: 1px solid #ccc;
            padding: 15px;
            border-radius: 5px;
            max-height: 400px;
            overflow-y: auto;
            margin-bottom: 20px;
        }
        .message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 15px;
            max-width: 75%;
            word-wrap: break-word;
        }
        .message p {
            margin: 0;
        }
        .message small {
            display: block;
            color: #888;
        }
        .text-right {
            text-align: right;
        }
        .text-left {
            text-align: left;
        }
        .message-right {
            background-color: #d4edda;
            margin-left: auto;
        }
        .message-left {
            background-color: #f8d7da;
            margin-right: auto;
        }
    </style>
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
        <h2>Messagerie</h2>
        <div class="row">
            <div class="col-md-4">
                <h3>Amis</h3>
                <ul class="list-group">
                    <?php while ($row = $friends_result->fetch_assoc()): ?>
                        <li class="list-group-item">
                            <a href="messagerie.php?friend_id=<?= $row['id'] ?>"><?= htmlspecialchars($row['pseudo']) ?></a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>
            <div class="col-md-8">
                <?php if ($friend_id): ?>
                    <h3>Conversation</h3>
                    <div class="messages" id="messages">
                        <?php if ($messages_result && $messages_result->num_rows > 0): ?>
                            <?php while ($msg = $messages_result->fetch_assoc()): ?>
                                <div class="message <?= $msg['utilisateur_id'] == $user_id ? 'message-right text-right' : 'message-left text-left' ?>">
                                    <p><strong><?= $usernames[$msg['utilisateur_id']] ?>:</strong> <?= htmlspecialchars($msg['contenu']) ?></p>
                                    <small><?= date('d/m/Y H:i:s', strtotime($msg['date_envoi'])) ?></small>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p>Aucun message.</p>
                        <?php endif; ?>
                    </div>
                    <form action="messagerie.php" method="post">
                        <input type="hidden" name="friend_id" value="<?= $friend_id ?>">
                        <div class="form-group">
                            <label for="message">Message :</label>
                            <textarea class="form-control" id="message" name="message" required></textarea>
                        </div>
                        <button type="submit" name="send_message" class="btn btn-primary">Envoyer</button>
                    </form>
                <?php else: ?>
                    <p>Sélectionnez un ami pour commencer une conversation.</p>
                <?php endif; ?>
            </div>
        </div>
        <a href="index.php" class="btn btn-secondary mt-3">Retour à l'accueil</a>
    </main>
    <footer class="bg-light text-center py-3">
        <p>&copy; 2024 ECE In - Tous droits réservés</p>
    </footer>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Fonction pour défiler automatiquement vers le bas
        function scrollToBottom() {
            var messagesDiv = document.getElementById("messages");
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }

        // Appeler la fonction après le chargement de la page
        window.onload = scrollToBottom;
    </script>
</body>
</html>
