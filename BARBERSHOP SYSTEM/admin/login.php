<?php require_once __DIR__ . '/../config.php';
session_start();
if($_SERVER['REQUEST_METHOD']==='POST'){
  $email = $_POST['email'] ?? '';
  $pass = $_POST['password'] ?? '';
  $stmt = $pdo->prepare("SELECT * FROM users WHERE email=?");
  $stmt->execute([$email]);
  $u = $stmt->fetch();
  if($u && hash('sha256', $pass) === $u['password_hash']){
    $_SESSION['uid'] = $u['id'];
    $_SESSION['name'] = $u['name'];
    $_SESSION['role'] = $u['role'];
    header('Location: dashboard.php'); exit;
  } else {
    $err = "Invalid login";
  }
}
?>
<!doctype html><html><head><meta charset="utf-8"><title>Admin Login</title>
<link rel="stylesheet" href="../assets/css/style.css"></head>
<body>
<header><h2>Barbershop — Admin</h2></header>
<div class="container">
  <div class="card" style="max-width:420px;margin:40px auto;">
    <form method="post">
      <h3>Log Masuk</h3>
      <?php if(isset($err)) echo '<div class="alert">'.htmlspecialchars($err).'</div>'; ?>
      <label>Email</label><input name="email" placeholder="admin@example.com" required>
      <label>Kata Laluan</label><input type="password" name="password" required>
      <br><button class="btn-primary" type="submit">Masuk</button>
    </form>
    <p>Default: admin@example.com / admin123</p>
  </div>
</div>
</body></html>
