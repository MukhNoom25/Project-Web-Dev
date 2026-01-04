<?php
// Start the session
session_start();

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    
    session_unset();
    session_destroy();
}

// Redirect to the login page after logout
header("Location: login.php");

// Display a logout success message using JavaScript
echo '<script>alert("Successfully logged out.");</script>';
exit; 
?>