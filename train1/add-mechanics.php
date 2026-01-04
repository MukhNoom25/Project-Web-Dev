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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["saveMechanic"])) {
    // Retrieve data from the form
    $name = $_POST["name"];
    $contact = $_POST["contact"];
    $email = $_POST["email"];
    $status = $_POST["status"];

	// ✅ Basic Input Validation (with alert and reopen modal)
    if (!preg_match('/^[a-zA-Z0-9\s\-]{1,50}$/', $name)) {
    
        // Display success message
        echo '<script>alert("Invalid mechanic name. Only letters, numbers, spaces, and hyphens are allowed.");</script>';

        // Redirect to service.php after 500 milliseconds
        echo '<script>window.location.href = "add-mechanics.php";</script>';
        exit();
    }

    // Insert data into the database
    $insertQuery = "INSERT INTO mechanics_list (mechanic, contact, email, status) VALUES (:name, :contact, :email, :status)";
    $stmt = $pdo->prepare($insertQuery);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':contact', $contact);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':status', $status);

    if ($stmt->execute()) {
        // Set a success message in a session variable
        $_SESSION['success_message'] = 'Mechanic added successfully!';
		
        // Redirect back to mechanics.php
        header("Location: mechanics.php");
        exit();
    } else {
        // Handle the case where insertion failed
        echo "Error: Unable to add mechanic.";
    }
}

$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
		<title>Add New Mechanic</title>
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
					<h3 class="content-title">Add New Mechanic</h3>
				</div>

                <div class="add-mechanic-form">
                    <form action="" method="post">
                        <div class="form-group">
                            <label for="name">Name:</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="contact">Contact Number:</label>
                            <input type="digit" id="contact" name="contact" pattern="\d{10,11}" title="Please enter 10 or 11 digits" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="status">Status:</label>
                            <select id="status" name="status" required>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div class="form-buttons">
                            <button type="submit" class="btn btn-flat btn-primary" name="saveMechanic">Save</button>
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

			// Display success message if the URL has a success parameter
				var urlParams = new URLSearchParams(window.location.search);
				if (urlParams.has('success')) {
					alert('Mechanic added successfully!');
					// Redirect to mechanics.php after the user clicks "OK"
					window.location.href = 'mechanics.php';
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
		  </script>
	</body>
</html>