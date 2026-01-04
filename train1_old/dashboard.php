<?php

@include 'config.php';
include('timeout.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page
    header('Location: login.php');
    exit();
}

// Get the user ID from the session
$loggedInUserId = $_SESSION['user_id'];

$currentPage = basename($_SERVER['PHP_SELF']);

// Fetch user details from the database
$query = "SELECT * FROM users WHERE id = :userId";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':userId', $loggedInUserId, PDO::PARAM_INT);
$stmt->execute();
$userDetails = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if the user details are fetched successfully
if (!$userDetails) {
    // Redirect to the login page
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
		<title>Dashboard</title>
		<link rel="icon" type="image/x-icon" href="images/favicon.ico"/>
		<link href="css/admin/admin.css" rel="stylesheet"/>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-...." crossorigin="anonymous" />
		<link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
	</head>
	<body>
		<div class="sidebar close">
			<div class="logo-details">
			  <a href="dashboard.php">
				<img src="images/favicon.ico" alt="TSMS" style="padding-left: 20px; padding-right: 40px; width: 2.5rem;height: 2.5rem;max-height: unset">
			  </a>
			  <span class="logo_name"><a href="dashboard.php">TSMS</a></span>
			</div>
			<ul class="nav-links">
			  <li class="<?php echo ($currentPage == 'dashboard.php') ? 'active' : ''; ?>">
				<a href="dashboard.php">
					<i class='bx bxs-tachometer' ></i>
				  	<span class="link_name">Dashboard</span>
				</a>
				<ul class="sub-menu blank">
					<li><a class="link_name" href="dashboard.php">Dashboard</a></li>
				</ul>
			  </li>
			  <li>
				<a href="mechanics.php">
					<i class='bx bxs-group'></i>
				  	<span class="link_name">Mechanics</span>
				</a>
				<ul class="sub-menu blank">
					<li><a class="link_name" href="mechanics.php">Mechanics</a></li>
				</ul>
			  </li>
			  <li> 
				<a href="category.php">
					<i class='bx bxs-category' ></i>
				  	<span class="link_name">Train Categories</span>
				</a>
				<ul class="sub-menu blank">
					<li><a class="link_name" href="category.php">Train Categories</a></li>
				</ul>
			  </li>
			  <li>
				<a href="service.php">
					<i class='bx bxs-spreadsheet'></i>
				  	<span class="link_name">Service</span>
				</a>
				<ul class="sub-menu blank">
					<li><a class="link_name" href="service.php">Service</a></li>
				</ul>
			  </li>
			  <li>
				<a href="service-request.php">
					<i class='bx bx-edit'></i>
				  	<span class="link_name">Service Request</span>
				</a>
				<ul class="sub-menu blank">
					<li><a class="link_name" href="service-request.php">Service Request</a></li>
				</ul>
			  </li>
			  <li>
				<a href="report.php">
					<i class='bx bxs-report'></i>
				  	<span class="link_name">Report</span>
				</a>
				<ul class="sub-menu blank">
					<li><a class="link_name" href="report.php">Report</a></li>
				</ul>
			  </li>
			  <li>
				<a href="#" id="logoutBtn">
					<i class='bx bx-log-out'></i>
				  	<span class="link_name">Logout</span>
				</a>
				<ul class="sub-menu blank">
					<li><a class="link_name" href="logout.php">Logout</a></li>
				</ul>
			  </li>
			</ul>
			<li>
			<div class="profile-details">
			  <div class="profile-content">
				<img src="images/user-gear.png" alt="profileImg">
			  </div>
			  <div class="name-job">
                <div class="profile_name"><?php echo htmlspecialchars($userDetails['username']); ?></div>
                <div class="job" style="text-align: center; text-transform:capitalize;"><?php echo htmlspecialchars($userDetails['identity']); ?></div>
			  </div>
			  <i class=''></i>
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
					<h3 class="content-title">Dashboard</h3>
				</div>

                <div class="statistics-container">
                    <?php
                    // Fetch counts from the database
					// Fetch counts from the database
$categoriesCount = $pdo->query("SELECT COUNT(*) FROM categories WHERE status=1")->fetchColumn();
$servicesCount = $pdo->query("SELECT COUNT(*) FROM service_list WHERE status = 1")->fetchColumn();
$totalMechanics = $pdo->query("SELECT COUNT(*) FROM mechanics_list WHERE status = 1")->fetchColumn();
$totalServiceRequests = $pdo->query("SELECT COUNT(*) FROM service_request")->fetchColumn();
$pendingServiceRequests = $pdo->query("SELECT COUNT(*) FROM service_request WHERE status = 0 OR status IS NULL")->fetchColumn();
$completedServiceRequests = $pdo->query("SELECT COUNT(*) FROM service_request WHERE status = 1")->fetchColumn();
                    ?>

                    <div class="stat-box">
                        <h4>Total of Train Categories Available</h4>
                        <div class="count-wrapper">
                            <div class="circle"><?php echo $categoriesCount; ?></div>
                        </div>
                    </div>

                    <!-- Box for total services available -->
                    <div class="stat-box">
                        <h4>Total of Services Available                </h4>
                        <div class="count-wrapper">
                            <div class="circle"><?php echo $servicesCount; ?></div>
                        </div>
                    </div>

                    <!-- Box for total service requests -->
                    <div class="stat-box">
                        <h4>Total of Mechanics Available</h4>
                        <div class="count-wrapper">
                            <div class="circle"><?php echo $totalMechanics; ?></div>
                        </div>
                    </div>

                    <!-- Box for total service requests -->
                    <div class="stat-box">
                        <h4>Total of Service Requests Received</h4>
                        <div class="count-wrapper">
                            <div class="circle"><?php echo $totalServiceRequests; ?></div>
                        </div>
                    </div>
                </div>

                <div class="content-header">
					<h3 class="content-title">Status of Service Request Received</h3>
				</div>

                <div class="chart-box">
                    <canvas id="serviceRequestChart"></canvas>
                </div>
            </div>
		  </section>

		  
		  <script>
			document.addEventListener("DOMContentLoaded", function() {
			// Get the logout button
			var logoutBtn = document.getElementById("logoutBtn");

			// Add a click event listener to the logout button
			logoutBtn.addEventListener("click", function(event) {
				// Prevent the default behavior of the link
				event.preventDefault();

				// Show the confirmation dialog
				var confirmation = confirm("Are you sure you want to logout?");

				// If the user clicks "OK" (true), proceed with logout
				if (confirmation) {
					// Redirect to the logout page
					window.location.href = "logout.php";
				}
			});
			});

			let arrow = document.querySelectorAll(".arrow");
			for (var i = 0; i < arrow.length; i++) {
				arrow[i].addEventListener("click", (e)=>{
			let arrowParent = e.target.parentElement.parentElement;
			arrowParent.classList.toggle("showMenu");
				});
			}
			let sidebar = document.querySelector(".sidebar");
			let sidebarBtn = document.querySelector(".bx-menu");
			console.log(sidebarBtn);
			sidebarBtn.addEventListener("click", ()=>{
				sidebar.classList.toggle("close");
			});

            // Fetch counts for pending and completed service requests
            let pendingServiceRequestsCount = <?php echo $pendingServiceRequests; ?>;
            let completedServiceRequestsCount = <?php echo $completedServiceRequests; ?>;

            // Chart.js configuration
            let ctx = document.getElementById('serviceRequestChart').getContext('2d');
            let serviceRequestChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Pending Requests', 'Completed Requests'],
                    datasets: [{
                        label: 'Service Requests',
                        backgroundColor: ['rgba(128, 3, 30, 0.6)', 'rgba(75, 192, 192, 0.2)'],
                        borderColor: ['red', 'green'],
                        borderWidth: 1,
                        data: [pendingServiceRequestsCount, completedServiceRequestsCount],
                    }],
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
		  </script>
	</body>
</html>