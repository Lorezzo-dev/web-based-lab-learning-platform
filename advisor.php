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

// Prepare SQL statement to fetch username and role based on user_id
$user_id = $_SESSION['user_id'];
$sql_username = "SELECT username, role FROM users WHERE user_id = ?";
$stmt_username = $conn->prepare($sql_username);
$stmt_username->bind_param("i", $user_id);
$stmt_username->execute();
$stmt_username->store_result();

// Check if user exists
if ($stmt_username->num_rows == 1) {
    $stmt_username->bind_result($username, $role);
    $stmt_username->fetch();

    // Check if the user has an unauthorized role (role = 0)
    if ($role === 0) {
        header('Location: unauthorized.php');
        exit();
    }
} else {
    $username = "Unknown";
}

$stmt_username->close();

// Prepare SQL statement to fetch all users' usernames and their grades
$sql = "SELECT users.username,
               grades.module1_lab_grade, grades.module1_quiz_grade,
               grades.module2_lab_grade, grades.module2_quiz_grade,
               grades.module3_lab_grade, grades.module3_quiz_grade,
               grades.module4_lab_grade, grades.module4_quiz_grade,
               grades.quarterly_quiz_grade
        FROM users
        LEFT JOIN grades ON users.user_id = grades.user_id
        WHERE users.role IN (0)";

$result = $conn->query($sql);

$conn->close();
include 'sidebar.php'; // Include the sidebar
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GenB Distance Learning</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <style>
        body {
            font-family: 'Quicksand', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin-left: 300px;
            background-color: rgba(247, 238, 231, 0.47);
        }
        header {
            background-color: #F7EEE7;
            padding: 20px;
            text-align: center;
            position: relative;
        }
        header h2 {
            margin: 0;
        }
        footer {
            text-align: center;
            padding: 10px 0;
            background: #333;
            color: #fff;
        }
        .buttons {
            margin-top: 20px;
        }
        .buttons button {
            padding: 10px 20px;
            margin: 5px;
            background-color: #005f73;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .buttons button:hover {
            background-color: #ffc107;
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
            background-color: transparent;
            border: none;
        }
        table th, table td {
            padding: 15px;
            text-align: center;
            font-weight: bold;
            color: #284290;
        }
        table th {
            border-bottom: 2px solid #ccc;
            color: #333;
            font-weight: bolder;
        }
        table tr:nth-child(even) {
            background-color: transparent;
        }
        table td {
            border-top: none;
        }
        .remarks-passed {
            color: lightgreen;
            font-weight: bold;
        }
        .remarks-failed {
            color: red;
            font-weight: bold;
        }
        .dashboard-label {
            position: absolute;
            left: 20px;
            top: 20px;
            font-size: 20px;
            font-weight: bold;
            color: #284290;
        }
        .profile-icon {
            position: absolute;
            right: 20px;
            top: 20px;
            display: flex;
            align-items: center;
            font-size: 18px;
            color: #284290;
            font-weight: bold;
        }
        .profile-icon img {
            width: 50px;
            height: 50px;
            margin-right: 8px;
        }

        .admin-buttons button {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #ffc107;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .admin-buttons button:hover {
            background-color: #ff9800;
        }
    </style>
</head>
<body>
    <header>
        <div class="dashboard-label">TEACHER DASHBOARD</div>
        <div class="profile-icon">
            <img src="assets/profileicon.png" alt="Profile Icon">
            Teacher <?php echo $username; ?>
        </div>
        <br>
        <?php include 'progressbar.php'; ?>
        <p>Welcome, <?php echo $username; ?>! Here's a list of users and their respective grades.</p><br><br>
    </header>
    <div class="admin-buttons">
        <?php
        // Check the role and set the redirect URL accordingly
        $redirect_url = ($role == 1) ? 'advisory.php' : 'admin.php';
        ?>
        <button onclick="window.location.href='<?php echo $redirect_url; ?>'">View Accounts</button>
    </div>
    <div class="buttons">
        <button onclick="window.location.href='advisoredit.php'">Edit Grades</button>
        <button onclick="exportToNotepad()">Extract to Notepad</button>
        <button onclick="exportToExcel()">Extract to Excel</button>
    </div>
    <table id="gradeTable">
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
                <th>Overall Grade</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $quiz_grades = [
                        $row['module1_quiz_grade'] ?? 0,
                        $row['module2_quiz_grade'] ?? 0,
                        $row['module3_quiz_grade'] ?? 0,
                        $row['module4_quiz_grade'] ?? 0,
                    ];
                    $lab_grades = [
                        $row['module1_lab_grade'] ?? 0,
                        $row['module2_lab_grade'] ?? 0,
                        $row['module3_lab_grade'] ?? 0,
                        $row['module4_lab_grade'] ?? 0,
                    ];
                    $quarterly_grade = $row['quarterly_quiz_grade'] ?? 0;

                    $average_quiz = array_sum($quiz_grades) / count($quiz_grades);
                    $average_lab = array_sum($lab_grades) / count($lab_grades);
                    $overall_grade = ($average_quiz * 0.20) + ($average_lab * 0.60) + ($quarterly_grade * 0.20);
                    $remarks = ($overall_grade >= 75.0) ? 'Passed' : 'Failed';

                    $remarks_class = ($remarks === 'Passed') ? 'remarks-passed' : 'remarks-failed';

                    echo "<tr>
                            <td>{$row['username']}</td>
                            <td>" . number_format($row['module1_lab_grade'] ?? 0, 2) . "</td>
                            <td>" . number_format($row['module1_quiz_grade'] ?? 0, 2) . "</td>
                            <td>" . number_format($row['module2_lab_grade'] ?? 0, 2) . "</td>
                            <td>" . number_format($row['module2_quiz_grade'] ?? 0, 2) . "</td>
                            <td>" . number_format($row['module3_lab_grade'] ?? 0, 2) . "</td>
                            <td>" . number_format($row['module3_quiz_grade'] ?? 0, 2) . "</td>
                            <td>" . number_format($row['module4_lab_grade'] ?? 0, 2) . "</td>
                            <td>" . number_format($row['module4_quiz_grade'] ?? 0, 2) . "</td>
                            <td>" . number_format($quarterly_grade, 2) . "</td>
                            <td>" . number_format($overall_grade, 2) . "</td>
                            <td class='$remarks_class'>{$remarks}</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='12'>No records found</td></tr>";
            }
            ?>
        </tbody>
    </table>
    
    <script>
function exportToNotepad() {
    const table = document.getElementById("gradeTable");
    let textContent = "";

    // Loop through table rows and cells
    for (let i = 0; i < table.rows.length; i++) {
        const row = table.rows[i];
        let rowText = Array.from(row.cells).map(cell => cell.innerText).join("\t");
        textContent += rowText + "\n";
    }

    // Create a Blob and download it
    const blob = new Blob([textContent], { type: "text/plain" });
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = "grades.txt";
    link.click();
}

function exportToExcel() {
    const table = document.getElementById("gradeTable");

    // Convert table to worksheet
    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.table_to_sheet(table);

    // Append worksheet to workbook
    XLSX.utils.book_append_sheet(wb, ws, "Grades");

    // Export the workbook
    XLSX.writeFile(wb, "grades.xlsx");
}

</script>
</body>
</html>
