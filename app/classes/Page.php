<?php

namespace App;

class Page
{
    private \Twig\Environment $twig;
    private \PDO $pdo;

    function __construct()
    {
        $loader = new \Twig\Loader\FilesystemLoader('../templates');
        $this->twig = new \Twig\Environment($loader, [
            'cache' => '../var/cache/compilation_cache',
            'debug' => true,
        ]);

        $this->pdo = new \PDO('mysql:host=mysql;dbname=AccorEnergie', "root", "");
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public function render(string $name, array $data) : string
    {
        return $this->twig->render($name, $data);
    }

    public function getDashboardData()
    {
        $data = [];

        // Récupération des interventions
        $interventionsQuery = "SELECT 
            Interventions.*, 
            Status.status, 
            Urgences.urgency_level, 
            GROUP_CONCAT(DISTINCT InterventionIntervenants.intervenant_id SEPARATOR ', ') AS intervenants_ids 
        FROM 
            Interventions 
        JOIN 
            Status ON Interventions.ids = Status.ids 
        JOIN 
            Urgences ON Interventions.idu = Urgences.idu
        LEFT JOIN 
            InterventionIntervenants ON Interventions.intervention_id = InterventionIntervenants.intervention_id 
        GROUP BY 
            Interventions.intervention_id;";
        $data['interventions'] = $this->pdo->query($interventionsQuery)->fetchAll(\PDO::FETCH_ASSOC);

        // Récupération des utilisateurs
        $data['users'] = $this->pdo->query("SELECT * FROM Users")->fetchAll(\PDO::FETCH_ASSOC);

        // Récupération des statuts et urgences
        $data['statuses'] = $this->pdo->query("SELECT ids, status FROM Status")->fetchAll(\PDO::FETCH_ASSOC);
        $data['urgences'] = $this->pdo->query("SELECT idu, urgency_level FROM Urgences")->fetchAll(\PDO::FETCH_ASSOC);

        return $data;
    }

    // Ajoutez d'autres méthodes au besoin...

    // Dans votre classe Page.php
    public function getClientDashboardData(int $clientId): array
    {
        // Assurez-vous que $clientId est passé en paramètre de la fonction
        $interventionsQuery = "SELECT * FROM Interventions WHERE client_id = :clientId";
        $stmt = $this->pdo->prepare($interventionsQuery);
        $stmt->bindParam(':clientId', $clientId, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function getIntervenantDashboardData(): array
    {
      
        $interventionsQuery = "SELECT Interventions.* FROM Interventions 
        JOIN InterventionIntervenants ON Interventions.intervention_id = InterventionIntervenants.intervention_id 
        WHERE InterventionIntervenants.intervenant_id = :intervenantId";
        $stmt = $this->pdo->prepare($interventionsQuery);
        $stmt->bindParam(':intervenantId', $intervenantId, \PDO::PARAM_INT);
        $stmt->execute();
        $data['interventions'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $data['statuses'] = $this->pdo->query("SELECT ids, status FROM Status")->fetchAll(\PDO::FETCH_ASSOC);

        return $data;
    }

    public function getStandardisteDashboardData()
{
    $data = [];
    $interventionsQuery = "SELECT * FROM Interventions";
    $data['interventions'] = $this->pdo->query($interventionsQuery)->fetchAll(\PDO::FETCH_ASSOC);

    $clientsQuery = "SELECT user_id, username FROM Users WHERE role_id = (SELECT role_id FROM Roles WHERE role_name = 'client')";
    $clientsStatement = $this->pdo->query($clientsQuery);
    $clients = [];
    while ($client = $clientsStatement->fetch(\PDO::FETCH_ASSOC)) {
        $clients[$client['user_id']] = $client['username'];
    }
    $data['clients'] = $clients;

    $intervenantsQuery = "SELECT user_id, username FROM Users WHERE role_id = (SELECT role_id FROM Roles WHERE role_name = 'intervenant')";
    $intervenantsStatement = $this->pdo->query($intervenantsQuery);
    $intervenants = [];
    while ($intervenant = $intervenantsStatement->fetch(\PDO::FETCH_ASSOC)) {
        $intervenants[$intervenant['user_id']] = $intervenant['username'];
    }
    $data['intervenants'] = $intervenants;

    return $data;
}
public function createIntervention(array $interventionData, array $intervenantIds)
{
    try {
        $this->pdo->beginTransaction();

        // Insérer l'intervention
        $insertQuery = "INSERT INTO Interventions (title, description, client_id, date_planned, ids, urgency_level) VALUES (:title, :description, :client_id, :date_planned, '1', :urgency_level)";
        $stmt = $this->pdo->prepare($insertQuery);
        $stmt->execute([
            ':title' => $interventionData['title'],
            ':description' => $interventionData['description'],
            ':client_id' => $interventionData['client_id'],
            ':date_planned' => $interventionData['date_planned'],
            ':urgency_level' => $interventionData['urgency_level']
        ]);
        $interventionId = $this->pdo->lastInsertId();

        // Associer les intervenants à l'intervention
        $insertIntervenantQuery = "INSERT INTO InterventionIntervenants (intervention_id, intervenant_id) VALUES (:intervention_id, :intervenant_id)";
        $stmtIntervenant = $this->pdo->prepare($insertIntervenantQuery);

        foreach ($intervenantIds as $intervenantId) {
            $stmtIntervenant->execute([
                ':intervention_id' => $interventionId,
                ':intervenant_id' => $intervenantId
            ]);
        }

        $this->pdo->commit();
        return "Intervention créée et intervenants associés avec succès.";
    } catch (\PDOException $e) {
        $this->pdo->rollBack();
        return "Erreur lors de la création de l'intervention: " . $e->getMessage();
    }
}
public function deleteIntervention(int $interventionId): bool {
    $stmt = $this->pdo->prepare("DELETE FROM Interventions WHERE intervention_id = ?");
    return $stmt->execute([$interventionId]);
}
public function editIntervention(array $data) {
    $query = "
        UPDATE Interventions
        SET 
            title = :title,
            description = :description,
            ids = :status_id, 
            idu = :urgence_id, 
            date_planned = STR_TO_DATE(:date_planned, '%Y-%m-%dT%H:%i')
        WHERE intervention_id = :interventionId
    ";

    $stmt = $this->pdo->prepare($query);

    return $stmt->execute([
        ':title' => $data['title'],
        ':description' => $data['description'],
        ':status_id' => $data['status'],
        ':urgence_id' => $data['urgency_level'],
        ':date_planned' => date('Y-m-d H:i:s', strtotime($data['date_planned'])),
        ':interventionId' => $data['interventionId']
    ]);
}
public function createStatus($status) {
    $query = "INSERT INTO Status (status) VALUES (:status)";
    $stmt = $this->pdo->prepare($query);
    
    return $stmt->execute([':status' => $status]);
}
public function updateStatus($ids, $newStatus) {
    $updateStmt = $this->pdo->prepare("UPDATE status SET status = :status WHERE ids = :ids");
    return $updateStmt->execute([':status' => $newStatus, ':ids' => $ids]);
}

}
