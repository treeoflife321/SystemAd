<?php
// Include database connection
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

// Check if a reservation needs to be deleted
if (isset($_POST['delete_reservation'])) {
    $oid = $_POST['reservation_id'];
    $delete_query = "DELETE FROM ovrd WHERE oid = ?";
    $stmt = $mysqli->prepare($delete_query);
    $stmt->bind_param("i", $oid);
    if ($stmt->execute()) {
        $successMessage = "Data successfully deleted.";
    } else {
        $successMessage = "Error: Failed to delete reservation.";
    }
    $stmt->close();
    // Redirect back to the same page to prevent form resubmission
    header("Location: admin_ol.php" . (isset($aid) ? '?aid=' . $aid : ''));
    exit();
}
?>
<?php
// Include database connection
include 'config.php';

// Initialize variables for search parameters
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
    // Convert the startDate to Y-m-d format for comparison
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
    <title>Overdued Books</title>
    <link rel="icon" type="image/x-icon" href="css/pics/logop.png">
    <link rel="stylesheet" href="css/admin_srch.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        <a href="admin_attd.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Library Logs</a>
        <a href="admin_stat.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">User Statistics</a>
        <a href="admin_preq.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Pending Requests</a>
        <a href="admin_brel.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Borrowed Books</a>
        <a href="admin_ob.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item active">Overdue Books</a>
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
            <a href="admin_ob.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item">Overdue Books</a>
            <a href="admin_ol.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item active">Overdue Logs</a>
        </nav>
    </div>

        <!-- Fixed-position date and time display -->
    <div class="fixed-date-time">
        <p>Date: <span id="current-date"><?php echo date("m-d-Y"); ?></span></p>
        <p>Time: <span id="current-time"></span></p>
    </div>

    <div class="content-container">
        <div class="search-bar">
        <h2>Overdue Books Log</h2>
        <form method="get" action="admin_ol.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>">
            <input type="text" name="searchInfo" value="<?php echo htmlspecialchars($searchInfo); ?>" placeholder="Search user info">
            <input type="text" name="searchTitle" value="<?php echo htmlspecialchars($searchTitle); ?>" placeholder="Search book title">
            <label for="start-date">From:</label>
            <input type="date" name="startDate" value="<?php echo htmlspecialchars($startDate); ?>">
            <label for="end-date">To:</label>
            <input type="date" name="endDate" value="<?php echo htmlspecialchars($endDate); ?>">
            <?php if(isset($aid)) echo '<input type="hidden" name="aid" value="'.$aid.'">'; ?>
            <button type="submit"><i class='fas fa-search'></i> Search</button>
        </form>
        </div>

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
            <th style="display: none;">OID</th>
            <th hidden>Actions:</th>
        </tr>
    </thead>
    <tbody>
    <?php
    if ($result && $result->num_rows > 0) {
        $counter = 1;
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $counter++ . "</td>"; // Counter
            echo "<td>" . ($row["uid"] == 0 ? $row["info"] : $row["student_info"]) . "</td>"; // Student Info
            echo "<td>" . $row["book_title"] . "</td>"; // Book Title
            echo "<td>" . $row["due_date"] . "</td>"; // Due Date
            echo "<td>" . $row["fines"] . "</td>"; // Fines
            echo "<td>" . date('m-d-Y', strtotime($row["date_set"])) . "</td>"; // Date Settled
            echo "<td>" . $row["remarks"] . "</td>"; // Remarks
            echo "<td style='display: none;'>" . $row["oid"] . "</td>"; // OID (hidden)
            echo "<td hidden style='text-align: center;'>
                <form id='delete_form_" . $row["oid"] . "' name='delete_form_" . $row["oid"] . "' method='post'>
                    <input type='hidden' name='delete_reservation' value='true'>
                    <input type='hidden' name='reservation_id' value='" . $row["oid"] . "'>
                    <button type='button' class='delete-btn' onclick='deleteReservation(" . $row["oid"] . ")'>
                        <i class='fas fa-trash-alt'></i>
                    </button>
                </form>
                </td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='7'>No records found.</td></tr>";
    }
    ?>
