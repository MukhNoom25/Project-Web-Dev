<?php include 'guard.php'; require_once __DIR__ . '/../config.php'; ?>
<title>Tempahan</title>
<link rel="stylesheet" href="../assets/css/style.css">
<script src="../assets/js/main.js"></script></head>
<body><header><h2>Tempahan</h2></header><div class="container"><div class="card">
<?php
if(isset($_POST['update_status'])){
  $stmt=$pdo->prepare("UPDATE bookings SET status=? WHERE id=?");
  $stmt->execute([$_POST['status'], intval($_POST['booking_id'])]);
}
$rows=$pdo->query("SELECT b.*, s.name AS service_name, st.name AS staff_name FROM bookings b 
LEFT JOIN services s ON s.id=b.service_id 
LEFT JOIN staff st ON st.id=b.staff_id
ORDER BY b.start_time DESC LIMIT 100")->fetchAll();
echo '<table><tr><th>Masa</th><th>Pelanggan</th><th>Barber</th><th>Servis</th><th>Status</th><th>Tindakan</th></tr>';
foreach($rows as $r){
  echo '<tr><td>'.$r['start_time'].'</td><td>'.htmlspecialchars($r['customer_name']).'</td><td>'.htmlspecialchars($r['staff_name']).'</td><td>'.htmlspecialchars($r['service_name']).'</td><td>'.$r['status'].'</td>
  <td>
    <select id="status-'.$r['id'].'" onchange="updateStatus('.$r['id'].', this.value)">
      <option '.($r['status']=='pending'?'selected':'').' value="pending">Pending</option>
      <option '.($r['status']=='confirmed'?'selected':'').' value="confirmed">Confirmed</option>
      <option '.($r['status']=='completed'?'selected':'').' value="completed">Completed</option>
      <option '.($r['status']=='cancelled'?'selected':'').' value="cancelled">Cancelled</option>
    </select>
  </td></tr>';
}
echo '</table>';
?>
<script>
function updateStatus(bookingId, newStatus) {
  if (!confirm('Are you sure you want to update the status to ' + newStatus + '?')) {
    // Revert the select
    document.getElementById('status-' + bookingId).value = document.getElementById('status-' + bookingId).getAttribute('data-original');
    return;
  }
  showLoading(document.getElementById('status-' + bookingId));
  ajaxPost('', { update_status: 1, booking_id: bookingId, status: newStatus }, function(err, response) {
    hideLoading(document.getElementById('status-' + bookingId));
    if (err) {
      showAlert('Error updating status.', 'error');
      // Revert
      document.getElementById('status-' + bookingId).value = document.getElementById('status-' + bookingId).getAttribute('data-original');
    } else {
      showAlert('Status updated successfully!', 'success');
      document.getElementById('status-' + bookingId).setAttribute('data-original', newStatus);
    }
  });
}

// Set original values
document.querySelectorAll('select[id^="status-"]').forEach(select => {
  select.setAttribute('data-original', select.value);
});
</script>
</div></div></body></html>
