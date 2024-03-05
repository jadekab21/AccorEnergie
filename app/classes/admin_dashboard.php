<?php
require_once '../vendor/autoload.php';
use App\Database;
use App\Session;

$session = new Session();
$db = new Database("mysql", "root", "", "AccorEnergie");
$pdo = $db->getPdo();

$interventionsQuery = "SELECT 
    Interventions.*, 
    Status.status, 
    Urgences.urgency_level, 
    GROUP_CONCAT(DISTINCT InterventionIntervenants.intervenant_id SEPARATOR ', ') AS intervenants_ids 
FROM 
    Interventions 
JOIN 
    Status ON Interventions.ids = Status.ids 
JOIN 
    Urgences ON Interventions.idu = Urgences.idu
LEFT JOIN 
    InterventionIntervenants ON Interventions.intervention_id = InterventionIntervenants.intervention_id 
GROUP BY 
    Interventions.intervention_id;";

$interventions = $pdo->query($interventionsQuery)->fetchAll(PDO::FETCH_ASSOC);

$users = $pdo->query("SELECT * FROM Users")->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT ids, status FROM Status");
$statuses = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT idu, urgency_level FROM urgences");
$urgences = $stmt->fetchAll(PDO::FETCH_ASSOC);

$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
$twig = new \Twig\Environment($loader);

echo $twig->render('admin_dashboard.html.twig', [
    'interventions' => $interventions,
    'statuses' => $statuses,
    'users' => $users,
    'urgences'=>$urgences
]);
