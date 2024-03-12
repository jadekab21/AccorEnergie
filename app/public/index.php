<?php

require_once '../vendor/autoload.php';

use App\Session;
use App\Database;

use App\Page;


$session = new Session();
$page = new Page();
$clientId = $session->get('user_id');
$intervenantId = $session->get('user_id');
ini_set('display_errors', 1);
error_reporting(E_ALL);

$session = new Session();
$db = new Database("mysql", "root", "", "AccorEnergie");

$loader = new \Twig\Loader\FilesystemLoader('../templates');
$twig = new \Twig\Environment($loader);

$requestUri = $_SERVER['REQUEST_URI'];

if (strpos($requestUri, '/classes/cancel_intervention.php') !== false) {
    require '../classes/cancel_intervention.php';
    exit;
}
if (strpos($requestUri, '/classes/edit_intervention.php') !== false) {
    require '../classes/edit_intervention.php';
    exit;
}
// Vérification de la demande de suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'deleteIntervention') {
    $interventionId = (int) $_POST['intervention_id'];

    // Appel de la méthode de suppression
    $result = $page->deleteIntervention($interventionId);

    // Gestion de la réponse
    if ($result) {
        // Redirection vers une page de succès ou de dashboard avec un message de succès
        header('Location: admin_dashboard.php?success=Intervention supprimée avec succès');
    } else {
        // Redirection vers une page d'erreur ou retour au dashboard avec un message d'erreur
        header('Location: admin_dashboard.php?error=Erreur lors de la suppression de l\'intervention');
    }
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'create_intervention') {
    $interventionData = [
        'title' => $_POST['title'],
        'description' => $_POST['description'],
        'client_id' => $_POST['client_id'],
        'urgency_level' => $_POST['urgency_level'],
        'date_planned' => $_POST['date_planned']
    ];
    $intervenantIds = $_POST['intervenant_ids']; // Assurez-vous que c'est un tableau

    $message = $page->createIntervention($interventionData, $intervenantIds);
    echo $twig->render('message.twig', ['message' => $message]);
    exit;
}

if (strpos($requestUri, '/classes/edit_user.php') !== false) {
    require '../classes/edit_user.php';
    exit;
}


if (strpos($requestUri, '/classes/create_urgence.php') !== false) {
    require '../classes/create_urgence.php';
    exit;
}
if (strpos($requestUri, '/classes/delete_urgence.php') !== false) {
    require '../classes/delete_urgence.php';
    exit;
}
if (strpos($requestUri, '/classes/edit_urgence.php') !== false) {
    require '../classes/create_status.php';
    exit;
}

$errorMessage = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['form_type']) && $_POST['form_type'] == 'submit') {
        require '../classes/register.php';
        exit;
    }

    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $db->getPdo()->prepare("SELECT user_id, password, role_name FROM Users INNER JOIN Roles ON Users.role_id = Roles.role_id WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $session->add('user_id', $user['user_id']);
        $session->add('role', $user['role_name']);

        switch ($session->get('role')) {
            case 'admin':
                $dashboardData = $page->getDashboardData();
                echo $page->render('admin_dashboard.html.twig', $dashboardData);
                break;
            case 'client':
                $dashboardData = $page->getClientDashboardData($clientId);

                // Rendez le template pour le tableau de bord client avec les données récupérées
                echo $page->render('client_dashboard.html.twig', ['interventions' => $dashboardData]);
                break;
            case 'intervenant':
                $intervenantId = $_SESSION['user_id'];
    $dashboardData = $page->getIntervenantDashboardData($intervenantId);
    echo $page->render('intervenant_dashboard.html.twig', $dashboardData);
                break;
            case 'standardiste':
                $dashboardData = $page->getStandardisteDashboardData();
                echo $page->render('standardiste_dashboard.html.twig', $dashboardData);
                break;
            default:
                require 'public/error_page.php';
                break;
        }
        exit;
    } else {
        $errorMessage = "Nom d'utilisateur ou mot de passe incorrect";
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'createStatus') {
    if (!empty($_POST['status'])) {
        $status = trim($_POST['status']);
        $createResult = $page->createStatus($status);

        if ($createResult) {
            // Redirection vers la page de gestion des statuts avec un message de succès
           echo ('status ajouté');
        } else {
            // Gestion de l'erreur
            header('Location: gestionStatus.php?error=Erreur lors de l\'ajout du status');
        }
    } else {
        // Données du formulaire non valides
        header('Location: gestionStatus.php?error=Données du formulaire non valides');
    }
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'editStatus') {
    if(!empty($_POST['ids'])) {
    $ids = trim($_POST['ids']);
    $newStatus = $_POST['newStatus'];
    $result = $page->updateStatus($ids, $newStatus);
    if ($result){
        echo ("updated");
    }else{
        echo("Error during update process");
    }
    // Appel à une méthode de votre objet Page pour supprimer le statut
    $result = $page->updateStatus($ids, $newStatus);
    exit;
}
}
// Supposons que vous avez déjà inclus vos dépendances et initialisé les objets nécessaires

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'deleteStatus') {
    $statusId = $_POST['ids'];

    // Appel à une méthode de votre objet Page pour supprimer le statut
    $success = $page->deleteStatus($statusId);
    exit;
}
if (isset($_GET['action']) && $_GET['action'] === 'editIntervention' && isset($_GET['id'])) {
    $interventionId = $_GET['id'];
    echo $page->editInterventionForm($interventionId);
    exit;
}


echo $twig->render('index.html.twig', ['error' => $errorMessage]);



