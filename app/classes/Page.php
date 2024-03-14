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
    
    public function registerUser($postData) {
    $username = $postData['username'];
    $email = $postData['email'];
    $password = password_hash($postData['password'], PASSWORD_DEFAULT);

    // Requête pour obtenir l'ID du rôle 'client'
    $roleQuery = "SELECT role_id FROM Roles WHERE role_name = 'client'";
    $stmt = $this->pdo->prepare($roleQuery);
    $stmt->execute();
    $roleRow = $stmt->fetch(\PDO::FETCH_ASSOC);
    $clientRoleId = $roleRow ? $roleRow['role_id'] : null;

    if (!$clientRoleId) {
        $message = "Erreur : le rôle client n'est pas défini dans la base de données.";
    } else {
        $insertQuery = "INSERT INTO Users (username, password, email, role_id) VALUES (:username, :password, :email, :role_id)";
        $stmt = $this->pdo->prepare($insertQuery);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':role_id', $clientRoleId);

        if ($stmt->execute()) {
            $message = "Inscription réussie. <a href='index.html'>Se connecter</a>";
        } else {
            $message = "Erreur lors de l'inscription.";
        }
    }
    
    // Il est recommandé de gérer l'affichage des messages et la redirection via une approche plus centralisée
    return $this->render('index.html.twig', ['message' => $message]);
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
        
        // Requête pour récupérer tous les utilisateurs ayant le rôle 'intervenant'
        $intervenantsQuery = "
        SELECT Users.user_id, Users.username
        FROM Users
        JOIN Roles ON Users.role_id = Roles.role_id
        WHERE Roles.role_name = 'intervenant'";
        $data['intervenants'] = $this->pdo->query($intervenantsQuery)->fetchAll();
    
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
    public function getIntervenantDashboardData($intervenantId) {
        $data = [];
      
        $interventionsQuery = "SELECT Interventions.*, Status.status, Urgences.urgency_level
            FROM Interventions
            JOIN Status ON Interventions.ids = Status.ids
            JOIN Urgences ON Interventions.idu = Urgences.idu
            JOIN InterventionIntervenants ON Interventions.intervention_id = InterventionIntervenants.intervention_id
            WHERE InterventionIntervenants.intervenant_id = :intervenantId
            GROUP BY Interventions.intervention_id;";
    
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
    try {
        // Activer l'affichage des erreurs pour le débogage
        ini_set('display_errors', 1);
        error_reporting(E_ALL);

        $this->pdo->beginTransaction();

        // Préparation de la requête de mise à jour de l'intervention
        $query = "
            UPDATE Interventions
            SET 
                title = :title,
                description = :description,
                ids = :status, 
                idu = :urgency_level, 
                date_planned = :date_planned
            WHERE intervention_id = :interventionId
        ";

        // Conversion de la date planifiée au format MySQL
        $datePlanned = \DateTime::createFromFormat('Y-m-d\TH:i', $data['date_planned']);
        if (!$datePlanned) {
            throw new \Exception("Format de date incorrect: {$data['date_planned']}");
        }
        $datePlannedFormatted = $datePlanned->format('Y-m-d H:i:s');

        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            ':title' => $data['title'],
            ':description' => $data['description'],
            ':status' => $data['status'],
            ':urgency_level' => $data['urgency_level'],
            ':date_planned' => $datePlannedFormatted,
            ':interventionId' => $data['interventionId']
        ]);

        // Mise à jour des associations d'intervenants
        $deleteQuery = "DELETE FROM InterventionIntervenants WHERE intervention_id = :interventionId";
        $this->pdo->prepare($deleteQuery)->execute([':interventionId' => $data['interventionId']]);

        $insertQuery = "INSERT INTO InterventionIntervenants (intervention_id, intervenant_id) VALUES (:interventionId, :intervenantId)";
        foreach ($data['intervenant_ids'] as $intervenantId) {
            $this->pdo->prepare($insertQuery)->execute([
                ':interventionId' => $data['interventionId'],
                ':intervenantId' => $intervenantId
            ]);
        }

        $this->pdo->commit();
        return ['success' => true];
    } catch (\Exception $e) {
        $this->pdo->rollBack();
        error_log("Échec de la mise à jour de l'intervention: " . $e->getMessage());
        return ['success' => false, 'error' => "Échec de la mise à jour de l'intervention. Veuillez vérifier les données et réessayer."];
    }
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
public function deleteStatus($statusId) {
    $sql = "DELETE FROM status WHERE ids = ?";
    $stmt = $this->pdo->prepare($sql);
    return $stmt->execute([$statusId]);
}
public function editInterventionForm($interventionId) {
    // Logique pour récupérer les données de l'intervention, des statuts, et des urgences
    $intervention = $this->pdo->query("SELECT * FROM Interventions WHERE intervention_id = $interventionId")->fetch();
    $statuses = $this->pdo->query("SELECT * FROM Status")->fetchAll();
    $urgences = $this->pdo->query("SELECT * FROM Urgences")->fetchAll();

    // Requête pour récupérer tous les utilisateurs ayant le rôle 'intervenant'
    $intervenantsQuery = "
        SELECT Users.user_id, Users.username
        FROM Users
        JOIN Roles ON Users.role_id = Roles.role_id
        WHERE Roles.role_name = 'intervenant'";
    $stmt = $this->pdo->query($intervenantsQuery); // Utilisation de query() car il n'y a pas de paramètres
    $intervenants = $stmt->fetchAll();

    // Rendre le template Twig avec les données récupérées
    return $this->render('edit_intervention.twig', [
        'intervention' => $intervention,
        'statuses' => $statuses,
        'urgences' => $urgences,
        'intervenants' => $intervenants // Passer les intervenants au template
    ]);
}
public function postComment($interventionId, $userId, $content) {
    $stmt = $this->pdo->prepare("INSERT INTO Comments (intervention_id, user_id, content) VALUES (?, ?, ?)");
    return $stmt->execute([$interventionId, $userId, $content]);
}
public function getCommentsByInterventionId($interventionId) {
    $stmt = $this->pdo->prepare("SELECT * FROM Comments WHERE intervention_id = ?");
    $stmt->execute([$interventionId]);
    return $stmt->fetchAll();
}
public function addUrgence($urgenceLevel) {
    // La requête SQL insère maintenant dans la colonne `urgency_level`
    $stmt = $this->pdo->prepare("INSERT INTO urgences (urgency_level) VALUES (?)");
    return $stmt->execute([$urgenceLevel]);
}
public function deleteUrgence($idu) {
    $stmt = $this->pdo->prepare("DELETE FROM urgences WHERE idu = ?");
    return $stmt->execute([$idu]);
}
public function editUrgence($idu, $newUrgencyLevel) {
    $stmt = $this->pdo->prepare("UPDATE urgences SET urgency_level = ? WHERE idu = ?");
    return $stmt->execute([$newUrgencyLevel, $idu]);
}

