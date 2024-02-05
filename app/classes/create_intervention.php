<?php
$dbHost = "mysql";
$dbUser = "root";
$dbPassword = "";
$dbName = "AccorEnergie";
$conn = new mysqli($dbHost, $dbUser, $dbPassword, $dbName);


// Vérifier si l'utilisateur est connecté et est un standardiste
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'standardiste') {
    header('Location: login.php');
    exit;
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collecte et nettoyage des données du formulaire
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $client_id = mysqli_real_escape_string($conn, $_POST['client_id']);
    $urgency_level = mysqli_real_escape_string($conn, $_POST['urgency_level']);
    $date_planned = mysqli_real_escape_string($conn, $_POST['date_planned']);

    // Insertion de la nouvelle intervention dans la base de données
    $insertQuery = "INSERT INTO Interventions (title, description, client_id, date_planned, status, urgency_level) 
                    VALUES ('$title', '$description', '$client_id', '$date_planned', 'En attente', '$urgency_level')";

    if (mysqli_query($conn, $insertQuery)) {
        $message = "Intervention créée avec succès.";
    } else {
        $message = "Erreur lors de la création de l'intervention: " . mysqli_error($conn);
    }
}

// Utiliser Twig pour afficher le message
require_once '../vendor/autoload.php';
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
$twig = new \Twig\Environment($loader);

echo $twig->render('message.twig', ['message' => $message]);
exit;
