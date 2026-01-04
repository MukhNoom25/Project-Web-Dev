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

// Check if a progress filter is applied
$progressFilter = isset($_GET['status']) ? $_GET['status'] : 'all';

if ($progressFilter == 'all') {
    $serviceRequestQuery = "SELECT sr.*, c.category, sl.service, ml.mechanic, u.username
                            FROM service_request sr
                            LEFT JOIN categories c ON sr.train_type_id = c.id
                            LEFT JOIN service_list sl ON sr.service_id = sl.id
                            LEFT JOIN mechanics_list ml ON sr.assigned_to_id = ml.id
                            LEFT JOIN users u ON sr.user_id = u.id";
} elseif ($progressFilter == 'complete') {
    $serviceRequestQuery = "SELECT sr.*, c.category, sl.service, ml.mechanic, u.username
                            FROM service_request sr
                            LEFT JOIN categories c ON sr.train_type_id = c.id
                            LEFT JOIN service_list sl ON sr.service_id = sl.id
                            LEFT JOIN mechanics_list ml ON sr.assigned_to_id = ml.id
                            LEFT JOIN users u ON sr.user_id = u.id
                            WHERE sr.status = 1";
} elseif ($progressFilter == 'pending') {
    $serviceRequestQuery = "SELECT sr.*, c.category, sl.service, ml.mechanic, u.username
                            FROM service_request sr
                            LEFT JOIN categories c ON sr.train_type_id = c.id
                            LEFT JOIN service_list sl ON sr.service_id = sl.id
                            LEFT JOIN mechanics_list ml ON sr.assigned_to_id = ml.id
                            LEFT JOIN users u ON sr.user_id = u.id
                            WHERE sr.status = 0";
} else {
    $serviceRequestQuery = "SELECT sr.*, c.category, sl.service, ml.mechanic, u.username
                            FROM service_request sr
                            LEFT JOIN categories c ON sr.train_type_id = c.id
                            LEFT JOIN service_list sl ON sr.service_id = sl.id
                            LEFT JOIN mechanics_list ml ON sr.assigned_to_id = ml.id
                            LEFT JOIN users u ON sr.user_id = u.id";
}

$serviceRequestStmt = $pdo->prepare($serviceRequestQuery);

if ($progressFilter != 'all' && $progressFilter != 'complete' && $progressFilter != 'pending') {
    $progressFilter = intval($progressFilter);
    $serviceRequestStmt->bindParam(':status', $progressFilter, PDO::PARAM_INT);
}

$serviceRequestStmt->execute();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'toggleProgress' && isset($_POST['serviceRequestId']) && isset($_POST['status'])) {
            $serviceRequestId = $_POST['serviceRequestId'];
            $newStatus = $_POST['status'] == 1 ? 0 : 1;

            // Update the status in the database
            $updateStatusQuery = "UPDATE service_request SET status = :status WHERE id = :id";
            $updateStatusStmt = $pdo->prepare($updateStatusQuery);
            $updateStatusStmt->bindParam(':status', $newStatus);
            $updateStatusStmt->bindParam(':id', $serviceRequestId);

            // Execute the update statement
            $updateStatusStmt->execute();
        } elseif ($_POST['action'] == 'delete' && isset($_POST['serviceRequestId'])) {
            $serviceRequestId = $_POST['serviceRequestId'];

            // Delete data from the database
            $deleteQuery = "DELETE FROM service_request WHERE request_id = :id";
            $deleteStmt = $pdo->prepare($deleteQuery);
            $deleteStmt->bindParam(':id', $serviceRequestId);

            // Execute the delete statement
            $deleteStmt->execute();
        }
    }
}

$currentPage = basename($_SERVER['PHP_SELF']);
?>


