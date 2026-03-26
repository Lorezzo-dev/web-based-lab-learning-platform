<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include "connection.php";

$conn = new mysqli($servername, $db_username, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare SQL statement to fetch all users' usernames and their grades
$sql = "SELECT users.user_id, users.username,
               grades.module1_lab_grade, grades.module1_quiz_grade,
               grades.module2_lab_grade, grades.module2_quiz_grade,
               grades.module3_lab_grade, grades.module3_quiz_grade,
               grades.module4_lab_grade, grades.module4_quiz_grade,
               grades.quarterly_quiz_grade
        FROM users
        LEFT JOIN grades ON users.user_id = grades.user_id
        WHERE users.username NOT IN ('admin', 'Advisor')";

$result = $conn->query($sql);
include 'sidebar.php'; // Include the sidebar
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GenB Distance Learning</title>
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
    footer {
        text-align: center;
        padding: 10px 0;
        background: #333;
        color: #fff;
    }
    /* Additional CSS styles for text fields and the OK button */
    input[type="text"] {
        width: 100px; /* Set a fixed width for text fields */
        padding: 5px;
        border: 1px solid #ddd;
        border-radius: 3px;
        font-size: 14px; /* Adjust font size as needed */
    }

    .buttons button {
        padding: 10px 20px;
        margin: 5px;
        background-color: #28a745; /* Green background color */
        color: #fff;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .buttons button:hover {
        background-color: #218838; /* Darker green on hover */
    }
    </style>
</head>
<body>


    <header>
        <h2>Edit Grades</h2>
    </header>

    <form action="update_grade_advisor.php" method="POST">
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Module 1 Lab Grade</th>
                    <th>Module 1 Quiz Grade</th>
                    <th>Module 2 Lab Grade</th>
                    <th>Module 2 Quiz Grade</th>
                    <th>Module 3 Lab Grade</th>
                    <th>Module 3 Quiz Grade</th>
                    <th>Module 4 Lab Grade</th>
                    <th>Module 4 Quiz Grade</th>
                    <th>Quarterly Quiz Grade</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['username']}</td>
                                <td><input type='text' name='module1_lab_grade[{$row['user_id']}]' value='{$row['module1_lab_grade']}'></td>
                                <td><input type='text' name='module1_quiz_grade[{$row['user_id']}]' value='{$row['module1_quiz_grade']}'></td>
                                <td><input type='text' name='module2_lab_grade[{$row['user_id']}]' value='{$row['module2_lab_grade']}'></td>
                                <td><input type='text' name='module2_quiz_grade[{$row['user_id']}]' value='{$row['module2_quiz_grade']}'></td>
                                <td><input type='text' name='module3_lab_grade[{$row['user_id']}]' value='{$row['module3_lab_grade']}'></td>
                                <td><input type='text' name='module3_quiz_grade[{$row['user_id']}]' value='{$row['module3_quiz_grade']}'></td>
                                <td><input type='text' name='module4_lab_grade[{$row['user_id']}]' value='{$row['module4_lab_grade']}'></td>
                                <td><input type='text' name='module4_quiz_grade[{$row['user_id']}]' value='{$row['module4_quiz_grade']}'></td>
                                <td><input type='text' name='quarterly_quiz_grade[{$row['user_id']}]' value='{$row['quarterly_quiz_grade']}'></td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='10'>No records found</td></tr>";
                }
                ?>
            </tbody>
        </table>
        <div class="buttons">
            <button type="submit" onclick="confirmSubmission(event)">OK</button>
        </div>
    </form>
    <script>
        function confirmSubmission(event) {
            // Prevent the form submission until user confirms
            event.preventDefault();

            // Show confirmation popup
            const confirmation = confirm("Are you sure of the changes?");

            // If user clicks "Yes", submit the form
            if (confirmation) {
                event.target.form.submit();
            }
        }
    </script>

</body>
</html>
