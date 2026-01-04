<?php require_once __DIR__ . '/../config.php'; ?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Barbershop Booking</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <script src="../assets/js/main.js"></script>
</head>
<body>
<header><h2>Barbershop Online Booking</h2></header>
<div class="container">
  <div class="card">
    <form method="post" action="submit_booking.php">
      <div class="grid">
        <div>
          <label>Nama Penuh</label>
          <input required name="customer_name" placeholder="cth: Ahmad"/>
        </div>
        <div>
          <label>No. Telefon (WhatsApp)</label>
          <input name="phone" placeholder="0123456789"/>
        </div>
        <div>
          <label>Perkhidmatan</label>
          <select name="service_id" required>
            <option value="">-- Pilih --</option>
            <?php
              $services = $pdo->query("SELECT id,name,price_cents,duration_min FROM services WHERE active=1")->fetchAll();
              foreach($services as $s){
                echo '<option value="'.$s['id'].'">'.htmlspecialchars($s['name']).' — '.money_format_rm($s['price_cents']).' ('.$s['duration_min'].' min)</option>';
              }
            ?>
          </select>
        </div>
        <div>
          <label>Barber</label>
          <select name="staff_id" required>
            <option value="">-- Mana-mana --</option>
            <?php
              $staff = $pdo->query("SELECT id,name FROM staff WHERE active=1")->fetchAll();
              foreach($staff as $st){
                echo '<option value="'.$st['id'].'">'.htmlspecialchars($st['name']).'</option>';
              }
            ?>
          </select>
        </div>
        <div>
          <label>Tarikh & Masa Mula</label>
          <input type="datetime-local" name="start_time" required/>
        </div>
        <div>
          <label>Catatan (pilihan)</label>
          <textarea name="notes" placeholder="Gaya rambut, dll"></textarea>
        </div>
      </div>
      <br>
      <button class="btn-primary" id="submit-booking">Tempah Sekarang</button>
    </form>
    <div id="booking-response"></div>
  </div>

  <div class="card">
    <h3>Slot Tempahan Akan Datang</h3>
    <table>
      <thead><tr><th>Pelanggan</th><th>Barber</th><th>Servis</th><th>Mula</th><th>Status</th></tr></thead>
      <tbody>
      <?php
        $rows = $pdo->query("SELECT b.*, s.name AS service_name, st.name AS staff_name FROM bookings b 
                             LEFT JOIN services s ON s.id=b.service_id 
                             LEFT JOIN staff st ON st.id=b.staff_id 
                             WHERE b.start_time >= NOW() ORDER BY b.start_time ASC LIMIT 20")->fetchAll();
        foreach($rows as $r){
          echo '<tr><td>'.htmlspecialchars($r['customer_name']).'</td><td>'.htmlspecialchars($r['staff_name']).'</td><td>'.htmlspecialchars($r['service_name']).'</td><td>'.htmlspecialchars($r['start_time']).'</td><td><span class="badge">'.htmlspecialchars($r['status']).'</span></td></tr>';
        }
      ?>
      </tbody>
    </table>
  </div>
</div>
<footer>© '.date('Y').' Barbershop</footer>
<script>
document.getElementById('submit-booking').addEventListener('click', function(e) {
  e.preventDefault();
  const form = document.querySelector('form');
  if (!validateForm(form)) return;
  const formData = new FormData(form);
  const data = {};
  for (let [key, value] of formData.entries()) {
    data[key] = value;
  }
  showLoading(this);
  ajaxPost('submit_booking.php', data, function(err, response) {
    hideLoading(document.getElementById('submit-booking'));
    if (err) {
      showAlert('Error submitting booking. Please try again.', 'error');
    } else {
      showAlert('Booking submitted successfully!', 'success');
      form.reset();
      // Optionally refresh the upcoming slots table
      location.reload(); // Simple reload for now; could be AJAX later
    }
  });
});
</script>
</body>
</html>
