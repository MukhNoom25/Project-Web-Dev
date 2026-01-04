<?php
// Start the session
session_start();

// Set the session timeout period in seconds (15 minutes in this case)
$timeout = 15*60;

// Check if the last activity time is set
if (isset($_SESSION['last_activity'])) {
    // Calculate the elapsed time since the last activity
    $elapsed_time = time() - $_SESSION['last_activity'];

    // If the elapsed time is greater than the timeout period, destroy the session
    if ($elapsed_time > $timeout) {
        session_unset();
        session_destroy();
        header('Location: login.php');
        exit;
    }
}

// Update the last activity time
$_SESSION['last_activity'] = time();
?>
