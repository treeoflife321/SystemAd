<?php
// Include database connection
include 'config.php';

// Fetch parameters from URL
$search = isset($_GET['search']) ? $_GET['search'] : '';
$user_type = isset($_GET['user_type']) ? $_GET['user_type'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$purpose = isset($_GET['purpose']) ? $_GET['purpose'] : '';
$idnum = isset($_GET['idnum']) ? $_GET['idnum'] : '';
$year_level = isset($_GET['year_level']) ? $_GET['year_level'] : '';
$gender = isset($_GET['gender']) ? $_GET['gender'] : '';

// Construct SQL query
$query = "SELECT * FROM chkin WHERE archived = ''";

// Add filters to the query
if (!empty($search)) {
    $query .= " AND info LIKE '%" . $mysqli->real_escape_string($search) . "%'";
}
if (!empty($user_type)) {
    $query .= " AND user_type = '" . $mysqli->real_escape_string($user_type) . "'";
}
if (!empty($start_date)) {
    $query .= " AND STR_TO_DATE(date, '%m-%d-%Y') >= '" . $mysqli->real_escape_string($start_date) . "'";
}
if (!empty($end_date)) {
    $query .= " AND STR_TO_DATE(date, '%m-%d-%Y') <= '" . $mysqli->real_escape_string($end_date) . "'";
}
if (!empty($purpose)) {
    $query .= " AND purpose = '" . $mysqli->real_escape_string($purpose) . "'";
}
if (!empty($idnum)) {
    $query .= " AND idnum LIKE '%" . $mysqli->real_escape_string($idnum) . "%'";
}
if (!empty($year_level)) {
    $query .= " AND year_level = '" . $mysqli->real_escape_string($year_level) . "'";
}
if (!empty($gender)) {
    $query .= " AND gender = '" . $mysqli->real_escape_string($gender) . "'";
}

// Execute the query
$result = $mysqli->query($query);
if ($result === false) {
    die("Error in executing query: " . $mysqli->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Library Logs</title>
    <link rel="stylesheet" href="css/admin_srch.css">
    <style>
        @media print {
            .print-button {
                display: none;
            }
        }
        .header-img {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            display: block;
        }
    </style>
</head>
<body onload="window.print()">
    <!-- Header Image -->
    <img src="css/pics/ustp-header.png" alt="USTP Header" class="header-img">
    
    <?php 
    // Generate a dynamic heading based on filters
    $heading = "Library Logs";
    if (!empty($start_date) && !empty($end_date)) {
        $heading .= " from " . date("F j, Y", strtotime($start_date)) . " to " . date("F j, Y", strtotime($end_date));
    } elseif (!empty($start_date)) {
        $heading .= " starting from " . date("F j, Y", strtotime($start_date));
    } elseif (!empty($end_date)) {
        $heading .= " up to " . date("F j, Y", strtotime($end_date));
    } else {
        $heading .= " for All Dates";
    }
    ?>
    <h2><?php echo $heading; ?></h2>

    <?php if ($result && $result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>User Info</th>
                    <th>ID Number</th>
                    <th>User Type</th>
                    <th>Year Level</th>
                    <th>Gender</th>
                    <th>Date</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                    <th>Purpose</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $counter = 0;
                while ($row = $result->fetch_assoc()): 
                    $counter++;
                ?>
                    <tr>
                        <td><?php echo $counter; ?></td>
                        <td><?php echo $row['info']; ?></td>
                        <td><?php echo $row['idnum']; ?></td>
                        <td><?php echo $row['user_type']; ?></td>
                        <td><?php echo $row['year_level']; ?></td>
                        <td><?php echo $row['gender']; ?></td>
                        <td><?php echo $row['date']; ?></td>
                        <td><?php echo $row['timein']; ?></td>
                        <td><?php echo ($row['timeout'] ? $row['timeout'] : 'N/A'); ?></td>
                        <td><?php echo $row['purpose']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="no-data">No logs found for the selected filters.</p>
    <?php endif; ?>

    <?php
// Free the result set and close the connection
if ($result) {
    $result->free();
}
$mysqli->close();

// Get the name from the URL
$name = isset($_GET['name']) ? htmlspecialchars($_GET['name']) : 'Admin';
?>
<div style="margin-top: 20px; text-align: left; font-size: 16px;">
    <p style="margin-bottom: 0px;"> Prepared By:</p>
    <p style="margin-top: 5px; margin-left: 35px; margin-bottom: 0px; "><?php echo $name; ?></p>
    <p style="margin-top: 0px; text-decoration: overline; margin-left: 18px;">Libraran USTP-Jasaan</p>
</div>

</body>
</html>
