<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || ($_SESSION['role'] != 1 && $_SESSION['role'] != 2)) {
    // Redirect to login if user is not logged in or does not have permission
    header('Location: login.php');
    exit();
}

// Database connection parameters
include "connection.php";

// Establish connection
$conn = new mysqli($servername, $db_username, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check for form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'add') {
            // Add new quiz question
            $question = $_POST['question'];
            $a = $_POST['optionA'];
            $b = $_POST['optionB'];
            $c = $_POST['optionC'];
            $d = $_POST['optionD'];
            $correct = $_POST['correctOption'];

            $sql = "INSERT INTO quiz_questions (question, optionA, optionB, optionC, optionD, correctOption) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $question, $a, $b, $c, $d, $correct);

            if ($stmt->execute()) {
                echo "Quiz question added successfully.";
            } else {
                echo "Error adding question: " . $stmt->error;
            }
            $stmt->close();
        } elseif ($action === 'edit') {
            // Edit existing quiz question
            $id = $_POST['id'];
            $question = $_POST['question'];
            $a = $_POST['optionA'];
            $b = $_POST['optionB'];
            $c = $_POST['optionC'];
            $d = $_POST['optionD'];
            $correct = $_POST['correctOption'];

            $sql = "UPDATE quiz_questions SET question = ?, optionA = ?, optionB = ?, optionC = ?, optionD = ?, correctOption = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssi", $question, $a, $b, $c, $d, $correct, $id);

            if ($stmt->execute()) {
                echo "Quiz question updated successfully.";
            } else {
                echo "Error updating question: " . $stmt->error;
            }
            $stmt->close();
        } elseif ($action === 'delete') {
            // Delete quiz question
            $id = $_POST['id'];

            $sql = "DELETE FROM quiz_questions WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                echo "Quiz question deleted successfully.";
            } else {
                echo "Error deleting question: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Close connection
$conn->close();
?>
