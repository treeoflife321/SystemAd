<?php
// Include database connection
include 'config.php';

header('Content-Type: application/json');

if (isset($_GET['uid'])) {
    $uid = intval($_GET['uid']);
    
    $query = "SELECT COUNT(*) as activeCount FROM rsv WHERE uid = ? AND status IN ('Pending', 'Overdue', 'Reserved', 'Released')";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $row = $result->fetch_assoc()) {
        echo json_encode(['success' => true, 'activeCount' => $row['activeCount']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to fetch transaction count.']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'User ID not provided.']);
}

$mysqli->close();
?>
