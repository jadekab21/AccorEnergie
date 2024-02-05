<?php
require_once '../vendor/autoload.php';

// Redirige vers la page de connexion si l'utilisateur n'est pas connecté ou n'est pas un admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Configuration de la base de données et connexion avec PDO
$dbHost = "mysql";
$dbUser = "root";
$dbPassword = "";
$dbName = "AccorEnergie";

try {
    $conn = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPassword);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Récupération des interventions depuis la base de données
$interventionsQuery = "SELECT * FROM Interventions";
$interventionsResult = $conn->query($interventionsQuery);
$interventions = $interventionsResult->fetchAll(PDO::FETCH_ASSOC);

// Récupération des utilisateurs depuis la base de données
$usersQuery = "SELECT * FROM Users";
$usersResult = $conn->query($usersQuery);
$users = $usersResult->fetchAll(PDO::FETCH_ASSOC);

// Initialisation de l'environnement Twig
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
$twig = new \Twig\Environment($loader);

// Render le template Twig avec les données récupérées
echo $twig->render('admin_dashboard.html.twig', [
    'interventions' => $interventions,
    'users' => $users
]);

// Fermeture de la connexion à la base de données
$conn = null;
