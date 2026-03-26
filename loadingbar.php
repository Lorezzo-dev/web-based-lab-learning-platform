<?php


// Database connection parameters
include "connection.php";

$conn = new mysqli($servername, $db_username, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the user's progress
$user_id = $_SESSION['user_id'];
$sql_progress = "SELECT module1_completed, module1lab_completed, module1quiz_completed,
                        module2_completed, module2lab_completed, module2quiz_completed,
                        module3_completed, module3lab_completed, module3quiz_completed,
                        module4_completed, module4lab_completed, module4quiz_completed
                 FROM progress WHERE user_id = ?";
$stmt_progress = $conn->prepare($sql_progress);
$stmt_progress->bind_param("i", $user_id);
$stmt_progress->execute();
$stmt_progress->bind_result(
    $module1_completed, $module1lab_completed, $module1quiz_completed,
    $module2_completed, $module2lab_completed, $module2quiz_completed,
    $module3_completed, $module3lab_completed, $module3quiz_completed,
    $module4_completed, $module4lab_completed, $module4quiz_completed
);
$stmt_progress->fetch();
$stmt_progress->close();
$conn->close();

// Calculate the total progress percentage
$total_tasks = 12; // 4 modules * 3 tasks per module (completed, lab, quiz)
$completed_tasks = $module1_completed + $module1lab_completed + $module1quiz_completed +
                   $module2_completed + $module2lab_completed + $module2quiz_completed +
                   $module3_completed + $module3lab_completed + $module3quiz_completed +
                   $module4_completed + $module4lab_completed + $module4quiz_completed;
$progress_percentage = ($completed_tasks / $total_tasks) * 100;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loading Bar Example</title>
    <style>
        /* Styling for the loading bar */
        .loading-bar {
            width: 80%; /* Adjust the width of the loading bar */
            margin: 50px auto; /* Center the loading bar */
            border: 2px solid #ccc;
            border-radius: 20px;
            overflow: hidden; /* Ensures rounded corners apply properly */
        }
        .bar {
            width: 0%;
            height: 30px; /* Adjust the height of the loading bar */
            background-color: #38D313; /* Adjust the color of the loading bar */
            text-align: center; /* Center text horizontally */
            color: white;
            font-weight: bold;
            line-height: 30px; /* Vertical centering */
            transition: width 0.5s ease-in-out; /* Adjust transition speed as needed */
        }
    </style>
</head>
<body>

    <div class="loading-bar">
        <div class="bar" id="progressBar">0%</div>
    </div>

    <script>
        // Set the progress bar width and text based on the PHP calculated progress
        var progressPercentage = <?php echo $progress_percentage; ?>;
        var progressBar = document.getElementById('progressBar');
        progressBar.style.width = progressPercentage + '%';
        progressBar.innerHTML = progressPercentage.toFixed(2) + '%'; // Display with two decimal places
    </script>
</body>
</html>
