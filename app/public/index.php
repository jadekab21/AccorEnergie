<?php

require_once '../vendor/autoload.php';

use App\Session;
use App\Database;

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
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'create_intervention') {
    require '../classes/create_intervention.php';
    exit;
}
if (strpos($requestUri, '/classes/edit_user.php') !== false) {
    require '../classes/edit_user.php';
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
                require '../classes/admin_dashboard.php';
                break;
            case 'client':
                require '../classes/client_dashboard.php';
                break;
            case 'intervenant':
                require '../classes/intervenant_dashboard.php';
                break;
            case 'standardiste':
                require '../classes/standardiste_dashboard.php';
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

echo $twig->render('index.html.twig', ['error' => $errorMessage]);