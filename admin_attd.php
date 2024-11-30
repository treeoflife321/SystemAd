<?php
function checkAdminSession() {
    if (!isset($_GET['aid']) || empty($_GET['aid'])) {
        header("Location: login.php");
        exit;
    }
}

// Call the function at the top of your files
checkAdminSession();

// Include database connection
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
        $admin_username_display = $admin_username;
    } else {
        $admin_username_display = "Username";
    }
    $stmt->close();
}

// Function to update `idnum` if empty
function updateIdnumIfEmpty($mysqli) {
    $currentDate = date("m-d-Y");

    // Fetch all rows for the current date
    $query = "SELECT id, info FROM chkin WHERE date = ? AND idnum = '' ";
    $stmt = $mysqli->prepare($query);
    if (!$stmt) {
        error_log("Error preparing statement: " . $mysqli->error);
        return;
    }
    $stmt->bind_param("s", $currentDate);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check for rows with empty idnum
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $info = $row['info'];
            $id = $row['id'];

            // Find the most recent non-empty idnum for the same info
            $subQuery = "
                SELECT idnum 
                FROM chkin 
                WHERE info = ? AND idnum IS NOT NULL 
                ORDER BY id ASC
                LIMIT 1
            ";
            $subStmt = $mysqli->prepare($subQuery);
            if (!$subStmt) {
                error_log("Error preparing sub-statement: " . $mysqli->error);
                continue;
            }
            $subStmt->bind_param("s", $info);
            $subStmt->execute();
            $subResult = $subStmt->get_result();

            if ($subResult->num_rows > 0) {
                $match = $subResult->fetch_assoc();
                $idnum = $match['idnum'];

                // Update the current row with the found idnum
                $updateQuery = "UPDATE chkin SET idnum = ? WHERE id = ?";
                $updateStmt = $mysqli->prepare($updateQuery);
                if (!$updateStmt) {
                    error_log("Error preparing update statement: " . $mysqli->error);
                    continue;
                }
                $updateStmt->bind_param("si", $idnum, $id);
                if (!$updateStmt->execute()) {
                    error_log("Error executing update statement: " . $mysqli->error);
                }
                $updateStmt->close();
            }
            $subStmt->close();
        }
    } else {
        error_log("No rows found with empty idnum for the current date.");
    }

    $stmt->close();
}

// Function to update `user_type` if empty
function updateUserTypeIfEmpty($mysqli) {
    $currentDate = date("m-d-Y");

    // Fetch all rows for the current date with empty user_type
    $query = "SELECT id, info FROM chkin WHERE date = ? AND (user_type = '' OR user_type IS NULL)";
    $stmt = $mysqli->prepare($query);
    if (!$stmt) {
        error_log("Error preparing statement: " . $mysqli->error);
        return;
    }
    $stmt->bind_param("s", $currentDate);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check for rows with empty user_type
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $info = $row['info'];
            $id = $row['id'];

            // Find the most recent non-empty user_type for the same info
            $subQuery = "
                SELECT user_type 
                FROM chkin 
                WHERE info = ? AND (user_type IS NOT NULL AND user_type != '') 
                ORDER BY id ASC
                LIMIT 1
            ";
            $subStmt = $mysqli->prepare($subQuery);
            if (!$subStmt) {
                error_log("Error preparing sub-statement: " . $mysqli->error);
                continue;
            }
            $subStmt->bind_param("s", $info);
            $subStmt->execute();
            $subResult = $subStmt->get_result();

            if ($subResult->num_rows > 0) {
                $match = $subResult->fetch_assoc();
                $user_type = $match['user_type'];

                // Update the current row with the found user_type
                $updateQuery = "UPDATE chkin SET user_type = ? WHERE id = ?";
                $updateStmt = $mysqli->prepare($updateQuery);
                if (!$updateStmt) {
                    error_log("Error preparing update statement: " . $mysqli->error);
                    continue;
                }
                $updateStmt->bind_param("si", $user_type, $id);
                if (!$updateStmt->execute()) {
                    error_log("Error executing update statement: " . $mysqli->error);
                }
                $updateStmt->close();
            }
            $subStmt->close();
        }
    } else {
        error_log("No rows found with empty user_type for the current date.");
    }

    $stmt->close();
}

