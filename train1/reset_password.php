<?php
include 'config.php';
session_start();

if (!isset($_SESSION['reset_email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['reset_email'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['otp'])) {
        // Verifikasi OTP
        $otp = $_POST['otp'];
        
        $stmt = $pdo->prepare("SELECT id, otp_expiration FROM users WHERE email = ? AND otp = ?");
        $stmt->execute([$email, $otp]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && strtotime($user['otp_expiration']) > time()) {
            $_SESSION['reset_verified'] = true;
            $_SESSION['reset_user_id'] = $user['id'];
        } else {
            $_SESSION['error'] = "Invalid or expired OTP.";
        }
    } 
    elseif (isset($_POST['new_password']) && isset($_SESSION['reset_verified'])) {
        $userId = $_SESSION['reset_user_id'];
        $rawNewPassword = $_POST['new_password'];

        // 1. Get the existing salt from the database for this user
        $stmtSalt = $pdo->prepare("SELECT password_salt FROM users WHERE id = ?");
        $stmtSalt->execute([$userId]);
        $stored_salt = $stmtSalt->fetchColumn(); 

        // 2. Combine the new password with the salt (EXACTLY like login.php does)
        $saltedPassword = $rawNewPassword . $stored_salt;

        // 3. Hash the combined string
        $newPasswordHash = password_hash($saltedPassword, PASSWORD_DEFAULT);

        // 4. Update the database
        $updateStmt = $pdo->prepare("UPDATE users SET password = ?, otp = NULL, otp_expiration = NULL WHERE id = ?");
        $updateStmt->execute([$newPasswordHash, $userId]);

        session_unset();
        $_SESSION['success'] = "Password updated successfully!";
        header("Location: login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link href="css/login,signup.css" rel="stylesheet">
    <style>
        body { background-image: url('images/bg.jpg'); background-size: cover; }
    </style>
</head>
<body>
    <div id="right-column">
        <h1 class="login-form-title">TSMS | RESET PASSWORD</h1>
        <div id="center-container">
            <div class="login-form">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="message error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>

                <?php if (!isset($_SESSION['reset_verified'])): ?>
                    <!-- Form verifikasi OTP -->
                    <form method="POST">
                        <div>
                            <label for="otp">Enter OTP:</label>
                            <input type="text" name="otp" pattern="\d{6}" required>
                        </div>
                        <button type="submit">Verify OTP</button>
                    </form>
                <?php else: ?>
                    <!-- Form password baru -->
                    <form method="POST">
                        <div>
                            <label for="new_password">New Password:</label>
                            <input type="password" name="new_password" required>
                        </div>
                        <button type="submit">Reset Password</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>