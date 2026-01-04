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

// Check if the form is submitted for updating mechanic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["updateMechanic"])) {
    // Retrieve data from the form
    $mechanicId = $_POST["mechanicId"];
    $name = $_POST["name"];
    $contact = $_POST["contact"];
    $email = $_POST["email"];
    $status = $_POST["status"];

    // Update data in the database
    $updateQuery = "UPDATE mechanics_list SET mechanic = :name, contact = :contact, email = :email, status = :status WHERE id = :id";
    $updateStmt = $pdo->prepare($updateQuery);
    $updateStmt->bindParam(':id', $mechanicId);
    $updateStmt->bindParam(':name', $name);
    $updateStmt->bindParam(':contact', $contact);
    $updateStmt->bindParam(':email', $email);
    $updateStmt->bindParam(':status', $status);

	// ✅ Basic Input Validation (with alert and reopen modal)
    if (!preg_match('/^[a-zA-Z0-9\s\-]{1,50}$/', $name)) {
    
        // Display success message
        echo '<script>alert("Invalid name. Only letters, numbers, spaces, and hyphens are allowed.");</script>';

        // Redirect to service.php after 500 milliseconds
        echo '<script>window.location.href = "update-mechanics.php";</script>';
        exit();
    }


    if ($updateStmt->execute()) {
        // Display success message
        echo '<script>alert("Mechanic updated successfully!");</script>';

        // Redirect to mechanics.php after 500 milliseconds
        echo '<script>window.location.href = "mechanics.php";</script>';
        exit();
    } else {
        // Handle the case where update failed
        echo "Error: Unable to update mechanic.";
    }
}

// Check if the mechanic ID is set in the URL
if (isset($_GET['id'])) {
    // Retrieve the mechanic ID from the URL
    $mechanicId = $_GET['id'];

    // Fetch the existing mechanic data from the database
    $mechanicQuery = "SELECT * FROM mechanics_list WHERE id = :id";
    $mechanicStmt = $pdo->prepare($mechanicQuery);
    $mechanicStmt->bindParam(':id', $mechanicId);
    $mechanicStmt->execute();
    $mechanic = $mechanicStmt->fetch(PDO::FETCH_ASSOC);

    // Check if the mechanic exists
    if (!$mechanic) {
        // Redirect to the mechanics.php page if the mechanic is not found
        header("Location: mechanics.php");
        exit();
    }
} else {
    // Redirect to the mechanics.php page if the ID is not set
    header("Location: mechanics.php");
    exit();
}

$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
		<title>Update Mechanic</title>
		<link rel="icon" type="image/x-icon" href="images/icon.png"/>
		<link href="css/admin/admin.css" rel="stylesheet"/>
		<link href="css/admin/mechanic.css" rel="stylesheet"/>
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
			  <li  class="<?php echo ($currentPage == 'mechanics.php') ? 'active' : ''; ?>">
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
					<h3 class="content-title">Update Mechanic</h3>
				</div>

                <div class="update-mechanic-form">
                    <form action="" method="post">
                        <input type="hidden" name="mechanicId" value="<?php echo $mechanic['id']; ?>">

                        <div class="form-group">
                            <label for="name">Name:</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($mechanic['mechanic']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="contact">Contact Number:</label>
                            <input type="text" id="contact" name="contact" value="<?php echo htmlspecialchars($mechanic['contact']); ?>" pattern="\d{10,11}" title="Please enter 10 or 11 digits" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($mechanic['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="status">Status:</label>
                            <select id="status" name="status" required>
                                <option value="1" <?php echo ($mechanic['status'] == 1) ? 'selected' : ''; ?>>Active</option>
                                <option value="0" <?php echo ($mechanic['status'] == 0) ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="form-buttons">
                            <button type="submit" class="btn btn-flat btn-primary" name="updateMechanic">Update</button>
                            <a href="mechanics.php" class="btn btn-flat btn-secondary cancel-btn">Cancel</a>
                        </div>
                    </form>
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