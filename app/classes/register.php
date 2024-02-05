<?php
require_once '../vendor/autoload.php';

// Initialiser l'environnement Twig
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
$twig = new \Twig\Environment($loader);

// Connexion à la base de données
$dbHost = "mysql";
$dbUser = "root";
$dbPassword = "";
$dbName = "AccorEnergie";
$conn = new mysqli($dbHost, $dbUser, $dbPassword, $dbName);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialiser les variables de message
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Requête pour obtenir l'ID du rôle 'client'
    $roleQuery = "SELECT role_id FROM Roles WHERE role_name = 'client'";
    $roleResult = mysqli_query($conn, $roleQuery);
    $roleRow = mysqli_fetch_assoc($roleResult);
    $clientRoleId = $roleRow['role_id'];

    if (!$clientRoleId) {
        $message = "Erreur : le rôle client n'est pas défini dans la base de données.";
    } else {
        $insertQuery = "INSERT INTO Users (username, password, email, role_id) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insertQuery);
        mysqli_stmt_bind_param($stmt, 'sssi', $username, $password, $email, $clientRoleId);

        if (mysqli_stmt_execute($stmt)) {
            $message = "Inscription réussie. <a href='index.html'>Se connecter</a>";
        } else {
            $message = "Erreur lors de l'inscription : " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }

    // Fermeture de la connexion à la base de données
    mysqli_close($conn);

    // Afficher un message sur le formulaire d'inscription
    echo $twig->render('index.html.twig', ['message' => $message]);
    exit();
}

// Si la méthode n'est pas POST, afficher simplement le formulaire d'inscription
echo $twig->render('index.html.twig', ['message' => $message]);
