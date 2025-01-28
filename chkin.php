<?php
// Include database connection
include 'config.php';

// Check if 'aid' parameter is present in the URL
if(isset($_GET['aid'])) {
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
    margin-top: 0;
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

/* The Modal (background) */
.modal {
    display: none; /* Hidden by default */
    position: fixed;
    z-index: 1; /* Sit on top */
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto; /* Enable scroll if needed */
    background-color: rgb(0,0,0); /* Fallback color */
    background-color: rgba(0,0,0,0.4); /* Black with opacity */
    padding-top: 60px;
}

/* Modal Content */
.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 30px;
    border: 2px solid #888;
    border-radius: 10px;
    width: 60%; /* Reduced width for better visual balance */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Add shadow for depth */
    text-align: center; /* Center text and form inside */
}

/* Form labels */
.modal-content label {
    font-size: 24px;
    margin: 10px 0;
    display: block;
}

/* Form input fields */
.modal-content input,
.modal-content select {
    width: 90%;
    padding: 10px;
    margin-bottom: 20px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 20px;
}

/* Submit Button (inside modal) */
.modal-content button {
    background-color: #4CAF50; /* Green background */
    color: white; /* White text */
    padding: 12px 20px;
    font-size: 18px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    display: inline-block; /* Make button fit content */
}

.modal-content button:hover {
    background-color: #45a049; /* Darker green on hover */
}

/* Close Button */
.close {
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    position: absolute;
    top: 10px;
    right: 25px;
    cursor: pointer;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}

/* Modal Button */
#openModalBtn {
    background-color: #4CAF50; /* Green background */
    color: white; /* White text */
    border: none;
    padding: 12px 20px;
    font-size: 18px;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    display: block;
    margin: 20px auto; /* Center the button */
}

#openModalBtn:hover {
    background-color: #45a049; /* Darker green when hovered */
}

