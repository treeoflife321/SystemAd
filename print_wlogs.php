<?php
// Include database connection
include 'config.php';

// Initialize the WHERE clause for the SQL query
$where_clause = " WHERE r.uid = 0 AND (r.status = 'Returned' OR r.status = 'Cancelled')";

// Check if any search parameters are provided
if (isset($_GET['search_info']) || isset($_GET['search_title']) || (isset($_GET['start_date']) && isset($_GET['end_date'])) || isset($_GET['search_status'])) {
    $search_info = isset($_GET['search_info']) ? $_GET['search_info'] : '';
    $search_title = isset($_GET['search_title']) ? $_GET['search_title'] : '';
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
    $search_status = isset($_GET['search_status']) ? $_GET['search_status'] : '';

    // Add conditions based on provided search parameters
    if (!empty($search_info)) {
        $where_clause .= " AND r.info LIKE '%$search_info%'";
    }
    if (!empty($search_title)) {
        $where_clause .= " AND i.title LIKE '%$search_title%'";
    }
    if (!empty($start_date) && !empty($end_date)) {
        // Add date range condition without changing the format
        $where_clause .= " AND STR_TO_DATE(r.date_rel, '%m-%d-%Y') >= STR_TO_DATE('$start_date', '%Y-%m-%d') AND STR_TO_DATE(r.date_ret, '%m-%d-%Y') <= STR_TO_DATE('$end_date', '%Y-%m-%d')";
    }
    if (!empty($search_status)) {
        $where_clause .= " AND r.status = '$search_status'";
    }
}

// Finalize the SQL query
$query = "SELECT r.rid, r.info, r.contact, i.title, r.status, r.date_rel, r.date_ret
          FROM rsv r
          INNER JOIN inventory i ON r.bid = i.bid 
          $where_clause";

$result = $mysqli->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Borrowed Books Logs</title>
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
        
    <h1>Borrowed Books Logs</h1>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>User Info</th>
                <th>Contact Number</th>
                <th>Book Title</th>
                <th>Status</th>
                <th>Date Released</th>
                <th>Date Returned</th>
            </tr>
        </thead>
        <tbody>
        <?php
        if ($result && $result->num_rows > 0) {
            $counter = 1;
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $counter++ . "</td>";
                echo "<td>" . htmlspecialchars($row["info"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["contact"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["title"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["status"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["date_rel"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["date_ret"]) . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='7'>No data found.</td></tr>";
        }
        ?>
        </tbody>
    </table>
</body>
</html>
