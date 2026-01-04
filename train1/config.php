<?php

$host = 'localhost'; // database host
$db_username = 'root'; // database username
$password = ''; // database password
$database = 'train_service_db'; // database name

try {
    // Create a PDO database connection
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $db_username, $password);

    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} 

catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

?>
