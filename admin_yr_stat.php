<?php
function checkAdminSession() {
    if (!isset($_GET['aid']) || empty($_GET['aid'])) {
        header("Location: login.php");
        exit;
    }
}
checkAdminSession();
include 'config.php';

// Check if 'aid' parameter is present in the URL
if(isset($_GET['aid'])) {
    $aid = $_GET['aid'];
// Fetch the 'name' column along with 'username'
$query = "SELECT username, name FROM admin WHERE aid = ?";
$stmt = $mysqli->prepare($query);
if ($stmt === false) {
    die("Error in preparing statement: " . $mysqli->error);
}

// Bind parameters and execute
$stmt->bind_param("i", $aid);
if (!$stmt->execute()) {
    die("Error in executing statement: " . $stmt->error);
}

// Get result
$result = $stmt->get_result();

// Check if the result is not empty
if ($result && $result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    $admin_username_display = $admin['username'];
    $admin_name_display = $admin['name']; // Fetch the 'name' column
} else {
    $admin_username_display = "Username";
    $admin_name_display = "Name";
}

// Close statement
$stmt->close();
}

$year_levels = ["1st Year", "2nd Year", "3rd Year", "4th Year", "5th Year"];
$year_level_counts = array_fill_keys($year_levels, 0);

$query = "SELECT year_level, COUNT(*) as count FROM chkin WHERE archived = ' ' GROUP BY year_level";
$result = $mysqli->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $level = $row['year_level'];
        if (array_key_exists($level, $year_level_counts)) {
            $year_level_counts[$level] += $row['count'];
        }
    }
}

$total_students = array_sum($year_level_counts);
?>

<?php
// Initialize the start and end date variables
$start_date = $end_date = null;
if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];
}

// Initialize year level counts
$year_level_counts = [
    "1st Year" => 0,
    "2nd Year" => 0,
    "3rd Year" => 0,
    "4th Year" => 0,
    "5th Year" => 0
];

// Query to count students per year level, with optional date filter
$query = "SELECT year_level, COUNT(*) as count FROM chkin WHERE archived = ' '";

// Apply date filter if both start and end dates are set
if ($start_date && $end_date) {
    $query .= " AND STR_TO_DATE(`date`, '%m-%d-%Y') BETWEEN STR_TO_DATE(?, '%Y-%m-%d') AND STR_TO_DATE(?, '%Y-%m-%d')";
} 

$query .= " GROUP BY year_level";
$stmt = $mysqli->prepare($query);

if ($stmt === false) {
    // Log the error message if prepare fails
    error_log('MySQL prepare error: ' . $mysqli->error);
    die('Prepare failed: ' . htmlspecialchars($mysqli->error));
}

// Bind parameters if date filters are applied
if ($start_date && $end_date) {
    $stmt->bind_param("ss", $start_date, $end_date);
}
$stmt->execute();
$result = $stmt->get_result();

// Process result to populate year level counts
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $level = $row['year_level'];
        if (array_key_exists($level, $year_level_counts)) {
            $year_level_counts[$level] += $row['count'];
        }
    }
}

// Calculate total students
$total_students = array_sum($year_level_counts);
$stmt->close();