// Function to update `year_level` if empty
function updateYearLevelIfEmpty($mysqli) {
    $currentDate = date("m-d-Y");

    // Fetch all rows for the current date with empty year_level
    $query = "SELECT id, info FROM chkin WHERE date = ? AND (year_level = '' OR year_level IS NULL)";
    $stmt = $mysqli->prepare($query);
    if (!$stmt) {
        error_log("Error preparing statement: " . $mysqli->error);
        return;
    }
    $stmt->bind_param("s", $currentDate);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check for rows with empty year_level
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $info = $row['info'];
            $id = $row['id'];

            // Find the most recent non-empty year_level for the same info
            $subQuery = "
                SELECT year_level 
                FROM chkin 
                WHERE info = ? AND (year_level IS NOT NULL AND year_level != '') 
                ORDER BY id ASC
                LIMIT 1
            ";
            $subStmt = $mysqli->prepare($subQuery);
            if (!$subStmt) {
                error_log("Error preparing sub-statement: " . $mysqli->error);
                continue;
            }
            $subStmt->bind_param("s", $info);
            $subStmt->execute();
            $subResult = $subStmt->get_result();

            if ($subResult->num_rows > 0) {
                $match = $subResult->fetch_assoc();
                $year_level = $match['year_level'];

                // Update the current row with the found year_level
                $updateQuery = "UPDATE chkin SET year_level = ? WHERE id = ?";
                $updateStmt = $mysqli->prepare($updateQuery);
                if (!$updateStmt) {
                    error_log("Error preparing update statement: " . $mysqli->error);
                    continue;
                }
                $updateStmt->bind_param("si", $year_level, $id);
                if (!$updateStmt->execute()) {
                    error_log("Error executing update statement: " . $mysqli->error);
                }
                $updateStmt->close();
            }
            $subStmt->close();
        }
    } else {
        error_log("No rows found with empty year_level for the current date.");
    }

    $stmt->close();
}

// Function to update `gender` if empty
function updateGenderIfEmpty($mysqli) {
    $currentDate = date("m-d-Y");

    // Fetch all rows for the current date with empty gender
    $query = "SELECT id, info FROM chkin WHERE date = ? AND (gender = '' OR gender IS NULL)";
    $stmt = $mysqli->prepare($query);
    if (!$stmt) {
        error_log("Error preparing statement: " . $mysqli->error);
        return;
    }
    $stmt->bind_param("s", $currentDate);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check for rows with empty gender
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $info = $row['info'];
            $id = $row['id'];

            // Find the most recent non-empty gender for the same info
            $subQuery = "
                SELECT gender 
                FROM chkin 
                WHERE info = ? AND (gender IS NOT NULL AND gender != '') 
                ORDER BY id ASC
                LIMIT 1
            ";
            $subStmt = $mysqli->prepare($subQuery);
            if (!$subStmt) {
                error_log("Error preparing sub-statement: " . $mysqli->error);
                continue;
            }
            $subStmt->bind_param("s", $info);
            $subStmt->execute();
            $subResult = $subStmt->get_result();

            if ($subResult->num_rows > 0) {
                $match = $subResult->fetch_assoc();
                $gender = $match['gender'];

                // Update the current row with the found gender
                $updateQuery = "UPDATE chkin SET gender = ? WHERE id = ?";
                $updateStmt = $mysqli->prepare($updateQuery);
                if (!$updateStmt) {
                    error_log("Error preparing update statement: " . $mysqli->error);
                    continue;
                }
                $updateStmt->bind_param("si", $gender, $id);
                if (!$updateStmt->execute()) {
                    error_log("Error executing update statement: " . $mysqli->error);
                }
                $updateStmt->close();
            }
            $subStmt->close();
        }
    } else {
        error_log("No rows found with empty gender for the current date.");
    }

    $stmt->close();
}

// Call the function to handle empty `user_type`
updateUserTypeIfEmpty($mysqli);

// Call the function to handle empty `idnum`
updateIdnumIfEmpty($mysqli);

// Call the function to handle empty `year_level`
updateYearLevelIfEmpty($mysqli);

