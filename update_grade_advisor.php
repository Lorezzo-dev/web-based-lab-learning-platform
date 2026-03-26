<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include "connection.php";

$conn = new mysqli($servername, $db_username, $db_password, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Loop through each user ID and update their grades
foreach ($_POST['module1_lab_grade'] as $user_id => $grade) {
    $module1_lab_grade = $conn->real_escape_string($grade);
    $module1_quiz_grade = $conn->real_escape_string($_POST['module1_quiz_grade'][$user_id]);
    $module2_lab_grade = $conn->real_escape_string($_POST['module2_lab_grade'][$user_id]);
    $module2_quiz_grade = $conn->real_escape_string($_POST['module2_quiz_grade'][$user_id]);
    $module3_lab_grade = $conn->real_escape_string($_POST['module3_lab_grade'][$user_id]);
    $module3_quiz_grade = $conn->real_escape_string($_POST['module3_quiz_grade'][$user_id]);
    $module4_lab_grade = $conn->real_escape_string($_POST['module4_lab_grade'][$user_id]);
    $module4_quiz_grade = $conn->real_escape_string($_POST['module4_quiz_grade'][$user_id]);
    $quarterly_quiz_grade = $conn->real_escape_string($_POST['quarterly_quiz_grade'][$user_id]);

    // Prepare the update SQL statement
    $sql = "UPDATE grades SET
            module1_lab_grade = '$module1_lab_grade',
            module1_quiz_grade = '$module1_quiz_grade',
            module2_lab_grade = '$module2_lab_grade',
            module2_quiz_grade = '$module2_quiz_grade',
            module3_lab_grade = '$module3_lab_grade',
            module3_quiz_grade = '$module3_quiz_grade',
            module4_lab_grade = '$module4_lab_grade',
            module4_quiz_grade = '$module4_quiz_grade',
            quarterly_quiz_grade = '$quarterly_quiz_grade'
            WHERE user_id = $user_id";

    // Execute the update query
    if (!$conn->query($sql)) {
        echo "Error updating record: " . $conn->error;
    }
}

$conn->close();

// Redirect back to the advisor dashboard or any desired page
header('Location: advisor.php');
exit();
?>
