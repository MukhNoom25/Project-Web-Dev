<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "shoutbox";


// Create connection
$conn = mysqli_connect($servername, $username,$password, $dbname);


// Check connection if (!$conn) 
if (!$conn) {
die("Connection failed: " . mysqli_connect_error());
}

    echo "Connected successfully";
?> 
