<?php
// Database connection parameters
include "connection.php";

$conn = new mysqli($servername, $db_username, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to count users based on the categories
$sql_completed = "SELECT COUNT(*) AS completed FROM progress WHERE
                  module1_completed = 1 AND module1lab_completed = 1 AND module1quiz_completed = 1 AND
                  module2_completed = 1 AND module2lab_completed = 1 AND module2quiz_completed = 1 AND
                  module3_completed = 1 AND module3lab_completed = 1 AND module3quiz_completed = 1 AND
                  module4_completed = 1 AND module4lab_completed = 1 AND module4quiz_completed = 1";
$sql_not_started = "SELECT COUNT(*) AS not_started FROM progress WHERE
                  module1_completed = 0 AND module1lab_completed = 0 AND module1quiz_completed = 0 AND
                  module2_completed = 0 AND module2lab_completed = 0 AND module2quiz_completed = 0 AND
                  module3_completed = 0 AND module3lab_completed = 0 AND module3quiz_completed = 0 AND
                  module4_completed = 0 AND module4lab_completed = 0 AND module4quiz_completed = 0";
$sql_in_progress = "SELECT COUNT(*) AS in_progress FROM progress WHERE NOT (
                  (module1_completed = 1 AND module1lab_completed = 1 AND module1quiz_completed = 1 AND
                   module2_completed = 1 AND module2lab_completed = 1 AND module2quiz_completed = 1 AND
                   module3_completed = 1 AND module3lab_completed = 1 AND module3quiz_completed = 1 AND
                   module4_completed = 1 AND module4lab_completed = 1 AND module4quiz_completed = 1) OR
                  (module1_completed = 0 AND module1lab_completed = 0 AND module1quiz_completed = 0 AND
                   module2_completed = 0 AND module2lab_completed = 0 AND module2quiz_completed = 0 AND
                   module3_completed = 0 AND module3lab_completed = 0 AND module3quiz_completed = 0 AND
                   module4_completed = 0 AND module4lab_completed = 0 AND module4quiz_completed = 0))";

$result_completed = $conn->query($sql_completed);
$result_not_started = $conn->query($sql_not_started);
$result_in_progress = $conn->query($sql_in_progress);

$completed = $result_completed->fetch_assoc()['completed'];
$not_started = $result_not_started->fetch_assoc()['not_started'];
$in_progress = $result_in_progress->fetch_assoc()['in_progress'];

// Calculate total users for percentage
$total_users = $completed + $not_started + $in_progress;
$percent_completed = ($total_users > 0) ? ($completed / $total_users) * 100 : 0;
$percent_not_started = ($total_users > 0) ? ($not_started / $total_users) * 100 : 0;
$percent_in_progress = ($total_users > 0) ? ($in_progress / $total_users) * 100 : 0;

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progress Overview</title>
    <style>
        .progress-section {
            display: flex;
            justify-content: space-around;
            margin: 50px 0;
        }
        .progress-bar-container {
            width: 30%; /* Adjust the width of each section */
            text-align: center;
        }
        .outer-bar {
            width: 100%;
            height: 40px;
            border-radius: 20px;
            background-color: #e6e6e6;
            overflow: hidden;
            position: relative;
        }
        .inner-bar {
            height: 100%;
            text-align: center;
            color: white;
            font-weight: bold;
            line-height: 40px; /* Center text vertically */
            border-radius: 20px 0 0 20px; /* Round the left edge of the progress bar */
        }
        .completed {
            background-color: #38D313;
        }
        .in-progress {
            background-color: #FFA500;
        }
        .not-started {
            background-color: #FF4500;
        }
        .label {
            font-size: 18px;
            font-weight: bold;
            margin-top: 10px;
        }
        .completed-label { color: #38D313; }
        .in-progress-label { color: #FFA500; }
        .not-started-label { color: #FF4500; }
    </style>
</head>
<body>
    <div class="progress-section">
        <!-- Completed Section -->
        <div class="progress-bar-container">
            <div class="outer-bar">
                <div class="inner-bar completed" style="width: <?php echo $percent_completed; ?>%;">
                    <?php echo number_format($percent_completed, 2); ?>%
                </div>
            </div>
            <p class="label completed-label">Complete</p>
        </div>

        <!-- In Progress Section -->
        <div class="progress-bar-container">
            <div class="outer-bar">
                <div class="inner-bar in-progress" style="width: <?php echo $percent_in_progress; ?>%;">
                    <?php echo number_format($percent_in_progress, 2); ?>%
                </div>
            </div>
            <p class="label in-progress-label">In Progress</p>
        </div>

        <!-- Not Started Section -->
        <div class="progress-bar-container">
            <div class="outer-bar">
                <div class="inner-bar not-started" style="width: <?php echo $percent_not_started; ?>%;">
                    <?php echo number_format($percent_not_started, 2); ?>%
                </div>
            </div>
            <p class="label not-started-label">Have Not Started</p>
        </div>
    </div>
</body>
</html>
