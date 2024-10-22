<?php
include 'config.php';

// Initialize response array
$response = array('success' => false, 'message' => '', 'info' => '', 'profile_image' => '');

// Check if the required parameters are set in the POST request
if (isset($_POST['info']) && isset($_POST['date']) && isset($_POST['timein'])) {
    // Extract data from POST request
    $info = $_POST['info'];
    $date = $_POST['date'];
    $timein = $_POST['timein'];

    // Format date as month-day-year
    $date = date("m-d-Y");

    // Check if the user is already checked in
    $check_query = "SELECT * FROM chkin WHERE info = ? AND date = ? AND timeout = ''";
    $check_stmt = $mysqli->prepare($check_query);
    $check_stmt->bind_param("ss", $info, $date);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result && $check_result->num_rows > 0) {
        // User is already checked in
        $response['message'] = "The user is currently timed in.";
    } else {
        // Check the users table for a matching info
        $user_query = "SELECT profile_image, user_type FROM users WHERE info = ?";
        $user_stmt = $mysqli->prepare($user_query);
        $user_stmt->bind_param("s", $info);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();

        if ($user_result && $user_result->num_rows > 0) {
            // Match found, fetch profile_image and user_type
            $user = $user_result->fetch_assoc();
            $profile_image = $user['profile_image'];
            $user_type = $user['user_type'];

            // Create the absolute path for file_exists check
            $absolute_path = '../' . $profile_image;

            // Ensure the profile_image path is correct and accessible
            if (!file_exists($absolute_path)) {
                $profile_image = 'uploads/default.jpg'; // Fallback to default image if the file does not exist
            }
        } else {
            // No match found, use default image and user_type as NULL
            $profile_image = 'uploads/default.jpg';
            $user_type = '';
        }

        // Insert the data into the "chkin" table
        $query = "INSERT INTO chkin (info, date, timein, user_type) VALUES (?, ?, ?, ?)";
        $stmt = $mysqli->prepare($query);

        if ($stmt) {
            // Bind parameters to the statement
            $stmt->bind_param("ssss", $info, $date, $timein, $user_type);

            // Execute the statement
            if ($stmt->execute()) {
                // Set success message
                $response['success'] = true;
                $response['message'] = "Welcome to the Library!";
                $response['info'] = $info; // Include the scanned info in the response
                $response['profile_image'] = '../' . $profile_image; // Return the relative path stored in the database
            } else {
                // Set error message if execution fails
                $response['message'] = "Execution failed: " . $stmt->error;
            }

            // Close the statement
            $stmt->close();
        } else {
            // Set error message if preparation fails
            $response['message'] = "Preparation failed: " . $mysqli->error;
        }

        // Close user statement
        $user_stmt->close();
    }

    // Close check statement
    $check_stmt->close();
} else {
    // Set error message if required parameters are missing
    $response['message'] = "Error: Missing required parameters.";
}

// Return JSON response
header('Content-Type: application/json'); // Ensure the correct content type is set
echo json_encode($response);
?>
