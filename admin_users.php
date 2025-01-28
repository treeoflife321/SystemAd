<?php
include 'config.php';

// Check if 'aid' parameter is present in the URL
if (isset($_GET['aid'])) {
    $aid = $_GET['aid'];
    // Query to fetch the username corresponding to the aid
    $query = "SELECT username FROM admin WHERE aid = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $aid);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the result is not empty
    if ($result && $result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        $admin_username = $admin['username'];
        // Display the admin username in the sidebar
        $admin_username_display = $admin_username;
    } else {
        // Display a default message if admin username is not found
        $admin_username_display = "Username";
    }
    // Close statement
    $stmt->close();
}

// Delete functionality
if (isset($_POST['delete_uid'])) {
    $delete_uid = $_POST['delete_uid'];

    // Query to delete user record
    $query = "DELETE FROM users WHERE uid = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $delete_uid);

    if ($stmt->execute()) {
        // Deletion successful
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@10'></script>"; // Include SweetAlerts library
        echo "<script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'The user has been deleted successfully.',
                        icon: 'success'
                    }).then(() => {
                        window.location.href = 'admin_users.php?aid=$aid';
                    });
                });
              </script>";
    } else {
        // Deletion failed
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@10'></script>"; // Include SweetAlerts library
        echo "<script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to delete user.',
                        icon: 'error'
                    });
                });
              </script>";
    }
    $stmt->close();
}

// Update status functionality
if (isset($_POST['action']) && isset($_POST['checked_uids'])) {
    $action = $_POST['action'];
    $checked_uids = explode(',', $_POST['checked_uids']);
    $status = ($action === 'activate') ? 'Active' : 'Disabled';

    if (!empty($checked_uids)) {
        // Prepare the placeholders for the IN clause
        $placeholders = implode(',', array_fill(0, count($checked_uids), '?'));

        // Query to update the status of selected users
        $query = "UPDATE users SET status = ? WHERE uid IN ($placeholders)";
        $stmt = $mysqli->prepare($query);

        // Bind parameters
        $types = str_repeat('i', count($checked_uids));
        $stmt->bind_param("s" . $types, $status, ...$checked_uids);

        if ($stmt->execute()) {
            // Update successful
            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@10'></script>"; // Include SweetAlerts library
            echo "<script>
            document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        title: 'Success!',
                        text: 'The status has been updated successfully.',
                        icon: 'success'
                    }).then(() => {
                        window.location.href = 'admin_users.php?aid=$aid';
                    });
                });
                  </script>";
        } else {
            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@10'></script>"; // Include SweetAlerts library
            echo "<script>
            document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to update status.',
                        icon: 'error'
                    });
                });
                  </script>";
        }
        $stmt->close();
    } else {
        // Handle case where checked_uids is not an array or is empty
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@10'></script>"; // Include SweetAlerts library
        echo "<script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        title: 'Error!',
                        text: 'No users selected.',
                        icon: 'error'
                    });
                });
              </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Accounts</title>
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
            echo '<div class="hell">Admin: ' . $admin_username_display . '</span></div>';
        } else {
            // Display a default message if admin username is not found
            echo '<div>Admin: <br>Username</div>';
        }
        ?>
<a href="admin_dash.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Dashboard</a>
<a href="admin_pf.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">User Credentials</a>
<a href="admin_users.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item active">Accounts</a>
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
    <a href="login.php" class="sidebar-item logout-btn">Logout</a>
</div>

<div class="content">
    <nav class="secondary-navbar">
        <a href="admin_srch.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item">Search Librarian</a>
        <a href="admin_users.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item active">Search Users</a>
    </nav>
</div>

<div class="fixed-date-time">
        <p>Date: <span id="current-date"><?php echo date("m-d-Y"); ?></span></p>
        <p>Time: <span id="current-time"></span></p>
    </div>
    
<div class="content-container">
<div class="search-bar">
    <h1 style="color: black;">Search User Accounts</h1>
    
    <!-- General search by info or username -->
    <input type="text" id="searchInput" placeholder="Search by Info or Username...">
    
    <!-- Specific filters -->
    <input type="text" id="idNumberFilter" placeholder="Search by ID Number...">
    <select id="courseFilter">
    <option value="">Choose Course:</option>
    <option value="BSESM">BSESM</option>
    <option value="BSIT">BSIT</option>
    <option value="BSMET">BSMET</option>
    <option value="BSNAME">BSNAME</option>
    <option value="BSTCM">BSTCM</option>
    </select>
    <select id="yearLevelFilter">
        <option value="">Choose Year Level:</option>
        <option value="1st Year">1st Year</option>
        <option value="2nd Year">2nd Year</option>
        <option value="3rd Year">3rd Year</option>
        <option value="4th Year">4th Year</option>
        <option value="5th Year">5th Year</option>
    </select>
    <select id="genderFilter">
        <option value="">Choose Gender:</option>
        <option value="Male">Male</option>
        <option value="Female">Female</option>
        <option value="Non-Binary">Non-Binary</option>
    </select>
    <select id="userTypeFilter">
        <option value="">Choose User Type:</option>
        <option value="Student">Student</option>
        <option value="Faculty">Faculty</option>
        <option value="Staff">Staff</option>
    </select>
    <select id="statusFilter">
        <option value="">Choose Status:</option>
        <option value="Active">Active</option>
        <option value="Pending">Pending</option>
        <option value="Disabled">Disabled</option>
    </select>
    
    <!-- Search and clear buttons -->
    <button type="button" onclick="applyFilters()"><i class='fas fa-search'></i> Search</button>
    <button type="button" onclick="clearFilters()"><i class="fa-regular fa-circle-xmark"></i> Clear</button>
