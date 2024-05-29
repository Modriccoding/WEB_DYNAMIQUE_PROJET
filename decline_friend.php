<?php
session_start();
include('connexion.php');

if (isset($_POST['decline_friend'])) {
    $sender_id = $_POST['sender_id'];
    $receiver_id = $_SESSION['user_id'];

    // Supprimer la demande d'ami
    $delete_request = "DELETE FROM demandes_amis WHERE demandeur_id='$sender_id' AND destinataire_id='$receiver_id'";
    if (mysqli_query($conn, $delete_request)) {
        echo "Demande d'ami refusée.";
    } else {
        echo "Erreur lors du refus de la demande d'ami.";
    }
}
?>
