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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['offre_id']) && isset($_SESSION['utilisateur_id']) && isset($_FILES['lettre_motivation'])) {
        $offre_id = $_POST['offre_id'];
        $utilisateur_id = $_SESSION['utilisateur_id'];
        $file = $_FILES['lettre_motivation'];

        // Vérifier si une candidature existe déjà
        $check_query = "SELECT * FROM postulations WHERE utilisateur_id = ? AND emploi_id = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("ii", $utilisateur_id, $offre_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            echo "Vous avez déjà postulé pour cette offre.";
        } else {
            // Gérer le téléchargement du fichier
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $upload_file = $upload_dir . basename($file['name']);

            if (move_uploaded_file($file['tmp_name'], $upload_file)) {
                // Insérer la candidature dans la base de données
                $query = "INSERT INTO postulations (utilisateur_id, emploi_id, lettre_motivation, date_postulation) VALUES (?, ?, ?, NOW())";
                $stmt = $conn->prepare($query);
                if ($stmt === false) {
                    die("Erreur de préparation de la requête: " . $conn->error);
                }
                $stmt->bind_param("iis", $utilisateur_id, $offre_id, $upload_file);

                if ($stmt->execute()) {
                    echo "Candidature envoyée avec succès.";
                } else {
                    echo "Erreur lors de l'envoi de la candidature : " . $stmt->error;
                }
            } else {
                echo "Erreur lors du téléchargement de la lettre de motivation.";
            }
        }
    } else {
        echo "Erreur: Informations manquantes.";
    }
} else {
    if (isset($_GET['offre_id'])) {
        $offre_id = $_GET['offre_id'];
    } else {
        die("Erreur: Offre d'emploi non spécifiée.");
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Postuler à une offre</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
   
    <div class="container">
        <h1>Postuler à une offre</h1>
        <form action="postuler.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="offre_id" value="<?= htmlspecialchars($offre_id) ?>">
            <div class="form-group">
                <label for="lettre_motivation">Lettre de motivation :</label>
                <input type="file" class="form-control" id="lettre_motivation" name="lettre_motivation" required>
            </div>
            <button type="submit" class="btn btn-primary">Valider la postulation</button>
            <a href="emplois.php" class="btn btn-secondary">Retour à Emplois</a>
        </form>
    </div>
</body>
</html>