<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
		<title>Service Request Management</title>
		<link rel="icon" type="image/x-icon" href="images/icon.png"/>
		<link href="css/admin/admin.css" rel="stylesheet"/>
        <link href="css/admin/service-request.css" rel="stylesheet"/>
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
			  <li>
				<a href="service.php">
					<i class='bx bxs-spreadsheet'></i>
				  	<span class="link_name">Service</span>
				</a>
				<ul class="sub-menu blank">
					<li><a class="link_name" href="service.php">Service</a></li>
				</ul>
			  </li>
			  <li class="<?php echo ($currentPage == 'service-request.php') ? 'active' : ''; ?>">
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
                <div class="job" style="text-align: center; text-transform:capitalize;"><?php echo htmlspecialchars($userDetails['role']); ?></div>
			  </div>
			  <i class='bx bx-log-out'></i>
			</div>
		  </li>
		</ul>
		  </div>
		  <section class="home-section" style="height: 200vh;">
			<div class="home-content">
			  <i class='bx bx-menu'></i>
			  <span class="text">Train Cleaning Service Maintenance System</span>
			</div>

			<div class="content">
				<div class="content-header">
					<h3 class="content-title">List of Service Request</h3>
					<div class="content-tools">
						<a id = "createNewServiceRequestBtn" href="add_service_request.php" class="btn btn-flat btn-primary"><span class="fas fa-plus"></span>	Create New</a>
					</div>
				</div>

				<div class="entries-count">
                    <label for="progressFilter">Filter by Progress:</label>
                    <select id="progressFilter" onchange="applyProgressFilter()">
                        <option value="all" <?php echo ($progressFilter == 'all') ? 'selected' : ''; ?>>All</option>
                        <option value="complete" <?php echo ($progressFilter == 'complete') ? 'selected' : ''; ?>>Complete</option>
                        <option value="pending" <?php echo ($progressFilter == 'pending') ? 'selected' : ''; ?>>Pending</option>
                    </select>
                </div>

				<div class="services-request-table-container">
                    <table class="services-request-table">
                        <thead>
                        <tr>
                            <th>No</th>
                            <th>Train Type</th>
                            <th>Train Brand</th>
                            <th>Train Registration Number</th>
                            <th>Train Model</th>
                            <th>Gate Number</th>
                            <th>Service</th>
                            <th>Assigned To</th>
                            <th>Progress</th>
                            <th>Created By</th>
                            <th>Created At</th>
                            <th class="actions-column">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        // Counter for numbering rows
                        $rowCount = 0;

                        while ($serviceRequest = $serviceRequestStmt->fetch(PDO::FETCH_ASSOC)) {
                            $rowCount++;

                            // Set a variable for the status class
                            $statusClass = ($serviceRequest['status'] == 1) ? 'complete' : 'pending';

                            // Use the $statusClass variable to apply different styles or content
                            ?>
                            <tr>
                                <td><?php echo $rowCount; ?></td>
                                <td><?php echo htmlspecialchars($serviceRequest['category']); ?></td>
                                <td><?php echo htmlspecialchars($serviceRequest['train_brand']); ?></td>
                                <td><?php echo htmlspecialchars($serviceRequest['train_registration_number']); ?></td>
                                <td><?php echo htmlspecialchars($serviceRequest['train_model']); ?></td>
                                <td><?php echo htmlspecialchars($serviceRequest['gate_number']); ?></td>
                                <td><?php echo htmlspecialchars($serviceRequest['service']); ?></td>
                                <td><?php echo htmlspecialchars($serviceRequest['mechanic']); ?></td>
                                <td>
                                <button class="status-btn <?php echo $statusClass; ?>"
                                        data-service-request-id="<?php echo $serviceRequest['request_id']; ?>"
                                        onclick="toggleProgress(<?php echo $serviceRequest['request_id']; ?>, <?php echo $serviceRequest['status']; ?>)">
                                    <?php echo $serviceRequest['status'] == 1 ? 'Complete' : 'Pending'; ?>
                                </button>
                                </td>
                                
                                <td><?php echo htmlspecialchars($serviceRequest['username']); ?></td>
                                <td><?php echo htmlspecialchars($serviceRequest['created_at']); ?></td>
                                <td class="actions-column">
                                    <button class="update-btn"
                                        data-service-request-id="<?php echo $serviceRequest['request_id']; ?>">Update
                                    </button>
                                    <button class="delete-btn" 
                                        data-service-request-id="<?php echo $serviceRequest['request_id']; ?>" onclick="confirmDelete(<?php echo $serviceRequest['request_id']; ?>)">Delete
                                    </button>
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

            // Add a click event listener to each "update-btn" element for service requests
            updateServiceButtons.forEach(function (button) {
                button.addEventListener("click", function (event) {
                    // Prevent the default behavior of the button
                    event.preventDefault();

                    // Get the service request ID associated with the clicked button
                    var serviceRequestId = button.getAttribute("data-service-request-id");
                

                    // Redirect to the update_service_request.php page with the service request ID as a query parameter
                    window.location.href = "update_service_request.php?id=" + serviceRequestId;
                });
            });

            // Get the create new service request button
            var createNewServiceRequestBtn = document.getElementById("createNewServiceRequestBtn");

            // Add a click event listener to the create new service request button
            createNewServiceRequestBtn.addEventListener("click", function (event) {
                // Prevent the default behavior of the link
                event.preventDefault();

                // Redirect to the add-service-request.php page
                window.location.href = "add_service_request.php";
            });

            // Get the progress filter element
			var progressFilter = document.getElementById("progressFilter");

            // Set the selected value of the progress filter based on the URL parameter
            var urlParams = new URLSearchParams(window.location.search);
            var statusParam = urlParams.get('status');
            if (statusParam !== null) {
                progressFilter.value = statusParam;
            }

            // Add an event listener to the progress filter
            progressFilter.addEventListener("change", function() {
                // Get the selected status
                var selectedStatus = progressFilter.value;

                // Redirect to the same page with the selected status as a query parameter
                window.location.href = "service-request.php?status=" + selectedStatus;
            });	
        });

        // Function to toggle the service status
		function toggleProgress(id) {
            if (id) {
                console.log("Progress toggled for ID: " + id);
                // Implement the toggle logic here
            } else {
                console.error("ID not provided");
            }
        }

        // // Use AJAX to update the service status in the database
        // var xhr = new XMLHttpRequest();
        // xhr.open("POST", "service-request.php", true);
        // xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

        // // Send the service Request ID, current status, and action as data
        // xhr.send("serviceRequestId=" + serviceRequestId + "&status=" + currentStatus + "&action=toggleProgress");

        // // Refresh the table after successful service status update
        // xhr.onreadystatechange = function() {
        //     if (xhr.readyState == 4 && xhr.status == 200) {
        //         // Reload the page after successful service status update
        //         location.reload();
        //     }
        // };

        // Function to confirm and delete a service
		function confirmDelete(serviceRequestId) {
			// Show a confirmation dialog
			var confirmation = confirm("Are you sure you want to delete this service?");

			// If the user clicks "OK" (true), proceed with delete
			if (confirmation) {
				// Use AJAX to delete data from the database
				var xhr = new XMLHttpRequest();
				xhr.open("POST", "service-request.php", true);
				xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

				// Send the service ID and action as data
				xhr.send("serviceRequestId=" + serviceRequestId + "&action=delete");

				// Refresh the table after successful service deletion
				xhr.onreadystatechange = function () {
					if (xhr.readyState == 4 && xhr.status == 200) {
						// Reload the page after successful service deletion
						location.reload();
					}
				};
			}
		}

        // Function to apply progress filter
		function applyProgressFilter() {
			var selectedStatus = document.getElementById("progressFilter").value;
			// Redirect to the same page with the selected status as a query parameter
			window.location.href = "service-request.php?status=" + selectedStatus;
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