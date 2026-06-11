<?php

class Database
{
    private $host;
    private $dbname;
    private $username;
    private $password;
    private $conn;

    public function __construct()
    {
        $this->host = $this->readSecret('db_host', 'db');
        $this->dbname = $this->readSecret('db_name', 'savings_db');
        $this->username = $this->readSecret('db_username', 'root');
        $this->password = $this->readSecret('db_password', 'root');
    }

    private function readSecret($secretName, $default = '')
    {
        $secretPath = "/run/secrets/{$secretName}";
        if (file_exists($secretPath)) {
            $value = trim(file_get_contents($secretPath));
            return $value !== '' ? $value : $default;
        }
        return $default;
    }

    public function getConnection()
    {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname}",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
        }
        return $this->conn;
    }
}
