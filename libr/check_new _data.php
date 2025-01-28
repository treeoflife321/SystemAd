<?php
include 'config.php';

session_start();

// Get the latest noise level and timestamp
$query = "SELECT noise_level, timestamp FROM t_endpoint ORDER BY timestamp DESC LIMIT 1";
$result = $mysqli->query($query);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();

    // Check if this is new data
    if (!isset($_SESSION['last_timestamp']) || $_SESSION['last_timestamp'] !== $row['timestamp']) {
        $_SESSION['last_timestamp'] = $row['timestamp']; // Update the session value
        echo json_encode([
            'status' => 'data_found',
            'noise_level' => $row['noise_level'],
            'timestamp' => $row['timestamp']
        ]);
    } else {
        echo json_encode(['status' => 'no_data']);
    }
} else {
    echo json_encode(['status' => 'no_data']);
}
?>