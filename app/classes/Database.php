<?php
namespace App;

use PDO;
use PDOException;

class Database
{
    private $pdo;

    public function __construct($host, $user, $password, $dbName)
    {
        $this->pdo = new PDO("mysql:host=$host;dbname=$dbName", $user, $password);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getPdo()
    {
        return $this->pdo;
    }
}