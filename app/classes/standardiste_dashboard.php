<?php

require_once '../vendor/autoload.php';
use App\Database;
use App\Session;

$session = new Session();

// Check if the user is logged in and is a standardiste

$db = new Database("mysql", "root", "", "AccorEnergie");
$pdo = $db->getPdo();

// Fetch interventions
$interventionsStatement = $pdo->query("SELECT * FROM Interventions");
$interventions = $interventionsStatement->fetchAll(PDO::FETCH_ASSOC);

// Fetch clients
$clientsStatement = $pdo->query("SELECT user_id, username FROM Users WHERE role_id = (SELECT role_id FROM Roles WHERE role_name = 'client')");
$clients = [];

while ($client = $clientsStatement->fetch(PDO::FETCH_ASSOC)) {
    $clients[$client['user_id']] = $client['username'];
}

$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
$twig = new \Twig\Environment($loader);

echo $twig->render('standardiste_dashboard.html.twig', [
    'interventions' => $interventions,
    'clients' => $clients
]);