// Call the function to handle empty `gender`
updateGenderIfEmpty($mysqli);
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
        <a href="admin_pf.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Profile</a>
        <a href="admin_srch.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Accounts</a>
        <a href="admin_attd.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item active">Library Logs</a>
        <a href="admin_stat.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">User Statistics</a>
        <a href="admin_wres.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Walk-in-Borrow</a>
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
            <a href="admin_attd.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item active">Attendance</a>
            <a href="liblogs.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item">User Logs</a>
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
            <h1 style="color: black;">Library Users Today:</h1>
            <button type="button" onclick="addUserPopup()"><i class='fas fa-plus'></i> New Library User</button>
        </div>
        <?php
        // Get the current date
        $currentDate = date("m-d-Y");

        // Include database connection configuration
        include 'config.php';

        // Prepare and execute SQL query
        $query = "SELECT * FROM chkin WHERE date = '$currentDate'";
        $result = $mysqli->query($query);

        // Check if there are results
        if ($result && $result->num_rows > 0) {
            echo '<table id="dataTable">';
            echo '<thead>';
            echo '<tr><th>#</th><th>User Info</th><th>ID Number</th><th>User Type</th><th>Year Level</th><th>Gender</th><th>Date</th><th>Time In</th><th>Time Out</th><th>Purpose</th><th>Edit:</th></tr>';
            echo '</thead>';
            echo '<tbody id="dataTableBody">'; // Added id to tbody

            // Loop through all rows and fetch the data
            $counter = 0; // Initialize counter
            while ($row = $result->fetch_assoc()) {
                $counter++; // Increment counter
                echo '<tr>';
                echo '<td class="counter">' . $counter . '</td>'; // Display counter
                echo '<td hidden>' . $row['id'] . '</td>';
                echo '<td>' . $row['info'] . '</td>';
                echo '<td>' . $row['idnum'] . '</td>';
                echo '<td>' . $row['user_type'] . '</td>';
                echo '<td>' . $row['year_level'] . '</td>';
                echo '<td>' . $row['gender'] . '</td>';
                echo '<td>' . $row['date'] . '</td>';
                echo '<td>' . $row['timein'] . '</td>';
                echo '<td>' . ($row['timeout'] ? $row['timeout'] : 'N/A') . '</td>';
                echo '<td>' . $row['purpose'] . '</td>';
                echo '<td style="text-align: center;">';
                echo '<button class="edit-btn" onclick="editBook(' . $row['id'] . ')"><i class="fas fa-edit"></i></button>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<p>No data found for the current date.</p>';
        }

        // Free the result set
        if ($result) {
            $result->free();
        }

        // Close the database connection
        $mysqli->close();
        ?>
    </div>
<script>
function addUserPopup() {
    Swal.fire({
        title: 'Add Library User',
        html: `<input type="text" id="userInfo" class="swal2-input" placeholder="User Info">
               <select id="userType" class="swal2-input">
                   <option value="" disabled selected>User Type</option>
                   <option value="Student">Student</option>
                   <option value="Faculty">Faculty</option>
                   <option value="Staff">Staff</option>
                   <option value="Visitor">Visitor</option>
               </select>
               <select id="purpose" class="swal2-input">
                   <option value="" disabled selected>Purpose</option>
                   <option value="Study">Study</option>
                   <option value="Research">Research</option>
                   <option value="Printing">Printing</option>
                   <option value="Clearance">Clearance</option>
                   <option value="Borrow">Borrow Book(s)</option>
                   <option value="Return">Return Book(s)</option>
               </select>`,
        focusConfirm: false,
        preConfirm: () => {
            const userInfo = Swal.getPopup().querySelector('#userInfo').value;
            const userType = Swal.getPopup().querySelector('#userType').value;
            const purpose = Swal.getPopup().querySelector('#purpose').value;
            if (!userInfo || !userType || !purpose) {
                Swal.showValidationMessage(`Please enter all fields`);
            }
            return { userInfo: userInfo, userType: userType, purpose: purpose };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const { userInfo, userType, purpose } = result.value;
            $.ajax({
                url: 'add_chkin.php',
                type: 'POST',
                data: {
                    userInfo: userInfo,
                    userType: userType,
                    purpose: purpose
                },
                success: function(response) {
                    console.log(response); // Debugging line to check the response
                    if(response.trim() === 'success') {
                        Swal.fire('Saved!', 'Library user has been added.', 'success')
                        .then(() => {
                            // Reload the page to see the new data
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error!', 'There was a problem adding the user.', 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error!', 'There was a problem connecting to the server.', 'error');
                }
            });
        }
    });
}
</script>
    <!-- JavaScript code to update date and time -->
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
<script>
    function editBook(id) {
        // Get the current URL
        var url = window.location.href;
        // Find the last occurrence of '/'
        var lastSlashIndex = url.lastIndexOf('/');
        // Extract the base URL
        var baseUrl = url.substring(0, lastSlashIndex);
        // Redirect to bk_edit.php with the parameters
        window.location.href = baseUrl + '/edit_logs.php?aid=<?php echo isset($aid) ? $aid : ''; ?>&id=' + id;
    }
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
</body>
</html>
