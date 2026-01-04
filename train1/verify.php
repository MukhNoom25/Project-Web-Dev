<?php
// Set the timezone to Malaysia Time
date_default_timezone_set('Asia/Kuala_Lumpur');

@include 'config.php';
session_start();

if (isset($_GET["verify"])) {
    // Get the verification code from the URL
    $verification_code = $_GET["verify"];

    // Check if the verification code exists in the database
    $stmt = $pdo->prepare("SELECT id, is_verified FROM users WHERE verification_code = ?");
    $stmt->execute([$verification_code]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Check if the user is already verified
        if ($user["is_verified"] == 1) {
            $_SESSION["verification_message"] = "Email is already verified. You can now log in.";
        } 
        
        else {
            // Update user as verified
            $stmt = $pdo->prepare("UPDATE users SET is_verified = 1 WHERE id = ?");
            $stmt->execute([$user["id"]]);

            $_SESSION["verification_message"] = "Email verification successful. You can now log in.";
        }
    } 
    
    else {
        $_SESSION["error"] = "Invalid verification code.";
        header("Location: signup.php");
        exit();
    }

    header("Location: login.php");
    exit();
} 

else {
    $_SESSION["error"] = "Invalid verification link.";
    header("Location: signup.php");
    exit();
}
?>
