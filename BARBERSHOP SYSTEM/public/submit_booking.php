<?php require_once __DIR__ . '/../config.php';
if($_SERVER['REQUEST_METHOD'] === 'POST'){
  $customer_name = trim($_POST['customer_name'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $service_id = intval($_POST['service_id'] ?? 0);
  $staff_id = intval($_POST['staff_id'] ?? 0);
  $start_time = $_POST['start_time'] ?? '';
  $notes = trim($_POST['notes'] ?? '');

  if(!$customer_name || !$service_id || !$staff_id || !$start_time){
    header('Location: index.php?err=missing');
    exit;
  }

  // fetch service to compute end time
  $stmt = $pdo->prepare("SELECT duration_min FROM services WHERE id=?");
  $stmt->execute([$service_id]);
  $svc = $stmt->fetch();
  if(!$svc){ die('Service not found'); }
  $end_time = date('Y-m-d H:i:s', strtotime($start_time . ' +' . intval($svc['duration_min']) . ' minutes'));

  // check overlap user submit request 
  $check = $pdo->prepare("SELECT COUNT(*) AS c FROM bookings WHERE staff_id=? AND status IN ('pending','confirmed') AND (
    (start_time <= ? AND end_time > ?) OR
    (start_time < ? AND end_time >= ?) OR
    (start_time >= ? AND end_time <= ?)
  )");
  $check->execute([$staff_id, $start_time, $start_time, $end_time, $end_time, $start_time, $end_time]);
  $c = $check->fetch()['c'] ?? 0;
  if($c > 0){
    echo "<script>alert('Slot bertembung. Sila pilih masa lain.'); window.location='index.php';</script>";
    exit;
  }

  $ins = $pdo->prepare("INSERT INTO bookings (customer_name, phone, staff_id, service_id, start_time, end_time, status, notes) VALUES (?,?,?,?,?,?, 'pending', ?)");
  $ins->execute([$customer_name, $phone, $staff_id, $service_id, $start_time, $end_time, $notes]);

  echo "<script>alert('Tempahan dihantar! Kami akan sahkan melalui WhatsApp/SMS.'); window.location='index.php';</script>";
} else {
  header('Location: index.php'); exit;
}
