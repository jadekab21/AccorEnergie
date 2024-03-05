<?php
session_start();
$conn = mysqli_connect("localhost:8889", "root", "root", "AccorEnergie");

// Logique pour déterminer qui est connecté : admin, client, intervenant ou standardiste

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // Récupérez l'ID et le mot de passe de l'utilisateur, ainsi que son rôle
    $query = "SELECT u.user_id, u.password, r.role_name FROM Users u INNER JOIN Roles r ON u.role_id = r.role_id WHERE u.username = '$username'";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role'] = $user['role_name'];  // Stockez le rôle dans la session

        // Redirection en fonction du rôle
        if ($user['role_name'] === 'admin') {
            header("Location: admin_dashboard.php");
        } elseif ($user['role_name'] === 'client') {
            header("Location: client_dashboard.php");
        } elseif ($user['role_name'] === 'intervenant') {
            header("Location: intervenant_dashboard.php");
        } elseif ($user['role_name'] === 'standardiste') {
            header("Location: standardiste_dashboard.php");
        } else {
            // Role not recognized, redirect to a default page or error page
            header("Location: error_page.php");
        }
        exit;
    } else {
        // Gestion de l'erreur d'authentification
        echo "Nom d'utilisateur ou mot de passe incorrect";
    }
}
?>