<?php
class Database {
    private array $credentials;

    public function __construct() {
        $credentialsFile = __DIR__ . '/db_credentials.php';

        if (!is_file($credentialsFile)) {
            throw new RuntimeException('Database configuration is missing.');
        }

        $credentials = require $credentialsFile;
        $requiredKeys = ['host', 'port', 'dbname', 'username', 'password'];

        if (!is_array($credentials)) {
            throw new RuntimeException('Database configuration is invalid.');
        }

        foreach ($requiredKeys as $key) {
            if (!isset($credentials[$key]) || $credentials[$key] === '') {
                throw new RuntimeException('Database configuration is incomplete.');
            }
        }

        $this->credentials = $credentials;
    }

    public function connect(): PDO {
        $dsn = "pgsql:host={$this->credentials['host']};"
            . "port={$this->credentials['port']};"
            . "dbname={$this->credentials['dbname']};"
            . 'sslmode=require';

        try {
            $pdo = new PDO(
                $dsn,
                $this->credentials['username'],
                $this->credentials['password']
            );

            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            return $pdo;
        } catch (PDOException) {
            throw new RuntimeException('Unable to connect to the database. Please try again later.');
        }
    }
}
