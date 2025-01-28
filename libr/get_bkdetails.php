<?php
include 'config.php';

if (isset($_GET['bid'])) {
    $bid = $_GET['bid'];
    $query = "SELECT * FROM inventory WHERE bid = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $bid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $book = $result->fetch_assoc();
        echo json_encode(['success' => true, 'book' => $book]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Book not found']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
