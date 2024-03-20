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
    $message = ""; 
    if (isset($_POST['form_type']) && $_POST['form_type'] == 'submit') {
        $result = $page->registerUser($_POST); 
        if ($result) {
           
            $message = "Utilisateur créé avec succès.";
        } else {

            $message = "La création de l'utilisateur a échoué.";
        }

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

                echo $page->render('client_dashboard.html.twig', ['interventions' => $dashboardData]);
                break;
            case 'intervenant':
                $intervenantId = $_SESSION['user_id'];
    $dashboardData = $page->getIntervenantDashboardData($intervenantId);
    echo $page->render('intervenant_dashboard.html.twig', $dashboardData);
                break;
                case 'standardiste':
               
                    $standardisteId = $session->get('user_id'); 
                
                  
                    $dashboardData = $page->getStandardisteDashboardData($standardisteId);
                
                  
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
         
            header('Location: gestionStatus.php?error=Erreur lors de l\'ajout du status');
        }
    } else {
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

    $result = $page->updateStatus($ids, $newStatus);
    exit;
}
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'deleteStatus') {
    $statusId = $_POST['ids'];

   
    $success = $page->deleteStatus($statusId);
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'editInterventionForm' && isset($_GET['intervention_id'])) {
    $interventionId = $_GET['intervention_id'];
  
    echo $page->editInterventionForm($interventionId);
    exit;
}



if (isset($_GET['action']) && $_GET['action'] === 'editUserForm' && isset($_GET['user_id'])) {
    $userId = $_GET['user_id'];
   
    $userDetails = $page->getUserDetailsById($userId);
    
  
    $roles = $page->getAllRoles();

    echo $page->render('edit_user.twig', [
        'user' => $userDetails,
        'roles' => $roles
    ]);
    exit;
}

if (isset($_POST['action']) && $_POST['action'] === 'registerUser') {
    
    $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
  
    $postData = [
        'username' => $_POST['username'],
        'email' => $_POST['email'],
        'password' => $hashedPassword,
        'role_id' => $_POST['role_id'],
    ];
    
  
    $result = $page->registerUser($postData);

    if ($result) {
       
        switch ($session->get('role')) {
            case 'admin':
                $dashboardData = $page->getDashboardData();
                echo $page->render('admin_dashboard.html.twig', $dashboardData);
                break;
            case 'client':
                $clientId = $session->get('user_id');
                $dashboardData = $page->getClientDashboardData($clientId);

             
                echo $page->render('client_dashboard.html.twig', ['interventions' => $dashboardData]);
                break;
            case 'intervenant':
                $intervenantId = $_SESSION['user_id'];
    $dashboardData = $page->getIntervenantDashboardData($intervenantId);
    echo $page->render('intervenant_dashboard.html.twig', $dashboardData);
                break;
                case 'standardiste':
                    
                    $standardisteId = $session->get('user_id'); 
                
                 
                    $dashboardData = $page->getStandardisteDashboardData($standardisteId);
                
                 
                    echo $page->render('standardiste_dashboard.html.twig', $dashboardData);
                    break;
                
            default:
                require 'public/error_page.php';
                break;
        }
    
}
}



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
    $success = false;
    switch ($_POST['action']) {
        case 'addUrgence':
            if (!empty($_POST['urgence'])) {
                $urgence = trim($_POST['urgence']);
                if ($page->addUrgence($urgence)) {
                  
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

              
                $result = $page->deleteIntervention($interventionId);
                if($result){
                    $success = true; 
                }            
            case 'deleteUrgence':
                if (isset($_POST['id'])) {
                    $idu = $_POST['id'];
                    if ($page->deleteUrgence($idu)) {
                        
                       $success = true; 
                    } else {
                      
                    }
                } else {
                   
                }
                break;
                case 'editUrgence':
                    if (isset($_POST['idu'], $_POST['urgence'])) {
                        $idu = $_POST['idu'];
                        $newUrgencyLevel = $_POST['urgence'];
                        if ($page->editUrgence($idu, $newUrgencyLevel)) {
                            
                            $success = true; 
                        } else {
                           
                        }
                    } else {
                      
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
                        
                            } else {
                              
                            }
                        } else {
                           // rediretion
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
                            $intervenantIds = $_POST['intervenant_ids']; 
                        
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
                              
                               $success = true; 
                                error_log(print_r($_POST, true));
                            } else {
                               
                                header('Location: admin_dashboard.php?error=Erreur lors de la mise à jour');
                                error_log(print_r($_POST, true));
                            }
                            break;
                            case 'cancel_intervention': 
                                $interventionId = $_POST['intervention_id'] ?? null;
                                $userRole = $session->get('role');
                            
                             
                                if ($userRole === 'standardiste') {
                                    $message = $page->cancelIntervention($interventionId);
                                   
                                    echo $page->render('message_page.twig', ['message' => $message]);
                                } else {
                                   
                                    $message = "Vous n'avez pas les droits nécessaires pour annuler cette intervention.";
                                   
                                    echo $page->render('error_page.twig', ['message' => $message]);
                                }
                                exit;
                                break;
                                case 'deleteUser' : 
                                    $userId = $_GET['user_id'];
                                   
                                    $success = $page->deleteUserById($userId);
                                    
                                    if ($success) {
                                        $dashboardData = $page->getDashboardData();
                                        echo $twig->render('admin_dashboard.html.twig', $dashboardData, [
                                            'success' => 'Intervention mise à jour'
                                        ]);
                                        exit;
                                    } else {
                                      
                                       // header('Location: index.php?error=Error deleting user');
                                    }
                                    exit;
                                    break;

                               
                            }
             
              if ($success) {
                $role = $session->get('role');
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
                      
                       // echo $page->render('index.html.twig');
                        break;
                }
                exit; 
              }
            
}

if (isset($_GET['action']) && $_GET['action'] === 'deleteUser' && isset($_GET['user_id'])) {
    $userId = $_GET['user_id'];

    $success = $page->deleteUserById($userId);
    
    if ($success) {
        $dashboardData = $page->getDashboardData();
        echo $twig->render('admin_dashboard.html.twig', $dashboardData, [
            'success' => 'Intervention mise à jour'
        ]);
        exit;
    } else {
      
       // header('Location: index.php?error=Error deleting user');
    }
    exit;
}

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




if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'cancelIntervention') {
    $interventionId = $_POST['intervention_id'] ?? null;
    $userRole = $session->get('role');

 
    if ($userRole === 'standardiste') {
        $message = $page->cancelIntervention($interventionId);
       
        $dashboardData = $page->getStandardisteDashboardData($session->get('user_id'));
                        echo $page->render('standardiste_dashboard.html.twig', $dashboardData);
    } else {
        
        $message = "Vous n'avez pas les droits nécessaires pour annuler cette intervention.";
        
        echo $page->render('error_page.twig', ['message' => $message]);
    }
    exit;
}



if (isset($_GET['action']) && $_GET['action'] === 'editStatusIntervention') {
    if (isset($_GET['intervention_id']) && isset($_GET['new_status_id'])) {
       
        $interventionId = $_GET['intervention_id'];
        $newStatusId = $_GET['new_status_id'];

      
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



