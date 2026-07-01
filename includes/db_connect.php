<?php
// Define connection constants
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'student_admission_db');

try {
    // Create a new PDO instance with UTF-8 character encoding set
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    
    // Set PDO options for error handling and default fetch mode
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on SQL errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch records as associative arrays
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Use actual prepared statements (more secure)
    ];
    
    // Establish connection
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
} catch (PDOException $e) {
    // In production, log the error and show a generic message.
    // For a college project, we can output the error details to make debugging easier.
    die("Database Connection Failed: " . $e->getMessage());
}
?>

