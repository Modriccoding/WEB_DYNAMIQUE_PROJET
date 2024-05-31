<?php
session_start();
include 'connexion.php';

// Activer les messages d'erreur pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['utilisateur_id'])) {
    echo 'Erreur: Utilisateur non connecté.';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['message_id'])) {
        $message_id = $_POST['message_id'];
        $user_id = $_SESSION['utilisateur_id'];

        // Vérifier si l'utilisateur a déjà liké le message
        $check_like_query = "SELECT * FROM likes WHERE message_id = ? AND user_id = ?";
        $stmt = $conn->prepare($check_like_query);
        $stmt->bind_param("ii", $message_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Si l'utilisateur a déjà liké, supprimer le like
            $delete_like_query = "DELETE FROM likes WHERE message_id = ? AND user_id = ?";
            $stmt = $conn->prepare($delete_like_query);
            $stmt->bind_param("ii", $message_id, $user_id);
            $stmt->execute();
        } else {
            // Sinon, ajouter un like
            $insert_like_query = "INSERT INTO likes (message_id, user_id) VALUES (?, ?)";
            $stmt = $conn->prepare($insert_like_query);
            $stmt->bind_param("ii", $message_id, $user_id);
            $stmt->execute();
        }

        // Mettre à jour le nombre de likes dans la table des messages
        $update_likes_query = "UPDATE messages SET likes = (SELECT COUNT(*) FROM likes WHERE message_id = ?) WHERE id = ?";
        $stmt = $conn->prepare($update_likes_query);
        $stmt->bind_param("ii", $message_id, $message_id);
        $stmt->execute();

        echo 'success';
    } else {
        echo 'Erreur: ID de message non spécifié.';
    }
} else {
    echo 'Erreur: Requête non valide.';
}
?>

