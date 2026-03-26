<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Database connection parameters
include "connection.php";

$conn = new mysqli($servername, $db_username, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare SQL statement to fetch username based on user_id
$user_id = $_SESSION['user_id'];
$sql_username = "SELECT username FROM users WHERE user_id = ?";
$stmt_username = $conn->prepare($sql_username);
$stmt_username->bind_param("i", $user_id); // Bind parameter
$stmt_username->execute();
$stmt_username->store_result();

// Check if user exists
if ($stmt_username->num_rows == 1) {
    // Bind result variables
    $stmt_username->bind_result($username);
    $stmt_username->fetch();
} else {
    // If user not found (though it should not happen if session user_id is valid)
    $username = "Unknown";
}

$stmt_username->close(); // Close username statement

// Prepare SQL statement to fetch grades based on user_id
$sql_grades = "SELECT module1_lab_grade, module1_quiz_grade, module2_lab_grade, module2_quiz_grade, module3_lab_grade, module3_quiz_grade, module4_lab_grade, module4_quiz_grade, quarterly_quiz_grade FROM grades WHERE user_id = ?";
$stmt_grades = $conn->prepare($sql_grades);
$stmt_grades->bind_param("i", $user_id); // Bind parameter
$stmt_grades->execute();
$stmt_grades->store_result();

// Variables to store fetched grades
$module1_lab_grade = $module1_quiz_grade = $module2_lab_grade = $module2_quiz_grade = $module3_lab_grade = $module3_quiz_grade = $module4_lab_grade = $module4_quiz_grade = $quarterly_quiz_grade = null; // Initialize quarterly_quiz_grade

// Check if grades exist
if ($stmt_grades->num_rows == 1) {
    // Bind result variables
    $stmt_grades->bind_result($module1_lab_grade, $module1_quiz_grade, $module2_lab_grade, $module2_quiz_grade, $module3_lab_grade, $module3_quiz_grade, $module4_lab_grade, $module4_quiz_grade, $quarterly_quiz_grade);
    $stmt_grades->fetch();
}

$stmt_grades->close(); // Close grades statement
$conn->close(); // Close the database connection
include 'sidebar.php'; // Include the sidebar

function getStatus($grade) {
    if ($grade === null || $grade == 0) {
        return '<span class="status-not-taken">NOT TAKEN</span>';
    } elseif ($grade >= 70) {
        return '<span class="status-passed">PASSED</span>';
    } else {
        return '<span class="status-failed">FAILED</span>';
    }
}


// Calculate Final Grade as the average of all available lab and quiz grades
$quiz_grades = [
    $module1_quiz_grade ?? 0,
    $module2_quiz_grade ?? 0,
    $module3_quiz_grade ?? 0,
    $module4_quiz_grade ?? 0,
];

$lab_grades = [
    $module1_lab_grade ?? 0,
    $module2_lab_grade ?? 0,
    $module3_lab_grade ?? 0,
    $module4_lab_grade ?? 0,
];

// Use the existing variable for quarterly quiz grade
$quarterly_grade = $quarterly_quiz_grade ?? 0;

// Calculate average quiz and lab grades
$average_quiz = array_sum($quiz_grades) / count($quiz_grades); // Average of quiz grades
$average_lab = array_sum($lab_grades) / count($lab_grades);     // Average of lab grades

// Calculate the overall grade using the provided weights
$overall_grade = ($average_quiz * 0.20) + ($average_lab * 0.60) + ($quarterly_grade * 0.20);

function getFStatus($overall_grade) {
    if ($overall_grade === null || $overall_grade == 0) {
        return '<span class="status-not-taken">NOT TAKEN</span>';
    } elseif ($overall_grade >= 70) {
        return '<span class="status-passed">PASSED</span>';
    } else {
        return '<span class="status-failed">FAILED</span>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Virtual Lab Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300&display=swap" rel="stylesheet"> <!-- Add Quicksand font -->
    <style>
        body {
            font-family: 'Quicksand', sans-serif; /* Use Quicksand font */
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin-left: 300px; /* Move the whole page to the right by 350px */
            background-color: rgba(247, 238, 231, 0.47); /* 47% opacity */

        }
        header {
            background-color: #F7EEE7;
            padding: 20px;
            text-align: center;
            position: relative; /* Positioning for absolute elements */
        }
        header h2 {
            margin: 0;
        }
        .dashboard-label {
            position: absolute; /* Position label in top left */
            left: 20px;
            top: 20px;
            font-size: 20px; /* Font size for the label */
            font-weight: bold;
            color: #284290;
        }
        .course-progress-label {
            margin-top: 40px; /* Space above the progress bar */
            font-size: 48px; /* Large font size for the course progress label */
            font-weight: bold;
            color: #284290;
            text-shadow: 4px 4px 8px rgba(0, 0, 0, 0.5); /* More pronounced shadow effect */
        }
        .profile-icon {
            position: absolute; /* Positioning for the profile icon */
            right: 20px; /* Aligns to the right */
            top: 20px; /* Aligns to the top */
            display: flex;
            align-items: center; /* Center the icon and text vertically */
            font-size: 16px; /* Font size for the username */
            color: #284290; /* Color for the username */
            font-size: 18px; /* Increased font size for the username */
            font-family: 'Quicksand', sans-serif; /* Use Quicksand font */
            font-weight: bold;
        }
        .profile-icon img {
            width: 50px; /* Width of the profile icon */
            height: 50px; /* Height of the profile icon */
            margin-right: 8px; /* Space between icon and username */
        }
        p {
            margin: 15px 0;
        }
        hr {
            border: none;
            height: 2px;
            background: #ddd;
        }
        ul {
            padding: 0;
        }
        ul li {
            list-style: none;
            padding: 5px 0;
        }

        .content {
            padding: 16px;
            flex: 1; /* Allows the content area to grow */
        }
        .table-container {
            width: 100%;
            padding: 0 20px;
            box-sizing: border-box;
            margin: 20px auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: transparent; /* Make the background transparent */
            box-shadow: none; /* Remove box shadow */
            border: none; /* Remove border */

        }

        table th, table td {
            padding: 15px;
            text-align: center;
            border: none; /* Remove border from cells */
            font-weight: bold; /* Make font thicker */
            color: #284290; /* Set font color */

        }

        table th {
            border-bottom: 2px solid #ccc; /* Add a single bottom border to the header */
            background-color: transparent; /* Transparent header background */
            color: #333;
            font-weight: bolder;
        }

        table tr {
            border: none; /* Remove borders from table rows */
        }

        table tr:nth-child(even) {
            background-color: transparent; /* Remove alternate row color */
        }

        table td {
            border-top: none; /* Remove the top border from rows */
        }
        .grade-table {
            width: 100%;
            margin: 20px auto;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .grade-table th {
            color: #284290;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .status-passed {
            color: #38D313;
            font-weight: bold;
        }
        .status-failed {
            color: red;
            font-weight: bold;
        }
        .status-not-taken {
            color: darkblue;
            font-weight: bold;
        }

        .admin-buttons button {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #ffc107;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-right: 10px;
        }
        .admin-buttons button:hover {
            background-color: #ff9800;
        }

    </style>
</head>
<body>

    <!-- Header with Loading Bar -->
    <header>
        <div class="dashboard-label">STUDENT DASHBOARD</div> <!-- Label in the top left -->
        <h2 class="course-progress-label">STUDENT'S COURSE PROGRESS</h2> <!-- Large header above the progress bar -->
        <div class="profile-icon">
          <div class="admin-buttons">
              <button onclick="window.location.href='changepass.php'">Change Password</button>
          </div>
            <img src="assets/profileicon.png" alt="Profile Icon"> <!-- Profile icon -->
            <?php echo $username; ?> <!-- Current user's name --> <br>

        </div>

        <?php include 'loadingbar.php'; ?>
        <p>Welcome, <?php echo $username; ?>! This is your profile page.</p>
    </header>
    <hr>

    <div class="content">
        <div class="table-container">
            <!-- Display Lab Grades -->
            <div>
                <h3>Lab Grades</h3>
                <table class="grade-table">
                    <thead>
                        <tr>
                            <th><h3>Modules</h3></th>
                            <th><h3>Grades</h3></th>
                            <th><h3>Status</h3></th> <!-- Add status column -->
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Module 1 Lab Grade</td>
                            <td><?php echo number_format($module1_lab_grade ?? 0, 2); ?></td>
                            <td><?php echo getStatus($module1_lab_grade); ?></td>
                        </tr>
                        <tr>
                            <td>Module 2 Lab Grade</td>
                            <td><?php echo number_format($module2_lab_grade ?? 0, 2); ?></td>
                            <td><?php echo getStatus($module2_lab_grade); ?></td>
                        </tr>
                        <tr>
                            <td>Module 3 Lab Grade</td>
                            <td><?php echo number_format($module3_lab_grade ?? 0, 2); ?></td>
                            <td><?php echo getStatus($module3_lab_grade); ?></td>
                        </tr>
                        <tr>
                            <td>Module 4 Lab Grade</td>
                            <td><?php echo number_format($module4_lab_grade ?? 0, 2); ?></td>
                            <td><?php echo getStatus($module4_lab_grade); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Display Quiz Grades -->
            <div>
                <h3>Quiz Grades</h3>
                <table class="grade-table">
                    <thead>
                        <tr>
                            <th><h3>Modules</h3></th>
                            <th><h3>Grades</h3></th>
                            <th><h3>Status</h3></th> <!-- Add status column -->
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Module 1 Quiz Grade</td>
                            <td><?php echo number_format($module1_quiz_grade ?? 0, 2); ?></td>
                            <td><?php echo getStatus($module1_quiz_grade); ?></td>
                        </tr>
                        <tr>
                            <td>Module 2 Quiz Grade</td>
                            <td><?php echo number_format($module2_quiz_grade ?? 0, 2); ?></td>
                            <td><?php echo getStatus($module2_quiz_grade); ?></td>
                        </tr>
                        <tr>
                            <td>Module 3 Quiz Grade</td>
                            <td><?php echo number_format($module3_quiz_grade ?? 0, 2); ?></td>
                            <td><?php echo getStatus($module3_quiz_grade); ?></td>
                        </tr>
                        <tr>
                            <td>Module 4 Quiz Grade</td>
                            <td><?php echo number_format($module4_quiz_grade ?? 0, 2); ?></td>
                            <td><?php echo getStatus($module4_quiz_grade); ?></td>
                        </tr>
                        <tr>
                            <td>Quarterly Exam Grade</td>
                            <td><?php echo number_format($quarterly_quiz_grade ?? 0, 2); ?></td>
                            <td><?php echo getStatus($quarterly_quiz_grade); ?></td>
                        </tr>
                    </tbody>


                </table>
            </div>

            <table>
              <tbody>
                <tr>
                    <td><h1><strong>Final Grade</strong></h1></td>
                    <td><h1><strong><?php echo number_format($overall_grade, 2); ?></strong></h1></td> <!-- Display final grade -->
                    <td><h1><strong><?php echo getFStatus($overall_grade); ?>
                    </strong></h1></td> <!-- Display status -->
                </tr>
              </tbody>
            </table>
        </div>
    </div>

</body>
</html>
