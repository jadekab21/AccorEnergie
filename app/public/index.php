<?php

require_once '../vendor/autoload.php';

use App\Session;
use App\Database;

use App\Page;


$session = new Session();
error_log('Session Debug: ' . print_r($_SESSION, true));
$page = new Page();
$clientId = $session->get('user_id');
$intervenantId = $session->get('user_id');
ini_set('display_errors', 1);
error_reporting(E_ALL);


$db = new Database("mysql", "root", "", "AccorEnergie");

$loader = new \Twig\Loader\FilesystemLoader('../templates');
$twig = new \Twig\Environment($loader);

$requestUri = $_SERVER['REQUEST_URI'];

$errorMessage = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['form_type']) && $_POST['form_type'] == 'submit') {
        
        $page->registerUser($_POST);
        exit;
    }

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    

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
                $clientId = $session->get('user_id');
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
                    // Supposons que vous stockez l'ID de l'utilisateur connecté dans la session sous la clé 'user_id'.
                    $standardisteId = $session->get('user_id'); // Assurez-vous d'avoir la bonne clé pour l'ID de l'utilisateur.
                
                    // Appeler la fonction en passant l'ID du standardiste.
                    $dashboardData = $page->getStandardisteDashboardData($standardisteId);
                
                    // Rendre le tableau de bord du standardiste avec les données récupérées.
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

if (isset($_GET['action']) && $_GET['action'] === 'editInterventionForm' && isset($_GET['intervention_id'])) {
    $interventionId = $_GET['intervention_id'];
    // Assurez-vous d'avoir une méthode dans la classe `Page` pour récupérer les détails de l'utilisateur par ID
    echo $page->editInterventionForm($interventionId);
    exit;
}



if (isset($_GET['action']) && $_GET['action'] === 'editUserForm' && isset($_GET['user_id'])) {
    $userId = $_GET['user_id'];
    // Assurez-vous d'avoir une méthode dans la classe `Page` pour récupérer les détails de l'utilisateur par ID
    $userDetails = $page->getUserDetailsById($userId);
    
    // Assurez-vous également d'avoir une méthode pour récupérer tous les rôles si votre formulaire en a besoin
    $roles = $page->getAllRoles();

    echo $page->render('edit_user.twig', [
        'user' => $userDetails,
        'roles' => $roles
    ]);
    exit;
}

if (isset($_POST['action']) && $_POST['action'] === 'registerUser') {
    // Hachage du mot de passe
    $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Préparation des données à passer
    $postData = [
        'username' => $_POST['username'],
        'email' => $_POST['email'],
        'password' => $hashedPassword, // Utilisation du mot de passe haché
        'role_id' => $_POST['role_id'],
    ];
    
    // Appel de la fonction avec les données, y compris le mot de passe haché
    $result = $page->registerUser($postData);

    if ($result) {
        // L'enregistrement est réussi, redirection en fonction du rôle
        switch ($session->get('role')) {
            case 'admin':
                $dashboardData = $page->getDashboardData();
                echo $page->render('admin_dashboard.html.twig', $dashboardData);
                break;
            case 'client':
                $clientId = $session->get('user_id');
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
                    // Supposons que vous stockez l'ID de l'utilisateur connecté dans la session sous la clé 'user_id'.
                    $standardisteId = $session->get('user_id'); // Assurez-vous d'avoir la bonne clé pour l'ID de l'utilisateur.
                
                    // Appeler la fonction en passant l'ID du standardiste.
                    $dashboardData = $page->getStandardisteDashboardData($standardisteId);
                
                    // Rendre le tableau de bord du standardiste avec les données récupérées.
                    echo $page->render('standardiste_dashboard.html.twig', $dashboardData);
                    break;
                
            default:
                require 'public/error_page.php';
                break;
        }
    
}
}


// Vérification de la déconnexion
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $session->logout();
}


