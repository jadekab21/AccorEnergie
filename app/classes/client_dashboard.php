<?php


// Connect to the database
$dbHost = "mysql";
$dbUser = "root";
$dbPassword = "";
$dbName = "AccorEnergie";
$conn = new mysqli($dbHost, $dbUser, $dbPassword, $dbName);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the user is logged in and is a client
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header('Location: index.html.twig');
    exit;
}

// Fetch interventions for the logged-in client
$clientId = $_SESSION['user_id'];
$interventionsQuery = "SELECT * FROM Interventions WHERE client_id = $clientId";
$interventionsResult = mysqli_query($conn, $interventionsQuery);
$interventions = [];

while ($intervention = mysqli_fetch_assoc($interventionsResult)) {
    $interventions[] = $intervention;
}

mysqli_close($conn);

require_once '../vendor/autoload.php';
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
$twig = new \Twig\Environment($loader);

echo $twig->render('client_dashboard.html.twig', ['interventions' => $interventions]);
?>