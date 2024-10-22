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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Logs</title>
    <link rel="icon" type="image/x-icon" href="css/pics/logop.png">
    <link rel="stylesheet" href="css/qrscan.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
.purpose-container {
    display: flex;
    flex-direction: column;   /* Arrange buttons vertically */
    align-items: center;      /* Center the buttons */
}
.purpose-container h3 {
    margin-bottom: 10px;      /* Add some space below the heading */
}
.purpose-buttons {
    display: flex;
    justify-content: center;  /* Center the buttons horizontally */
    gap: 10px;                /* Add some space between the buttons */
}
.purpose-btn {
    font-size: 17px;
    padding: 10px 20px;
    margin: 5px;
    border: none;
    border-radius: 5px;
    background-color: #ddd;
    cursor: pointer;
    transition: background-color 0.3s ease;
}
.purpose-btn.active {
    background-color: gold;
    color: black;
}
.purpose-btn:hover {
    background-color: gold;
    color: black;
}
</style>
</head>
<body class="bg">
<div class="content">
        <nav class="secondary-navbar">
        <img src="css/pics/logop.png" alt="Logo" class="logo" style="margin-right:20px;">
            <a href="chkin.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item active">Time In</a>
        </nav>
    </div>

        <!-- Fixed-position date and time display -->
    <div class="fixed-date-time">
        <p>Date: <span id="current-date"><?php echo date("m-d-Y"); ?></span></p>
        <p>Time: <span id="current-time"></span></p>
    </div>
    
    <div class="content-container">
        <div class="container-inner">
            <h1>Scan Here</h1>
            <h2>ID QR Code</h2>
            <video id="qr-video" width="500px"></video>
            <h2>Time In</h2>
        </div>
    </div>

    <div hidden id="scanned-results"></div>

<script src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    let scanner = new Instascan.Scanner({ video: document.getElementById('qr-video') });

    scanner.addListener('scan', function (content) {
        handleScannedResult(content);
    });

    Instascan.Camera.getCameras().then(function (cameras) {
        if (cameras.length > 0) {
            // Start the scanner with the first available camera
            scanner.start(cameras[0]);
        } else {
            console.error('No cameras found.');
        }
    }).catch(function (e) {
        console.error(e);
    });

    function handleScannedResult(content) {
        // Update the HTML to display the scanned content
        const resultContainer = document.createElement('div');
        resultContainer.textContent = 'Scanned: ' + content;
        document.getElementById('scanned-results').appendChild(resultContainer);

        // Send the scanned content to the server for database insertion
        insertScannedData(content);
    }

    function insertScannedData(data) {
        // Get the current date and time
        const currentDate = new Date().toISOString().slice(0, 10);
        const currentTime = new Date().toLocaleTimeString();

        // Perform a Fetch API request to insert the scanned data into the chkin table
        fetch('insert_data.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'info=' + encodeURIComponent(data) + '&date=' + encodeURIComponent(currentDate) + '&timein=' + encodeURIComponent(currentTime),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: `<img src="${data.profile_image}" alt="Profile Image" style="border-radius:50%;" width="200" height="200"><br>Welcome to the Library!`,
                    text: data.info,
                    timer: 5000, // 5 seconds
                    showConfirmButton: false // No need to click OK
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message,
                    timer: 5000, // 5 seconds
                    showConfirmButton: false // No need to click OK
                });
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Fetch Error',
                text: 'An error occurred while sending the data to the server.',
                timer: 5000, // 5 seconds
                showConfirmButton: false // No need to click OK
            });
        });
    }
});
</script>
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
