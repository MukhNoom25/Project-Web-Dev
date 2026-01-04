<?php
session_start();

// Use the vendor folder because you have Composer installed
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$msg = "";
$error = "";

if (isset($_POST['send_contact'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'trainservice60@gmail.com'; 
        $mail->Password   = 'nncxourxpfpdizsw';       
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
        ];

        $mail->setFrom('trainservice60@gmail.com', 'TSMS Support System');
        $mail->addAddress('trainservice60@gmail.com'); 
        $mail->addReplyTo($email, $name); 

        $mail->isHTML(true);
        $mail->Subject = "Support Request: $subject";
        $mail->Body    = "<h3>New Contact Message</h3><p><strong>Name:</strong> $name</p><p><strong>Email:</strong> $email</p><p><strong>Subject:</strong> $subject</p><hr><p>$message</p>";

        $mail->send();
        $msg = "Message sent successfully!";
    } catch (Exception $e) {
        $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact Support</title>
    <link rel="stylesheet" href="css/support.css">
</head>
<body>
    <div class="visual-side">
        <div class="visual-content">
            <h1>IT Support</h1>
            <p>Report system bugs or request hardware repairs.</p>
        </div>
    </div>

    <div class="login-side">
        <div class="login-container">
            <h2>Contact Us</h2>
            
            <?php if($msg) echo "<p style='color:green;background:#dfd;padding:10px;'>$msg</p>"; ?>
            <?php if($error) echo "<p style='color:red;background:#fdd;padding:10px;'>$error</p>"; ?>

            <form action="" method="POST">
                <div class="input-field">
                    <label>Your Name</label>
                    <input type="text" name="name" required>
                </div>
                <div class="input-field">
                    <label>Email Address</label>
                    <input type="email" name="email" required>
                </div>
                <div class="input-field">
                    <label>Subject</label>
                    <select name="subject" required>
                        <option value="Login Issue">Login Issue</option>
                        <option value="Hardware Report">Hardware Report</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="input-field">
                    <label>Message</label>
                    <textarea name="message" required style="height:100px;"></textarea>
                </div>
                <button type="submit" name="send_contact" class="btn-login">Send Message</button>
            </form>
            <div class="links">
                <a href="login.php">Back to Login</a> | <a href="faq.php">View FAQ</a>
            </div>
        </div>
    </div>
</body>
</html>