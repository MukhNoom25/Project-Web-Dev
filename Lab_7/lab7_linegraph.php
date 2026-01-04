<?php
// Database Connection Configuration
$servername = "localhost";
$username = "root"; 
$password = "";     
$dbname = "tourism_stats"; 

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch data from database (Table 1 Data)
$sql = "SELECT component, amount_2010, amount_2011 FROM visitor_expenditure";
$result = $conn->query($sql);

$components = [];
$data2010 = [];
$data2011 = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $components[] = $row['component'];
        $data2010[] = $row['amount_2010'];
        $data2011[] = $row['amount_2011'];
    }
} else {
    echo "0 results";
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab 7: Line Graph (Domestic Visitor Expenditure)</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .chart-container { width: 80%; margin: auto; }
        h2 { text-align: center; }
    </style>
</head>
<body>

    <h2>Line Graph: Components of Expenditure (2010 vs 2011)</h2>
    
    <div class="chart-container">
        <canvas id="expenditureLineChart"></canvas>
    </div>

    <script>
        // Pass PHP arrays to JavaScript
        const labels = <?php echo json_encode($components); ?>;
        const data2010 = <?php echo json_encode($data2010); ?>;
        const data2011 = <?php echo json_encode($data2011); ?>;

        const ctx = document.getElementById('expenditureLineChart').getContext('2d');
        const myChart = new Chart(ctx, {
            type: 'line', 
            data: {
                labels: labels,
                datasets: [
                    {
                        label: '2010 Expenditure (RM Million)',
                        data: data2010,
                        borderColor: 'rgba(54, 162, 235, 1)', 
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderWidth: 2,
                        tension: 0.3, 
                        fill: false
                    },
                    {
                        label: '2011 Expenditure (RM Million)',
                        data: data2011,
                        borderColor: 'rgba(255, 99, 132, 1)', 
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Source: Table 1 - Components of Expenditure by Domestic Visitors'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Expenditure (RM Million)'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>