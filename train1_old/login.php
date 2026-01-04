<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require 'vendor/autoload.php';

// Include the PDO connection
include 'config.php';
session_start();

// Function to sanitize user inputs
function sanitize_input($data) {
    return htmlspecialchars(trim($data));
}

// Function to validate email format
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Function to check if user enrollment year is 2020 or below
function is_user_enrollment_blocked($created_at) {
    if (empty($created_at)) {
        return false; // If no creation date, allow login
    }
    
    // Extract year from created_at timestamp
    $creation_year = date('Y', strtotime($created_at));
    
    // Block if enrolled in 2020 or earlier
    return $creation_year <= 2020;
}

// Display success or error message from the verification process
if (isset($_SESSION["verification_message"])) {
    echo "<script>alert('{$_SESSION["verification_message"]}');</script>";
    unset($_SESSION["verification_message"]);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate email
    $email = validate_email(sanitize_input($_POST["email"]));

    // Sanitize password
    $password = sanitize_input($_POST["password"]);

    // Check if email and password are not empty
    if (!empty($email) && !empty($password)) {
        // Retrieve the hashed password and salt from the database
        $query = "SELECT * FROM users WHERE email=:email AND is_verified=1";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $stored_password = $user['password'];
            $stored_salt = $user['password_salt']; // Get the salt from database

            // Check if user enrollment year is blocked (2020 and below)
            if (is_user_enrollment_blocked($user['created_at'])) {
                $_SESSION["error"] = "Sorry, your email has been blocked after 5 years not login. Please register with new email.";
            } else {
                // Verify the entered password with salt using password_verify
                if (password_verify($password . $stored_salt, $stored_password)) {
                    // User exists and is verified, generate OTP and redirect to otp-verification.php
                    $otp = mt_rand(100000, 999999); // Generate a random 6-digit OTP
                    $expiry_time = time() + 300; // Set OTP expiration time to 5 minute (300 seconds)

                    // Set the default time zone to Malaysia
                    date_default_timezone_set('Asia/Kuala_Lumpur');

                    // Convert expiration time to MySQL timestamp
                    $mysql_expiry_time = date('Y-m-d H:i:s', $expiry_time);

                    // Store OTP, expiration time, and user ID in session
                    $_SESSION['otp'] = $otp;
                    $_SESSION['otp_expiration'] = $expiry_time;
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['role'] = $user['role'];

                    // Update the database with the generated OTP and expiration time
                    $update_query = "UPDATE users SET otp=:otp, otp_expiration=:expiry_time WHERE email=:email";
                    $update_stmt = $pdo->prepare($update_query);
                    $update_stmt->bindParam(':otp', $otp);
                    $update_stmt->bindParam(':expiry_time', $mysql_expiry_time); // Converted timestamp
                    $update_stmt->bindParam(':email', $email);
                    $update_stmt->execute();

                    // Send OTP to user's email using PHPMailer
                    sendOtpEmail($email, $otp);

                    // Redirect to otp-verification.php
                    header("Location: http://localhost/train1/otp_verification.php?email={$email}&id={$user['id']}");
                    exit();
                } 
                
                else {
                    // Incorrect username or password
                    $_SESSION["error"] = "Email or password is incorrect.";
                }
            }
        } 
        
        else {
            // User does not exist in the database
            $_SESSION["error"] = "User does not exist.";
        }
    }
}

// Function to send OTP email using PHPMailer
function sendOtpEmail($email, $otp) {
    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'trainservice60@gmail.com'; 
        $mail->Password = 'nncxourxpfpdizsw'; 
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        //Recipients
        $mail->setFrom('trainservice60@gmail.com', 'Train Service Management System');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Action Required: Train Service Management System Login Verification';
        $mail->Body = "
            <p>This email is to verify your identity for accessing the Train Service Management System.</p>
            <p>Please enter the following One-Time Password (OTP) to complete your login: $otp</p>
            <p><strong>Please note:</strong></p>
            <ul>
                <li>This OTP is valid for 5 minutes only.</li>
                <li>If the OTP expires, please request a new one.</li>
                <li>If you did not initiate this login attempt, please disregard this email and immediately contact the Train Service Management System team for security purposes.</li>
            </ul>
            <p>We appreciate your cooperation in maintaining the security of our system.</p>
            <p>Sincerely,<br>Train Service Management System Team</p>
        ";

        // Send the new OTP to the user's email
        $mail->send();

        // Store new OTP and expiration time in the session
        $_SESSION['otp'] = $otp;
        $_SESSION['otp_expiration'] = time() + 300; // 5 minutes validity
        $_SESSION["success"] = "OTP sent successfully. Check your email to verify your identity.";
    } 
    catch (Exception $e) {
        // Handle errors
        $_SESSION["error"] = "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Login</title>
    <link rel="icon" type="image/x-icon" href="images/favicon.ico"/>
    <link href="css/login,signup.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-...." crossorigin="anonymous" />
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
</head><style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      /* Background image styling */
      background-image: url('images/161.jpg'); /* Replace with your image path */
      background-size: cover; /* Makes the image cover the entire background */
      background-repeat: no-repeat; /* Prevents the image from repeating */
      background-position: center; /* Centers the image */
    }
    </style>
<body>
    <div id="right-column">
        <h1 class="login-form-title">TSMS | LOGIN</h1>

        <div id="center-container">
            <div class="login-form">
                <!-- Display success or error message -->
                <?php if (isset($_SESSION["success"])) : ?>
                    <div class="message success">
                        <?php echo $_SESSION["success"]; ?>
                    </div>
                <?php elseif (isset($_SESSION["error"])) : ?>
                    <div class="message error">
                        <?php echo $_SESSION["error"]; ?>
                    </div>
                <?php endif; ?>

                <form action="" method="post">
                    <div>
                        <label for="email">Email:</label>
                        <input type="email" name="email" required>
                    </div>

                    <div>
                        <label for="password">Password:</label>
                        <input type="password" name="password" required>
                    </div>

                    <button type="submit">Login</button>
                </form>
            </div>
            <div class="login-forgot">
                    <a href="forgot_password.php">Forgot Passsword </a>
                </div>
        </div>
        <br>
        <footer class="login-footer">Not a member yet? <a href="signup.php">Sign Up Now</a></footer>
    </div>
</body>
</html>