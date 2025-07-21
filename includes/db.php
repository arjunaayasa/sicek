<?php
/**
 * Database Connection
 */

// Include configuration
require_once __DIR__ . '/../config/database.php';

/**
 * Get database connection
 * 
 * @return mysqli Database connection
 */
function getDbConnection() {
    static $conn = null;
    
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        
        // Set charset
        $conn->set_charset("utf8mb4");
    }
    
    return $conn;
}

/**
 * Initialize database
 * 
 * @return bool True if initialized successfully, false otherwise
 */
function initializeDatabase() {
    // Connect to MySQL server without selecting a database
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    
    if ($conn->connect_error) {
        return false;
    }
    
    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if (!$conn->query($sql)) {
        return false;
    }
    
    // Select the database
    $conn->select_db(DB_NAME);
    
    // Create scan_logs table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS scan_logs (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        url VARCHAR(255) NOT NULL,
        domain VARCHAR(255) NOT NULL,
        domain_age_years FLOAT DEFAULT 0,
        ssl_valid TINYINT(1) DEFAULT 0,
        has_login_form TINYINT(1) DEFAULT 0,
        has_redirect TINYINT(1) DEFAULT 0,
        similarity_domain VARCHAR(255) DEFAULT '',
        similarity_score FLOAT DEFAULT 0,
        ai_analysis TEXT,
        score INT(11) DEFAULT 0,
        status VARCHAR(20) DEFAULT 'aman',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (domain),
        INDEX (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $result = $conn->query($sql);
    $conn->close();
    
    return $result;
}