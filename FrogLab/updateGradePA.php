<?php
session_start();
include('connection.php'); // Include your database connection file

// Establish connection
$conn = new mysqli($servername, $db_username, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the user ID from the session
$user_id = $_SESSION['user_id']; // Assuming the user's ID is stored in the session

if (!$user_id) {
    die('User not authenticated.');
}

// Check if the request method is POST and if the percentage value is sent
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['percentage'])) {
    $percentage = (float)$_POST['percentage']; // Get the percentage as a float

    // Debugging: Log the received percentage to the error log
    error_log("Received percentage: $percentage");

    // Prepare and bind the SQL statement
    $stmt = $conn->prepare("UPDATE grades SET module1_lab_grade = ? WHERE user_id = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param('di', $percentage, $user_id); // Bind the percentage and user ID

    // Execute the statement
    if ($stmt->execute()) {
        echo "Grade updated successfully.";
    } else {
        echo "Error updating grade: " . $stmt->error;
    }

    // Close the prepared statement
    $stmt->close();
} else {
    echo "Invalid request.";
}

// Close the database connection
$conn->close();
?>
