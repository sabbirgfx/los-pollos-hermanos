<?php
// Turn on error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config/database.php';

try {
    // Try to connect to MySQL first without specifying a database
    $conn = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p>Successfully connected to MySQL server.</p>";
    
    // Check if database exists
    $stmt = $conn->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
    $dbExists = $stmt->rowCount() > 0;
    
    if ($dbExists) {
        echo "<p>Database '" . DB_NAME . "' exists.</p>";
        
        // Connect to the database and check tables
        $dbConn = getDBConnection();
        
        $stmt = $dbConn->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<p>Tables in database:</p>";
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>" . $table . "</li>";
        }
        echo "</ul>";
        
        // Check if categories exist
        $stmt = $dbConn->query("SELECT * FROM categories");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p>Categories:</p>";
        echo "<pre>";
        print_r($categories);
        echo "</pre>";
        
    } else {
        echo "<p>Database '" . DB_NAME . "' does not exist.</p>";
    }
    
} catch (PDOException $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?> 