<?php

// Set the timezone to Malaysia Time
date_default_timezone_set('Asia/Kuala_Lumpur');

// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require 'vendor/autoload.php';

@include 'config.php';
session_start();

unset($_SESSION["success"]);
unset($_SESSION["error"]);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize form data
    $username = htmlspecialchars($_POST["username"]);
    $email = htmlspecialchars($_POST["email"]);
    $password = htmlspecialchars($_POST["password"]);
    $confirm_password = htmlspecialchars($_POST["confirm_password"]);

    // Check if the username and email already exist
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $existingUsername = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingUsername && $existingUser) {
        $_SESSION["error"] = "User already exist.";
    } 
    
    elseif ($existingUsername) {
        $_SESSION["error"] = "Username already exists. Please choose a different username.";
    } 
    
    elseif ($existingUser) {
        $_SESSION["error"] = "Email already exists. Please choose a different email.";
    } 
    
    else {
        // Check password strength
        $passwordRequirements = isValidPassword($password);
        if (!$passwordRequirements["isValid"]) {
            $_SESSION["error"] = $passwordRequirements["message"];
        } 
        
        else {
            // Check if passwords match
            if ($password !== $confirm_password) {
                $_SESSION["error"] = "Password and Confirmed Password Are Not Match!";
            } 
            
            else {
                // Generate verification code
                $verification_code = md5(uniqid());

                // Generate random salt (32 characters)
                $salt = bin2hex(random_bytes(16));

                // Hash the password with salt
                $hashed_password = password_hash($password . $salt, PASSWORD_DEFAULT);

                // Insert user data into the database (including salt)
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, password_salt, verification_code, created_at) VALUES (?,?,?,?,?, NOW())");
                $stmt->execute([$username, $email, $hashed_password, $salt, $verification_code]);

                // Send verification email using PHPMailer
                $mail = new PHPMailer(true);

                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'trainservice60@gmail.com'; 
                    $mail->Password = 'nncxourxpfpdizsw'; 
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;

                    $mail->SMTPOptions = [
                        'ssl' => [
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true,
                        ],
                    ];

                    // Sender and recipient settings
                    $mail->setFrom('trainservice60@gmail.com', 'Train Service Management System');
                    $mail->addAddress($email, $username);

                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = 'Welcome to Train Service Management System! Verify your Account & Get Rolling!';
                    $mail->Body = "Welcome aboard the Train Service Management System! We're excited to have you on board and ready to keep your train running smoothly.<br><br>"
                                . "To complete your registration and unlock all the features, simply click the link below to verify your email address:<br>"
                                . "<a href='http://localhost/train1/verify.php?verify=$verification_code'>Verify Your Email</a><br><br>"
                                . "Click the link and join the journey to a reliable, efficient train service!<br><br>"
                                . "If you have any questions or trouble verifying your email, feel free to contact us at trainservice60@gmail.com.<br><br>"
                                . "We're looking forward to serving you!<br><br>"
                                . "Sincerely,<br>"
                                . "The Train Service Management System Team";

                    $mail->send();
                    $_SESSION["success"] = "Registration successful. Check your email to verify your account.";
                } 
                catch (Exception $e) {
                    $_SESSION["error"] = "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }
            }
        }
    }
}

// Function to check password strength
function isValidPassword($password) {
    $requirements = array(
        "length" => strlen($password) >= 12,
        "uppercase" => preg_match('/[A-Z]/', $password),
        "lowercase" => preg_match('/[a-z]/', $password),
        "numbers" => preg_match('/\d/', $password),
        "specialChars" => preg_match('/[!@#$%^&*]/', $password)
    );

    $isValid = array_reduce($requirements, function ($carry, $item) {
        return $carry && $item;
    }, true);

    $message = "Password must contain at least 12 characters, one uppercase letter, one lowercase letter, one digit, and one special character.";

    return array("isValid" => $isValid, "message" => $isValid ? "" : $message);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Sign Up</title>
    <link rel="icon" type="image/x-icon" href="images/icon.png"/>
    <link href="css/login,signup.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-...." crossorigin="anonymous" />
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
</head>
<style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      /* Background image styling */
      background-image: url('images/bg.jpg'); /* Replace with your image path */
      background-size: cover; /* Makes the image cover the entire background */
      background-repeat: no-repeat; /* Prevents the image from repeating */
      background-position: center; /* Centers the image */
    }
    </style>
<body>

    <div id="right-column">
        <h1 class="signup-form-title">TSMS | SIGN UP</h1>

        <div id="center-container">
            <div class="signup-form">

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
                        <label for="username">Username:</label>
                        <input type="text" name="username" required value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>">
                    </div>

                    <div>
                        <label for="email">Email:</label>
                        <input type="email" name="email" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                    </div>

                    <div>
                        <label for="password">Password:</label>
                        <input type="password" name="password" id="password" required>
                        <i class="toggle-password fas fa-eye-slash" onclick="togglePassword('password')" style="float: right;"></i>
                    </div>

                    <div>
                        <label for="confirm_password">Confirm Password:</label>
                        <input type="password" name="confirm_password" id="confirm_password" required>
                        <i class="toggle-password fas fa-eye-slash" onclick="togglePassword('confirm_password')" style="float: right;"></i>
                    </div>

                    <div>
                        <label for="agree">
                            <input type="checkbox" name="agree" id="agree" value="yes" required/> I agree with the
                            <a href="term.php" title="term of services">Term of Services</a>
                        </label>
                    </div>

                    <button type="submit">Sign Up</button>
                </form>
            </div>
        </div>
        <footer class="signup-footer" style="text-align: center;">
            Already a member? <a href="login.php">Login Now</a> | <a href="faq.php">FAQ</a>
            <br><br>
            <p style="font-size: 0.9rem;">Having trouble? <a href="contact.php" style="color: #004e92; text-decoration: none;">Contact Support</a></p>
        </footer>
    </div>
    <script>
        <?php
        if (isset($_SESSION["success"])) {
            unset($_SESSION["success"]);
        }

        if (isset($_SESSION["error"])) {
            echo "var storedUsername = '" . (isset($username) ? htmlspecialchars($username) : '') . "';";
            echo "var storedEmail = '" . (isset($email) ? htmlspecialchars($email) : '') . "';";
            unset($_SESSION["error"]);
        } else {
            echo "var storedUsername = '';";
            echo "var storedEmail = '';";
        }
        ?>

        // JavaScript function to prefill the form with stored values
        function prefillForm() {
            document.getElementById('username').value = storedUsername;
            document.getElementById('email').value = storedEmail;
        }

        // Ensure the DOM has fully loaded before executing the JavaScript code
        document.addEventListener("DOMContentLoaded", function () {
            prefillForm(); // Prefill the form with stored value
        });

        //Toogle eye
        function togglePassword(inputId) {
        var passwordInput = document.getElementById(inputId);
        var eyeIcon = document.querySelector('#' + inputId + ' + i.toggle-password');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.classList.remove('fa-eye-slash');
            eyeIcon.classList.add('fa-eye');
        } else {
            passwordInput.type = 'password';
            eyeIcon.classList.remove('fa-eye');
            eyeIcon.classList.add('fa-eye-slash');
        }
    }
    </script>

</body>
</html>