<?php
$servername = "localhost";
$username = "root"; // Change if needed
$password = ""; // Change to DB password
$dbname = "jppb";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
