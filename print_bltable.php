<?php
// Include database connection
include 'config.php';

// Check if 'aid' parameter is present in the URL
if (isset($_GET['aid'])) {
    $aid = $_GET['aid'];
}

// Initialize the WHERE clause for the SQL query
$where_clause = " WHERE r.status IN ('Returned', 'Cancelled', 'Rejected')";

// Check if any search parameters are provided
if (isset($_GET['search_info']) || isset($_GET['search_title']) || isset($_GET['search_date']) || isset($_GET['search_status']) || isset($_GET['search_rsv_due']) || isset($_GET['search_date_returned']) || (isset($_GET['start_date']) && isset($_GET['end_date']))) {
    $search_info = isset($_GET['search_info']) ? $_GET['search_info'] : '';
    $search_title = isset($_GET['search_title']) ? $_GET['search_title'] : '';
    $search_date = isset($_GET['search_date']) ? $_GET['search_date'] : '';
    $search_rsv_due = isset($_GET['search_rsv_due']) ? $_GET['search_rsv_due'] : '';
    $search_date_returned = isset($_GET['search_date_returned']) ? $_GET['search_date_returned'] : '';
    $search_status = isset($_GET['search_status']) ? $_GET['search_status'] : '';
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

    // Add conditions based on provided search parameters
    if (!empty($search_info)) {
        $where_clause .= " AND u.info LIKE '%$search_info%'";
    }
    if (!empty($search_title)) {
        $where_clause .= " AND r.title LIKE '%$search_title%'";
    }
    if (!empty($search_date)) {
        // Format the date to match the database format
        $where_clause .= " AND (DATE_FORMAT(r.date_rel, '%m-%d-%Y') = DATE_FORMAT('$search_date', '%m-%d-%Y') OR DATE_FORMAT(r.date_ret, '%m-%d-%Y') = DATE_FORMAT('$search_date', '%m-%d-%Y'))";
    }
    if (!empty($search_rsv_due)) {
        // Format the date to match the database format
        $where_clause .= " AND DATE_FORMAT(r.rsv_end, '%m-%d-%Y') = DATE_FORMAT('$search_rsv_due', '%m-%d-%Y')";
    }
    if (!empty($search_date_returned)) {
        // Format the date to match the database format
        $where_clause .= " AND DATE_FORMAT(r.date_ret, '%m-%d-%Y') = DATE_FORMAT('$search_date_returned', '%m-%d-%Y')";
    }
    if (!empty($search_status)) {
        $where_clause .= " AND r.status = '$search_status'";
    }
    if (!empty($start_date) && !empty($end_date)) {
        // Add date range condition with correct date format
        $where_clause .= " AND r.rsv_end >= DATE_FORMAT('$start_date', '%m-%d-%Y') AND r.date_ret <= DATE_FORMAT('$end_date', '%m-%d-%Y')";
    } 
}

// Fetch data for the table
$query = "SELECT r.rid, u.info, u.contact, r.status, r.title, r.date_rel, r.date_ret, r.rsv_end, r.remarks  
          FROM users u 
          JOIN rsv r ON u.uid = r.uid 
          $where_clause";
$result = $mysqli->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrow Logs - Print</title>
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
<body>
    <!-- Header Image -->
    <img src="css/pics/ustp-header.png" alt="USTP Header" class="header-img">

    <h1>Borrow Logs</h1>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>User Info</th>
                <th>Contact Number</th>
                <th>Book Title</th>
                <th>Status</th>
                <th>Reservation Due</th>
                <th>Date Released</th>
                <th>Date Returned</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result && $result->num_rows > 0) {
                $counter = 1;
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $counter++ . "</td>";
                    echo "<td>" . $row["info"] . "</td>";
                    echo "<td>" . $row["contact"] . "</td>";
                    echo "<td>" . $row["title"] . "</td>";
                    echo "<td>" . $row["status"] . "</td>";
                    echo "<td>" . $row["rsv_end"] . "</td>";
                    echo "<td>" . $row["date_rel"] . "</td>";
                    echo "<td>" . $row["date_ret"] . "</td>";
                    echo "<td>" . $row["remarks"] . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='8'>No data available.</td></tr>";
            }
            ?>
        </tbody>
    </table>
    
    <?php
    // Get the name from the URL
$name = isset($_GET['name']) ? htmlspecialchars($_GET['name']) : 'Admin';
?>
<div style="margin-top: 20px; text-align: left; font-size: 16px;">
    <p style="margin-bottom: 0px;"> Prepared By:</p>
    <p style="margin-top: 5px; margin-left: 35px; margin-bottom: 0px; "><?php echo $name; ?></p>
    <p style="margin-top: 0px; text-decoration: overline; margin-left: 18px;">Libraran USTP-Jasaan</p>
</div>

    <script>
        // Automatically trigger print on page load
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
