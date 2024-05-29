<?php
require 'connexion.php'; // Contient les informations de connexion à la base de données

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données du formulaire
    $pseudo = $_POST['pseudo'];
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $mot_de_passe = password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT);

    // Vérifier si l'email ou le pseudo existent déjà
    $sql = "SELECT email, pseudo FROM utilisateurs WHERE email = ? OR pseudo = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ss", $email, $pseudo);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            // Email ou pseudo déjà utilisé
            $stmt->bind_result($existing_email, $existing_pseudo);
            $stmt->fetch();
            if ($existing_email == $email) {
                header("location: register.html?error=email");
            } elseif ($existing_pseudo == $pseudo) {
                header("location: register.html?error=pseudo");
            }
            exit();
        } else {
            // Insérer les données de l'utilisateur
            $stmt->close();
            $sql = "INSERT INTO utilisateurs (pseudo, nom, email, mot_de_passe) VALUES (?, ?, ?, ?)";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("ssss", $pseudo, $nom, $email, $mot_de_passe);
                if ($stmt->execute()) {
                    // Rediriger l'utilisateur vers la page de connexion après une inscription réussie
                    header("location: login.html");
                    exit();
                } else {
                    header("location: register.html?error=insert");
                    exit();
                }
            }
        }
        $stmt->close();
    }
}
$conn->close();
?>
