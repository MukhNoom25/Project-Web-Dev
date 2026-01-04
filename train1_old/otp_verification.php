<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader for PHPMailer
require 'vendor/autoload.php';

// Set the timezone to Malaysia Time
date_default_timezone_set('Asia/Kuala_Lumpur');

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

// Function to send OTP to user's email using PHPMailer
function sendOTPToEmail($newOTP, $userEmail) {
    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'trainservice6360@gmail.com';
        $mail->Password = '@Khairul0077'; //encrypted password 
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        //Recipients
        $mail->setFrom('trainservice60@gmail.com', 'Train Service Management System');
        $mail->addAddress($userEmail);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'New OTP for Train Service Management System Login';
        $mail->Body = "
            <p>We've noticed you're having trouble logging into the Train Service Management System. To help you get back on track, we've sent you a new One-Time Password (OTP): $newOTP</p>
            <p>Please remember:</p>
            <ul>
                <li>This OTP is valid for only 5 minutes after you receive this email.</li>
                <li>Enter the OTP promptly to access the system.</li>
                <li>If you don't use the OTP within 5 minutes, you can request another one.</li>
            </ul>
            <p>For your security:</p>
            <ul>
                <li>Never share your OTP with anyone.</li>
                <li>If you're still having trouble logging in, please don't hesitate to contact our support team at trainservice60@gmail.com. We're happy to help!</li>
            </ul>
            <p>Sincerely,</p>
            <p>The Train Service Management System Team</p>
        ";


        // Send the new OTP to the user's email
        $mail->send();

        // Store new OTP and expiration time in the session
        $_SESSION['otp'] = $newOTP;
        $_SESSION['otp_expiration'] = time() + 300; // 5 minutes validity
        $_SESSION["success"] = "New OTP sent successfully. Check your email to verify your identity.";
    } 
    catch (Exception $e) {
        // Handle errors
        $_SESSION["error"] = "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

// Function to generate and send OTP to user's email
function generateAndSendOTP($userId, $pdo, $userEmail) {
    // Generate a new OTP
    $newOTP = mt_rand(100000, 999999);

    // Set OTP in the database
    $query = "UPDATE users SET otp = :otp, otp_expiration = DATE_ADD(NOW(), INTERVAL 5 MINUTE) WHERE id = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':otp', $newOTP);
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();

    // Email sending function
    sendOTPToEmail($newOTP, $userEmail);

    // Return the new OTP
    return $newOTP;
}

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    // Get user ID from the session
    $userId = $_SESSION['user_id'];

    // Check if the form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Sanitize and validate OTP
        $enteredOTP = filter_var($_POST["otp"], FILTER_SANITIZE_NUMBER_INT);

        // Retrieve the user's OTP and expiration time from the database
        $query = "SELECT otp, otp_expiration, email FROM users WHERE id=:user_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            $storedOTP = $userData['otp'];
            $expirationTime = strtotime($userData['otp_expiration']);
            $userEmail = $userData['email']; // Retrieve the user's email

            // Verify the entered OTP
            if ($enteredOTP == $storedOTP) {
                // Check if OTP is expired
                if (time() <= $expirationTime) {
                    // OTP is correct and not expired
                    unset($_SESSION['otp']); // Clear stored OTP from the session

                    // Retrieve user's role from the database
                    $queryIdentity = "SELECT identity FROM users WHERE id=:user_id";
                    $stmtIdentity = $pdo->prepare($queryIdentity);
                    $stmtIdentity->bindParam(':user_id', $userId);
                    $stmtIdentity->execute();

                    if ($stmtIdentity->rowCount() == 1) {
                        $userDataIdentity = $stmtIdentity->fetch(PDO::FETCH_ASSOC);
                        $userIdentity = $userDataIdentity['identity'];

                        // Redirect based on user's identity
                        if ($userIdentity == 'staff') {
                            header("Location: staff-homepage.php?user_id=$userId");
                        } elseif ($userIdentity == 'admin') {
                            header("Location: dashboard.php?user_id=$userId");
                        } else {
                            $_SESSION["error"] = "Invalid user identity.";
                        }
                    } else {
                        $_SESSION["error"] = "Error fetching user identity.";
                    }
                } else {
                    // OTP is correct but expired
                    $_SESSION["error"] = "OTP has expired. Please request a new one.";
                }
            } else {
                // Incorrect OTP
                $_SESSION["error"] = "Invalid OTP.";
            }
        }
    }

    // Check if the resend button is clicked
    if (isset($_POST["resend"])) {
        // Generate and send a new OTP
        $newOTP = generateAndSendOTP($userId, $pdo, $userEmail);
    }
} else {
    // Redirect to the login page or handle the case when the user is not logged in
    header('Location: login.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Two Factor Authentication</title>
    <link rel="icon" type="image/x-icon" href="images/favicon.ico" />
    <link href="css/login,signup.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-...." crossorigin="anonymous" />
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
</head>
<style>
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
    <h1 class="login-form-title">TSMS | TWO FACTOR AUTHENTICATION</h1>

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
                    <label for="otp">Enter OTP:</label>
                    <input type="text" name="otp" id="otp" pattern="\d{6}" title="Please enter a 6-digit OTP" required value="<?php echo isset($_POST['otp']) ? htmlspecialchars($_POST['otp']) : ''; ?>">
                </div>

                <button type="submit" id="verifyBtn">Verify OTP</button>
                <p id="timer" style="display: block;"></p>

                <div id="resend-container" style="display: none;">
                <br>
                    <form action="" method="post">
                        <button type="submit" name="resend" id="resendBtn">Get New OTP</button>
                    </form>
                </div>
            </form>
        </div>
    </div>
    <br>
</div>

<script>
    // Countdown timer in seconds
    var countdownTime = 30;

    // Function to update the timer display
    function updateTimerDisplay() {
        if (countdownTime <= 1) {
            document.getElementById("timer").style.display = "none";
            document.getElementById("resend-container").style.display = "block";
        } else {
            document.getElementById("timer").textContent = "Get New OTP in " + countdownTime + " seconds";
        }
    }

    // Function to handle the countdown
    function startCountdown() {
        var countdownInterval = setInterval(function () {
            countdownTime--;

            if (countdownTime <= 0) {
                // Display the Resend OTP button and clear the interval
                document.getElementById("resend-container").style.display = "block";
                clearInterval(countdownInterval);
            } 
            
            else {
                // Update the timer display
                updateTimerDisplay();
            }
        }, 1000);
    }

    // Function to resend OTP
    function resendOTP() {
        countdownTime = 30;
        document.getElementById("timer").style.display = "block"; // Show the timer
        document.getElementById("resend-container").style.display = "none";

        // Clear the entered OTP value when resending
        document.getElementById("otp").value = "";

        updateTimerDisplay();
        startCountdown();
    }

    // Show the timer initially
    updateTimerDisplay();

    // Start the initial countdown
    startCountdown();
</script>


</body>
</html>