?>
<?php
// Determine the header text based on the date filters
$header_text = "Year Level Distribution";
if ($start_date && $end_date) {
    $header_text .= " from " . date("F d, Y", strtotime($start_date)) . " to " . date("F d, Y", strtotime($end_date));
} elseif ($start_date) {
    $header_text .= " from " . date("F d, Y", strtotime($start_date));
} elseif ($end_date) {
    $header_text .= " up to " . date("F d, Y", strtotime($end_date));
} else {
    $header_text .= " (All Time)";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Statistics</title>
    <link rel="icon" type="image/x-icon" href="css/pics/logop.png">
    <link rel="stylesheet" href="css/admin_stat.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
    /* Existing styles for charts and printing */
    .chart-container {
        display: grid;
        justify-content: center;
        padding: 10px; /* Added padding to create space around charts */
        background-color: white;
        border-radius: 15px;
        width: 96%;
        height: 100%;
    }
    .ustp-header{
        width: 100%;
        max-width: 500px;
        margin: 0 auto;
        display: block;
    }
/* CSS for printing */
@media print {
    @page {
        size: A4 landscape; /* Ensure landscape orientation */
        margin: 10mm; /* Standard A4 margins */
    }

    body {
        background-color: white !important;
        margin: 0 !important;
        font-size: 16px; /* Adjust font size to make content fit */
    }

    .sidebar, .fixed-date-time, .search-bar, .secondary-navbar {
        display: none !important; /* Hide non-essential elements */
    }

    .content-container {
        padding: 0 !important;
        margin: 0 !important;
    }

    .chart-container {
        margin-left: 5%;
        display: grid !important;
        justify-content: center !important;
        flex-wrap: wrap !important;
        padding: 5px !important;
        background-color: white !important;
        width: 90%;
        max-height: auto; /* Reduced height to fit A4 paper */
        overflow: hidden;
        margin-bottom: 10px; /* Reduced spacing between sections */
        font-size: 16px; /* Adjusted font size for better fitting */
    }

    .chart-container canvas {
        width: 870px !important; /* Make the canvas take up full width */
        height: auto !important; /* Adjust height automatically */
    }

    .chart-counts {
        display: block !important;
        color: black !important;
        text-align: center;
        font-size: 14px; /* Adjust font size */
    }

    #date-range-heading {
        text-align: center;
        width: 100%;
        margin: 0;
        padding: 5px 0; /* Reduce padding */
    }

    .header img {
        width: 60%; /* Adjust image to fit the page */
        max-width: 400px; /* Limit the max width */
        height: auto;
        margin: 0 auto;
        display: block;
    }

    h1 {
        font-size: 16px !important; /* Adjust header font size */
        text-align: center;
        margin: 5px 0 !important;
    }

    .chart-container div {
        margin-bottom: 10px; /* Reduced margin */
    }

    .prepared-by {
        margin-top: 10px !important;
        font-size: 16px !important;
        text-align: left !important;
        page-break-inside: avoid !important; /* Ensure section doesn't split across pages */
    }

    .prepared-by p {
        margin: 2px 0 !important;
    }

    .print-button {
        display: none !important; /* Hide print button during print */
    }

    /* Ensure everything inside .chart-container is printed */
    .chart-container * {
        visibility: visible !important; /* Ensure all elements inside are visible */
    }
}
    </style>
</head>
<body class="bg">
    <div class="sidebar">
    <span style="margin-left: 25%;"><img src="css/pics/logop.png" alt="Logo" class="logo"></span>
        <?php
        // Check if $admin_username_display is set
        if(isset($admin_username_display)) {
            // Add spaces before the admin username to align it
            echo '<div class="hell">Admin: ' . $admin_username_display . '</span></div>';
        } else {
            // Display a default message if admin username is not found
            echo '<div>Admin: <br>Username</div>';
        }
        ?>
        <a href="admin_dash.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Dashboard</a>
        <a href="admin_pf.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">User Credentials</a>
        <a href="admin_srch.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Accounts</a>
        <a href="admin_attd.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Library Logs</a>
        <a href="admin_stat.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item active">User Statistics</a>
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
        <a href="login.php" class="sidebar-item logout-btn">Logout</a>
    </div>

    <div class="content">
        <nav class="secondary-navbar">
            <a href="admin_stat.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item">Library User Charts</a>
            <a href="admin_yr_stat.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item active">Year Level Graph</a>
            <a href="admin_prps_stat.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item">User Purpose Graph</a>
            <a href="admin_win.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item">Top Library User</a>
            <a href="admin_win2.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item">Top Book Borrrower</a>
        </nav>
    </div>

    <div class="fixed-date-time">
        <p>Date: <span id="current-date"><?php echo date("m-d-Y"); ?></span></p>
        <p>Time: <span id="current-time"></span></p>
    </div>

