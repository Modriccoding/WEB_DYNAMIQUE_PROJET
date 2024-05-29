<?php
session_start();
include 'connexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $mot_de_passe = $_POST['mot_de_passe'];

    // Vérifiez si l'email existe
    $sql = "SELECT * FROM utilisateurs WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $utilisateur = $result->fetch_assoc();

        // Vérifiez le mot de passe
        if (password_verify($mot_de_passe, $utilisateur['mot_de_passe'])) {
            // Connexion réussie
            $_SESSION['utilisateur_id'] = $utilisateur['id'];
            $_SESSION['pseudo'] = $utilisateur['pseudo']; // Ajoutez cette ligne pour enregistrer le pseudo
            header("Location: index.php");
            exit();
        } else {
            // Mot de passe incorrect
            header("Location: login.html?error=password");
            exit();
        }
    } else {
        // Email incorrect
        header("Location: login.html?error=email");
        exit();
    }
}
?>
