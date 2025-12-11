<?php
class Database {

  // Database connection parameters
  private $servername = 'localhost';
  private $dbname = "u273173398_dot63"; //u273173398_dot63 dot63
  private $username = "u273173398_test"; //u273173398_test root
  private $password = "32skiff32!CI"; //32skiff32!CI ""
  private $connection;

  // Constructor to establish a database connection
   public function __construct() {

        try {
            // Create a PDO connection
            $this->connection = new PDO("mysql:host=$this->servername;dbname=$this->dbname", $this->username, $this->password);

            // Set PDO error mode to exception for better error handling
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            // Handle connection errors
            echo "Connection failed: " . $e->getMessage();
        }
    }

    // Method to get the database connection
    public function getConnection() {
        return $this->connection;
    }

    // Method to close the database connection
    public function closeConnection() {
        $this->connection = null;
    }
}
 ?>
