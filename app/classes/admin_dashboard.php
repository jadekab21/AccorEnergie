<?php
require_once '../vendor/autoload.php';
use App\Database;
use App\Session;

$session = new Session();


$db = new Database("mysql", "root", "", "AccorEnergie");
$pdo = $db->getPdo();

$interventions = $pdo->query("SELECT interventions.*, Status.* FROM interventions JOIN Status ON interventions.ids = Status.ids;
")->fetchAll(PDO::FETCH_ASSOC);
$users = $pdo->query("SELECT * 444 Users")->fetchAll(PDO::FETCH_ASSOC);

$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
$twig = new \Twig\Environment($loader);

echo $twig->render('admin_dashboard.html.twig', [
    'interventions' => $interventions,
    'users' => $users
]);