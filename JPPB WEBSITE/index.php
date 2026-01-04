<?php
session_start();
include "config.php";

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ic_number = $_POST['ic_number'];
    $nombor_badan = $_POST['nombor_badan'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE ic_number=? AND nombor_badan=?");
    $stmt->bind_param("ss", $ic_number, $nombor_badan);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['ic_number'];
        $_SESSION['role'] = $user['role'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "IC number or Nombor Badan is incorrect!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Login - JPPB</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="login-box">
    <h2>Login JPPB</h2>
    <form method="post">
        <input type="text" name="ic_number" placeholder="Nombor IC" required>
        <input type="password" name="nombor_badan" placeholder="Nombor Badan" required>
        <button type="submit">Login</button>
    </form>
    <?php if ($error) echo "<p class='error'>$error</p>"; ?>
</div>
</body>
</html>
