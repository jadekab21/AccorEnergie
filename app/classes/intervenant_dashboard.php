<?php

// Connect to the database

// Configuration de la base de données et connexion
$dbHost = "mysql";
$dbUser = "root";
$dbPassword = "";
$dbName = "AccorEnergie";
$conn = new mysqli($dbHost, $dbUser, $dbPassword, $dbName);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Check if the user is logged in and is an intervenant
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'intervenant') {
    header('Location: index.html.twig');
    exit;
}

// Fetch interventions
$intervenantId = $_SESSION['user_id'];
$interventionsQuery = "SELECT * FROM Interventions WHERE intervenant_id = $intervenantId";
$interventionsResult = mysqli_query($conn, $interventionsQuery);
$interventions = [];

while ($intervention = mysqli_fetch_assoc($interventionsResult)) {
    $interventions[] = $intervention;
}

mysqli_close($conn);

require_once '../vendor/autoload.php';
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
$twig = new \Twig\Environment($loader);

echo $twig->render('intervenant_dashboard.html.twig', ['interventions' => $interventions]);
?>
