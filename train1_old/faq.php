<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>FAQ - APG System</title>
    <link rel="stylesheet" href="css/support.css"> 
</head>
<body>

    <div class="faq-container">
        <div class="faq-header">
            <h1>Frequently Asked Questions</h1>
            <p>Common troubleshooting steps for APG Staff & Engineers.</p>
        </div>

        <div class="faq-item">
            <button class="faq-question">How do I reset my password?</button>
            <div class="faq-answer">
                <p>Go to the Login page and click "Forgot Password". Enter your email to receive a reset code.</p>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">What does "Error 503" mean?</button>
            <div class="faq-answer">
                <p>This usually indicates a Sensor Malfunction. Please inspect the gate for debris.</p>
            </div>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <a href="login.php" class="btn-login" style="padding: 10px 20px; text-decoration: none;">Back to Login</a>
            <br><br>
            <p>Still need help? <a href="contact.php" style="color: #004e92;">Contact Support</a></p>
        </div>
    </div>

    <script>
        var acc = document.getElementsByClassName("faq-question");
        for (var i = 0; i < acc.length; i++) {
            acc[i].addEventListener("click", function() {
                this.classList.toggle("active");
                var panel = this.nextElementSibling;
                if (panel.style.display === "block") {
                    panel.style.display = "none";
                } else {
                    panel.style.display = "block";
                }
            });
        }
    </script>
</body>
</html>