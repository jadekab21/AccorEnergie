<?php

require_once '../vendor/autoload.php';
use App\Database;
use App\Session;

$session = new Session();

// Check if the user is logged in and is an intervenant



$db = new Database("mysql", "root", "", "AccorEnergie");
$pdo = $db->getPdo();

// Fetch interventions
$intervenantId = $_SESSION['user_id'];
$interventionsQuery = "SELECT Interventions.* FROM Interventions 
JOIN InterventionIntervenants ON Interventions.intervention_id = InterventionIntervenants.intervention_id 
WHERE InterventionIntervenants.intervenant_id = :intervenantId";
$interventionsStatement = $pdo->prepare($interventionsQuery);
$interventionsStatement->bindParam(':intervenantId', $intervenantId, PDO::PARAM_INT);
$interventionsStatement->execute();
$interventions = $interventionsStatement->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT ids, status FROM Status");
$statuses = $stmt->fetchAll(PDO::FETCH_ASSOC);

$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
$twig = new \Twig\Environment($loader);

echo $twig->render('intervenant_dashboard.html.twig', ['interventions' => $interventions,  'statuses' => $statuses,]);
