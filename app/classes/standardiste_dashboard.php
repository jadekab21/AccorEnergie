<?
$dbHost = "mysql";
$dbUser = "root";
$dbPassword = "";
$dbName = "AccorEnergie";
$conn = new mysqli($dbHost, $dbUser, $dbPassword, $dbName);

// Check if the user is logged in and is a standardiste
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'standardiste') {
    header('Location: login.php');
    exit;
}

$interventionsResult = mysqli_query($conn, "SELECT * FROM Interventions");
$clientsResult = mysqli_query($conn, "SELECT user_id, username FROM Users WHERE role_id = (SELECT role_id FROM Roles WHERE role_name = 'client')");

$interventions = [];
while ($intervention = mysqli_fetch_assoc($interventionsResult)) {
    $interventions[] = $intervention;
}

$clients = [];
while ($client = mysqli_fetch_assoc($clientsResult)) {
    $clients[$client['user_id']] = $client['username'];
}

mysqli_close($conn);

require_once '../vendor/autoload.php';
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
$twig = new \Twig\Environment($loader);

echo $twig->render('standardiste_dashboard.html.twig', [
    'interventions' => $interventions,
    'clients' => $clients
]);
?>