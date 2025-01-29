<?php
class Database
{
    private $dbServer = 'localhost';
    private $dbUser = 'root';
    private $dbPassword = "";
    private $dbName = 'db_courierservice';
    protected $conn;
    
    public function getConnection() {
 
        $this->conn = null;

        
        try {
            
            $this->conn = new PDO("mysql:host=" . $this->dbServer . ";dbname=" . $this->dbName, $this->dbUser, $this->dbPassword);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch (Exception $exception) {
            echo "Connection failed: " . $exception->getMessage();
        }
        return $this->conn;
}

}?>