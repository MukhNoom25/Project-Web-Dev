<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
include 'config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);

    // Cek email terdaftar dan sudah verified
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND is_verified = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
    $reset_otp = mt_rand(100000, 999999);
    $reset_otp_expiry = date('Y-m-d H:i:s', time() + 300); // 5 minit
    
    // Debug 1: Log sebelum update
    error_log("[DEBUG] Attempting to update OTP for: $email, OTP: $reset_otp");
    
    $updateStmt = $pdo->prepare("UPDATE users SET otp = ?, otp_expiration = ? WHERE id = ?");
    $updateStmt->execute([$reset_otp, $reset_otp_expiry, $user['id']]);
    
    // Debug 2: Log hasil update
    error_log("[DEBUG] Updated rows: " . $updateStmt->rowCount() . " for user ID: " . $user['id']);
    
    // Debug 3: Log error jika ada
    if ($updateStmt->rowCount() === 0) {
        error_log("[ERROR] Failed to update OTP for user ID: " . $user['id']);
    }

        // Kirim email OTP reset
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

            $mail->setFrom('trainservice60@gmail.com', 'Train Service');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset OTP';
            $mail->Body = "
                <h3>Password Reset Request</h3>
                <p>Use this OTP to reset your password:</p>
                <h2>$reset_otp</h2>
                <p>Valid for 5 minutes only.</p>
                <p>If you didn't request this, please ignore this email.</p>
            ";

            $mail->send();
            $_SESSION['reset_email'] = $email;
            $_SESSION['success'] = "OTP sent to your email.";
            header("Location: reset_password.php");
            exit();
        } catch (Exception $e) {
            $_SESSION['error'] = "Failed to send OTP: " . $e->getMessage();
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Email not found or not verified.";
        header("Location: forgot_password.php");
        exit();
    }

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link href="css/login,signup.css" rel="stylesheet">
    <style>
        body { background-image: url('images/bg.jpg'); background-size: cover; }
    </style>
</head>
<body>
    <div id="right-column">
        <h1 class="login-form-title">TSMS | FORGOT PASSWORD</h1>
        <div id="center-container">
            <div class="login-form">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="message error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div>
                        <label for="email">Email:</label>
                        <input type="email" name="email" required>
                    </div>
                    <button type="submit">Send OTP</button>
                </form>
                <br><div class="login-forgot">
                    <a href="login.php">Back to Login</a>
                </div>
        </div>
    </div>
</body>
</html>