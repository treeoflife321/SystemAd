<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'config.php';

// Initialize response array
$response = array('success' => false, 'message' => '', 'info' => '', 'profile_image' => '');

// Check if the required parameters are set in the POST request
if (isset($_POST['info']) && isset($_POST['date']) && isset($_POST['timein']) && isset($_POST['purpose'])) {
    // Extract data from POST request
    $info = $_POST['info'];
    $date = $_POST['date'];
    $timein = $_POST['timein'];
    $purpose = $_POST['purpose'];

    // Fetch exact 'info' and other details from the users table
    $user_query = "SELECT info, profile_image, user_type, idnum, gender, year_level FROM users WHERE info LIKE CONCAT('%', ?, '%')";
    $user_stmt = $mysqli->prepare($user_query);
    $user_stmt->bind_param("s", $info);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();

    if ($user_result && $user_result->num_rows > 0) {
        // Match found
        $user = $user_result->fetch_assoc();
        $exact_info = $user['info'];
        $profile_image = $user['profile_image'];
        $user_type = $user['user_type'];
        $idnum = $user['idnum'];
        $gender = $user['gender'];
        $year_level = $user['year_level'];

        // Check if the user has already checked in on the same date
        $check_query = "SELECT id, timeout FROM chkin WHERE info = ? AND date = ? ORDER BY timein DESC LIMIT 1";
        $check_stmt = $mysqli->prepare($check_query);
        $check_stmt->bind_param("ss", $exact_info, $date);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result && $check_result->num_rows > 0) {
            // Fetch the last check-in record
            $last_checkin = $check_result->fetch_assoc();

            if ($last_checkin['timeout'] === '') {
                // Update the timeout
                $update_query = "UPDATE chkin SET timeout = ? WHERE id = ?";
                $update_stmt = $mysqli->prepare($update_query);
                $update_stmt->bind_param("si", $timein, $last_checkin['id']);

                if ($update_stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = "Time-out recorded successfully!";
                    $response['info'] = $exact_info;
                    $response['profile_image'] = $profile_image;
                } else {
                    $response['message'] = "Timeout update failed: " . $update_stmt->error;
                }
                $update_stmt->close();
            } else {
                // User has already timed out, allow a new check-in
                insertNewCheckin($mysqli, $exact_info, $date, $timein, $purpose, $profile_image, $user_type, $idnum, $gender, $year_level, $response);
            }
        } else {
            // No prior check-in found today, allow a new check-in
            insertNewCheckin($mysqli, $exact_info, $date, $timein, $purpose, $profile_image, $user_type, $idnum, $gender, $year_level, $response);
        }

        $check_stmt->close();
    } else {
        $response['message'] = "Error: User not found.";
    }

    $user_stmt->close();
} else {
    $response['message'] = "Error: Missing required parameters.";
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);

// Function to handle new check-ins
function insertNewCheckin($mysqli, $exact_info, $date, $timein, $purpose, $profile_image, $user_type, $idnum, $gender, $year_level, &$response) {
    $query = "INSERT INTO chkin (info, date, timein, user_type, purpose, idnum, gender, year_level) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($query);

    if ($stmt) {
        $stmt->bind_param("ssssssss", $exact_info, $date, $timein, $user_type, $purpose, $idnum, $gender, $year_level);

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = "Welcome to the Library!";
            $response['info'] = $exact_info;
            $response['profile_image'] = $profile_image;
        } else {
            $response['message'] = "Execution failed: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $response['message'] = "Preparation failed: " . $mysqli->error;
    }
}
?>
