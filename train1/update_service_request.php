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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["updateServiceRequest"])) {

    // Retrieve data from the form
    $serviceRequestId = $_POST["serviceRequestId"];
    $trainTypeId = $_POST["trainTypeId"];
    $trainBrand = $_POST["trainBrand"];
    $trainRegistrationNumber = $_POST["trainRegistrationNumber"];
    $trainModel = $_POST["trainModel"];
    $gateNumber = $_POST["gateNumber"];
    $serviceId = $_POST["serviceId"];
    $assignedToId = $_POST["assignedToId"];
    $status = $_POST["status"];

    // Update data in the database
    $updateQuery = "UPDATE service_request SET train_type_id = :trainTypeId, train_brand = :trainBrand, train_registration_number = :trainRegistrationNumber, train_model = :trainModel, gate_number = :gateNumber, service_id = :serviceId, assigned_to_id = :assignedToId, status = :status WHERE request_id = :serviceRequestId";
    $stmt = $pdo->prepare($updateQuery);
    $stmt->bindParam(':serviceRequestId', $serviceRequestId);
    $stmt->bindParam(':trainTypeId', $trainTypeId);
    $stmt->bindParam(':trainBrand', $trainBrand);
    $stmt->bindParam(':trainRegistrationNumber', $trainRegistrationNumber);
    $stmt->bindParam(':trainModel', $trainModel);
    $stmt->bindParam(':gateNumber', $gateNumber);
    $stmt->bindParam(':serviceId', $serviceId);
    $stmt->bindParam(':assignedToId', $assignedToId);
    $stmt->bindParam(':status', $status);

    // ✅ Basic Input Validation (with alert and reopen modal)
    if (!preg_match('/^[a-zA-Z0-9\s\-]{1,50}$/', $trainBrand)) {
    
        // Display success message
        echo '<script>alert("Invalid train brand. Only letters, numbers, spaces, and hyphens are allowed.");</script>';

        // Redirect to service.php after 500 milliseconds
        echo '<script>window.location.href = "add_service_request.php";</script>';
        exit();
    }
	if (!preg_match('/^[a-zA-Z0-9\s\-]{1,50}$/', $trainRegistrationNumber)) {
    
        // Display success message
        echo '<script>alert("Invalid train registration number. Only letters, numbers, spaces, and hyphens are allowed.");</script>';

        // Redirect to service.php after 500 milliseconds
        echo '<script>window.location.href = "add_service_request.php";</script>';
        exit();
    }
	if (!preg_match('/^[a-zA-Z0-9\s\-]{1,50}$/', $trainModel)) {
    
        // Display success message
        echo '<script>alert("Invalid train model. Only letters, numbers, spaces, and hyphens are allowed.");</script>';

        // Redirect to service.php after 500 milliseconds
        echo '<script>window.location.href = "add_service_request.php";</script>';
        exit();
    }
    if (!preg_match('/^[a-zA-Z0-9\s\-]{1,50}$/', $gateNumber)) {
    
        // Display success message
        echo '<script>alert("Invalid gate number. Only letters, numbers, spaces, and hyphens are allowed.");</script>';

        // Redirect to service.php after 500 milliseconds
        echo '<script>window.location.href = "add_service_request.php";</script>';
        exit();
    }

    if ($stmt->execute()) {
        // Set a success message in a session variable
        $_SESSION['success_message'] = 'Service request updated successfully!';

        // Redirect back to service-request.php with success parameter
        header("Location: service-request.php?success=true");
        exit();
    } else {
        // Handle the case where update failed
        echo "Error: Unable to update service request.";
    }
}

// Fetch active train types
$trainTypesQuery = "SELECT * FROM categories WHERE status = 1";
$trainTypesStmt = $pdo->prepare($trainTypesQuery);
$trainTypesStmt->execute();

// Fetch active services
$servicesQuery = "SELECT * FROM service_list WHERE status = 1";
$servicesStmt = $pdo->prepare($servicesQuery);
$servicesStmt->execute();

// Fetch active mechanics
$mechanicsQuery = "SELECT * FROM mechanics_list WHERE status = 1";
$mechanicsStmt = $pdo->prepare($mechanicsQuery);
$mechanicsStmt->execute();