if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'getComments' && isset($_GET['interventionId'])) {
    $interventionId = $_GET['interventionId'];
    $comments = $page->getCommentsByInterventionId($interventionId);

    header('Content-Type: application/json');
    echo json_encode($comments);
    exit;
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $success = false; // Initialiser la variable de succès
    switch ($_POST['action']) {
        case 'addUrgence':
            if (!empty($_POST['urgence'])) {
                $urgence = trim($_POST['urgence']);
                if ($page->addUrgence($urgence)) {
                    // Redirection ou gestion de la réponse avec succès
                   // header('Location: /chemin/vers/gestionUrgences.php?success=Urgence ajoutée');
                   $success = true; 
                } else {
                    die('Erreur lors de l\'ajout de l\'urgence.');
                }
            } else {
                die('Données du formulaire non valides.');
            }
            break;
            case 'deleteIntervention': 
                $interventionId = (int) $_POST['intervention_id'];

                // Appel de la méthode de suppression
                $result = $page->deleteIntervention($interventionId);
                if($result){
                    $success = true; 
                }            
            case 'deleteUrgence':
                if (isset($_POST['id'])) {
                    $idu = $_POST['id'];
                    if ($page->deleteUrgence($idu)) {
                        // Redirection avec message de succès
                       $success = true; 
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
                            $success = true; 
                        } else {
                            // Gestion de l'erreur
                           // header('Location: gestionUrgences.php?error=Erreur lors de la mise à jour de l\'urgence');
                        }
                    } else {
                        // Données manquantes
                       // header('Location: gestionUrgences.php?error=Données manquantes pour la mise à jour');
                    }
                    break;
                    case 'editUser':
                        if (isset($_POST['user_id'], $_POST['username'], $_POST['email'], $_POST['role_id'])) {
                            $userId = $_POST['user_id'];
                            $username = $_POST['username'];
                            $email = $_POST['email'];
                            $roleId = $_POST['role_id'];
                            if ($page->editUser($userId, $username, $email, $roleId)) {
                                $success = true; 
                                // Redirection avec message de succès
                               // header('Location: user_management.php?success=User updated successfully');
                            } else {
                                // Gestion de l'erreur
                               // header('Location: user_management.php?error=Error updating user');
                            }
                        } else {
                            // Données manquantes
                           // header('Location: user_management.php?error=Missing data for updating');
                        }
                        break;
                        case 'create_intervention' :
                            $interventionData = [
                                'title' => $_POST['title'],
                                'description' => $_POST['description'],
                                'client_id' => $_POST['client_id'],
                                'urgency_level' => $_POST['urgency_level'],
                                'date_planned' => $_POST['date_planned'],
                                'status_id' => $_POST['status']
                            ];
                            $intervenantIds = $_POST['intervenant_ids']; // Assurez-vous que c'est un tableau
                        
                            $result = $page->createIntervention($interventionData, $intervenantIds);
                            if ($result) {
                               $success = true; 
                                error_log(print_r($_POST, true));
                            }
                            break;
                        case'editIntervention': 
                            $interventionId = $_POST['interventionId'];
                            $title = $_POST['title'];
                            $description = $_POST['description'];
                            $status = $_POST['status'];
                            $urgencyLevel = $_POST['urgency_level'];
                            $datePlanned = $_POST['date_planned'];
                            $intervenantIds = $_POST['intervenant_ids'] ?? [];

                                                    // Appeler votre méthode pour mettre à jour l'intervention
                            $result = $page->editIntervention([
                                'interventionId' => $interventionId,
                                'title' => $title,
                                'description' => $description,
                                'status' => $status,
                                'urgency_level' => $urgencyLevel,
                                'date_planned' => $datePlanned,
                                'intervenant_ids' => $intervenantIds,
                            ]);

                            if ($result) {
                                // Gestion de la réussite
                               // Gestion de la réussite, rendu direct avec Twig
                               $success = true; 
                                error_log(print_r($_POST, true));
                            } else {
                                // Gestion de l'échec
                                header('Location: admin_dashboard.php?error=Erreur lors de la mise à jour');
                                error_log(print_r($_POST, true));
                            }
                            break;
                            case 'cancel_intervention': 
                                $interventionId = $_POST['intervention_id'] ?? null;
                                $userRole = $session->get('role');
                            
                                // Vérifiez ici si l'utilisateur est un standardiste
                                if ($userRole === 'standardiste') {
                                    $message = $page->cancelIntervention($interventionId);
                                    // Gérer le message de succès ou d'erreur
                                    // Par exemple, vous pouvez rediriger vers une page avec le message
                                    echo $page->render('message_page.twig', ['message' => $message]);
                                } else {
                                    // Gérer le cas où l'utilisateur n'a pas le droit d'annuler l'intervention
                                    $message = "Vous n'avez pas les droits nécessaires pour annuler cette intervention.";
                                    // Redirection ou affichage du message
                                    echo $page->render('error_page.twig', ['message' => $message]);
                                }
                                exit;
                                break;
                                // D'autres cas pour d'autres actions
                            }
              // Si une action a été effectuée avec succès, rediriger selon le rôle
              if ($success) {
                $role = $session->get('role'); // Assumer que le rôle est stocké dans la session
                switch ($role) {
                    case 'admin':
                        $dashboardData = $page->getDashboardData();
                        echo $page->render('admin_dashboard.html.twig', $dashboardData);
                        break;
                    case 'client':
                        $dashboardData = $page->getClientDashboardData($session->get('user_id'));
                        echo $page->render('client_dashboard.html.twig', ['interventions' => $dashboardData]);
                        break;
                    case 'intervenant':
                        $dashboardData = $page->getIntervenantDashboardData($session->get('user_id'));
                        echo $page->render('intervenant_dashboard.html.twig', $dashboardData);
                        break;
                    case 'standardiste':
                        $dashboardData = $page->getStandardisteDashboardData($session->get('user_id'));
                        echo $page->render('standardiste_dashboard.html.twig', $dashboardData);
                        break;
                    default:
                        // Redirection vers la page de connexion ou une autre page par défaut
                       // echo $page->render('index.html.twig');
                        break;
                }
                exit; // Assurez-vous de quitter pour éviter d'exécuter du code supplémentaire inutilement
            }
            
}

