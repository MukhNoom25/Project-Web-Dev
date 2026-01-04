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

// Check if a status filter is applied
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';

if ($statusFilter == 'all') {
    $serviceQuery = "SELECT * FROM service_list";
} elseif ($statusFilter == 'active') {
    $serviceQuery = "SELECT * FROM service_list WHERE status = 1";
} elseif ($statusFilter == 'inactive') {
    $serviceQuery = "SELECT * FROM service_list WHERE status = 0";
} else {
	$serviceQuery = "SELECT * FROM service_list WHERE status = 1 OR status = 0";
}

$serviceStmt = $pdo->prepare($serviceQuery);

if ($statusFilter != 'all' && $statusFilter != 'active' && $statusFilter != 'inactive') {
    $statusFilter = intval($statusFilter);
    $serviceStmt->bindParam(':status', $statusFilter, PDO::PARAM_INT);
}

$serviceStmt->execute();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'toggleStatus' && isset($_POST['serviceId']) && isset($_POST['status'])) {
            $serviceId = $_POST['serviceId'];
            $newStatus = $_POST['status'] == 1 ? 0 : 1;

            // Update the status in the database
            $updateStatusQuery = "UPDATE service_list SET status = :status WHERE id = :id";
            $updateStatusStmt = $pdo->prepare($updateStatusQuery);
            $updateStatusStmt->bindParam(':status', $newStatus);
            $updateStatusStmt->bindParam(':id', $serviceId);

            // Execute the update statement
            $updateStatusStmt->execute();
        }
    }

    // Check if the service ID is provided and the action is "delete"
	if (isset($_POST['serviceId']) && isset($_POST['action']) && $_POST['action'] == 'delete') {
		$serviceId = $_POST['serviceId'];

		// Delete data from the database
		$deleteQuery = "DELETE FROM service_list WHERE id = :id";
		$deleteStmt = $pdo->prepare($deleteQuery);
		$deleteStmt->bindParam(':id', $serviceId);

		// Execute the delete statement
		$deleteStmt->execute();
	}
}

