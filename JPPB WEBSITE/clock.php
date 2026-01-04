<?php
session_start();
include "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Set timezone for accurate time
date_default_timezone_set('Asia/Kuala_Lumpur'); // Adjust to your timezone
$current_time = date('Y-m-d H:i:s');
$today = date('Y-m-d');

// Get current clock status
$check_status = $conn->query("SELECT id, clock_in, clock_out 
    FROM clock 
    WHERE user_id = $user_id 
    AND date = '$today' 
    ORDER BY id DESC LIMIT 1");

$is_clocked_in = false;
$current_record_id = null;
$last_clock_in = null;

if ($check_status && $check_status->num_rows > 0) {
    $last_record = $check_status->fetch_assoc();
    $is_clocked_in = ($last_record['clock_out'] === null);
    $current_record_id = $last_record['id'];
    $last_clock_in = $last_record['clock_in'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        try {
            if ($_POST['action'] === 'in' && !$is_clocked_in) {
                $stmt = $conn->prepare("INSERT INTO clock (user_id, clock_in, date) VALUES (?, NOW(), CURDATE())");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                echo "<div class='alert success'>Clocked in successfully!</div>";
                header("Refresh:1; url=clock.php");
            } 
            elseif ($_POST['action'] === 'out' && $is_clocked_in) {
                $stmt = $conn->prepare("UPDATE clock SET clock_out = NOW() WHERE id = ? AND user_id = ? AND clock_out IS NULL");
                $stmt->bind_param("ii", $current_record_id, $user_id);
                $stmt->execute();
                echo "<div class='alert success'>Clocked out successfully!</div>";
                header("Refresh:1; url=clock.php");
            }
        } catch (mysqli_sql_exception $e) {
            error_log("Clock error: " . $e->getMessage());
            echo "<div class='alert error'>Error recording clock action</div>";
        }
    }
}

// Get clock history
$result = $conn->query("SELECT * FROM clock WHERE user_id = " . intval($user_id) . " ORDER BY date DESC, clock_in DESC LIMIT 10");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Clock System</title>
    <style>
        .alert { padding: 10px; margin: 10px 0; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .status-box { 
            background: #f8f9fa;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin: 5px;
        }
        .btn-in { background: #28a745; color: white; }
        .btn-out { background: #dc3545; color: white; }
        table { border-collapse: collapse; width: 100%; }
        th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        .clock-buttons { margin: 20px 0; }
        .btn { 
            padding: 12px 24px;
            margin: 5px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        .current-time {
            font-size: 24px;
            font-weight: bold;
            margin: 20px 0;
        }
        #server-time {
            font-size: 20px;
            color: #333;
        }
        .back-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
        .back-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h2>Clock System</h2>

    <div class="current-time">
        Server Time: <span id="server-time"><?= $current_time ?></span>
    </div>

    <div class="clock-buttons">
        <form method="post" style="display: inline;">
            <?php if (!$is_clocked_in): ?>
                <button type="submit" name="action" value="in" class="btn btn-in">Clock In</button>
            <?php else: ?>
                Last Clock In: <?= $last_clock_in ?><br>
                <button type="submit" name="action" value="out" class="btn btn-out">Clock Out</button>
            <?php endif; ?>
        </form>
    </div>

    <h3>Recent Clock History</h3>
    <table>
        <tr>
            <th>Date</th>
            <th>Clock In</th>
            <th>Clock Out</th>
            <th>Duration</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): 
            $duration = '';
            if ($row['clock_in'] && $row['clock_out']) {
                $in = new DateTime($row['clock_in']);
                $out = new DateTime($row['clock_out']);
                $duration = $out->diff($in)->format('%H:%I:%S');
            }
        ?>
        <tr>
            <td><?= htmlspecialchars($row['date']) ?></td>
            <td><?= htmlspecialchars($row['clock_in']) ?></td>
            <td><?= $row['clock_out'] ? htmlspecialchars($row['clock_out']) : 'Still clocked in' ?></td>
            <td><?= $duration ?: '-' ?></td>
        </tr>
        <?php endwhile; ?>
    </table>

    <a href="dashboard.php" class="back-btn">Back to Dashboard</a>

    <script>
    function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleString('en-US', { 
            timeZone: 'Asia/Jakarta',
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false
        });
        document.getElementById('server-time').textContent = timeString;
    }
    
    // Update time every second
    setInterval(updateTime, 1000);
    updateTime(); // Initial call
    </script>
</body>
</html>