</div>

    <a href="admin_adu.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="add-lib"><i class='fas fa-plus'></i> Add User</a>

    <div class="table-actions">
        <button type="button" onclick="checkAll()"><i class="fa-regular fa-square-check"></i> Check All</button>
        <button type="button" onclick="uncheckAll()"><i class="fa-regular fa-rectangle-xmark"></i> Uncheck All</button>
    </div>

    <table style="margin-top: 20px;">
        <thead>
            <tr>
                <th>#</th>
                <th>User Info</th>
                <th>ID #</th>
                <th>Year Level</th>
                <th>Birthdate</th>
                <th>Gender</th>
                <th>Contact #</th>
                <th>Username</th>
                <th>User Type</th>
                <th>Status</th>
                <th colspan="1">Actions:</th>
                <th>Check</th>
            </tr>
        </thead>
        <tbody id="userTableBody">
    <?php
    // Check if a search or filter is applied
    $hasFilters = isset($_GET['searchInput']) || isset($_GET['idNumberFilter']) || isset($_GET['courseFilter']) || isset($_GET['yearLevelFilter']) || isset($_GET['genderFilter']) || isset($_GET['userTypeFilter']) || isset($_GET['statusFilter']);

    if ($hasFilters) {
        // Base query to fetch user records
        $query = "SELECT * FROM users WHERE 1=1";

        // Append conditions based on filters
        if (!empty($_GET['searchInput'])) {
            $searchInput = $mysqli->real_escape_string($_GET['searchInput']);
            $query .= " AND (info LIKE '%$searchInput%' OR username LIKE '%$searchInput%')";
        }
        if (!empty($_GET['idNumberFilter'])) {
            $idNumberFilter = $mysqli->real_escape_string($_GET['idNumberFilter']);
            $query .= " AND idnum LIKE '%$idNumberFilter%'";
        }
        if (!empty($_GET['courseFilter'])) {
            $courseFilter = $mysqli->real_escape_string($_GET['courseFilter']);
            // Use case-insensitive matching to find the course in the 'info' column
            $query .= " AND LOWER(info) LIKE LOWER('%$courseFilter%')";
        }            
        if (!empty($_GET['yearLevelFilter'])) {
            $yearLevelFilter = $mysqli->real_escape_string($_GET['yearLevelFilter']);
            $query .= " AND year_level = '$yearLevelFilter'";
        }
        if (!empty($_GET['genderFilter'])) {
            $genderFilter = $mysqli->real_escape_string($_GET['genderFilter']);
            $query .= " AND gender = '$genderFilter'";
        }
        if (!empty($_GET['userTypeFilter'])) {
            $userTypeFilter = $mysqli->real_escape_string($_GET['userTypeFilter']);
            $query .= " AND user_type = '$userTypeFilter'";
        }
        if (!empty($_GET['statusFilter'])) {
            $statusFilter = $mysqli->real_escape_string($_GET['statusFilter']);
            $query .= " AND status = '$statusFilter'";
        }

        // Execute the query
        $result = $mysqli->query($query);

        // Counter for the first column
        $counter = 1;

        // Check if results are found
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $counter++ . "</td>";
                echo "<td>" . $row['info'] . "</td>";
                echo "<td>" . $row['idnum'] . "</td>";
                echo "<td>" . $row['year_level'] . "</td>";
                echo "<td>" . $row['birthdate'] . "</td>";
                echo "<td>" . $row['gender'] . "</td>";
                echo "<td>" . $row['contact'] . "</td>";
                echo "<td>" . $row['username'] . "</td>";
                echo "<td>" . $row['user_type'] . "</td>";
                echo "<td>" . $row['status'] . "</td>";
                echo '<td style="text-align:center;">';
                echo '<button class="edit-btn" onclick="editUser(' . $row['uid'] . ',' . $aid . ')"><i class="fas fa-edit"></i></button>';
                echo '</td>';
                echo '<td style="text-align:center;">';
                echo '<input type="checkbox" name="user_check[]" value="' . $row['uid'] . '">';
                echo '</td>';
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='12' style='text-align:center;'>No users found.</td></tr>";
        }
    } else {
        // If no filters or search input, display a default empty message
        echo "<tr><td colspan='12' style='text-align:center;'>Please use the search bar to find users.</td></tr>";
    }
    ?>
</tbody>
    </table>

    <div class="status-actions">
        <button type="button" onclick="updateStatus('activate')">Activate</button>
        <button type="button" id="disable" onclick="updateStatus('disable')">Disable</button>
    </div>
