<?php

// Connexion à la base de données
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

// Vérifier la validité de l'ID de l'intervention dans l'URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $message = "Aucune intervention spécifiée pour l'annulation.";
    redirectToMessage($message);
}

$interventionId = mysqli_real_escape_string($conn, $_GET['id']);

// Annuler l'intervention
$cancelQuery = $conn->prepare("UPDATE Interventions SET status = 'Annulée' WHERE intervention_id = ?");
$cancelQuery->bind_param("i", $interventionId);

$message = '';
if ($cancelQuery->execute()) {
    $message = "Intervention annulée avec succès.";
} else {
    $message = "Erreur lors de l'annulation de l'intervention: " . mysqli_error($conn);
}

$cancelQuery->close();
$conn->close();

redirectToMessage($message);

function redirectToMessage($message) {
    require_once '../vendor/autoload.php';
    $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
    $twig = new \Twig\Environment($loader);

    echo $twig->render('message.twig', ['message' => $message]);
    exit;
}
?>