<div class="content-container">
<div class="search-bar">
    <form method="GET" action="">
        <input type="hidden" name="aid" value="<?php echo htmlspecialchars($aid); ?>">
        <label for="start_date">Start Date:</label>
        <input type="date" id="start_date" name="start_date" value="<?php echo isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : ''; ?>">
        
        <label for="end_date">End Date:</label>
        <input type="date" id="end_date" name="end_date" value="<?php echo isset($_GET['end_date']) ? htmlspecialchars($_GET['end_date']) : ''; ?>">
        
        <button type="submit"><i class='fas fa-filter'></i> Filter</button>
    </form>
</div>
<div class="chart-container">
<header class="header"><center>
            <img src="css/pics/ustp-header.png" alt="USTP Header" class="ustp-header">
        </header></center>
<h1><?php echo $header_text; ?></h1>
    
        <canvas id="yearLevelChart"></canvas>
        <div style="text-align:center;">Total Students: <?php echo $total_students; ?></div>
    
    <?php
    // Get the name from the URL
$name = isset($_GET['name']) ? htmlspecialchars($_GET['name']) : 'Admin';
?>
<div style="margin-top: 20px; text-align: left; font-size: 16px;">
    <p style="margin-bottom: 0px;">Prepared By:</p>
    <p style="margin-top: 5px; margin-left: 50px; margin-bottom: 0px; ">
        <?php echo isset($admin_name_display) ? htmlspecialchars($admin_name_display) : 'Admin'; ?>
    </p>
    <p style="margin-top: 0px; text-decoration: overline; margin-left: 18px;">Head Librarian USTP-Jasaan</p>

<button class="print-button" onclick="window.print()"><i class='fas fa-print'></i> Print Graph</button>
</div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    var ctx = document.getElementById('yearLevelChart').getContext('2d');

    // Define a color for each year level
    var yearLevelColors = {
        "1st Year": 'rgba(255, 99, 132)',  // Red
        "2nd Year": 'rgba(54, 162, 235)', // Blue
        "3rd Year": 'rgba(255, 206, 86)', // Yellow
        "4th Year": 'rgba(65, 185, 90)', // Green
        "5th Year": 'rgba(153, 102, 255)'  // Purple
    };

    // Prepare the labels (year levels) and data (student counts)
    var labels = <?php echo json_encode(array_keys($year_level_counts)); ?>;
    var data = <?php echo json_encode(array_values($year_level_counts)); ?>;

    // Prepare the background colors for each year level
    var backgroundColors = labels.map(function(year_level) {
        return yearLevelColors[year_level] || 'rgba(0, 0, 0, 0.5)';  // Fallback color
    });

    // Prepare the border colors for each year level
    var borderColors = labels.map(function(year_level) {
        return yearLevelColors[year_level]?.replace('0.5', '1') || 'rgba(0, 0, 0, 1)';  // Darken the border color
    });

    var yearLevelChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Total Students',
                data: data,
                backgroundColor: backgroundColors,  // Dynamic background colors
                borderColor: borderColors,          // Dynamic border colors
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Year Level'
                    }
                },
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Students'
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: 'Year Level Distribution'
                }
            }
        }
    });
});
</script>
<script>
            function updateTime() {
            var currentDate = new Date();
            var month = (currentDate.getMonth() + 1).toString().padStart(2, '0');
            var day = currentDate.getDate().toString().padStart(2, '0');
            var year = currentDate.getFullYear().toString();
            var dateString = month + '-' + day + '-' + year;
            var timeString = currentDate.toLocaleTimeString();
            document.getElementById("current-date").textContent = dateString;
            document.getElementById("current-time").textContent = timeString;
        }
        updateTime();
        setInterval(updateTime, 1000);
</script>
</body>
</html>
