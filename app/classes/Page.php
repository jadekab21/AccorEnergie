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

    // Requête pour obtenir l'ID de 'client'
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
        
        // Récuperation de tous les utilisateurs ayant le rôle 'intervenant'
        $intervenantsQuery = "
        SELECT Users.user_id, Users.username
        FROM Users
        JOIN Roles ON Users.role_id = Roles.role_id
        WHERE Roles.role_name = 'intervenant'";
        $data['intervenants'] = $this->pdo->query($intervenantsQuery)->fetchAll();
    
        return $data;
    }
    public function getClientDashboardData(int $clientId): array {
        $interventionsQuery = "
            SELECT 
                Interventions.*, 
                Status.status AS status_name, 
                Urgences.urgency_level AS urgency_level_name
            FROM 
                Interventions 
                JOIN Status ON Interventions.ids = Status.ids 
                JOIN Urgences ON Interventions.idu = Urgences.idu
            WHERE 
                Interventions.client_id = :clientId
        ";
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
    

    public function getStandardisteDashboardData($standardisteId) {
        $data = [];
        
        $interventionsQuery = "SELECT 
            Interventions.*, 
            Status.status, 
            Urgences.urgency_level, 
            GROUP_CONCAT(DISTINCT InterventionIntervenants.intervenant_id SEPARATOR ', ') AS intervenants_ids, 
            (Interventions.created_by = :standardisteId) AS isCreatedByStandardiste
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
    
        $stmt = $this->pdo->prepare($interventionsQuery);
        $stmt->bindParam(':standardisteId', $standardisteId, \PDO::PARAM_INT);
        $stmt->execute();
        $data['interventions'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    
        $data['users'] = $this->pdo->query("SELECT * FROM Users")->fetchAll(\PDO::FETCH_ASSOC);
        $data['statuses'] = $this->pdo->query("SELECT ids, status FROM Status")->fetchAll(\PDO::FETCH_ASSOC);
        $data['urgences'] = $this->pdo->query("SELECT idu, urgency_level FROM Urgences")->fetchAll(\PDO::FETCH_ASSOC);
        $data['intervenants'] = $this->pdo->query("SELECT Users.user_id, Users.username FROM Users JOIN Roles ON Users.role_id = Roles.role_id WHERE Roles.role_name = 'intervenant'")->fetchAll();
        $data['clients'] = $this->pdo->query("SELECT Users.user_id, Users.username FROM Users JOIN Roles ON Users.role_id = Roles.role_id WHERE Roles.role_name = 'client'")->fetchAll(\PDO::FETCH_ASSOC);
    
        return $data;
    }
    
public function createIntervention(array $interventionData, array $intervenantIds)
{
    try {
        $this->pdo->beginTransaction();

        // Insérer l'intervention
        $insertQuery = "INSERT INTO Interventions 
        (title, description, client_id, date_planned, ids, idu) 
        VALUES 
        (:title, :description, :client_id, STR_TO_DATE(:date_planned, '%Y-%m-%dT%H:%i'), :status_id, :urgency_level)";

        $stmt = $this->pdo->prepare($insertQuery);
        $stmt->execute([
            ':title' => $interventionData['title'],
            ':description' => $interventionData['description'],
            ':client_id' => $interventionData['client_id'],
            ':date_planned' => $interventionData['date_planned'],
            ':urgency_level' => $interventionData['urgency_level'],
            ':status_id' => $interventionData['status_id']
        
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
    // Récupère les détails de l'intervention spécifique par son ID.
    $interventionQuery = "SELECT * FROM Interventions WHERE intervention_id = :interventionId";
    $stmt = $this->pdo->prepare($interventionQuery);
    $stmt->execute([':interventionId' => $interventionId]);
    $intervention = $stmt->fetch();

    // Récupère tous les statuts disponibles dans la base de données.
    $statuses = $this->pdo->query("SELECT * FROM Status")->fetchAll();

    // Récupère toutes les urgences disponibles dans la base de données.
    $urgences = $this->pdo->query("SELECT * FROM Urgences")->fetchAll();

    // Récupère tous les utilisateurs ayant le rôle 'intervenant'.
    $intervenantsQuery = "
        SELECT Users.user_id, Users.username
        FROM Users
        JOIN Roles ON Users.role_id = Roles.role_id
        WHERE Roles.role_name = 'intervenant'";
    $intervenants = $this->pdo->query($intervenantsQuery)->fetchAll();

    // Récupère tous les utilisateurs ayant le rôle 'client'.
    $clientsQuery = "
        SELECT Users.user_id, Users.username
        FROM Users
        JOIN Roles ON Users.role_id = Roles.role_id
        WHERE Roles.role_name = 'client'";
    $clients = $this->pdo->query($clientsQuery)->fetchAll();

    // Prépare les données à passer au template Twig.
    $templateData = [
        'intervention' => $intervention,
        'statuses' => $statuses,
        'urgences' => $urgences,
        'intervenants' => $intervenants,
        'clients' => $clients // Ajout des clients au template
    ];

    // Rend le template Twig avec les données récupérées.
    return $this->render('edit_intervention.twig', $templateData);
}

public function addComment($interventionId, $userId, $content) {
    $stmt = $this->pdo->prepare("INSERT INTO Comments (intervention_id, user_id, content) VALUES (?, ?, ?)");
    $result = $stmt->execute([$interventionId, $userId, $content]);
    return $result; // True si le commentaire est ajouté, sinon false
}


public function getCommentsByInterventionId($interventionId) {
    $stmt = $this->pdo->prepare("SELECT * FROM Comments WHERE intervention_id = ? ORDER BY created_at DESC");
    $stmt->execute([$interventionId]);
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
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
public function cancelIntervention($interventionId) {
    // Identifier l'ID du statut 'Annulée' dans la table Status
    $statusQuery = "SELECT ids FROM Status WHERE status = :statusName";
    $cancelStatusName = 'Annulée'; // Assurez-vous que ce statut existe dans votre table Status

    try {
        // Récupérer l'ID du statut 'Annulée'
        $statusStmt = $this->pdo->prepare($statusQuery);
        $statusStmt->bindParam(':statusName', $cancelStatusName);
        $statusStmt->execute();
        $statusResult = $statusStmt->fetch(\PDO::FETCH_ASSOC);

        if ($statusResult) {
            $cancelStatusId = $statusResult['ids'];

            // Mettre à jour le statut de l'intervention
            $updateQuery = "UPDATE Interventions SET ids = :statusId WHERE intervention_id = :interventionId";
            $updateStmt = $this->pdo->prepare($updateQuery);
            $updateStmt->bindParam(':statusId', $cancelStatusId, \PDO::PARAM_INT);
            $updateStmt->bindParam(':interventionId', $interventionId, \PDO::PARAM_INT);
            $updateStmt->execute();

            if ($updateStmt->rowCount() > 0) {
                return "L'intervention a été annulée avec succès.";
            } else {
                return "Aucune intervention trouvée avec cet ID ou l'intervention est déjà annulée.";
            }
        } else {
            return "Statut 'Annulée' introuvable dans la base de données.";
        }
    } catch (\PDOException $e) {
        return "Erreur lors de l'annulation de l'intervention: " . $e->getMessage();
    }
}


}
