<?php
// Include database connection
include 'config.php';

// Get the parameters from the URL
$searchInfo = isset($_GET['searchInfo']) ? $_GET['searchInfo'] : '';
$searchTitle = isset($_GET['searchTitle']) ? $_GET['searchTitle'] : '';
$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : '';
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : '';

// Build the base query
$query = "SELECT ovrd.oid, ovrd.uid, ovrd.bid, ovrd.title, ovrd.fines, ovrd.date_set, ovrd.info, 
          users.info AS student_info, inventory.title AS book_title, rsv.due_date, ovrd.remarks
          FROM ovrd
          LEFT JOIN users ON ovrd.uid = users.uid
          LEFT JOIN inventory ON ovrd.bid = inventory.bid
          LEFT JOIN rsv ON ovrd.rid = rsv.rid
          WHERE 1=1";

// Append conditions based on search parameters
if (!empty($searchInfo)) {
    $query .= " AND (users.info LIKE ? OR ovrd.info LIKE ?)";
}
if (!empty($searchTitle)) {
    $query .= " AND inventory.title LIKE ?";
}
if (!empty($startDate)) {
    $query .= " AND STR_TO_DATE(rsv.due_date, '%m-%d-%Y') >= STR_TO_DATE(?, '%Y-%m-%d')";
}
if (!empty($endDate)) {
    $query .= " AND ovrd.date_set <= ?";
}

$stmt = $mysqli->prepare($query);

// Bind parameters dynamically
$params = [];
if (!empty($searchInfo)) {
    $searchInfoWildcard = '%' . $searchInfo . '%';
    $params[] = $searchInfoWildcard;
    $params[] = $searchInfoWildcard;
}
if (!empty($searchTitle)) {
    $searchTitleWildcard = '%' . $searchTitle . '%';
    $params[] = $searchTitleWildcard;
}
if (!empty($startDate)) {
    $params[] = $startDate;
}
if (!empty($endDate)) {
    $params[] = $endDate;
}

// Bind parameters to the prepared statement
if (!empty($params)) {
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Overdue Books</title>
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
    <img src="css/pics/ustp-header.png" alt="USTP Header" class="header-img">

    <h1>Overdue Books Report</h1>
    <table id="dataTable">
        <thead>
            <tr>
                <th>#</th>
                <th>User Info</th>
                <th>Book Title</th>
                <th>Due Date</th>
                <th>Fines</th>
                <th>Date Settled</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result && $result->num_rows > 0) {
                $counter = 1;
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $counter++ . "</td>"; // Counter
                    if ($row["uid"] == 0) {
                        echo "<td>" . $row["info"] . "</td>"; // Student Info
                    } else {
                        echo "<td>" . $row["student_info"] . "</td>"; // Student Info
                    }
                    echo "<td>" . $row["book_title"] . "</td>"; // Book Title
                    echo "<td>" . $row["due_date"] . "</td>"; // Due Date
                    echo "<td>" . $row["fines"] . "</td>"; // Fines
                    echo "<td>" . date('m-d-Y', strtotime($row["date_set"])) . "</td>"; // Date Settled
                    echo "<td>" . $row["remarks"] . "</td>"; // Remarks
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6'>No records found.</td></tr>";
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
        window.print(); // Trigger the print dialog
    </script>
</body>
</html>
