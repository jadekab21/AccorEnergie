<?php
// Assurez-vous que session_start est bien placé au début du fichier
session_start();

// Connexion à la base de données
$dbHost = "mysql";
$dbUser = "root";
$dbPassword = "";
$dbName = "AccorEnergie";

try {
    $conn = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPassword);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    // Debug pour voir le contenu de la session
    var_dump($_SESSION);
    header('Location: login.php');
    exit;
}

// Vérifier l'ID de l'intervention
$interventionId = $_GET['id'] ?? '';

if (empty($interventionId)) {
    die("Aucune intervention spécifiée pour l'édition.");
}

// ... (le reste de votre code)

// Debug pour voir le contenu de la session après la vérification
var_dump($_SESSION);

// ... (le reste de votre code)


// Traitement de la soumission du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collecte des données du formulaire
    $title = $_POST['title'];
    $description = $_POST['description'];
    $status = $_POST['status'];
    $urgency_level = $_POST['urgency_level'];
    $date_planned = $_POST['date_planned'];

    // Mise à jour de l'intervention dans la base de données avec PDO
    $updateQuery = "UPDATE Interventions SET 
                        title = :title, 
                        description = :description, 
                        status = :status, 
                        urgency_level = :urgency_level, 
                        date_planned = :date_planned 
                    WHERE intervention_id = :interventionId";

    $stmt = $conn->prepare($updateQuery);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':urgency_level', $urgency_level);
    $stmt->bindParam(':date_planned', $date_planned);
    $stmt->bindParam(':interventionId', $interventionId);

    if ($stmt->execute()) {
        $message = "Intervention mise à jour avec succès.";
    } else {
        $message = "Erreur lors de la mise à jour de l'intervention: " . $stmt->errorInfo()[2];
    }
}

// Récupérer les détails de l'intervention pour les afficher dans le formulaire avec PDO
$query = "SELECT * FROM Interventions WHERE intervention_id = :interventionId";
$stmt = $conn->prepare($query);
$stmt->bindParam(':interventionId', $interventionId);
$stmt->execute();
$intervention = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$intervention) {
    die("Intervention non trouvée.");
}

require_once '../vendor/autoload.php';
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
$twig = new \Twig\Environment($loader);

echo $twig->render('edit_intervention.twig', [
    'intervention' => $intervention,
    'interventionId' => $interventionId,
    'message' => $message ?? ''
]);