</div>

<form id="editForm" action="admin_ued.php" method="post">
    <input type="hidden" name="selected_uid" id="selected_uid">
    <input type="hidden" name="current_aid" id="current_aid">
</form>

<!-- Form for delete -->
<form id="deleteForm" method="post">
    <input type="hidden" name="delete_uid" id="delete_uid">
</form>

<!-- Form for status update -->
<form id="statusForm" method="post">
    <input type="hidden" name="action" id="statusAction">
    <input type="hidden" name="checked_uids" id="checkedUids">
</form>

<script>
function editUser(selectedUid, currentAid) {
    window.location.href = 'admin_ued.php?uid=' + selectedUid + '&aid=' + currentAid;
}

function deleteUser(deleteUid) {
    Swal.fire({
        title: 'Are you sure you want to delete this user?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete_uid').value = deleteUid;
            document.getElementById('deleteForm').submit();
        }
    });
}

// Function to check all checkboxes
function checkAll() {
    var checkboxes = document.getElementsByName('user_check[]');
    for (var checkbox of checkboxes) {
        checkbox.checked = true;
    }
}

// Function to uncheck all checkboxes
function uncheckAll() {
    var checkboxes = document.getElementsByName('user_check[]');
    for (var checkbox of checkboxes) {
        checkbox.checked = false;
    }
}

function applyFilters() {
    const searchInput = document.getElementById('searchInput').value;
    const idNumberFilter = document.getElementById('idNumberFilter').value;
    const yearLevelFilter = document.getElementById('yearLevelFilter').value;
    const genderFilter = document.getElementById('genderFilter').value;
    const userTypeFilter = document.getElementById('userTypeFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    const courseFilter = document.getElementById('courseFilter').value;

    const urlParams = new URLSearchParams(window.location.search);
    if (searchInput) urlParams.set('searchInput', searchInput);
    if (idNumberFilter) urlParams.set('idNumberFilter', idNumberFilter);
    if (yearLevelFilter) urlParams.set('yearLevelFilter', yearLevelFilter);
    if (genderFilter) urlParams.set('genderFilter', genderFilter);
    if (userTypeFilter) urlParams.set('userTypeFilter', userTypeFilter);
    if (statusFilter) urlParams.set('statusFilter', statusFilter);
    if (courseFilter) urlParams.set('courseFilter', courseFilter);

    window.location.search = urlParams.toString();
}

function clearFilters() {
    const urlParams = new URLSearchParams(window.location.search);
    const preservedParams = {};

    // Add essential parameters to be preserved
    if (urlParams.has('aid')) {
        preservedParams['aid'] = urlParams.get('aid');
    }
    if (urlParams.has('user')) {
        preservedParams['user'] = urlParams.get('user');
    }

    // Create a new query string with preserved parameters
    const newQueryString = new URLSearchParams(preservedParams).toString();

    // Update the URL without unwanted parameters
    window.location.search = newQueryString ? `?${newQueryString}` : '';
}

document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);

    const searchInput = urlParams.get('searchInput');
    const idNumberFilter = urlParams.get('idNumberFilter');
    const yearLevelFilter = urlParams.get('yearLevelFilter');
    const genderFilter = urlParams.get('genderFilter');
    const userTypeFilter = urlParams.get('userTypeFilter');
    const statusFilter = urlParams.get('statusFilter');
    const courseFilter = urlParams.get('courseFilter');

    if (searchInput) document.getElementById('searchInput').value = searchInput;
    if (idNumberFilter) document.getElementById('idNumberFilter').value = idNumberFilter;
    if (yearLevelFilter) document.getElementById('yearLevelFilter').value = yearLevelFilter;
    if (genderFilter) document.getElementById('genderFilter').value = genderFilter;
    if (userTypeFilter) document.getElementById('userTypeFilter').value = userTypeFilter;
    if (statusFilter) document.getElementById('statusFilter').value = statusFilter;
    if (courseFilter) document.getElementById('courseFilter').value = courseFilter;
});

// Function to update status
function updateStatus(action) {
    // Get all rows in the table body
    var tableBody = document.getElementById("userTableBody");
    var rows = tableBody.getElementsByTagName("tr");
    var checkedUids = [];

    for (var i = 0; i < rows.length; i++) {
        // Check if the row is visible (not filtered out)
        if (rows[i].style.display !== "none") {
            // Find the checkbox in the row
            var checkbox = rows[i].querySelector('input[name="user_check[]"]');
            if (checkbox && checkbox.checked) {
                // Add the value (user ID) to the array
                checkedUids.push(checkbox.value);
            }
        }
    }

    if (checkedUids.length > 0) {
        // Set the action and checked user IDs in the form
        document.getElementById("statusAction").value = action;
        document.getElementById("checkedUids").value = checkedUids.join(",");
        document.getElementById("statusForm").submit();
    } else {
        Swal.fire({
            title: 'Error!',
            text: 'No users selected or visible for the update.',
            icon: 'error'
        });
    }
}

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
