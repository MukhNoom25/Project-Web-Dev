<?php

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
    <title>Lab 7: Bar Graph (Domestic Visitor Expenditure)</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 20px; background-color: #f9f9f9; }
        .chart-container { 
            width: 75%; 
            margin: auto; 
            background: white; 
            padding: 20px; 
            box-shadow: 0 0 10px rgba(0,0,0,0.1); 
            border-radius: 8px;
        }
        h2 { text-align: center; color: #333; }
        .back-link { display: block; text-align: center; margin-top: 20px; }
    </style>
</head>
<body>

    <h2>Bar Graph: Expenditure Comparison (2010 vs 2011)</h2>
    
    <div class="chart-container">
        <canvas id="expenditureBarChart"></canvas>
    </div>

    <a href="lab07_linegraph.php" class="back-link">View Line Graph Style</a>

    <script>
        // Pass PHP arrays to JavaScript
        const labels = <?php echo json_encode($components); ?>;
        const data2010 = <?php echo json_encode($data2010); ?>;
        const data2011 = <?php echo json_encode($data2011); ?>;

        const ctx = document.getElementById('expenditureBarChart').getContext('2d');
        const myChart = new Chart(ctx, {
            type: 'bar', 
            data: {
                labels: labels,
                datasets: [
                    {
                        label: '2010 (RM Million)',
                        data: data2010,
                        backgroundColor: 'rgba(75, 192, 192, 0.6)', 
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    },
                    {
                        label: '2011 (RM Million)',
                        data: data2011,
                        backgroundColor: 'rgba(153, 102, 255, 0.6)', 
                        borderColor: 'rgba(153, 102, 255, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Comparison of Expenditure Components',
                        font: { size: 16 }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Amount (RM Million)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Expenditure Components'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>