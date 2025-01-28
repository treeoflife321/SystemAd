<?php
// Include database connection
include 'config.php';

// Check if 'aid' parameter is present in the URL
if (isset($_GET['aid'])) {
    $aid = $_GET['aid'];
    // Query to fetch the name and username corresponding to the aid
    $query = "SELECT name, username FROM admin WHERE aid = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $aid);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Check if the result is not empty
    if ($result && $result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        $admin_name = $admin['name'];
        $admin_username = $admin['username'];
        // Display the admin name in the input field for 'added_by'
        $admin_name_display = $admin_name;
    } else {
        // Set a default message if admin details are not found
        $admin_name_display = "Unknown";
    }
    // Close statement
    $stmt->close();
}
?>
<?php
function getCurrentFileName() {
    return basename($_SERVER['PHP_SELF']);
}
$currentFile = getCurrentFileName();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory</title>
    <link rel="icon" type="image/x-icon" href="css/pics/logop.png">
    <link rel="stylesheet" href="libr/css/add_bk_inv.css">
    <!-- Include SweetAlert CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .form-container {
            margin-left: 22%;
            max-width: 900px;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        form {
            display: grid;
            grid-template-columns: 1fr 2fr; /* Adjusted for label and input alignment */
            gap: 15px;
        }
        form label {
            grid-column: 1; /* Labels in the first column */
            font-weight: bold;
            margin-bottom: 5px;
            display: flex;
            align-items: center; /* Vertically center text */
        }
        form input, form textarea, form select {
            grid-column: 2; /* Inputs in the second column */
            width: 96%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        textarea {
            resize: vertical;
        }
        .button-container {
            grid-column: 1 / -1; /* Span across all columns */
            display: flex;
            justify-content: space-between; /* Adjust alignment */
            margin-top: 20px;
        }
        .button-container button, .button-container a button {
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .button-container button {
            background-color: #007bff;
            color: white;
        }
        .button-container a button {
            background-color: red;
            color: white;
        }
    </style>
</head>
<body class="bg">
<div class="sidebar">
    <span style="margin-left: 25%;"><img src="css/pics/logop.png" alt="Logo" class="logo"></span>
        <?php
        // Check if $admin_username_display is set
        if(isset($admin_username)) {
            // Add spaces before the admin username to align it
            echo '<div class="hell">Admin: ' . $admin_username . '</span></div>';
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
        <a href="admin_ob.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Overdue Books</a>
        <div class="sidebar-item dropdown <?php if (strpos($currentFile, 'admin_add_asts.php') !== false || strpos($currentFile, 'add_bk_inv.php') !== false) echo 'show'; ?>">
            <a href="#" class="dropdown-link <?php if (strpos($currentFile, 'admin_add_asts.php') !== false || strpos($currentFile, 'add_bk_inv.php') !== false) echo 'active'; ?>" onclick="toggleDropdown(event)">Inventory</a>
            <div class="dropdown-content <?php if (strpos($currentFile, 'admin_add_asts.php') !== false || strpos($currentFile, 'add_bk_inv.php') !== false) echo 'show'; ?>">
                <a href="add_bk_inv.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item <?php if ($currentFile == 'add_bk_inv.php') echo 'active'; ?>">Books</a>
                <a href="admin_add_asts.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item <?php if ($currentFile == 'admin_add_asts.php') echo 'active'; ?>">Assets</a>
            </div>
        </div>
        <a href="login.php" class="sidebar-item logout-btn">Logout</a>
    </div>

    <div class="content">
        <nav class="secondary-navbar">
            <a href="add_bk_inv.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item active">Add</a>
        </nav>
    </div>
    
    <div class="fixed-date-time">
        <p>Date: <span id="current-date"><?php echo date("m-d-Y"); ?></span></p>
        <p>Time: <span id="current-time"></span></p>
    </div>

    <div class="content-container">
    <center><h2 style="margin-top: 6%;">Add Book to Inventory</h2></center>
        <div class="form-container">
            <!-- PHP code for handling form submission and displaying alerts -->
            <?php
            // Initialize alert message
            $alertMessage = '';

            // Check if form is submitted
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                // Retrieve form data
                $title = $_POST['title'];
                $author = $_POST['author'];
                $year = $_POST['year'];
                $genre = $_POST['genre'];
                $dew_num = $_POST['dew_num'];
                $ISBN = $_POST['ISBN'];
                $shlf_num = $_POST['shlf_num'];
                $condition = $_POST['condition'];
                $count = $_POST['count'];
                $add_info = $_POST['additional_info'];
                $status = $_POST['status'];
                $publisher = $_POST['publisher'];
                $edition = $_POST['edition'];
                $language = $_POST['language'];
                $physical_description = $_POST['physical_description'];
                $series_title = $_POST['series_title'];
                $subject_keywords = $_POST['subject_keywords'];
                $accession_number = $_POST['accession_number'];
                $barcode = $_POST['barcode'];
                $acquisition_date = $_POST['acquisition_date'];
                $location = $_POST['location'];
                $cataloging_notes = $_POST['cataloging_notes'];
                $added_by = $_POST['added_by'];
            
                // Prepare SQL insert statement
                $query = "INSERT INTO inventory (title, author, year, genre, dew_num, ISBN, shlf_num, cndtn, add_info, status, added_by, publisher, edition, language, physical_description, series_title, subject_keywords, accession_number, barcode, acquisition_date, location, cataloging_notes) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $mysqli->prepare($query);
            
                // Bind parameters and execute query
                $stmt->bind_param("ssssssssssssssssssssss", $title, $author, $year, $genre, $dew_num, $ISBN, $shlf_num, $condition, $add_info, $status, $added_by, $publisher, $edition, $language, $physical_description, $series_title, $subject_keywords, $accession_number, $barcode, $acquisition_date, $location, $cataloging_notes);
                $successful_inserts = 0;

                for ($i = 0; $i < $count; $i++) {
                    $stmt->execute();
                    if ($stmt->affected_rows > 0) {
                        $successful_inserts++;
                    }
                }

                // Check if all insertions were successful
                if ($successful_inserts == $count) {
                    $alertMessage = "Book(s) added successfully.";
                    // Show success message using SweetAlert
                    echo "<script>
                            document.addEventListener('DOMContentLoaded', function() {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success',
                                    text: '{$alertMessage}',
                                });
                            });
                        </script>";
                } else {
                    $alertMessage = "Error adding books: " . $mysqli->error;
                    // Show error message using SweetAlert
                    echo "<script>
                            document.addEventListener('DOMContentLoaded', function() {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: '{$alertMessage}',
                                });
                            });
                        </script>";
                }

                // Close statement
                $stmt->close();
            }
            ?>
            <!-- Form to add a book -->
            <form action="add_bk_inv.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" method="POST">
                <label for="title">Book Title</label>
                <input type="text" id="title" name="title" placeholder="Enter Book Title" required>

                <label for="author">Author</label>
                <input type="text" id="author" name="author" placeholder="Enter Author" required>

                <label for="year">Year Published</label>
                <input type="text" id="year" name="year" placeholder="Enter Year Published" required>

                <label for="genre">Genre</label>
                <input type="text" id="genre" name="genre" placeholder="Enter Genre">

                <label for="dew_num">Dewey Decimal Number</label>
                <input type="text" id="dew_num" name="dew_num" placeholder="Enter Dewey Decimal Number" required>

                <label for="ISBN">ISBN</label>
                <input type="text" id="ISBN" name="ISBN" placeholder="Enter ISBN" required>

                <label for="shlf_num">Shelf Number</label>
                <input type="text" id="shlf_num" name="shlf_num" placeholder="Enter Shelf Number" required>

                <label for="condition">Condition</label>
                <input type="text" id="condition" name="condition" placeholder="Enter Book Condition" required>

                <label for="count">Number of Copies</label>
                <input type="number" id="count" name="count" placeholder="Enter Number of Copies" required>

                <label for="additional_info">Additional Information</label>
                <input type="text" id="additional_info" name="additional_info" placeholder="Enter Additional Information">

                <label for="publisher">Publisher</label>
                <input type="text" id="publisher" name="publisher" placeholder="Enter Publisher">

                <label for="edition">Edition</label>
                <input type="text" id="edition" name="edition" placeholder="Enter Edition">

                <label for="language">Language</label>
                <input type="text" id="language" name="language" placeholder="Enter Language">

                <label for="physical_description">Physical Description</label>
                <textarea id="physical_description" name="physical_description" placeholder="Enter Physical Description"></textarea>

                <label for="series_title">Series Title</label>
                <input type="text" id="series_title" name="series_title" placeholder="Enter Series Title">

                <label for="subject_keywords">Subject Keywords</label>
                <input type="text" id="subject_keywords" name="subject_keywords" placeholder="Enter Subject Keywords">

                <label for="accession_number">Accession Number</label>
                <input type="text" id="accession_number" name="accession_number" placeholder="Enter Accession Number" required>

                <label for="barcode">Barcode</label>
                <input type="text" id="barcode" name="barcode" placeholder="Enter Barcode">

                <label for="acquisition_date">Acquisition Date</label>
                <input type="date" id="acquisition_date" name="acquisition_date" required>

                <label for="location">Location</label>
                <input type="text" id="location" name="location" placeholder="Enter Location">

                <label for="cataloging_notes">Cataloging Notes</label>
                <textarea id="cataloging_notes" name="cataloging_notes" placeholder="Enter Cataloging Notes"></textarea>

                <label for="status">Book Status</label>
                <select id="status" name="status">
                    <option value="Available">Available</option>
                    <option value="Not Reservable">Not Reservable</option>
                </select>

                <label for="added_by">Added By:</label>
                <input type="text" id="added_by" name="added_by" placeholder="Enter who added the book" 
                    required value="<?php echo htmlspecialchars($admin_name_display); ?>" readonly>

                <div class="button-container">
                    <a href="bk_inv.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>">
                        <button type="button">Cancel</button>
                    </a>
                    <button type="submit">Add Book</button>
                </div>

            </form>
        </div>
    </div>
    <!-- Include SweetAlert JavaScript library -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