.input-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin: 20px 0;
        }

        .input-container input {
            width: 300px;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .input-container button {
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .input-container button:hover {
            background-color: #45a049;
        }
</style>
</head>
<body class="bg">
    <div class="content">
        <nav class="secondary-navbar">
        <img src="css/pics/logop.png" alt="Logo" class="logo" style="margin-right:20px; cursor: pointer;" onclick="confirmLogout()">
            <a href="chkin.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item active">Time In/Out</a>
        </nav>
    </div>

        <!-- Fixed-position date and time display -->
    <div class="fixed-date-time">
        <p>Date: <span id="current-date"><?php echo date("m-d-Y"); ?></span></p>
        <p>Time: <span id="current-time"></span></p>
    </div>
    
    <div class="content-container">
        <div class="container-inner">
            <h1>Scan ID QR Code</h1>
            <img src="css/pics/scan.png" alt="scan-instructions" style= 'max-width:500px; max-height:300px;'>
            <div class="input-container">
                <input type="text" id="qr-input" placeholder="Scan QR Code here" autofocus>
                <button id="time-in-out-btn">Time In/Out</button>
            </div>
        </div>
    </div>

<div class="purpose-container">
    <h3>Select Purpose:</h3>
    <div class="purpose-buttons">
    <button class="purpose-btn" data-purpose="Study">Study</button>
    <button class="purpose-btn" data-purpose="Research">Research</button>
    <button class="purpose-btn" data-purpose="Printing">Printing</button>
    <button class="purpose-btn" data-purpose="Clearance">Clearance</button>
    <button class="purpose-btn" data-purpose="Borrow Book(s)">Borrow Book(s)</button>
    <button class="purpose-btn" data-purpose="Return Book(s)">Return Book(s)</button>
    </div>
</div>
<!-- Modal Structure -->
<div id="checkinModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Check-In</h2>
        <form id="checkinForm">
            <label for="idnum">ID Number:</label>
            <input type="text" id="idnum" name="idnum" required>
            <label for="purpose">Purpose:</label>
            <select id="purpose" name="purpose" required>
                <option value="Study">Study</option>
                <option value="Research">Research</option>
                <option value="Printing">Printing</option>
                <option value="Clearance">Clearance</option>
                <option value="Borrow Book(s)">Borrow Book(s)</option>
                <option value="Return Book(s)">Return Book(s)</option>
            </select>
            <button type="submit" class="purpose-btn">Submit</button>
        </form>
    </div>
</div>
<center><h3>OR</h3></center>
<!-- Button to open modal -->
<button id="openModalBtn" class="id-chk">Check In/Out Using ID Number</button>
 
<!-- <button id="toggle-camera-btn" class="purpose-btn">Switch Camera</button> -->

    <div hidden id="scanned-results"></div>

<script src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>
<script>
        document.addEventListener('DOMContentLoaded', function () {
            const qrInput = document.getElementById('qr-input');
            const timeInOutBtn = document.getElementById('time-in-out-btn');
            
            qrInput.addEventListener('keypress', function (event) {
                if (event.key === 'Enter') {
                    timeInOutBtn.click(); // Trigger button click on Enter key
                }
            });

            timeInOutBtn.addEventListener('click', function () {
                const qrCode = qrInput.value.trim();
                if (qrCode) {
                    handleScannedResult(qrCode);
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Input',
                        text: 'Please scan or enter a QR code.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            });

    function handleScannedResult(content) {
        const resultContainer = document.createElement('div');
        resultContainer.textContent = 'Scanned: ' + content;
        document.getElementById('scanned-results').appendChild(resultContainer);
        insertScannedData(content);
    }

    let selectedPurpose = '';

    document.querySelectorAll('.purpose-btn').forEach(button => {
        button.addEventListener('click', function() {
            document.querySelectorAll('.purpose-btn').forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            selectedPurpose = this.getAttribute('data-purpose');
        });
    });

    function insertScannedData(data) {
        const now = new Date();
        const day = ("0" + now.getDate()).slice(-2);
        const month = ("0" + (now.getMonth() + 1)).slice(-2);
        const year = now.getFullYear();
        const currentDate = `${month}-${day}-${year}`;
        const currentTime = now.toLocaleTimeString();

        fetch('insert_data.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'info=' + encodeURIComponent(data) + 
                  '&date=' + encodeURIComponent(currentDate) + 
                  '&timein=' + encodeURIComponent(currentTime) + 
                  '&purpose=' + encodeURIComponent(selectedPurpose),
        })
        .then(response => response.text())
        .then(text => {
            const data = JSON.parse(text);
            if (data.success) {
                let titleMessage = '';
                if (data.message.includes("Time-out recorded successfully!")) {
                    titleMessage = 'Time-out recorded successfully. Have a nice day!';
                } else {
                    titleMessage = 'Welcome to the Library!';
                }
                Swal.fire({
                    icon: 'success',
                    title: `<img src="${data.profile_image}" alt="Profile Image" style="border-radius:50%;" width="200" height="200"><br>${titleMessage}`,
                    text: data.info,
                    timer: 5000,
                    showConfirmButton: false
                    }).then(() => {
        location.reload(); // Refresh the page after the alert is closed
    });
    resetPurposeSelection();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message,
                    timer: 5000,
                    showConfirmButton: false
                    }).then(() => {
        location.reload(); // Refresh the page after the alert is closed
    });
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Fetch Error',
                text: 'An error occurred while sending the data to the server.',
                timer: 5000,
                showConfirmButton: false
            });
        });
    }

    function resetPurposeSelection() {
        document.querySelectorAll('.purpose-btn').forEach(btn => btn.classList.remove('active'));
        selectedPurpose = '';
    }
});
</script>

<script>
// ID number check in/out
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById("checkinModal");
    const openModalBtn = document.getElementById("openModalBtn");
    const closeBtn = document.querySelector(".close");

    openModalBtn.addEventListener("click", function () {
        modal.style.display = "block";
    });

    closeBtn.addEventListener("click", function () {
        modal.style.display = "none";
    });

    window.addEventListener("click", function (event) {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    });

    document.getElementById("checkinForm").addEventListener("submit", function (e) {
        e.preventDefault();

        let idnum = document.getElementById("idnum").value;
        let purpose = document.getElementById("purpose").value;

        // Get current date and time
        let currentDate = new Date();
        let date = ("0" + (currentDate.getMonth() + 1)).slice(-2) + "-" +
            ("0" + currentDate.getDate()).slice(-2) + "-" + currentDate.getFullYear();
        let time = currentDate.toLocaleString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });

        // Perform AJAX request to handle timeout or new check-in
        fetch('idchkin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `idnum=${encodeURIComponent(idnum)}&purpose=${encodeURIComponent(purpose)}&date=${encodeURIComponent(date)}&timein=${encodeURIComponent(time)}`
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: data.message,
                        timer: 3000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload(); // Refresh the page
                    });
                    modal.style.display = "none";
                    document.getElementById("checkinForm").reset();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'There was an issue with your check-in.',
                        timer: 3000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload(); // Optional: Refresh for consistency
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred. Please try again.',
                    timer: 3000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload(); // Optional: Refresh for error case
                });
            });
    });
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

<script>
    function confirmLogout() {
        // Show a confirmation dialog
        Swal.fire({
            title: 'Are you sure you want to logout?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, logout!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirect to index.php if the user confirms
                window.location.href = 'index.php';
            }
        });
    }
</script>
</body>
</html>
