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

// Check if 'bid' parameter is present in the URL
if(isset($_GET['bid'])) {
    $bid = $_GET['bid'];
    // Query to fetch the book data corresponding to the bid
    $query = "SELECT * FROM inventory WHERE bid = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $bid);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Check if the result is not empty
    if ($result && $result->num_rows > 0) {
        $book = $result->fetch_assoc();
        // Assign book data to variables for display
        $title = $book['title'];
        $author = $book['author'];
        $year = $book['year'];
        $genre = $book['genre'];
        $dew_num = $book['dew_num'];
        $ISBN = $book['ISBN'];
        $shlf_num = $book['shlf_num'];
        $cndtn = $book['cndtn'];
        $additional_info = $book['add_info'];
        $status = $book['status'];
        $added_by = $book['added_by'];
        $publisher = $book['publisher'];
        $edition = $book['edition'];
        $language = $book['language'];
        $physical_description = $book['physical_description'];
        $series_title = $book['series_title'];
        $subject_keywords = $book['subject_keywords'];
        $accession_number = $book['accession_number'];
        $barcode = $book['barcode'];
        $acquisition_date = $book['acquisition_date'];
        $location = $book['location'];
        $cataloging_notes = $book['cataloging_notes'] ? $book['cataloging_notes'] : '';
    } else {
        // Redirect back to bk_inv.php if book data is not found
        header("Location: bk_inv.php?aid=" . $aid);
        exit();
    }
    // Close statement
    $stmt->close();
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Extract data from the form
    $bid = $_POST['bid'];
    $title = $_POST['title'];
    $author = $_POST['author'];
    $year = $_POST['year'];
    $genre = $_POST['genre'];
    $dew_num = $_POST['dew_num'];
    $ISBN = $_POST['ISBN'];
    $shlf_num = $_POST['shlf_num'];
    $cndtn = $_POST['cndtn'];
    $additional_info = $_POST['additional_info'];
    $status = $_POST['status'];
    $added_by = $_POST['added_by'];
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

    // Update the book information in the database
    $query = "UPDATE inventory SET title=?, author=?, year=?, genre=?, dew_num=?, ISBN=?, shlf_num=?, cndtn=?, add_info=?, status=?, added_by=?, publisher=?, edition=?, language=?, physical_description=?, series_title=?, subject_keywords=?, accession_number=?, barcode=?, acquisition_date=?, location=?, cataloging_notes=? WHERE bid=?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ssssssssssssssssssssssi", $title, $author, $year, $genre, $dew_num, $ISBN, $shlf_num, $cndtn, $additional_info, $status, $added_by, $publisher, $edition, $language, $physical_description, $series_title, $subject_keywords, $accession_number, $barcode, $acquisition_date, $location, $cataloging_notes, $bid);
    $stmt->execute();
    $stmt->close();

    // Redirect back to bk_inv.php
    header("Location: bk_inv.php?aid=" . $aid);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Info Edit</title>
    <link rel="icon" type="image/x-icon" href="css/pics/logop.png">
    <link rel="stylesheet" href="css/edit.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- SweetAlert CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11">
    <!-- Include JsBarcode library -->
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        .form-container {
            margin-left: 10%;
            max-width: 900px;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            position: relative; /* This ensures the barcode is positioned relative to this container */
        }
        form {
            display: grid;
            grid-template-columns: 1fr 2fr; /* Adjusted for label and input alignment */
            gap: 5px;
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
            width: 95%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 5px;
        }
        textarea {
            resize: vertical;
        }
        .button-container {
            grid-column: 1 / -1;
            text-align: center;
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
        .barcode {
            position: absolute; /* Positions the barcode container relative to .form-container */
            top: 70px; /* Adjust this value to position the barcode lower/higher */
            right: 30px; /* Adjust this value to position the barcode more to the left/right */
            padding: 10px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .barcode h3 {
            margin-bottom: 5px;
            text-align: center;
        }
    </style>
</head>
<body class="bg">
<nav class="navbar">
    <div class="navbar-container">
        <img src="css/pics/logop.png" alt="Logo" class="logo">
    </div>
</nav>

    <div class="form-container">
    <h2 style="text-align:center">Edit Book Information</h2>
        <!-- Form to add a book -->
        <form id="editForm" method="POST">
            <input type="hidden" name="bid" value="<?php echo $bid; ?>">

            <!-- Other fields go here -->
            <label for="title">Book Title:</label>
            <input type="text" id="title" name="title" placeholder="Enter book title" required value="<?php echo $title; ?>">

            <label for="author">Book Author:</label>
            <input type="text" id="author" name="author" placeholder="Enter author" required value="<?php echo $author; ?>">

            <label for="year">Year:</label>
            <input type="text" id="year" name="year" placeholder="Enter year" required value="<?php echo $year; ?>">

            <label for="genre">Book Genre:</label>
            <input type="text" id="genre" name="genre" placeholder="Enter genre" value="<?php echo $genre; ?>">

            <label for="dew_num">Dewey Decimal Number:</label>
            <input type="text" id="dew_num" name="dew_num" placeholder="Enter Dewey Decimal number" required value="<?php echo $dew_num; ?>">

            <label for="ISBN">ISBN:</label>
            <input type="text" id="ISBN" name="ISBN" placeholder="Enter ISBN" required value="<?php echo $ISBN; ?>">

            <label for="shlf_num">Shelf Number:</label>
            <input type="text" id="shlf_num" name="shlf_num" placeholder="Enter Shelf Number" required value="<?php echo $shlf_num; ?>">

            <label for="cndtn">Condition:</label>
            <input type="text" id="cndtn" name="cndtn" placeholder="Enter Condition" required value="<?php echo $cndtn; ?>">

            <label for="additional_info">Additional Information:</label>
            <input type="text" id="additional_info" name="additional_info" placeholder="Enter additional information" value="<?php echo $additional_info; ?>">

            <label for="publisher">Publisher:</label>
            <input type="text" id="publisher" name="publisher" placeholder="Enter publisher" value="<?php echo $publisher; ?>">

            <label for="edition">Edition:</label>
            <input type="text" id="edition" name="edition" placeholder="Enter edition" value="<?php echo $edition; ?>">

            <label for="language">Language:</label>
            <input type="text" id="language" name="language" placeholder="Enter language" value="<?php echo $language; ?>">

            <label for="physical_description">Physical Description:</label>
            <textarea id="physical_description" name="physical_description" placeholder="Enter physical description"><?php echo $physical_description; ?></textarea>

            <label for="series_title">Series Title:</label>
            <input type="text" id="series_title" name="series_title" placeholder="Enter series title" value="<?php echo $series_title; ?>">

            <label for="subject_keywords">Subject Keywords:</label>
            <input type="text" id="subject_keywords" name="subject_keywords" placeholder="Enter subject keywords" value="<?php echo $subject_keywords; ?>">

            <label for="accession_number">Accession Number:</label>
            <input type="text" id="accession_number" name="accession_number" placeholder="Enter accession number" value="<?php echo $accession_number; ?>">

            <label for="barcode">Barcode:</label>
            <input type="text" id="barcode" name="barcode" placeholder="Enter barcode" value="<?php echo $barcode; ?>">

            <label for="acquisition_date">Acquisition Date:</label>
            <input type="date" id="acquisition_date" name="acquisition_date" value="<?php echo $acquisition_date; ?>">

            <label for="location">Location:</label>
            <input type="text" id="location" name="location" placeholder="Enter location" value="<?php echo $location; ?>">

            <label for="cataloging_notes">Cataloging Notes:</label>
            <textarea id="cataloging_notes" name="cataloging_notes" placeholder="Enter cataloging notes"><?php echo htmlspecialchars($cataloging_notes); ?></textarea>
            
            <!-- Dropdown for book status -->
            <label for="status">Book Status:</label>
            <select id="status" name="status" style="height: 35px;">
                <option value="Available" <?php if($status == 'Available') echo 'selected'; ?>>Available</option>
                <option value="Not Reservable" <?php if($status == 'Not Reservable') echo 'selected'; ?>>Not Reservable</option>
                <option value="Reserved" <?php if($status == 'Reserved') echo 'selected'; ?>>Reserved</option>
                <option value="Overdue" <?php if($status == 'Overdue') echo 'selected'; ?>>Overdue</option>
            </select>
            
            <label for="added_by">Added By:</label>
            <input type="text" id="added_by" name="added_by" placeholder="Enter who added the book" required value="<?php echo $added_by; ?>" readonly><br>

            <div class="button-container" style="margin-top:3%;">
                <!-- Cancel button -->
                <a href="bk_inv.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>"><button style="background-color:red; width: 100px; border-radius: 15px;" type="button">Cancel</button></a>
                <button id="saveBtn" style="width: 100px;" type="button">Save</button>
            </div>
        </form>
    </div>
    <div class="barcode">
    <h3>Barcode:</h3>
    <!-- Barcode Display -->
    <svg id="barcodeDisplay"></svg>
    <div>
        <button id="generateBarcodeBtn" style="background-color: green; color: white; width: 150px; border-radius: 15px;" type="button">
            Generate Barcode
        </button>
        <button id="saveButton" style="background-color: blue; color: white; width: 150px; border-radius: 15px; margin-left: 10px;" type="button">
            Save as Image
        </button>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.4.2/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.getElementById('saveBtn').addEventListener('click', function() {
        Swal.fire({
            title:'Success!',
            text: "Book Information Updated!",
            icon: "success",
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'OK'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('editForm').submit();
            }
        })
    });
</script>

<!-- Add JavaScript for Barcode Generation -->
<script>
    // Function to sanitize filenames
    function sanitizeFilename(name) {
        // Replace spaces with underscores
        let sanitized = name.replace(/\s+/g, '_');
        // Remove any characters that are not letters, numbers, underscores, or hyphens
        sanitized = sanitized.replace(/[^a-zA-Z0-9_-]/g, '');
        return sanitized;
    }

    // Event listener for the "Save as Image" button
    document.getElementById('saveButton').addEventListener("click", function () {
        const svgElement = document.querySelector("#barcodeDisplay");
        if (!svgElement) {
            Swal.fire({
                title: 'Error!',
                text: "No barcode generated to save.",
                icon: "error",
                confirmButtonColor: '#d33',
                confirmButtonText: 'OK'
            });
            return;
        }

        const canvas = document.createElement("canvas");
        const ctx = canvas.getContext("2d");
        const serializer = new XMLSerializer();
        const svgString = serializer.serializeToString(svgElement);

        const img = new Image();
        const svgBlob = new Blob([svgString], { type: "image/svg+xml;charset=utf-8" });
        const url = URL.createObjectURL(svgBlob);

        img.onload = function () {
            canvas.width = img.width;
            canvas.height = img.height;
            ctx.drawImage(img, 0, 0);
            URL.revokeObjectURL(url);

            // Retrieve and sanitize the book title
            const bookTitle = document.getElementById('title').value;
            const sanitizedTitle = sanitizeFilename(bookTitle) || 'barcode';

            // Construct the filename
            const filename = sanitizedTitle + "-barcode.png";

            // Download the image
            const link = document.createElement("a");
            link.download = filename;
            link.href = canvas.toDataURL("image/png");
            link.click();

            // Notify the user
            Swal.fire({
                title: 'Success!',
                text: `Barcode saved as ${filename}`,
                icon: "success",
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK'
            });
        };
        img.src = url;
    });

    // Event listener for the "Generate Barcode" button
    document.getElementById('generateBarcodeBtn').addEventListener('click', function() {
        const barcodeData = document.getElementById('barcode').value;

        if (!barcodeData) {
            Swal.fire({
                title: 'Error!',
                text: "No barcode data available to generate.",
                icon: "error",
                confirmButtonColor: '#d33',
                confirmButtonText: 'OK'
            });
            return;
        }

        // Generate barcode and display it
        JsBarcode("#barcodeDisplay", barcodeData, {
            format: "CODE128", // Barcode format
            lineColor: "#000",
            width: 2,
            height: 40,
            displayValue: true
        });

        Swal.fire({
            title: 'Success!',
            text: "Barcode generated successfully!",
            icon: "success",
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'OK'
        });
    });

    // Event listener for the "Save" button to submit the form
    document.getElementById('saveBtn').addEventListener('click', function() {
        Swal.fire({
            title:'Success!',
            text: "Book Information Updated!",
            icon: "success",
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'OK'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('editForm').submit();
            }
        })
    });
</script>
</body>
</html>
