<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'detectweb1');

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if ($conn->query($sql) !== TRUE) {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db(DB_NAME);

// Create table if not exists
$sql = "CREATE TABLE IF NOT EXISTS scan_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  url TEXT,
  domain VARCHAR(255),
  domain_age_years FLOAT,
  ssl_valid BOOLEAN,
  has_login_form BOOLEAN,
  has_redirect BOOLEAN,
  similarity_domain VARCHAR(255),
  similarity_score FLOAT,
  ai_analysis TEXT,
  score INT,
  status ENUM('aman', 'curiga', 'bahaya'),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) !== TRUE) {
    die("Error creating table: " . $conn->error);
}

// Return connection
return $conn;