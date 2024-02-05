<?php
// Inclure les dépendances Composer, y compris Twig
require_once '../vendor/autoload.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Configuration de la base de données
$dbHost = "mysql"; // Utilisez l'hôte de votre base de données
$dbUser = "root";  // Utilisez votre nom d'utilisateur de base de données
$dbPassword = "";  // Utilisez votre mot de passe de base de données
$dbName = "AccorEnergie"; // Utilisez votre nom de base de données

// Établir la connexion à la base de données avec PDO
try {
    $conn = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPassword);
    // Définir le mode d'erreur de PDO sur exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Échec de la connexion : " . $e->getMessage());
}

// Initialiser Twig
$loader = new \Twig\Loader\FilesystemLoader('../templates');
$twig = new \Twig\Environment($loader);

// Gestion des formulaires
$errorMessage = ""; // Initialisez la variable avant de l'utiliser

// Le reste de votre code reste inchangé...

// Utiliser PDO pour le traitement du formulaire de connexion
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['form_type']) && $_POST['form_type'] == 'submit') {
        require '../classes/register.php';
        exit;
    }

    // Traitement du formulaire de connexion
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Utiliser des requêtes préparées avec PDO
    $stmt = $conn->prepare("SELECT u.user_id, u.password, r.role_name FROM Users u INNER JOIN Roles r ON u.role_id = r.role_id WHERE u.username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role'] = $user['role_name'];
        $stmt->closeCursor();

        // Redirection en fonction du rôle
        switch ($_SESSION['role']) {
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
        $stmt->closeCursor();
    }
}

// Affichage de la page de connexion par défaut
echo $twig->render('index.html.twig', ['error' => $errorMessage]);
