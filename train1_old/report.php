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
    // Redirect to the login page (or handle accordingly)
    header('Location: login.php');
    exit();
}

// Fetch service request data with date filter
$serviceRequestQuery = "SELECT sr.*, c.category AS train_type, sl.service, ml.mechanic, u.username
                       FROM service_request sr
                       LEFT JOIN categories c ON sr.train_type_id = c.id
                       LEFT JOIN service_list sl ON sr.service_id = sl.id
                       LEFT JOIN mechanics_list ml ON sr.assigned_to_id = ml.id
                       LEFT JOIN users u ON sr.user_id = u.id";

// Check if the filter form is submitted
if (isset($_GET['filter'])) {
    // Get the start and end dates from the form submission
    $startDate = isset($_GET['startDate']) ? $_GET['startDate'] : '';
    $endDate = isset($_GET['endDate']) ? $_GET['endDate'] : '';

    // Add a WHERE clause to the query for date filtering if dates are provided
    if (!empty($startDate) && !empty($endDate)) {
        $serviceRequestQuery .= " WHERE sr.created_at BETWEEN :startDate AND :endDate";
    }
}

$serviceRequestStmt = $pdo->prepare($serviceRequestQuery);

// Bind parameters if they exist
if (!empty($startDate) && !empty($endDate)) {
    $serviceRequestStmt->bindParam(':startDate', $startDate, PDO::PARAM_STR);
    $serviceRequestStmt->bindParam(':endDate', $endDate, PDO::PARAM_STR);
}

if (!$serviceRequestStmt->execute()) {
    die('Error fetching service request data: ' . print_r($serviceRequestStmt->errorInfo(), true));
}

// Get the start and end dates from the request
$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : '';
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : '';

$serviceRequestStmt->bindParam(':startDate', $startDate, PDO::PARAM_STR);
$serviceRequestStmt->bindParam(':endDate', $endDate, PDO::PARAM_STR);

$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
		<title>Report Generation</title>
		<link rel="icon" type="image/x-icon" href="images/favicon.ico"/>
		<link href="css/admin/admin.css" rel="stylesheet"/>
    <link href="css/admin/report.css" rel="stylesheet"/>
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
			  <li>
				<a href="service-request.php">
					<i class='bx bx-edit'></i>
				  	<span class="link_name">Service Request</span>
				</a>
				<ul class="sub-menu blank">
					<li><a class="link_name" href="service-request.php">Service Request</a></li>
				</ul>
			  </li>
			  <li  class="<?php echo ($currentPage == 'report.php') ? 'active' : ''; ?>">
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
		  <section class="home-section">
			<div class="home-content">
			  <i class='bx bx-menu'></i>
			  <span class="text">Train Service Management System</span>
			</div>

			<div class="content">
				<div class="content-header">
					<h3 class="content-title">Report Generation</h3>
				</div>
          <div class="date-filter-row">
              <form method="get" action="">
                  <label for="startDate">Start Date:</label>
                  <input type="date" id="startDate" name="startDate" value="<?php echo htmlspecialchars($startDate); ?>">

                  <label for="endDate">End Date:</label>
                  <input type="date" id="endDate" name="endDate" value="<?php echo htmlspecialchars($endDate); ?>">

                  <button type="submit" name="filter">Filter</button>
                  <button type="button" onclick="printReport()">Print</button>
              </form>
          </div>

          <div class="content-header" style="display: block;">
					  <h3 class="content-title" style="margin-left: 36%; margin-bottom: 0px; font-size: 30px;">Train Service Request Report</h3>
				  </div>

          <div class="services-request-table-container">
            <?php
              // Counter for numbering rows
              $rowCount = 0;

              if ($serviceRequestStmt->rowCount() > 0) {
            ?>
            <table class="services-request-table print-table">
                <thead>
                <tr>
                    <th>No</th>
                    <th>Train Type</th>
                    <th>Train Brand</th>
                    <th>Train Registration Number</th>
                    <th>Train Model</th>
                    <th>Service</th>
                    <th>Assigned To</th>
                    <th>Progress</th>
                    <th>Created By</th>
                    <th>Created At</th>
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
                        ?>
                        <tr>
                          <td><?php echo $rowCount; ?></td>
                          <td><?php echo htmlspecialchars($serviceRequest['train_type']); ?></td>
                          <td><?php echo htmlspecialchars($serviceRequest['train_brand']); ?></td>
                          <td><?php echo htmlspecialchars($serviceRequest['train_registration_number']); ?></td>
                          <td><?php echo htmlspecialchars($serviceRequest['train_model']); ?></td>
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
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
                </table>
                <?php
                } else {
                    echo '<p style="text-align: center; color: red;">No records found between the selected dates.</p>';
                }
                ?>
          </div>
			</div>
		  </section>
		  
		  <script>
            function filterAndPrint() {
                // Get the selected start and end dates
                var startDate = document.getElementById("startDate").value;
                var endDate = document.getElementById("endDate").value;

                // Make an asynchronous request to fetch the filtered data
                var xhr = new XMLHttpRequest();
                var url = "service-request.php?startDate=" + startDate + "&endDate=" + endDate;

                xhr.open("GET", url, true);
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        // Replace the table body with the updated content
                        document.querySelector('.services-request-table tbody').innerHTML = xhr.responseText;
                    }
                };

                xhr.send();

                // Prevent the form from being submitted
                return false;
            }

            // Function to handle the print button
            function printReport() {

                // Get the selected start and end dates
                var startDate = document.getElementById("startDate").value;
                var endDate = document.getElementById("endDate").value;

                // Create a new window for printing
                var printWindow = window.open('', '_blank');

                // Write the table HTML to the new window with print styles
                printWindow.document.write('<html><head><title>Print Report</title>');
                printWindow.document.write('<style>' +
                    '.print-table { width: 97%; border-collapse: collapse; margin: 20px; }' +
                    '.print-table th,.print-table td { border: 1px solid midnightblue; padding: 12px; text-align: center;}' +
                    '.print-table th { background-color: #cddfef; color: midnightblue; }' +
                    '.print-status-complete { background-color: green; color: white; }' +
                    '.print-status-pending { background-color: red; color: white; }' +
                    '.print-actions-column { width: 100px;}' +
                    '.print-status-btn { width: 70%; padding: 5px; border-radius: 5%; }' +
                    '.print-status-btn.complete { background-color: #0FFF50; color: midnightblue; border: 1px solid #0FFF50; }' +
                    '.print-status-btn.pending { background-color: red; color: white; border: 1px solid red;}' +
                    '</style>');
                printWindow.document.write('</head><body>');
                printWindow.document.write('<h2 style="text-align: center; font-size: 40px; margin-top: 20px;">Train Service Request Report</h2>');
                printWindow.document.write('<p style="text-align: center;">From ' + startDate + ' to ' + endDate + '</p>');
                printWindow.document.write('<table class="print-table">' + document.querySelector('.services-request-table').innerHTML + '</table>');
                printWindow.document.write('</body></html>');

                // Print the new window
                printWindow.print();
            }

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
			});
		</script>
	</body>
</html>