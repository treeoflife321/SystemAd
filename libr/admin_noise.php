<?php
// Include database connection
include 'config.php';

// Check if 'aid' parameter is present in the URL
if(isset($_GET['aid'])) {
    $aid = $_GET['aid'];
    // Query to fetch the username corresponding to the aid
    $query = "SELECT name FROM libr WHERE aid = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $aid);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Check if the result is not empty
    if ($result && $result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        $admin_username = $admin['name'];
        // Display the admin username in the sidebar
        $admin_username_display = $admin_username;
    } else {
        // Display a default message if admin username is not found
        $admin_username_display = "Username";
    }
    // Close statement
    $stmt->close();
}

// Initialize date range filters
$from_date = isset($_POST['from_date']) ? $_POST['from_date'] : '';
$to_date = isset($_POST['to_date']) ? $_POST['to_date'] : '';

// Adjust the to_date to include the full end of the day
if (!empty($to_date)) {
    $to_date .= ' 23:59:59'; // Append time for the end of the day
}

// Build query based on filters
if (!empty($from_date) && !empty($to_date)) {
    $noise_query = "SELECT * FROM t_endpoint WHERE timestamp BETWEEN ? AND ? ORDER BY timestamp DESC";
    $stmt = $mysqli->prepare($noise_query);
    $stmt->bind_param("ss", $from_date, $to_date);
} else {
    $noise_query = "SELECT * FROM t_endpoint ORDER BY timestamp DESC";
    $stmt = $mysqli->prepare($noise_query);
}

// Execute the query
$stmt->execute();
$noise_result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Noise Levels</title>
    <link rel="icon" type="image/x-icon" href="css/pics/logop.png">
    <link rel="stylesheet" href="css/admin_srch.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
</head>
<body class="bg">
<div class="sidebar">
    <span style="margin-left: 25%;"><img src="css/pics/logop.png" alt="Logo" class="logo"></span>
    <?php
        // Check if $admin_username_display is set
        if(isset($admin_username_display)) {
            // Add spaces before the admin username to align it
            echo '<div class="hell">Librarian: ' . $admin_username_display . '</span></div>';
        } else {
            // Display a default message if admin username is not found
            echo '<div>Admin: <br>Username</div>';
        }
        ?>
    <a href="admin_dash.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Dashboard</a>
    <a href="admin_pf.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Profile</a>
    <a href="admin_attd.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Library Logs</a>
    <a href="admin_stat.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">User Statistics</a>
    <a href="admin_preq.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Pending Requests</a>
    <a href="admin_brel.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Borrowed Books</a>
    <a href="admin_ob.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Overdue Books</a>
    <div class="sidebar-item dropdown">
            <a href="#" class="dropdown-link" onmouseover="toggleDropdown(event)">Inventory</a>
            <div class="dropdown-content">
                <a href="bk_inv.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Books</a>
                <a href="admin_asts_inv.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Assets</a>
            </div>
        </div>
    <a href="../login.php" class="sidebar-item logout-btn">Logout</a>
</div>

<div class="content">
    <nav class="secondary-navbar">
        <a href="#" class="secondary-navbar-item active">Library Noise Levels</a>
    </nav>
</div>

<div class="content-container">
<div class="search-bar">
        <!-- Search Bar with Date Range -->
        <form method="POST" action="admin_noise.php?aid=<?php echo $aid; ?>">
            <label for="from_date">From:</label>
            <input type="date" id="from_date" name="from_date" value="<?php echo $from_date; ?>">
            <label for="to_date">To:</label>
            <input type="date" id="to_date" name="to_date" value="<?php echo $to_date; ?>">
            <button type="submit">Filter</button>
        </form>
        <h3>
        <?php
        if (!empty($_POST['from_date']) && !empty($_POST['to_date'])) {
            $formatted_from_date = date("F j, Y", strtotime($_POST['from_date']));
            $formatted_to_date = date("F j, Y", strtotime($_POST['to_date']));
            echo "Recorded Noise Levels From: " . htmlspecialchars($formatted_from_date) . " To: " . htmlspecialchars($formatted_to_date);
        } else {
            echo "All Recorded Noise Levels";
        }
        ?>
    </h3>
    </div>

    <div style="display: flex; justify-content: space-between;">
    <div style="width: 40%;">
        <table id="noiseTable" style="margin-top: 20px; width: 100%;">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Noise Level</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Check if there are any noise records
                if ($noise_result && $noise_result->num_rows > 0) {
                    // Loop through each row in the noise data and output it to the table
                    $count = 1;
                    $noise_levels = [];
                    $timestamps = [];
                    while ($row = $noise_result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $count . "</td>";
                        echo "<td>" . htmlspecialchars($row['noise_level']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['timestamp']) . "</td>";
                        echo "</tr>";
                        // Collect data for the graph
                        $noise_levels[] = $row['noise_level'];
                        $timestamps[] = $row['timestamp'];
                        $count++;
                    }
                } else {
                    // If no data is available, display a message
                    echo "<tr><td colspan='3'>No noise data available.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

        <!-- Line Graph -->
        <div style="width: 50%; margin-right:5%;">
        <canvas id="noiseChart"></canvas>
    </div>
</div>

<!-- Include Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Data for the chart
    const noiseLevels = <?php echo json_encode($noise_levels); ?>;
    const timestamps = <?php echo json_encode($timestamps); ?>;

    // Create a line chart
    const ctx = document.getElementById('noiseChart').getContext('2d');
    const noiseChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: timestamps, // X-axis labels
            datasets: [{
                label: 'Noise Levels',
                data: noiseLevels, // Y-axis data
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderWidth: 2,
                tension: 0.4, // Smooth curve
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Timestamp'
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Noise Level'
                    },
                    beginAtZero: true
                }
            }
        }
    });
</script>
</div>
<script>
    // Dropdown script
    function toggleDropdown(event) {
        event.preventDefault();
        var dropdownContent = event.target.nextElementSibling;
        dropdownContent.classList.toggle('show');
    }

    window.onclick = function(event) {
        if (!event.target.matches('.dropdown-link')) {
            var dropdowns = document.getElementsByClassName("dropdown-content");
            for (var i = 0; i < dropdowns.length; i++) {
                var openDropdown = dropdowns[i];
                if (openDropdown.classList.contains('show')) {
                    openDropdown.classList.remove('show');
                }
            }
        }
    }

    let lastTimestamp = null;

// Function to check for new data
function checkForNewData() {
    fetch('../check_new_data.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'data_found') {
                const { noise_level, timestamp } = data;

                if (lastTimestamp !== timestamp) {
                    lastTimestamp = timestamp;

                    Swal.fire({
                        title: 'Noise Detected!',
                        text: `Noise Level = ${noise_level}`,
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Get the current 'aid' parameter from the URL
                            const urlParams = new URLSearchParams(window.location.search);
                            const aid = urlParams.get('aid');

                            // Redirect to admin_noise.php with the aid parameter
                            window.location.href = `admin_noise.php?aid=${aid}`;
                        }
                    });
                }
            }
        })
        .catch(error => console.error('Error checking for new data:', error));
}

// Start checking for new data every 5 seconds
setInterval(checkForNewData, 3000);

</script>

</body>
</html>