</tbody>
</table>
<br>
<button class="print-button" onclick="printData()"><i class='fas fa-print'></i> Print Data</button>
</div>
<script>
// Function to handle reservation deletion
function deleteReservation(oid) {
    // Show a confirmation dialog
    Swal.fire({
        title: 'Are you sure?',
        text: 'You are about to delete this data.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Submit the form to delete the reservation
            document.forms['delete_form_' + oid].submit();
        }
    });
}
</script>
<script>
// Function to filter the table based on search inputs
function searchTable() {
    var searchInfo = document.getElementById('searchInfo').value.toLowerCase();
    var searchTitle = document.getElementById('searchTitle').value.toLowerCase();
    var startDate = document.getElementById('startDate').value;
    var endDate = document.getElementById('endDate').value;

    var table = document.getElementById('dataTable');
    var rows = table.getElementsByTagName('tr');

    for (var i = 1; i < rows.length; i++) {
        var cells = rows[i].getElementsByTagName('td');
        var showRow = true;

        var studentInfo = cells[1].textContent.toLowerCase();
        if (searchInfo && studentInfo.indexOf(searchInfo) === -1) {
            showRow = false;
        }

        var bookTitle = cells[2].textContent.toLowerCase();
        if (searchTitle && bookTitle.indexOf(searchTitle) === -1) {
            showRow = false;
        }

        var dueDate = cells[3].textContent;
        if (startDate && !compareDates(dueDate, startDate, 'start')) {
            showRow = false;
        }

        var dateSettled = cells[5].textContent;
        if (endDate && !compareDates(dateSettled, endDate, 'end')) {
            showRow = false;
        }

        rows[i].style.display = showRow ? '' : 'none';
    }

    // Update the print button URL with search parameters
    var printButton = document.querySelector('.print-button');
    var printUrl = 'print_ovdtable.php<?php if (isset($aid)) echo "?aid=" . $aid; ?>';
    var params = [];

    if (searchInfo) params.push('searchInfo=' + encodeURIComponent(searchInfo));
    if (searchTitle) params.push('searchTitle=' + encodeURIComponent(searchTitle));
    if (startDate) params.push('startDate=' + encodeURIComponent(startDate));
    if (endDate) params.push('endDate=' + encodeURIComponent(endDate));

    if (params.length > 0) {
        printUrl += (printUrl.includes('?') ? '&' : '?') + params.join('&');
    }

    printButton.addEventListener('click', function () {
        window.open(printUrl, '_blank');
    });
}
// Helper function to compare dates
function compareDates(dateStr, compareDateStr, compareType) {
    var parts = dateStr.split('-');
    var formattedDateStr = parts[2] + '-' + parts[0] + '-' + parts[1];
    var date = new Date(formattedDateStr);
    var compareDate = new Date(compareDateStr);

    if (compareType === 'start') {
        return date >= compareDate;
    } else if (compareType === 'end') {
        return date <= compareDate;
    }
    return false;
}
</script>
<script>
function printData() {
    var searchInfo = document.querySelector('input[name="searchInfo"]').value;
    var searchTitle = document.querySelector('input[name="searchTitle"]').value;
    var startDate = document.querySelector('input[name="startDate"]').value;
    var endDate = document.querySelector('input[name="endDate"]').value;

    var printUrl = 'print_ovdtable.php';
    var params = [];

    if (searchInfo) params.push('searchInfo=' + encodeURIComponent(searchInfo));
    if (searchTitle) params.push('searchTitle=' + encodeURIComponent(searchTitle));
    if (startDate) params.push('startDate=' + encodeURIComponent(startDate));
    if (endDate) params.push('endDate=' + encodeURIComponent(endDate));
    <?php if (isset($admin_name_display)) echo "params.push('name=' + encodeURIComponent('" . $admin_name_display . "'));"; ?>

    if (params.length > 0) {
        printUrl += '?' + params.join('&');
    }

    // Open a new window with the print page
    window.open(printUrl, '_blank');
}
    </script>
<script>
// Function to format date to mm-dd-yyyy
function formatDateString(dateStr) {
    // Check if the date is in yyyy-mm-dd format
    if (/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) {
        var parts = dateStr.split('-');
        return parts[1] + '-' + parts[2] + '-' + parts[0];
    }
    return dateStr; // Return the date as-is if it's not in yyyy-mm-dd format
}

// Apply the date formatting function to all due dates in the table
window.onload = function() {
    var table = document.getElementById('dataTable');
    var rows = table.getElementsByTagName('tr');

    for (var i = 1; i < rows.length; i++) {
        var cells = rows[i].getElementsByTagName('td');
        var dueDateCell = cells[3]; // Due Date column
        var dueDate = dueDateCell.textContent;

        // Format the due date and update the cell
        dueDateCell.textContent = formatDateString(dueDate);
    }
};
</script>
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
</script>
<script>
    // Function to update time
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

    updateTime(); // Call the function to update time immediately
    setInterval(updateTime, 1000); // Update time every second
</script>
</body>
</html>
