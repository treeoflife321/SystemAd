<?php
// Include database connection configuration
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_timeout') {
    // Set the timezone to Manila, Philippines
    date_default_timezone_set('Asia/Manila');

    // Get the current time in the desired format
    $currentTime = date("h:i:s A"); // 12-hour format with AM/PM

    // Update the `timeout` field where it is empty
    $query = "UPDATE chkin SET timeout = '$currentTime' WHERE timeout = ''";
    if ($mysqli->query($query)) {
        echo "Check-out times have been successfully updated.";
    } else {
        echo "Error: " . $mysqli->error;
    }

    // Close the database connection
    $mysqli->close();
    exit;
}
?>