public function editUser($userId, $username, $email, $roleId) {
    $stmt = $this->pdo->prepare("UPDATE Users SET username = ?, email = ?, role_id = ? WHERE user_id = ?");
    return $stmt->execute([$username, $email, $roleId, $userId]);
}
public function getUserDetailsById($userId) {
    $stmt = $this->pdo->prepare("SELECT * FROM Users WHERE user_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch();
}

public function getAllRoles() {
    return $this->pdo->query("SELECT * FROM Roles")->fetchAll();
}

public function deleteUserById($userId) {
    try {
        // Commencer une transaction
        $this->pdo->beginTransaction();

        // Supprimer les commentaires liés à l'utilisateur
        $stmt = $this->pdo->prepare("DELETE FROM comments WHERE user_id = ?");
        $stmt->execute([$userId]);

        // Mettre à jour ou supprimer les interventions liées à l'utilisateur
        // Exemple: Définir client_id à NULL ou à un autre utilisateur
        // Pour cet exemple, nous allons simplement définir client_id à NULL
        $stmt = $this->pdo->prepare("UPDATE interventions SET client_id = NULL WHERE client_id = ?");
        $stmt->execute([$userId]);

        // Enfin, supprimer l'utilisateur lui-même
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);

        // Valider la transaction
        $this->pdo->commit();

        return true;
    } catch (\PDOException $e) {
        // En cas d'erreur, annuler la transaction
        $this->pdo->rollBack();
        error_log("Erreur lors de la suppression de l'utilisateur $userId : " . $e->getMessage());
        return false;
    }
}



}
