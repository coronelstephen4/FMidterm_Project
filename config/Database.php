<?php
class Database {
    private array $credentials;

    public function __construct() {
        $environmentKeys = [
            'host' => 'DB_HOST',
            'port' => 'DB_PORT',
            'dbname' => 'DB_NAME',
            'username' => 'DB_USER',
            'password' => 'DB_PASS',
            'sslmode' => 'DB_SSLMODE'
        ];
        $environment = [];

        foreach ($environmentKeys as $key => $name) {
            $value = getenv($name);
            if (($value === false || $value === '') && isset($_ENV[$name])) {
                $value = (string) $_ENV[$name];
            }
            $environment[$key] = is_string($value) && $value !== '' ? $value : null;
        }

        $credentials = [];
        $credentialsFile = __DIR__ . '/db_credentials.php';
        $needsLocalCredentials = count(array_filter($environment, static fn($value) => $value === null)) > 0;

        if ($needsLocalCredentials && is_file($credentialsFile)) {
            $localCredentials = require $credentialsFile;
            if (is_array($localCredentials)) {
                $credentials = $localCredentials;
            }
        }

        foreach ($environmentKeys as $key => $name) {
            if ($environment[$key] !== null) {
                $credentials[$key] = $environment[$key];
            }
        }

        if (empty($credentials['sslmode'])) {
            $credentials['sslmode'] = 'require';
        }

        foreach (['host', 'port', 'dbname', 'username', 'password'] as $key) {
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
            . "sslmode={$this->credentials['sslmode']}";

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