// Traitement de l'ajout d'un commentaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'postComment') {
    $interventionId = $_POST['intervention_id'];
    $userId = $_SESSION['user_id']; // Récupérer l'ID de l'utilisateur connecté depuis la session
    $content = $_POST['content'];

    $result = $page->addComment($interventionId, $userId, $content);

    if ($result) {
        $dashboardData = $page->getStandardisteDashboardData($session->get('user_id'));
        echo $page->render('standardiste_dashboard.html.twig', $dashboardData);
    } else {
      //  echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout du commentaire']);
    }
    exit;
}



// Vérifiez si l'action est 'cancelIntervention'
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'cancelIntervention') {
    $interventionId = $_POST['intervention_id'] ?? null;
    $userRole = $session->get('role');

    // Vérifiez ici si l'utilisateur est un standardiste
    if ($userRole === 'standardiste') {
        $message = $page->cancelIntervention($interventionId);
        // Gérer le message de succès ou d'erreur
        // Par exemple, vous pouvez rediriger vers une page avec le message
        $dashboardData = $page->getStandardisteDashboardData($session->get('user_id'));
                        echo $page->render('standardiste_dashboard.html.twig', $dashboardData);
    } else {
        // Gérer le cas où l'utilisateur n'a pas le droit d'annuler l'intervention
        $message = "Vous n'avez pas les droits nécessaires pour annuler cette intervention.";
        // Redirection ou affichage du message
        echo $page->render('error_page.twig', ['message' => $message]);
    }
    exit; // Assurez-vous d'arrêter l'exécution du script après la redirection ou l'affichage du message
}

if (isset($_GET['action']) && $_GET['action'] === 'deleteUser' && isset($_GET['user_id'])) {
    $userId = $_GET['user_id'];
    // Assurez-vous d'avoir une méthode dans votre classe `Page` qui gère la suppression de l'utilisateur par ID
    $success = $page->deleteUserById($userId);
    
    if ($success) {
        $dashboardData = $page->getDashboardData();
        echo $twig->render('admin_dashboard.html.twig', $dashboardData, [
            'success' => 'Intervention mise à jour'
        ]);
        exit;
    } else {
        // Redirection ou affichage d'un message d'erreur
       // header('Location: index.php?error=Error deleting user');
    }
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'editStatusIntervention') {
    if (isset($_GET['intervention_id']) && isset($_GET['new_status_id'])) {
       
        $interventionId = $_GET['intervention_id'];
        $newStatusId = $_GET['new_status_id'];

        // Supposons que $pdo est passé à l'objet $page
        if ($page->editStatusIntervention($interventionId, $newStatusId)) {
            $dashboardData = $page->getIntervenantDashboardData($session->get('user_id'));
            echo $page->render('intervenant_dashboard.html.twig', $dashboardData);
            exit;
        } 
           
        
    } else {
        echo "Données manquantes pour modifier le statut de l'intervention.";
    }
}



echo $twig->render('index.html.twig', ['error' => $errorMessage]);



