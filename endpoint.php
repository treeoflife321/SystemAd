<?php
// Database configuration
$host = 'localhost'; // Database host
$username = 'root';  // Database username
$password = '';      // Database password
$database = 'easylib'; // Database name

// Create a connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $noiseLevel = isset($_POST['noiseLevel']) ? $_POST['noiseLevel'] : null;

    if ($noiseLevel !== null) {
        // Prepare an SQL statement
        $stmt = $conn->prepare("INSERT INTO t_endpoint (noise_level) VALUES (?)");
        $stmt->bind_param("i", $noiseLevel); // Bind the noiseLevel as an integer

        // Execute the statement
        if ($stmt->execute()) {
            echo "Noise level saved: $noiseLevel";
        } else {
            echo "Error: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    } else {
        echo "No noise level received.";
    }
} else {
    echo "Invalid request method.";
}

// Close the database connection
$conn->close();
?>
<!-- 
 
CREATE TABLE t_endpoint (
    id INT AUTO_INCREMENT PRIMARY KEY,
    noise_level INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-->