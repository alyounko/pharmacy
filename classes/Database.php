<?php
class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        $this->connect();
    }

    private function connect() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // In a real application, you'd log this error and show a user-friendly message
            error_log("Database Connection Error: " . $e->getMessage());
            die("Database connection failed. Please check logs for details.");
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        // Simple ping to check if the connection is alive
        try {
            $this->connection->query("SELECT 1");
        } catch (PDOException $e) {
            // If the connection is lost, try to reconnect
            $this->connect();
        }
        return $this->connection;
    }
}
?>
