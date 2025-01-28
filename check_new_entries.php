<?php
include 'config.php';

// Query to count unarchived entries
$query = "SELECT COUNT(*) AS count FROM chkin WHERE archived = ''";
$result = $mysqli->query($query);

if ($result) {
    $row = $result->fetch_assoc();
    echo json_encode(['count' => $row['count']]);
} else {
    echo json_encode(['count' => 0]);
}
?>
