<?php
session_start();
echo '<pre>';
print_r($_SESSION); // Print session data to debug
echo '</pre>';


// Database connection parameters
include "connection.php";
$conn = new mysqli($servername, $db_username, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);

    // Prepare SQL statement to delete user
    $sql = "DELETE FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        echo "User removed successfully.";
    } else {
        echo "Error removing user: " . $conn->error;
    }

    $stmt->close();
} else {
    echo "No user_id provided.";
}

$conn->close();
?>
