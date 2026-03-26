<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "User not logged in";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['module']) && isset($_POST['grade'])) {
    $user_id = $_SESSION['user_id'];
    $module = $_POST['module'];
    $grade = (float)$_POST['grade'];

    // Database connection parameters
    include "connection.php";

    // Establish connection
    $conn = new mysqli($servername, $db_username, $db_password, $db_name);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare SQL statement to update the grade
    $sql = "UPDATE grades SET $module = ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("di", $grade, $user_id);

    // Execute the update query
    if ($stmt->execute()) {
        echo "Grade updated successfully";
    } else {
        echo "Error updating grade: " . $conn->error;
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
}
?>
