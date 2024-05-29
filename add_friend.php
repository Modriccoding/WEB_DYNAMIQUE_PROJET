<?php
session_start();
include 'connexion.php';

// Activer les messages d'erreur pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['utilisateur_id'])) {
    die("Erreur: Utilisateur non connecté.");
}

$sender_id = $_SESSION['utilisateur_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_friend'])) {
        if (isset($_POST['friend_username'])) {
            $friend_username = $_POST['friend_username'];

            // Rechercher l'ID de l'utilisateur correspondant au nom d'utilisateur fourni
            $query = "SELECT id FROM utilisateurs WHERE pseudo = ?";
            $stmt = $conn->prepare($query);
            if ($stmt === false) {
                die("Erreur de préparation de la requête: " . $conn->error);
            }
            $stmt->bind_param("s", $friend_username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $row = $result->fetch_assoc();
                $receiver_id = $row['id'];

                // Vérifier si l'utilisateur essaie de s'ajouter lui-même
                if ($sender_id == $receiver_id) {
                    $message = "Erreur: Vous ne pouvez pas vous ajouter vous-même en tant qu'ami.";
                } else {
                    // Vérifier si une relation d'amitié existe déjà
                    $check_friendship = "SELECT * FROM amis WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)";
                    $stmt = $conn->prepare($check_friendship);
                    if ($stmt === false) {
                        die("Erreur de préparation de la requête: " . $conn->error);
                    }
                    $stmt->bind_param("iiii", $sender_id, $receiver_id, $receiver_id, $sender_id);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        // Les utilisateurs sont déjà amis
                        $message = "Erreur: Vous êtes déjà amis avec cet utilisateur.";
                    } else {
                        // Vérifier si une demande existe déjà
                        $check_request = "SELECT * FROM demandes_amis WHERE demandeur_id = ? AND destinataire_id = ?";
                        $stmt = $conn->prepare($check_request);
                        if ($stmt === false) {
                            die("Erreur de préparation de la requête: " . $conn->error);
                        }
                        $stmt->bind_param("ii", $sender_id, $receiver_id);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows == 0) {
                            // Insérer la demande dans la base de données
                            $query = "INSERT INTO demandes_amis (demandeur_id, destinataire_id) VALUES (?, ?)";
                            $stmt = $conn->prepare($query);
                            if ($stmt === false) {
                                die("Erreur de préparation de la requête: " . $conn->error);
                            }
                            $stmt->bind_param("ii", $sender_id, $receiver_id);

                            if ($stmt->execute()) {
                                $message = "Demande d'ami envoyée avec succès.";
                            } else {
                                $message = "Erreur lors de l'envoi de la demande d'ami : " . $stmt->error;
                            }
                        } else {
                            $message = "Erreur: Une demande d'ami a déjà été envoyée.";
                        }
                    }
                }
            } else {
                $message = "Erreur: Aucun utilisateur trouvé avec ce nom d'utilisateur.";
            }
        } else {
            $message = "Erreur: Nom d'utilisateur non spécifié.";
        }
    } else {
        $message = "Erreur: Bouton d'envoi de la demande non spécifié.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un ami</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h1>Ajouter un ami</h1>
        <?php
        if ($message != '') {
            echo "<div class='alert alert-info'>$message</div>";
        }
        ?>
        <form action="" method="post">
            <div class="form-group">
                <label for="friend_username">Nom d'utilisateur :</label>
                <input type="text" class="form-control" id="friend_username" name="friend_username" required>
            </div>
            <button type="submit" name="add_friend" class="btn btn-primary">Ajouter comme ami</button>
        </form>
        <a href="index.php" class="btn btn-secondary mt-3">Retour à l'accueil</a>
    </div>
</body>
</html>
