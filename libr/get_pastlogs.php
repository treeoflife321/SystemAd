<?php
include 'config.php';

if (isset($_GET['uid'])) {
    $uid = $_GET['uid'];
    
    $query = "SELECT title, status, due_date, date_ret FROM rsv WHERE uid = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $logs = [];
        while ($row = $result->fetch_assoc()) {
            $logs[] = $row;
        }
        echo json_encode(['success' => true, 'logs' => $logs]);
    } else {
        echo json_encode(['success' => false]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false]);
}
?>
