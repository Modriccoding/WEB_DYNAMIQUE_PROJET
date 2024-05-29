<?php
session_start();
include 'connexion.php';

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['utilisateur_id'])) {
    echo "Utilisateur non connecté.";
    exit();
}

$utilisateur_id = $_SESSION['utilisateur_id'];

// Récupérer les demandes d'amis pour l'utilisateur connecté
$sql = "SELECT * FROM demandes_amis WHERE destinataire_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $utilisateur_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $sender_id = $row['demandeur_id'];
        $user_query = "SELECT pseudo FROM utilisateurs WHERE id = ?";
        $user_stmt = $conn->prepare($user_query);
        $user_stmt->bind_param("i", $sender_id);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        
        if ($user_result->num_rows > 0) {
            $user = $user_result->fetch_assoc();
            echo "<p>Demande d'ami de : " . htmlspecialchars($user['pseudo']) . "</p>";
            echo "<form method='post' action='accept_friend.php'>
                    <input type='hidden' name='sender_id' value='$sender_id'>
                    <input type='submit' name='accept_friend' value='Accepter'>
                  </form>";
            echo "<form method='post' action='decline_friend.php'>
                    <input type='hidden' name='sender_id' value='$sender_id'>
                    <input type='submit' name='decline_friend' value='Refuser'>
                  </form>";
        } else {
            echo "Erreur lors de la récupération de l'utilisateur.";
        }
    }
} else {
    echo "Aucune demande d'ami.";
}
?>
