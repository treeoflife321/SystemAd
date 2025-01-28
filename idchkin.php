<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idnum = $_POST['idnum'];
    $purpose = $_POST['purpose'];
    $date = $_POST['date'];
    $timein = $_POST['timein'];
    $timeout = date('h:i:s A'); // Current time for timeout

    // Check if idnum exists in the 'users' table
    $userCheckQuery = "SELECT * FROM users WHERE idnum = ?";
    $userStmt = $mysqli->prepare($userCheckQuery);
    $userStmt->bind_param("s", $idnum);
    $userStmt->execute();
    $userResult = $userStmt->get_result();

    if ($userResult->num_rows > 0) {
        // Proceed with check-in or check-out logic
        $checkQuery = "SELECT * FROM chkin WHERE idnum = ? AND timeout ='' ";
        $stmt = $mysqli->prepare($checkQuery);
        $stmt->bind_param("s", $idnum);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Update the timeout for the existing record
            $updateQuery = "UPDATE chkin SET timeout = ? WHERE idnum = ? AND timeout ='' ";
            $updateStmt = $mysqli->prepare($updateQuery);
            $updateStmt->bind_param("ss", $timeout, $idnum);

            if ($updateStmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Timeout recorded successfully.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to record timeout.']);
            }
            $updateStmt->close();
        } else {
            // Insert new check-in record
            $insertQuery = "INSERT INTO chkin (idnum, purpose, date, timein) VALUES (?, ?, ?, ?)";
            $insertStmt = $mysqli->prepare($insertQuery);
            $insertStmt->bind_param("ssss", $idnum, $purpose, $date, $timein);

            if ($insertStmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Check-in successful. Welcome to the library!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database error during check-in.']);
            }
            $insertStmt->close();
        }

        $stmt->close();
    } else {
        // idnum does not exist in 'users' table
        echo json_encode(['success' => false, 'message' => 'Invalid. ID number does not exist.']);
    }

    $userStmt->close();
}
?>