// Check if an ID is provided in the URL parameters
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $serviceRequestId = $_GET['id'];

    // Fetch details of the service request from the database
    $serviceRequestQuery = "SELECT * FROM service_request WHERE request_id = :serviceRequestId";
    $serviceRequestStmt = $pdo->prepare($serviceRequestQuery);
    $serviceRequestStmt->bindParam(':serviceRequestId', $serviceRequestId);
    $serviceRequestStmt->execute();

    // Check if the service request exists
    if ($serviceRequest = $serviceRequestStmt->fetch(PDO::FETCH_ASSOC)) {
        $currentPage = basename($_SERVER['PHP_SELF']);
    } else {
        // Handle the case where the service request with the given ID doesn't exist
        echo "Error: Service request not found.";
        exit();
    }
} else {
    // Handle the case where no ID is provided in the URL parameters
    echo "Error: No service request ID provided.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
		<title>Update Service Request</title>
		<link rel="icon" type="image/x-icon" href="images/icon.png"/>
		<link href="css/admin/admin.css" rel="stylesheet"/>
        <link href="css/admin/service-request.css" rel="stylesheet"/>
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-...." crossorigin="anonymous" />
		<link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
	</head>
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
			  <li class="<?php echo ($currentPage == 'service_request.php') ? 'active' : ''; ?>">
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
		  
          <section class="home-section">
			<div class="home-content">
			  <i class='bx bx-menu'></i>
			  <span class="text">Train Service Management System</span>
			</div>
            
            <div class="content">
                <div class="content-header">
                    <h3 class="content-title">Update Service Request</h3>
                </div>

                <div class="add-services-request-form">
                    <form action="" method="post">
                        <div class="form-group">
                            <label for="trainTypeId">Train Type</label>
                            <select id="trainTypeId" name="trainTypeId" required>
                                <option value="" disabled selected>Please select train type</option>
                                <?php
                                while ($trainType = $trainTypesStmt->fetch(PDO::FETCH_ASSOC)) {
                                    $selected = ($trainType['id'] == $serviceRequest['train_type_id']) ? 'selected' : '';
                                    echo '<option value="' . $trainType['id'] . '" ' . $selected . '>' . htmlspecialchars($trainType['category']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="trainBrand">Train Brand</label>
                            <input type="text" id="trainBrand" name="trainBrand" value="<?php echo htmlspecialchars($serviceRequest['train_brand']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="registrationNumber">Registration Number</label>
                            <input type="text" id="registrationNumber" name="trainRegistrationNumber" value="<?php echo htmlspecialchars($serviceRequest['train_registration_number']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="trainModel">Train Model</label>
                            <input type="text" id="trainModel" name="trainModel" value="<?php echo htmlspecialchars($serviceRequest['train_model']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="gateNumber">Gate Number</label>
                            <input type="text" id="gateNumber" name="gateNumber" value="<?php echo htmlspecialchars($serviceRequest['gate_number']); ?>" placeholder="e.g., Gate A1, G-5" required>
                        </div>
                        <div class="form-group">
                            <label for="serviceId">Service</label>
                            <select id="serviceId" name="serviceId" required>
                                <option value="" disabled selected>Please select a service</option>
                                <?php
                                while ($service = $servicesStmt->fetch(PDO::FETCH_ASSOC)) {
                                    $selected = ($service['id'] == $serviceRequest['service_id']) ? 'selected' : '';
                                    echo '<option value="' . $service['id'] . '" ' . $selected . '>' . htmlspecialchars($service['service']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="assignedToId">Assigned To (Mechanic)</label>
                            <select id="assignedToId" name="assignedToId" required>
                                <option value="" disabled selected>Please select a mechanic</option>
                                <?php
                                while ($mechanic = $mechanicsStmt->fetch(PDO::FETCH_ASSOC)) {
                                    $selected = ($mechanic['id'] == $serviceRequest['assigned_to_id']) ? 'selected' : '';
                                    echo '<option value="' . $mechanic['id'] . '" ' . $selected . '>' . htmlspecialchars($mechanic['mechanic']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="status">Status:</label>
                            <select id="status" name="status" required>
                                <option value="1" <?php echo ($serviceRequest['status'] == 'complete') ? 'selected' : ''; ?>>Complete</option>
                                <option value="2" <?php echo ($serviceRequest['status'] == 'incomplete') ? 'selected' : ''; ?>>Incomplete</option>
                            </select>
                        </div>
                        <input type="hidden" name="serviceRequestId" value="<?php echo $serviceRequestId; ?>">
                        <div class="form-buttons">
                            <button type="submit" class="btn btn-flat btn-primary" name="updateServiceRequest">Update</button>
                            <a href="service-request.php" class="btn btn-flat btn-secondary cancel-btn">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
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
                });
                </script>
	</body>
</html>