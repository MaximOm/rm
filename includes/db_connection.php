<?php
/**
 * Database Connection
 * Provides a consistent connection to the database across all files
 * with enhanced security features using the Singleton pattern
 */

// Prevent direct access to this file
if (!defined('APP_INITIALIZED')) {
    define('APP_INITIALIZED', true);
    require_once __DIR__ . '/config.php';
}

/**
 * Database class for managing database connections and operations
 */
class Database {
    private static $instance = null;
    private $connection;
    
    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct() {
        try {
            // Create connection
            $this->connection = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
            
            // Check connection
            if ($this->connection->connect_error) {
                throw new Exception("Database connection failed: " . $this->connection->connect_error);
            }
            
            // Set charset
            $this->connection->set_charset("utf8mb4");
        } catch (Exception $e) {
            error_log($e->getMessage());
            $this->connection = null;
        }
    }
    
    /**
     * Get database instance (Singleton pattern)
     * 
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get database connection
     * 
     * @return mysqli|null
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Check if database connection is working
     * 
     * @return bool - true if connected, false otherwise
     */
    public function isConnected() {
        return ($this->connection && !$this->connection->connect_error);
    }
    
    /**
     * Check if database and tables exist
     * Returns true if setup is completed, false otherwise
     * 
     * @return bool - true if setup is complete, false otherwise
     */
    public function isDatabaseSetup() {
        // Try connecting to the database server first
        $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD);
        
        // Check connection
        if ($conn->connect_error) {
            return false;
        }
        
        // Check if database exists
        $result = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . DB_NAME . "'");
        if ($result->num_rows == 0) {
            $conn->close();
            return false;
        }
        
        // Select the database
        $conn->select_db(DB_NAME);
        
        // Check if required tables exist
        $tables = ['products', 'categories', 'orders'];
        foreach ($tables as $table) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            if ($result->num_rows == 0) {
                $conn->close();
                return false;
            }
        }
        
        $conn->close();
        return true;
    }
    
    /**
     * Sanitize input data to prevent SQL injection
     * 
     * @param mixed $input - Input to sanitize (string or array)
     * @return mixed - Sanitized input
     */
    public function sanitize($input) {
        if (!$this->connection) {
            return is_array($input) ? $input : trim($input);
        }
        
        if (is_array($input)) {
            foreach($input as $key => $value) {
                $input[$key] = $this->sanitize($value);
            }
            return $input;
        }
        return $this->connection->real_escape_string(trim($input));
    }
    
    /**
     * Execute database queries safely with prepared statements
     * 
     * @param string $sql - SQL query with placeholders
     * @param array $params - Parameters to bind to the query
     * @return mysqli_result|bool - Query result or false on failure
     */
    public function query($sql, $params = []) {
        // If connection is not available, return false
        if (!$this->connection) {
            error_log("Database query error: Connection not established");
            return false;
        }
        
        try {
            if (empty($params)) {
                $result = $this->connection->query($sql);
                if (!$result) {
                    throw new Exception("Query error: " . $this->connection->error . " in query: " . $sql);
                }
                return $result;
            } else {
                $stmt = $this->connection->prepare($sql);
                if (!$stmt) {
                    throw new Exception("Prepare error: " . $this->connection->error . " in query: " . $sql);
                }
                
                $types = '';
                foreach ($params as $param) {
                    if (is_int($param)) $types .= 'i';
                    elseif (is_float($param)) $types .= 'd';
                    elseif (is_string($param)) $types .= 's';
                    else $types .= 'b';
                }
                
                $stmt->bind_param($types, ...$params);
                $stmt->execute();
                
                if ($stmt->errno) {
                    throw new Exception("Execute error: " . $stmt->error);
                }
                
                $result = $stmt->get_result();
                $stmt->close();
                
                return $result;
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Get single row from database
     * 
     * @param string $sql The SQL query
     * @param array $params Optional parameters for prepared statements
     * @return array|null Single row or null
     */
    public function getRow($sql, $params = []) {
        $result = $this->query($sql, $params);
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    /**
     * Get multiple rows from database
     * 
     * @param string $sql The SQL query
     * @param array $params Optional parameters for prepared statements
     * @return array Array of rows
     */
    public function getRows($sql, $params = []) {
        $result = $this->query($sql, $params);
        $rows = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
        }
        
        return $rows;
    }
    
    /**
     * Close the database connection
     */
    public function close() {
        if ($this->connection) {
            $this->connection->close();
            $this->connection = null;
        }
    }
    
    /**
     * Prevent cloning of the instance
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization of the instance
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

/**
 * Legacy functions for backward compatibility
 */

/**
 * Check if database connection is working
 * 
 * @param mysqli $conn - Database connection
 * @return bool - true if connected, false otherwise
 */
function is_db_connected($conn) {
    return ($conn && !$conn->connect_error);
}

/**
 * Get a database connection
 * 
 * @return mysqli|false - Database connection or false on failure
 */
function getDbConnection() {
    $db = Database::getInstance();
    return $db->getConnection();
}

/**
 * Check if database and tables exist
 * 
 * @return bool - true if setup is complete, false otherwise
 */
function isDatabaseSetup() {
    $db = Database::getInstance();
    return $db->isDatabaseSetup();
}

/**
 * Sanitize input data to prevent SQL injection
 * 
 * @param mysqli $conn - Database connection (kept for backward compatibility)
 * @param mixed $input - Input to sanitize (string or array)
 * @return mixed - Sanitized input
 */
function sanitize($conn, $input) {
    $db = Database::getInstance();
    return $db->sanitize($input);
}

/**
 * Execute database queries safely with prepared statements
 * 
 * @param mysqli $conn - Database connection (kept for backward compatibility)
 * @param string $sql - SQL query with placeholders
 * @param array $params - Parameters to bind to the query
 * @return mysqli_result|bool - Query result or false on failure
 */
function query($conn, $sql, $params = []) {
    $db = Database::getInstance();
    return $db->query($sql, $params);
}