$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
		<title>Service Management</title>
		<link rel="icon" type="image/x-icon" href="images/icon.png"/>
		<link href="css/admin/admin.css" rel="stylesheet"/>
		<link href="css/admin/services.css" rel="stylesheet"/>
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-...." crossorigin="anonymous" />
		<link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
	</head>
	<body>
		<div class="sidebar close">
			<div class="logo-details">
			  <a href="dashboard.php">
				<img src="images/icon.png" alt="TSMS" style="padding-left: 20px; padding-right: 40px; width: 2.5rem;height: 2.5rem;max-height: unset">
			  </a>
			  <span class="logo_name"><a href="dashboard.php">TSMS</a></span>
			</div>
			<ul class="nav-links">
			  <li>
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
			  <li class="<?php echo ($currentPage == 'service.php') ? 'active' : ''; ?>">
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
			</div>
		  </li>
		</ul>
		  </div>
		  <section class="home-section" style="height: 200vh;">
			<div class="home-content">
			  <i class='bx bx-menu'></i>
			  <span class="text">Train Service Management System</span>
			</div>

			<div class="content">
				<div class="content-header">
					<h3 class="content-title">List of Service</h3>
					<div class="content-tools">
						<a href="add_service.php" class="btn btn-flat btn-primary"><span class="fas fa-plus"></span>	Create New</a>
					</div>
				</div>

				<div class="entries-count">
				<label for="statusFilter">Filter by Status:</label>
				<select id="statusFilter" onchange="applyStatusFilter()">
					<option value="all" <?php echo ($statusFilter == 'all') ? 'selected' : ''; ?>>All</option>
					<option value="active" <?php echo ($statusFilter == 'active') ? 'selected' : ''; ?>>Active</option>
					<option value="inactive" <?php echo ($statusFilter == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
				</select>
				</div>

				<div class="services-table-container">
                <table class="services-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Service Name</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Date Created</th>
                            <th class="actions-column">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        // Counter for numbering rows
                        $rowCount = 0;

                        while ($service = $serviceStmt->fetch(PDO::FETCH_ASSOC)) {
                            $rowCount++;

                            // Set a variable for the status class
                            $statusClass = ($service['status'] == 1) ? 'active' : 'inactive';

                            // Use the $statusClass variable to apply different styles or content
                            ?>
                            <tr>
                                <td><?php echo $rowCount; ?></td>
                                <td><?php echo htmlspecialchars($service['service']); ?></td>
                                <td><?php echo htmlspecialchars($service['description']); ?></td>
                                <td>
                                    <button class="status-btn <?php echo $statusClass; ?>" data-service-id="<?php echo $service['id']; ?>" 
											onclick="toggleStatus(<?php echo $service['id']; ?>, <?php echo $service['status']; ?>)">
                                        <?php echo $service['status'] == 1 ? 'Active' : 'Inactive'; ?>
                                    </button>
                                </td>
                                <td><?php echo htmlspecialchars($service['date_created']); ?></td>
                                <td class="actions-column">
                                    <button class="update-btn" data-service-id="<?php echo $service['id']; ?>">Update</button>
                                    <button class="delete-btn" data-service-id="<?php echo $service['id']; ?>" onclick="confirmDelete(<?php echo $service['id']; ?>)">Delete</button>
                                </td>
                            </tr>
                            <?php
                        }
                    ?>
                    </tbody>
                </table>
            </div>
			</div>
		  </section>

		  
		  <script defer>
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

			// Get all elements with class "update-btn"
			var updateServiceButtons = document.querySelectorAll(".update-btn");

			// Add a click event listener to each "update-btn" element for service
			updateServiceButtons.forEach(function(button) {
				button.addEventListener("click", function(event) {
					// Prevent the default behavior of the button
					event.preventDefault();

					// Get the service ID associated with the clicked button
					var serviceId = button.getAttribute("data-service-id");

					// Redirect to the update_service.php page with the service ID as a query parameter
					window.location.href = "update_service.php?id=" + serviceId;
				});
			});

			// Get the create new service button
			var createNewServiceBtn = document.getElementById("createNewServiceBtn");

			// Add a click event listener to the create new service button
			createNewServiceBtn.addEventListener("click", function(event) {
				// Prevent the default behavior of the link
				event.preventDefault();

				// Redirect to the add-service.php page
				window.location.href = "add_service.php";
			});

			// Get the status filter element
			var statusFilter = document.getElementById("statusFilter");

			// Set the selected value of the status filter based on the URL parameter
			var urlParams = new URLSearchParams(window.location.search);
			var statusParam = urlParams.get('status');
			if (statusParam !== null) {
				statusFilter.value = statusParam;
			}

			// Add an event listener to the status filter
			statusFilter.addEventListener("change", function() {
				// Get the selected status
				var selectedStatus = statusFilter.value;

				// Redirect to the same page with the selected status as a query parameter
				window.location.href = "service.php?status=" + selectedStatus;
			});
		});

		// Function to toggle the service status
		function toggleStatus(serviceId, currentStatus) {
			// Use AJAX to update the service status in the database
			var xhr = new XMLHttpRequest();
			xhr.open("POST", "service.php", true);
			xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

			// Send the service ID, current status, and action as data
			xhr.send("serviceId=" + serviceId + "&status=" + currentStatus + "&action=toggleStatus");

			// Refresh the table after successful service status update
			xhr.onreadystatechange = function() {
				if (xhr.readyState == 4 && xhr.status == 200) {
					// Reload the page after successful service status update
					location.reload();
				}
			};
		}

		// Function to confirm and delete a service
		function confirmDelete(serviceId) {
			// Show a confirmation dialog
			var confirmation = confirm("Are you sure you want to delete this service?");

			// If the user clicks "OK" (true), proceed with delete
			if (confirmation) {
				// Use AJAX to delete data from the database
				var xhr = new XMLHttpRequest();
				xhr.open("POST", "service.php", true);
				xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

				// Send the service ID and action as data
				xhr.send("serviceId=" + serviceId + "&action=delete");

				// Refresh the table after successful service deletion
				xhr.onreadystatechange = function () {
					if (xhr.readyState == 4 && xhr.status == 200) {
						// Reload the page after successful service deletion
						location.reload();
					}
				};
			}
		}

		// Function to apply status filter
		function applyStatusFilter() {
			var selectedStatus = document.getElementById("statusFilter").value;
			// Redirect to the same page with the selected status as a query parameter
			window.location.href = "service.php?status=" + selectedStatus;
		}

        // Get all elements with class "arrow"
		var arrow = document.querySelectorAll(".arrow");
		for (var i = 0; i < arrow.length; i++) {
			arrow[i].addEventListener("click", function(e) {
				let arrowParent = e.target.parentElement.parentElement;
				arrowParent.classList.toggle("showMenu");
			});
		}

		// Get the sidebar elements
		var sidebar = document.querySelector(".sidebar");
		var sidebarBtn = document.querySelector(".bx-menu");

		// Add a click event listener to the sidebar button
		sidebarBtn.addEventListener("click", function() {
			sidebar.classList.toggle("close");
		});

		</script>
	</body>
</html>