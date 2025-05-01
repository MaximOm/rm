<?php
/**
 * Database Status Checker
 * 
 * This file provides functionality to check if the database is properly set up.
 */

// Prevent direct access
if (!defined('APP_INITIALIZED')) {
    die('Direct access to this file is not allowed.');
}

/**
 * Check if the database is properly set up
 * 
 * @return bool True if database is set up, false otherwise
 */
function isDatabaseSetup() {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Check if we can establish a connection
    if (!$conn) {
        return false;
    }
    
    try {
        // Check if essential tables exist
        $requiredTables = ['products', 'categories', 'users', 'product_images'];
        $tablesExist = true;
        
        foreach ($requiredTables as $table) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            if (!$result || $result->num_rows === 0) {
                $tablesExist = false;
                break;
            }
        }
        
        return $tablesExist;
    } catch (Exception $e) {
        error_log('Database check error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get database status information
 * 
 * @return array Status information about the database
 */
function getDatabaseStatus() {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    $status = [
        'connected' => false,
        'tables' => [],
        'error' => null
    ];
    
    if (!$conn) {
        $status['error'] = 'Could not connect to database';
        return $status;
    }
    
    $status['connected'] = true;
    
    try {
        // Get list of tables
        $result = $conn->query("SHOW TABLES");
        if ($result) {
            while ($row = $result->fetch_array()) {
                $tableName = $row[0];
                
                // Get row count for each table
                $countResult = $conn->query("SELECT COUNT(*) as count FROM `$tableName`");
                $rowCount = $countResult ? $countResult->fetch_assoc()['count'] : '?';
                
                $status['tables'][$tableName] = [
                    'name' => $tableName,
                    'rows' => $rowCount
                ];
            }
        }
    } catch (Exception $e) {
        $status['error'] = $e->getMessage();
    }
    
    return $status;
}