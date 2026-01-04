<?php
// Include the PDO connection
include 'config.php';
include('timeout.php');

if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page
    header('Location: login.php');
    exit();
}

// Get the user ID from the session
$loggedInUserId = $_SESSION['user_id'];

// Function to get the category from the categories table
function getCategory($pdo, $categoryId) {
    $stmt = $pdo->prepare("SELECT category FROM categories WHERE id = :categoryId");
    $stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['category'];
}

// Function to get the service from the service_list table
function getService($pdo, $serviceId) {
    $stmt = $pdo->prepare("SELECT service FROM service_list WHERE id = :serviceId");
    $stmt->bindParam(':serviceId', $serviceId, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['service'];
}

// Function to get the status text
function getStatus($statusCode) {
    return $statusCode == 0 ? 'Pending' : 'Complete';
}

// Function to fetch all service requests for a user
function getAllServiceRequests($pdo, $userId) {
    $query = "SELECT sr.*, sl.service, c.category
              FROM service_request sr
              JOIN service_list sl ON sr.service_id = sl.id
              JOIN categories c ON sr.train_type_id = c.id
              WHERE sr.user_id = :userId";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch all service requests for the logged-in user
$userServiceRequests = getAllServiceRequests($pdo, $loggedInUserId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>My Service Request</title>
    <link rel="icon" type="image/x-icon" href="images/favicon.ico"/>
    <link rel="stylesheet" href="css/staff/view.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-...." crossorigin="anonymous" />
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <div class="background-container">
        <div class="title-container">
            <h1>TSMS</h1>
            <nav>
                <a href="staff-homepage.php?userId=<?php echo $loggedInUserId; ?>">
                    <i class="fas fa-home"></i> Home
                </a>
                <a href="staff-homepage.php?userId=<?php echo $loggedInUserId; ?>#about">
                    <i class="fas fa-info-circle"></i> About Us
                </a>
                <a href="view-requests.php?userId=<?php echo $loggedInUserId; ?>" id="myRequestBtn">
                    <i class="fas fa-clipboard-list"></i> My Request
                </a>
                <a href="#" onclick="confirmLogout()">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </div>
    </div>

    <div class="service-requests-container">
        <h1><i class="fas fa-clipboard-check"></i> My Service Requests</h1>

        <?php if (!empty($userServiceRequests)): ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th><i class="fas fa-calendar-alt"></i> Date Created</th>
                            <th><i class="fas fa-info-circle"></i> Request Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($userServiceRequests as $request): ?>
                            <tr>
                                <td>
                                    <div class="date-cell">
                                        <i class="fas fa-clock"></i>
                                        <span><?php echo htmlspecialchars($request['created_at']); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="details-cell">
                                        <div class="details-container">
                                            <div class="details-section">
                                                <table class="details-table">
                                                    <thead>
                                                        <tr>
                                                            <th colspan="2">
                                                                <i class="fas fa-info-circle"></i> Request Details
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td class="detail-label">Request ID:</td>
                                                            <td class="detail-value"><?php echo htmlspecialchars($request['request_id']); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="detail-label">Train Type:</td>
                                                            <td class="detail-value"><?php echo htmlspecialchars(getCategory($pdo, $request['train_type_id'])); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="detail-label">Train Brand:</td>
                                                            <td class="detail-value"><?php echo htmlspecialchars($request['train_brand']); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="detail-label">Train Model:</td>
                                                            <td class="detail-value"><?php echo htmlspecialchars($request['train_model']); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="detail-label">Registration No:</td>
                                                            <td class="detail-value"><?php echo htmlspecialchars($request['train_registration_number']); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="detail-label">Service:</td>
                                                            <td class="detail-value"><?php echo htmlspecialchars(getService($pdo, $request['service_id'])); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="detail-label">Status:</td>
                                                            <td class="detail-value">
                                                                <span class="status-badge <?php echo $request['status'] == 0 ? 'status-pending' : 'status-complete'; ?>">
                                                                    <i class="fas <?php echo $request['status'] == 0 ? 'fa-clock' : 'fa-check-circle'; ?>"></i>
                                                                    <?php echo getStatus($request['status']); ?>
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="no-data-message">
                <i class="fas fa-exclamation-triangle"></i>
                <p>No service requests found for your account.</p>
                <small>Create a new service request to get started!</small>
            </div>
        <?php endif; ?>
    </div>

    <footer>
        <div class="footer-content">
            <div class="footer-section about">
                <h2><i class="fas fa-info-circle"></i> About TSMS</h2>
                <p>
                    TSMS is committed to revolutionizing train service management. Our goal is to provide a reliable, efficient, and secure platform for managing all aspects of train services.
                </p>
            </div>
            <div class="footer-section contact">
                <h2><i class="fas fa-envelope"></i> Contact Us</h2>
                <p><i class="fas fa-envelope"></i> Email: trainservice60@gmail.com</p>
                <p><i class="fas fa-phone"></i> Phone: +123 456 7890</p>
            </div>
            <div class="footer-section social">
                <h2><i class="fas fa-share-alt"></i> Follow Us</h2>
                <p>Stay connected on social media for updates and announcements.</p>
                <div class="social-icons">
                    <a href="https://web.facebook.com/?_rdc=1&_rdr" title="Facebook">
                        <i class='bx bxl-facebook'></i>
                    </a>
                    <a href="https://www.instagram.com/" title="Instagram">
                        <i class='bx bxl-instagram'></i>
                    </a>
                    <a href="https://twitter.com/?lang=en" title="Twitter">
                        <i class='bx bxl-twitter'></i>
                    </a>
                    <a href="https://www.linkedin.com/" title="LinkedIn">
                        <i class='bx bxl-linkedin-square'></i>
                    </a>
                    <a href="https://www.whatsapp.com/" title="WhatsApp">
                        <i class='bx bxl-whatsapp'></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 TSMS. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Function to logout
        function confirmLogout() {
            var confirmLogout = confirm("Are you sure you want to logout?");
            if (confirmLogout) {
                // Redirect to the login page or perform logout action
                window.location.href = "logout.php";
            }
        }

        // Add smooth scrolling for internal links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Add fade-in animation on page load
        window.addEventListener('load', function() {
            document.body.style.opacity = '0';
            document.body.style.transition = 'opacity 0.5s ease-in-out';
            setTimeout(() => {
                document.body.style.opacity = '1';
            }, 100);
        });
    </script>

</body>
</html>