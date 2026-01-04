<?php
@include 'config.php';
include('timeout.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get the user ID from the session
$loggedInUserId = $_SESSION['user_id'];

// Fetch user details
$query = "SELECT * FROM users WHERE id = :userId";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':userId', $loggedInUserId, PDO::PARAM_INT);
$stmt->execute();
$userDetails = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$userDetails) {
    header('Location: login.php');
    exit();
}

// --- START OF AJAX LOGIC ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    
    // Toggle Status Action
    if ($_POST['action'] == 'toggleStatus' && isset($_POST['mechanicId'])) {
        $mechanicId = $_POST['mechanicId'];
        $newStatus = ($_POST['status'] == 1) ? 0 : 1;

        $updateStatusQuery = "UPDATE mechanics_list SET status = :status WHERE id = :id";
        $updateStatusStmt = $pdo->prepare($updateStatusQuery);
        $updateStatusStmt->execute([':status' => $newStatus, ':id' => $mechanicId]);
        
        echo "success"; 
        exit(); 
    }

    // --- FIXED DELETE ACTION (SOFT DELETE) ---
    if ($_POST['action'] == 'delete' && isset($_POST['mechanicId'])) {
        $mechanicId = $_POST['mechanicId'];

        // We use UPDATE instead of DELETE to avoid Foreign Key errors
        // This keeps the mechanic in the DB but marks them as Inactive
        $query = "UPDATE mechanics_list SET status = 0 WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id', $mechanicId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "error";
        }
        exit(); 
    }
}
// --- END OF AJAX LOGIC ---

// Handle Filtering
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
if ($statusFilter == 'active') {
    $mechanicsQuery = "SELECT * FROM mechanics_list WHERE status = 1";
} else if ($statusFilter == 'inactive') {
    $mechanicsQuery = "SELECT * FROM mechanics_list WHERE status = 0";
} else {
    $mechanicsQuery = "SELECT * FROM mechanics_list";
}

$mechanicsStmt = $pdo->prepare($mechanicsQuery);
$mechanicsStmt->execute();

$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Mechanic Management</title>
    <link rel="icon" type="image/x-icon" href="images/icon.png"/>
    <link href="css/admin/admin.css" rel="stylesheet"/>
    <link href="css/admin/mechanic.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <div class="sidebar close">
        <div class="logo-details">
            <a href="dashboard.php">
                <img src="images/icon.png" alt="TSMS" style="padding-left: 20px; padding-right: 40px; width: 2.5rem; height: 2.5rem;">
            </a>
            <span class="logo_name"><a href="dashboard.php">TSMS</a></span>
        </div>
        <ul class="nav-links">
            <li><a href="dashboard.php"><i class='bx bxs-tachometer'></i><span class="link_name">Dashboard</span></a></li>
            <li class="<?php echo ($currentPage == 'mechanics.php') ? 'active' : ''; ?>">
                <a href="mechanics.php"><i class='bx bxs-group'></i><span class="link_name">Mechanics</span></a>
            </li>
            <li><a href="category.php"><i class='bx bxs-category'></i><span class="link_name">Train Categories</span></a></li>
            <li><a href="service.php"><i class='bx bxs-spreadsheet'></i><span class="link_name">Service</span></a></li>
            <li><a href="service-request.php"><i class='bx bx-edit'></i><span class="link_name">Service Request</span></a></li>
            <li><a href="report.php"><i class='bx bxs-report'></i><span class="link_name">Report</span></a></li>
            <li><a href="#" id="logoutBtn"><i class='bx bx-log-out'></i><span class="link_name">Logout</span></a></li>
            <li>
                <div class="profile-details">
                    <div class="profile-content"><img src="images/user-gear.png" alt="profileImg"></div>
                    <div class="name-job">
                        <div class="profile_name"><?php echo htmlspecialchars($userDetails['username']); ?></div>
                        <div class="job" style="text-transform:capitalize;"><?php echo htmlspecialchars($userDetails['identity']); ?></div>
                    </div>
                </div>
            </li>
        </ul>
    </div>

    <section class="home-section">
        <div class="home-content">
            <i class='bx bx-menu'></i>
            <span class="text">Train Service Management System</span>
        </div>

        <div class="content">
            <div class="content-header">
                <h3 class="content-title">List of Mechanics</h3>
                <div class="content-tools">
                    <a href="add-mechanics.php" class="btn btn-flat btn-primary"><span class="fas fa-plus"></span> Create New</a>
                </div>
            </div>

            <div class="entries-count">
                <label for="statusFilter">Filter by Status:</label>
                <select id="statusFilter">
                    <option value="all" <?php echo ($statusFilter == 'all') ? 'selected' : ''; ?>>All</option>
                    <option value="active" <?php echo ($statusFilter == 'active') ? 'selected' : ''; ?>>Active Only</option>
                    <option value="inactive" <?php echo ($statusFilter == 'inactive') ? 'selected' : ''; ?>>Inactive (Deleted)</option>
                </select>
            </div>

            <div class="mechanics-table-container">
                <table class="mechanics-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Name</th>
                            <th>Contact Number</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Date Created</th>
                            <th class="actions-column">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $rowCount = 0;
                        while ($mechanic = $mechanicsStmt->fetch(PDO::FETCH_ASSOC)) {
                            $rowCount++;
                            $statusClass = ($mechanic['status'] == 1) ? 'active' : 'inactive';
                        ?>
                        <tr>
                            <td><?php echo $rowCount; ?></td>
                            <td><?php echo htmlspecialchars($mechanic['mechanic']); ?></td>
                            <td><?php echo htmlspecialchars($mechanic['contact']); ?></td>
                            <td><?php echo htmlspecialchars($mechanic['email']); ?></td>
                            <td>
                                <button class="status-btn <?php echo $statusClass; ?>" onclick="toggleStatus(<?php echo $mechanic['id']; ?>, <?php echo $mechanic['status']; ?>)">
                                    <?php echo $mechanic['status'] == 1 ? 'Active' : 'Inactive'; ?>
                                </button>
                            </td>
                            <td><?php echo htmlspecialchars($mechanic['date_created']); ?></td>
                            <td class="actions-column">
                                <button class="update-btn" onclick="window.location.href='update-mechanics.php?id=<?php echo $mechanic['id']; ?>'">Update</button>
                                <button class="delete-btn" onclick="confirmDelete(<?php echo $mechanic['id']; ?>)">Delete</button>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelector(".bx-menu").addEventListener("click", function() {
                document.querySelector(".sidebar").classList.toggle("close");
            });

            document.getElementById("logoutBtn").addEventListener("click", function(e) {
                e.preventDefault();
                if (confirm("Are you sure you want to logout?")) {
                    window.location.href = "logout.php";
                }
            });

            document.getElementById("statusFilter").addEventListener("change", function() {
                window.location.href = "mechanics.php?status=" + this.value;
            });
        });

        function toggleStatus(mechanicId, currentStatus) {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "mechanics.php", true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200 && xhr.responseText.trim() === "success") {
                    location.reload();
                }
            };
            xhr.send("mechanicId=" + mechanicId + "&status=" + currentStatus + "&action=toggleStatus");
        }

        function confirmDelete(mechanicId) {
            if (confirm("Are you sure you want to delete this mechanic? This will set their status to Inactive.")) {
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "mechanics.php", true);
                xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4 && xhr.status == 200 && xhr.responseText.trim() === "success") {
                        location.reload();
                    }
                };
                xhr.send("mechanicId=" + mechanicId + "&action=delete");
            }
        }
    </script>
</body>
</html>