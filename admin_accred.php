<?php
// Include database connection
include 'config.php';

// Check if 'aid' parameter is present in the URL
if (isset($_GET['aid'])) {
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
}

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
} else {
    // If no dates are selected, display a default message
    $heading = "Library Users Checked In";
    $formatted_start_date = $formatted_end_date = ''; // Set to empty strings to avoid undefined variable error
}

// Count checked-in active users
$checked_in_users_query = "
    SELECT COUNT(DISTINCT chkin.info) AS checked_in_users 
    FROM chkin 
    JOIN users ON chkin.info = users.info 
    WHERE users.status = 'Active'
";

if ($show_table) {
    $checked_in_users_query .= "
        AND STR_TO_DATE(chkin.date, '%m-%d-%Y') 
        BETWEEN STR_TO_DATE('$start_date', '%Y-%m-%d') 
        AND STR_TO_DATE('$end_date', '%Y-%m-%d')
    ";
}

$checked_in_users_result = $mysqli->query($checked_in_users_query);
if ($checked_in_users_result) {
    $checked_in_users_row = $checked_in_users_result->fetch_assoc();
    $checked_in_users = $checked_in_users_row['checked_in_users'];
} else {
    $checked_in_users = 0;
}

// Initialize filter variable
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'in';

// Count total active users
$total_users_query = "SELECT COUNT(*) AS total_users FROM users WHERE status = 'Active'";
$total_users_result = $mysqli->query($total_users_query);
if ($total_users_result) {
    $total_users_row = $total_users_result->fetch_assoc();
    $total_users = $total_users_row['total_users'];
} else {
    $total_users = 0; // Default to 0 if query fails
}

// Set the heading and user count display based on the filter value
if ($filter === 'in') {
    $heading = "Library Users Checked In from " . htmlspecialchars($formatted_start_date) . " to " . htmlspecialchars($formatted_end_date);
    $count_display = "Checked In Users: " . $checked_in_users . " / " . $total_users;
} elseif ($filter === 'not') {
    $not_checked_in_users = $total_users - $checked_in_users;
    $heading = "Library Users Not Checked In from " . htmlspecialchars($formatted_start_date) . " to " . htmlspecialchars($formatted_end_date);
    $count_display = "Not Checked In Users: " . $not_checked_in_users . " / " . $total_users;
}

// Base query for user logs
if ($filter === 'in') {
    $query = "
        SELECT 
            chkin.info, 
            users.idnum, 
            users.year_level, 
            COUNT(chkin.info) AS checkin_count 
        FROM chkin 
        JOIN users ON chkin.info = users.info 
        WHERE chkin.archived = ''
        AND users.status = 'Active'
    ";

    // Add date filter if applicable
    if ($show_table) {
        $query .= "
            AND STR_TO_DATE(chkin.date, '%m-%d-%Y') 
            BETWEEN STR_TO_DATE(?, '%Y-%m-%d') 
            AND STR_TO_DATE(?, '%Y-%m-%d')
        ";
    }

    $query .= " 
        GROUP BY chkin.info, users.idnum, users.year_level 
        ORDER BY chkin.info ASC
    ";
} elseif ($filter === 'not') {
    // Query to fetch all users who are not in the chkin table within the specified date range
    $query = "
        SELECT 
            users.info, 
            users.idnum, 
            users.year_level, 
            '0' AS checkin_count 
        FROM users 
        WHERE users.status = 'Active'
        AND NOT EXISTS (
            SELECT 1 
            FROM chkin 
            WHERE chkin.info = users.info 
            AND STR_TO_DATE(chkin.date, '%m-%d-%Y') 
            BETWEEN STR_TO_DATE(?, '%Y-%m-%d') 
            AND STR_TO_DATE(?, '%Y-%m-%d')
        )
        ORDER BY users.info ASC
    ";
}

// Prepare and execute the query
$stmt = $mysqli->prepare($query);
if ($stmt === false) {
    die("Error in preparing statement: " . $mysqli->error);
}

// Bind parameters for date range
if ($show_table || $filter === 'not') {
    $stmt->bind_param("ss", $start_date, $end_date);
}

if (!$stmt->execute()) {
    die("Error in executing statement: " . $stmt->error);
}

$result = $stmt->get_result();

// helper function to extract the course
function extractCourse($info) {
    $courses = ['BSIT', 'BSNAME', 'BSTCM', 'BSMET', 'BSESM'];
    foreach ($courses as $course) {
        if (strpos($info, $course) !== false) {
            return $course;
        }
    }
    return 'N/A'; // Default if no course is found
}
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
            echo '<div class="hell">Admin: ' . $admin_username_display . '</span></div>';
        } else {
            // Display a default message if admin username is not found
            echo '<div>Admin: <br>Username</div>';
        }
        ?>
        <a href="admin_dash.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Dashboard</a>
        <a href="admin_pf.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">User Credentials</a>
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
        <a href="login.php" class="sidebar-item logout-btn">Logout</a>
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
    <form method="get" action="admin_accred.php">
            <?php if (isset($aid)) echo '<input type="hidden" name="aid" value="' . $aid . '">'; ?>
            <label for="filter">Filter:</label>
            <select name="filter" id="filter" onchange="this.form.submit()">
                <option value="in" <?php if ($filter === 'in') echo 'selected'; ?>>Checked In</option>
                <option value="not" <?php if ($filter === 'not') echo 'selected'; ?>>Not Checked In</option>
            </select>
            <label for="start_date">Start Date:</label>
            <input type="date" id="start_date" name="start_date" value="<?php echo isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : ''; ?>">
            <label for="end_date">End Date:</label>
            <input type="date" id="end_date" name="end_date" value="<?php echo isset($_GET['end_date']) ? htmlspecialchars($_GET['end_date']) : ''; ?>">
            <button type="submit"><i class='fas fa-filter'></i> Filter</button>
        </form>
        <br>
        <form method="POST" action="export_admin_accred.php">
            <input type="hidden" name="aid" value="<?php echo isset($aid) ? htmlspecialchars($aid) : ''; ?>">
            <input type="hidden" name="filter" value="<?php echo htmlspecialchars($filter); ?>">
            <input type="hidden" name="start_date" value="<?php echo isset($start_date) ? htmlspecialchars($start_date) : ''; ?>">
            <input type="hidden" name="end_date" value="<?php echo isset($end_date) ? htmlspecialchars($end_date) : ''; ?>">
            <button type="submit"><i class="fas fa-file-excel"></i> Export to Excel</button>
        </form>
    </div>
    <h1><?php echo $heading; ?></h1>

    <!-- Counter Display -->
    <h3><?php echo $count_display; ?></h3>

    <!-- Conditional table display -->
    <?php if ($show_table): ?>
<table border="1" cellspacing="0" cellpadding="5" style="width: 80%; text-align: left;">
    <thead>
        <tr>
            <th>#</th>
            <th>Info</th>
            <th>Course</th> <!-- New Column -->
            <th>ID Number</th>
            <th>Year Level</th>
            <th>Check-In Count</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <?php $course = extractCourse($row['info']); // Extract course ?>
                <tr>
                    <td><?php echo $counter++; ?></td>
                    <td><?php echo htmlspecialchars(str_replace($course, '', $row['info'])); ?></td> <!-- Info without course -->
                    <td><?php echo htmlspecialchars($course); ?></td> <!-- Display Course -->
                    <td><?php echo htmlspecialchars($row['idnum']); ?></td>
                    <td><?php echo htmlspecialchars($row['year_level']); ?></td>
                    <td><?php echo htmlspecialchars($row['checkin_count']); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6">No users checked in.</td>
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