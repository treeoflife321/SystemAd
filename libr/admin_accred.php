<?php
function checkAdminSession() {
    if (!isset($_GET['aid']) || empty($_GET['aid'])) {
        header("Location: ../login.php");
        exit;
    }
}

// Call the function at the top of your files
checkAdminSession();
?>
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
?>
<?php
// Initialize variables
$counter = 1;
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;
$show_table = false;
$heading = "Library Users Checked In";

// Validate date inputs
if ($start_date && $end_date) {
    $show_table = true;
    // Convert dates to human-readable format
    $formatted_start_date = DateTime::createFromFormat('Y-m-d', $start_date)->format('F j, Y');
    $formatted_end_date = DateTime::createFromFormat('Y-m-d', $end_date)->format('F j, Y');
    // Update heading
    $heading = "Library Users Checked In from " . htmlspecialchars($formatted_start_date) . " to " . htmlspecialchars($formatted_end_date);
}

// Count total users
$total_users_query = "SELECT COUNT(*) AS total_users FROM users";
$total_users_result = $mysqli->query($total_users_query);
if ($total_users_result) {
    $total_users_row = $total_users_result->fetch_assoc();
    $total_users = $total_users_row['total_users'];
} else {
    $total_users = 0;
}

// Count checked-in users
$checked_in_users_query = "SELECT COUNT(DISTINCT chkin.info) AS checked_in_users 
                           FROM chkin 
                           JOIN users ON chkin.info = users.info 
                           WHERE chkin.status = ''";
if ($show_table) {
    $checked_in_users_query .= " WHERE STR_TO_DATE(chkin.date, '%m-%d-%Y') BETWEEN STR_TO_DATE('$start_date', '%Y-%m-%d') AND STR_TO_DATE('$end_date', '%Y-%m-%d')";
}
$checked_in_users_result = $mysqli->query($checked_in_users_query);
if ($checked_in_users_result) {
    $checked_in_users_row = $checked_in_users_result->fetch_assoc();
    $checked_in_users = $checked_in_users_row['checked_in_users'];
} else {
    $checked_in_users = 0;
}

// Base query for user logs
$query = "
    SELECT DISTINCT chkin.info, users.idnum, users.year_level 
    FROM chkin 
    JOIN users ON chkin.info = users.info
";

// Add date filter if applicable
if ($show_table) {
    $query .= " WHERE STR_TO_DATE(chkin.date, '%m-%d-%Y') BETWEEN STR_TO_DATE(?, '%Y-%m-%d') AND STR_TO_DATE(?, '%Y-%m-%d')";
}

$query .= " ORDER BY chkin.info ASC";

// Prepare and execute the query
$stmt = $mysqli->prepare($query);
if ($stmt === false) {
    die("Error in preparing statement: " . $mysqli->error);
}

if ($show_table) {
    $stmt->bind_param("ss", $start_date, $end_date);
}

if (!$stmt->execute()) {
    die("Error in executing statement: " . $stmt->error);
}

$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Logs</title>
    <link rel="icon" type="image/x-icon" href="css/pics/logop.png">
    <link rel="stylesheet" href="css/admin_srch.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
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
        <a href="admin_dash.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Dashboard</a>
        <a href="admin_pf.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Profile</a>
        <a href="admin_srch.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Accounts</a>
        <a href="admin_attd.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item active">Library Logs</a>
        <a href="admin_stat.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">User Statistics</a>
        <a href="admin_preq.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Pending Requests</a>
        <a href="admin_brel.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Borrowed Books</a>
        <a href="admin_ob.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Overdue Books</a>
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
            <a href="admin_attd.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item">Attendance</a>
            <a href="liblogs.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item">User Logs</a>
            <a href="admin_accred.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item active">User Monitoring</a>
            <a href="admin_aliblogs.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item">Archived User Logs</a>
        </nav>
    </div>

        <!-- Fixed-position date and time display -->
    <div class="fixed-date-time">
        <p>Date: <span id="current-date"><?php echo date("m-d-Y"); ?></span></p>
        <p>Time: <span id="current-time"></span></p>
    </div>

    <div class="content-container">
    <div class="search-bar">
        <!-- Date Filter Form -->
        <form method="get" action="admin_accred.php">
            <?php if (isset($aid)) echo '<input type="hidden" name="aid" value="' . $aid . '">'; ?>
            <label for="start_date">Start Date:</label>
            <input type="date" id="start_date" name="start_date" value="<?php echo isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : ''; ?>">
            <label for="end_date">End Date:</label>
            <input type="date" id="end_date" name="end_date" value="<?php echo isset($_GET['end_date']) ? htmlspecialchars($_GET['end_date']) : ''; ?>">
            <button type="submit"><i class='fas fa-filter'></i> Filter</button>
        </form>
        <br>
        <form method="POST" action="../export_admin_accred.php">
            <?php if (isset($aid)) echo '<input type="hidden" name="aid" value="' . $aid . '">'; ?>
            <input type="hidden" name="start_date" value="<?php echo isset($start_date) ? htmlspecialchars($start_date) : ''; ?>">
            <input type="hidden" name="end_date" value="<?php echo isset($end_date) ? htmlspecialchars($end_date) : ''; ?>">
            <button type="submit"><i class="fas fa-file-excel"></i> Export to Excel</button>
        </form>
    </div>
    <h1><?php echo $heading; ?></h1>

    <!-- Counter Display -->
    <h3>Checked In Users: <?php echo $checked_in_users . " / " . $total_users; ?></h3>

    <!-- Conditional table display -->
    <?php if ($show_table): ?>
        <table border="1" cellspacing="0" cellpadding="5" style="width: 70%; text-align: left;">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Info</th>
                    <th>ID Number</th>
                    <th>Year Level</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $counter++; ?></td>
                            <td><?php echo htmlspecialchars($row['info']); ?></td>
                            <td><?php echo htmlspecialchars($row['idnum']); ?></td>
                            <td><?php echo htmlspecialchars($row['year_level']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">No users checked in.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No data to display. Please filter by date.</p>
    <?php endif; ?>
</div>
<script>
        function updateTime() {
            var currentDate = new Date();
            var month = (currentDate.getMonth() + 1).toString().padStart(2, '0'); // Adding 1 to month since it's zero-based index
            var day = currentDate.getDate().toString().padStart(2, '0');
            var year = currentDate.getFullYear().toString();
            var dateString = month + '-' + day + '-' + year;
            var timeString = currentDate.toLocaleTimeString();
            document.getElementById("current-date").textContent = dateString;
            document.getElementById("current-time").textContent = timeString;
        }
        updateTime(); // Call the function to update time immediately
        setInterval(updateTime, 1000); // Update time every second
</script>    
</body>
</html>