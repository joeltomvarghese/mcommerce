<?php
// Database connection details
define('DB_SERVER', 'localhost');
// For local XAMPP: default user is 'root', password is '' (empty string)
// *** REMEMBER TO CHANGE THESE WHEN DEPLOYING TO AWS EC2 ***
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'mcommerce_app');

// Attempt to connect to MySQL database
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if($conn === false){
    // In a production environment, never display error details to the user
    // log the error instead.
    die("ERROR: Could not connect. " . $conn->connect_error);
}
?>
