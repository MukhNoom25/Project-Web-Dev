<?php
include 'config.php'; 
include('timeout.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get the user ID from the session
$loggedInUserId = $_SESSION['user_id'];

// ✅ CSRF Token Generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Function to fetch data from the database
function getCategories($pdo) {
    $query = "SELECT * FROM categories WHERE status = 1";
    $stmt = $pdo->query($query);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
// Fetch available gates for the dropdown
$gatesQuery = "SELECT gate_name FROM gates WHERE status = 1 ORDER BY gate_name";
$gatesStmt = $pdo->prepare($gatesQuery);
$gatesStmt->execute();
$gates = $gatesStmt->fetchAll(PDO::FETCH_ASSOC);

// Function to fetch services from the database
function getServices($pdo, $search = null) {
    $query = "SELECT * FROM service_list WHERE status = 1";
    
    if ($search) {
        $query .= " AND service LIKE :search";
    }

    $stmt = $pdo->prepare($query);

    if ($search) {
        $stmt->bindParam(':search', $search, PDO::PARAM_STR);
    }

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch categories
$categories = getCategories($pdo);

// Fetch services without search
$services = getServices($pdo);

// Fetch all services
$allServices = getServices($pdo); 

// Fetch services with search 
if (isset($_GET['search'])) {
    $search = '%' . $_GET['search'] . '%';
    $searchResult = getServices($pdo, $search);
    $services = $searchResult;
} 

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Retrieve form data
    $trainTypeId = $_POST['trainType'];
    $trainBrand = $_POST['trainBrand'];
    $registrationNumber = $_POST['registrationNumber'];
    $trainModel = $_POST['trainModel'];
    $gateNumber = $_POST['gateNumber'];
    $serviceId = $_POST['serviceType'];
    $status = 0;

    // ✅ Basic Input Validation (with alert and reopen modal)
    if (!preg_match('/^[a-zA-Z0-9\s\-]{1,50}$/', $trainBrand)) {
    
        // Display success message
        echo '<script>alert("Invalid train brand. Only letters, numbers, spaces, and hyphens are allowed.");</script>';

        // Redirect to service.php after 500 milliseconds
        echo '<script>window.location.href = "staff-homepage.php";</script>';
        exit();
    }

    if (!preg_match('/^[a-zA-Z0-9\s\-]{1,50}$/', $registrationNumber)) {

    // Display success message
        echo '<script>alert("Invalid train registration. Only letters, numbers, spaces, and hyphens are allowed.");</script>';

        // Redirect to service.php after 500 milliseconds
        echo '<script>window.location.href = "staff-homepage.php";</script>';
        exit();
    }


    if (!preg_match('/^[a-zA-Z0-9\s\-]{1,50}$/', $trainModel)) {
    // Display success message
        echo '<script>alert("Invalid train model. Only letters, numbers, spaces, and hyphens are allowed.");</script>';

        // Redirect to service.php after 500 milliseconds
        echo '<script>window.location.href = "staff-homepage.php";</script>';
        exit();
    }
    if (!preg_match('/^[a-zA-Z0-9\s\-]{1,50}$/', $gateNumber)) {
    // Display success message
        echo '<script>alert("Invalid gate number. Only letters, numbers, spaces, and hyphens are allowed.");</script>';

        // Redirect to service.php after 500 milliseconds
        echo '<script>window.location.href = "staff-homepage.php";</script>';
        exit();
    }


    // Insert data into the database
    $query = "INSERT INTO service_request (user_id, train_type_id, train_brand, train_registration_number, train_model, gate_number, service_id, status) 
              VALUES (:userId, :trainTypeId, :trainBrand, :registrationNumber, :trainModel, :gateNumber, :serviceId, :status)";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':userId', $loggedInUserId, PDO::PARAM_INT);
    $stmt->bindParam(':trainTypeId', $trainTypeId, PDO::PARAM_INT);
    $stmt->bindParam(':trainBrand', $trainBrand, PDO::PARAM_STR);
    $stmt->bindParam(':registrationNumber', $registrationNumber, PDO::PARAM_STR);
    $stmt->bindParam(':trainModel', $trainModel, PDO::PARAM_STR);
    $stmt->bindParam(':gateNumber', $gateNumber, PDO::PARAM_STR);
    $stmt->bindParam(':serviceId', $serviceId, PDO::PARAM_INT);
    $stmt->bindParam(':status', $status, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo "<script>alert('Success: Service request submitted successfully!');</script>";
    } else {
        echo "<script>alert('Error: Failed to submit service request.');</script>";
    }
}

// Check if the search form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['searchBtn'])) {
    $search = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '';
    $searchResult = getServices($pdo, $search); 
    $services = $searchResult;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Homepage</title>
    <link rel="icon" type="image/x-icon" href="images/icon.png"/>
    <link rel="stylesheet" href="css/staff/homepage.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-...." crossorigin="anonymous" />
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>

    <div class="background-container">
        <div class="title-container">
            <h1>TSMS</h1>
            <nav>
                <a href="staff-homepage.php?userId=<?php echo $loggedInUserId; ?>">Home</a>
                <a href="faq.php">FAQ</a>
                <a href="#" onclick="scrollToSection('about')">About Us</a>
                <a href="view-requests.php?userId=<?php echo $loggedInUserId; ?>" id="myRequestBtn">My Request</a>
                <a href="#" onclick="confirmLogout()">Logout</a>
            </nav>
        </div>
        <h2 class="center-title">Train Service Maintenance System For Maintenance Report Crew</h2>
        <div class="button-container">
            <button class="send-request-btn" onclick="openModal()">Service Request</button>
        </div>
    </div>
    <div class="main-container">
        <div class="left-container">
            <h2>We Do Service For:</h2>
            <table>
                <tbody>
                <?php foreach ($categories as $category) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($category['category']); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="right-container">
            <h2>Our Service</h2>
            <form method="get" class="search-bar" id="searchForm">
                <input type="text" name="search" placeholder="Search service" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit" name="searchBtn">Search</button>
            </form>
            <div class="service-boxes">
                <?php
                if (isset($_GET['search'])) {
                    if (empty($services)) {
                        echo '<p style="color: red; text-align: right; margin-left: 40%;">No results found.</p>';
                    } else {
                        foreach ($services as $service) {
                            echo '<div class="service-box">';
                            echo '<h3>' . htmlspecialchars($service['service']) . '</h3>';
                            echo '<p>' . htmlspecialchars($service['description']) . '</p>';
                            echo '</div>';
                        }
                    }
                } else {
                    foreach ($services as $service) {
                        echo '<div class="service-box">';
                        echo '<h3>' . htmlspecialchars($service['service']) . '</h3>';
                        echo '<p>' . htmlspecialchars($service['description']) . '</p>';
                        echo '</div>';
                    }
                }
                ?>
            </div>
        </div>
    </div>

    <section class="about-container" id="about" style="border-top: 1px dashed midnightblue;">
        <h2>About Us</h2>
        <p>
            Welcome to TSMS, your one-stop solution for efficient and reliable train service management. We are dedicated to providing a seamless experience for both staff and administrators in the management of train services.
        </p>
        <div class="video-con">
            <iframe width="560" height="315" src="https://www.youtube.com/embed/kQ-dTdOl2WE" frameborder="0" allowfullscreen></iframe>
        </div>
    </section>
    <footer style="background-color: azure;">
    <footer style="border-top: 1px dashed midnightblue;" >
        <div class="footer-content">
            <div class="footer-section about">
                <h2>About TSMS</h2>
                <p>
                    TSMS is committed to revolutionizing train service management. Our goal is to provide a reliable, efficient, and secure platform for managing all aspects of train services.
                </p>
            </div>
            <div class="footer-section contact">
                <h2>Contact Us</h2>
                <p>Email: trainservice60@gmail.com</p>
                <p>Phone: +123 456 7890</p>
             <br>
        <p><a href="contact.php" style="color: #004e92; font-weight: bold; text-decoration: none;">Need Support? Click Here</a></p>   
            </div>
            <div class="footer-section social">
                <h2>Follow Us</h2>
                <p>Stay connected on social media for updates and announcements.</p>
                <a href="https://web.facebook.com/?_rdc=1&_rdr"><i class='bx bxl-facebook'></i></a>
                <a href="https://www.instagram.com/"><i class='bx bxl-instagram' ></i></a>
                <a href="https://twitter.com/?lang=en"><i class='bx bxl-twitter' ></i></a>
                <a href="https://www.linkedin.com/"><i class='bx bxl-linkedin-square' s></i></a>
                <a href="https://www.whatsapp.com/"><i class='bx bxl-whatsapp' ></i></a>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 TSMS - BIC21203 Group 1. All rights reserved.</p>
        </div>
    </footer>

    <!-- Service Request Form -->
    <div id="serviceRequestModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Fill the Service Request Form</h2>
            <hr>
            <form id="serviceRequestForm" method="POST">
                <label for="trainType">Train Type:</label>
                <select id="trainType" name="trainType">
                    <?php foreach ($categories as $category) : ?>
                        <option value="<?php echo htmlspecialchars($category['id']); ?>"><?php echo htmlspecialchars($category['category']); ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="trainBrand">Train Brand:</label>
                <input type="text" id="trainBrand" name="trainBrand" required>

                <label for="registrationNumber">Train Registration Number:</label>
                <input type="text" id="registrationNumber" name="registrationNumber" required>

                <label for="trainModel">Train Model:</label>
                <input type="text" id="trainModel" name="trainModel" required>

                <label for="gateNumber">Gate Number:</label>
                <select id="gateNumber" name="gateNumber" required>
                    <option value="" disabled selected>Please select a gate</option>
                    <?php foreach ($gates as $gate): ?>
                        <option value="<?php echo htmlspecialchars($gate['gate_name']); ?>">
                            <?php echo htmlspecialchars($gate['gate_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="serviceType">Service:</label>
                <select id="serviceType" name="serviceType">
                    <?php foreach ($allServices as $service) : ?>
                        <option value="<?php echo htmlspecialchars($service['id']); ?>"><?php echo htmlspecialchars($service['service']); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Submit Request</button>
            </form>
        </div>
    </div>

    <script>
        //Function to scroll to About Us
        function scrollToSection(sectionId) {
            var section = document.getElementById(sectionId);
            if (section) {
                section.scrollIntoView({ behavior: 'smooth' });
            }
        }

        //Function to logout
        function confirmLogout() {
            var confirmLogout = confirm("Are you sure you want to logout?");
            if (confirmLogout) {
                // Redirect to the login page or perform logout action
                window.location.href = "logout.php";
            }
        }

        // Function to open the service form
        function openModal() {
            var modal = document.getElementById('serviceRequestModal');
            modal.style.display = 'block';
        }

        // Function to close the service form
        function closeModal() {
            var modal = document.getElementById('serviceRequestModal');
            modal.style.display = 'none';
        }
    </script>

</body>
</html>
