<?php
require_once '../vendor/autoload.php';
use App\Database;


// Assurez-vous que la classe Session gère correctement les sessions PHP.


// Utilisez la classe Database pour établir la connexion
$db = new Database("mysql", "root", "", "AccorEnergie");
$pdo = $db->getPdo();

$interventionId = $_GET['id'] ?? '';
if (empty($interventionId)) {
    die("Aucune intervention spécifiée pour l'édition.");
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Préparation de la requête pour éviter les injections SQL
    $stmt = $pdo->prepare("
    UPDATE Interventions
    SET 
        title = :title,
        description = :description,
        ids = :status_id, 
        idu = :urgence_id, 
        date_planned = STR_TO_DATE(:date_planned, '%Y-%m-%dT%H:%i')
    WHERE intervention_id = :interventionId
");

// Utilisez la fonction date() pour formater la date au format attendu par MySQL
$date_planned = date('Y-m-d\TH:i', strtotime($_POST['date_planned']));

// Exécution de la requête avec les données du formulaire
if ($stmt->execute([
    ':title' => $_POST['title'],
    ':description' => $_POST['description'],
    ':status_id' => $_POST['status'], 
    ':urgence_id' => $_POST['urgency_level'], 
    ':date_planned' => $date_planned,
    ':interventionId' => $interventionId
])) {
    $message = "Intervention mise à jour avec succès.";
} else {
    $errorInfo = $stmt->errorInfo();
    $message = "Erreur lors de la mise à jour de l'intervention: " . $errorInfo[2];
}

}

// Récupération de l'intervention pour affichage dans le formulaire
$stmt = $pdo->prepare("SELECT * FROM Interventions WHERE intervention_id = ?");
$stmt->execute([$interventionId]);
$intervention = $stmt->fetch();

if (!$intervention) {
    die("Intervention non trouvée.");
}
// Récupération des statuts depuis la table "Status"
// Récupération des statuts depuis la table "Status"
$stmtStatus = $pdo->query("SELECT ids, status FROM Status");
$statuses = $stmtStatus->fetchAll(PDO::FETCH_ASSOC);

// Récupération des niveaux d'urgence depuis la table "Urgences"
$stmtUrgences = $pdo->query("SELECT idu, urgency_level FROM Urgences");
$urgences = $stmtUrgences->fetchAll(PDO::FETCH_ASSOC);

// Initialisation de l'environnement Twig
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
$twig = new \Twig\Environment($loader);

// Affichage du template Twig avec les données de l'intervention
echo $twig->render('edit_intervention.twig', [
    'intervention' => $intervention,
    'statuses' => $statuses,
     'urgences' => $urgences,
    'message' => $message ?? ''
]);
