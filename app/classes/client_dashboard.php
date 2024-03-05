<?php

require_once '../vendor/autoload.php';
use App\Database;
use App\Session;

$session = new Session();

// Check if the user is logged in and is a client


$db = new Database("mysql", "root", "", "AccorEnergie");
$pdo = $db->getPdo();

$clientId = $_SESSION['user_id'];
$interventionsQuery = "SELECT * FROM Interventions WHERE client_id = :clientId";
$interventionsStatement = $pdo->prepare($interventionsQuery);
$interventionsStatement->bindParam(':clientId', $clientId, PDO::PARAM_INT);
$interventionsStatement->execute();
$interventions = $interventionsStatement->fetchAll(PDO::FETCH_ASSOC);

$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
$twig = new \Twig\Environment($loader);

echo $twig->render('client_dashboard.html.twig', ['interventions' => $interventions]);
