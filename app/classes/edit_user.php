<?php
// Configuration de la base de données
$dbHost = "mysql"; // Utilisez l'hôte de votre base de données
$dbUser = "root";  // Utilisez votre nom d'utilisateur de base de données
$dbPassword = "";  // Utilisez votre mot de passe de base de données
$dbName = "AccorEnergie"; // Utilisez votre nom de base de données

// Établir la connexion à la base de données avec PDO
try {
    $conn = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPassword);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Vérifier si l'utilisateur est connecté et est un admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Préparation de Twig
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
$twig = new \Twig\Environment($loader);

// Récupération de l'ID de l'utilisateur depuis l'URL
$userId = $_GET['id'] ?? '';

// Traitement du formulaire si soumis
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($userId)) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role_id = $_POST['role_id'];

    // Mise à jour de l'utilisateur dans la base de données avec PDO
    $updateQuery = "UPDATE Users SET username = :username, email = :email, role_id = :role_id WHERE user_id = :userId";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':role_id', $role_id);
    $stmt->bindParam(':userId', $userId);

    if ($stmt->execute()) {
        // Redirection ou affichage d'un message de succès
        header('Location: index.php'); // Remplacer par votre page de dashboard
        exit;
    } else {
        $message = "Erreur lors de la mise à jour de l'utilisateur: " . $stmt->errorInfo()[2];
        echo $twig->render('edit_user.twig', ['message' => $message]);
        exit;
    }
}

// Récupérer les détails de l'utilisateur pour le formulaire avec PDO
$query = "SELECT * FROM Users WHERE user_id = :userId";
$stmt = $conn->prepare($query);
$stmt->bindParam(':userId', $userId);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo $twig->render('error.twig', ['message' => 'Utilisateur non trouvé.']);
    exit;
}

// Récupérer les rôles pour le dropdown avec PDO
$rolesQuery = "SELECT * FROM Roles";
$rolesStmt = $conn->query($rolesQuery);
$roles = $rolesStmt->fetchAll(PDO::FETCH_ASSOC);

// Affichage du formulaire avec Twig
echo $twig->render('edit_user.twig', [
    'user' => $user,
    'userId' => $userId,
    'roles' => $roles
]);
