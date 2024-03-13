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
// Traitement du formulaire de mise à jour d'intervention
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'editIntervention') {
    // Récupération des données du formulaire
    $interventionId = $_POST['interventionId'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $status = $_POST['status'];
    $urgencyLevel = $_POST['urgency_level'];
    $datePlanned = $_POST['date_planned'];
    $intervenantIds = $_POST['intervenant_ids'] ?? []; // S'assurer que c'est un tableau

    // Préparation des données pour la mise à jour
    $interventionData = [
        'interventionId' => $interventionId,
        'title' => $title,
        'description' => $description,
        'status' => $status,
        'urgency_level' => $urgencyLevel,
        'date_planned' => $datePlanned,
        'intervenant_ids' => $intervenantIds, // Assurez-vous que la méthode editIntervention gère correctement les IDs des intervenants
    ];

    // Appel à la méthode de mise à jour
    $result = $page->editIntervention($interventionData);

    // Gestion de la réponse
    if ($result) {
        // Redirection vers la page d'accueil du tableau de bord avec un message de succès
      //  header('Location: dashboard.php?success=Intervention mise à jour avec succès');
    } else {
        // Afficher le message d'erreur (ou le renvoyer vers le formulaire avec le message d'erreur)
       // header('Location: edit_intervention.php?id=' . $interventionId . '&error=Erreur lors de la mise à jour de l\'intervention');
    }
    exit;
}

// Vérification de la déconnexion
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $session->logout();
}


if (isset($_GET['action']) === 'getComments' && isset($_GET['interventionId'])) {
    $comments = $page->getCommentsByInterventionId($_GET['interventionId']);
    header('Content-Type: application/json');
    echo json_encode($comments);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'addUrgence':
            if (!empty($_POST['urgence'])) {
                $urgence = trim($_POST['urgence']);
                if ($page->addUrgence($urgence)) {
                    // Redirection ou gestion de la réponse avec succès
                   // header('Location: /chemin/vers/gestionUrgences.php?success=Urgence ajoutée');
                } else {
                    die('Erreur lors de l\'ajout de l\'urgence.');
                }
            } else {
                die('Données du formulaire non valides.');
            }
            break;
            case 'deleteUrgence':
                if (isset($_POST['id'])) {
                    $idu = $_POST['id'];
                    if ($page->deleteUrgence($idu)) {
                        // Redirection avec message de succès
                       // header('Location: gestionUrgences.php?success=Urgence supprimée avec succès');
                    } else {
                        // Gestion de l'erreur
                       // header('Location: gestionUrgences.php?error=Erreur lors de la suppression de l\'urgence');
                    }
                } else {
                    // ID d'urgence manquant
                  //  header('Location: gestionUrgences.php?error=ID d\'urgence manquant');
                }
                break;
                case 'editUrgence':
                    if (isset($_POST['idu'], $_POST['urgence'])) {
                        $idu = $_POST['idu'];
                        $newUrgencyLevel = $_POST['urgence'];
                        if ($page->editUrgence($idu, $newUrgencyLevel)) {
                            // Redirection avec message de succès
                            //header('Location: gestionUrgences.php?success=Urgence mise à jour avec succès');
                        } else {
                            // Gestion de l'erreur
                           // header('Location: gestionUrgences.php?error=Erreur lors de la mise à jour de l\'urgence');
                        }
                    } else {
                        // Données manquantes
                       // header('Location: gestionUrgences.php?error=Données manquantes pour la mise à jour');
                    }
                    break;

        // D'autres cas pour d'autres actions
    }
}
// Traitement de l'ajout d'un commentaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) === 'postComment') {
    $interventionId = $_POST['intervention_id'];
    $userId = $session->get('user_id'); // Assurez-vous d'avoir la session démarrée et d'obtenir l'ID utilisateur
    $content = $_POST['content'];

    if ($page->postComment($interventionId, $userId, $content)) {
        // Redirection ou message de succès
        echo json_encode(['success' => true]);
    } else {
        // Gestion de l'erreur
        echo $twig->render('error_template.twig', ['error' => 'Erreur lors de l\'ajout du commentaire']);
    }
    exit;
}
echo $twig->render('index.html.twig', ['error' => $errorMessage]);



