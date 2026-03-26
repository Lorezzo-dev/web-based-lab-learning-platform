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
$sql = "SELECT username FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id); // Bind parameter
$stmt->execute();
$stmt->store_result();

// Check if user exists
if ($stmt->num_rows == 1) {
    // Bind result variables
    $stmt->bind_result($username);
    $stmt->fetch();
} else {
    // If user not found (though it should not happen if session user_id is valid)
    $username = "Unknown";
}

$stmt->close(); // Close statement
$conn->close(); // Close the database connection
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
    <style>
        body {
            font-family: 'Quicksand', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        h1, h2 {
            color: #284290;
        }
        .section {
            margin-bottom: 30px;
        }
        .course-description, .teacher-info {
            line-height: 1.6;
            font-size: 16px;
        }
        .teacher-info img {
            float: left;
            margin-right: 15px;
            border-radius: 50%;
            width: 80px;
            height: 80px;
        }
        .toggle-description {
            color: #284290;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
            display: inline-block;
        }
        .syllabus {
            display: flex;
            align-items: center;
            margin-top: 20px;
        }
        .syllabus-icon {
            font-size: 24px;
            color: #284290;
            margin-right: 10px;
        }
        .syllabus a {
            text-decoration: none;
            font-size: 18px;
            color: #284290;
            font-weight: bold;
        }
        .syllabus a:hover {
            text-decoration: underline;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="section">
            <h1>Course Information</h1>
            <h2>General Biology 2</h2>
            <p class="course-description" id="courseDescription">
                General Biology 2 is an advanced exploration of biological concepts, focusing on the intricate systems and processes that sustain life. This course delves into topics such as genetics, molecular biology, evolution, biodiversity, and ecological interactions. Students will analyze the structure and function of cells, understand the mechanisms of inheritance, and study the diversity of life forms and their adaptations to environments.
                <br><br>
                Through lectures, hands-on experiments, and discussions, students will develop critical thinking skills and a deeper appreciation for the complexity of living organisms. This course is ideal for those pursuing further studies in biology, environmental science, or related fields. By the end of the course, students will have gained a comprehensive understanding of the biological principles that govern life on Earth.
            </p>
            <span class="toggle-description" onclick="toggleDescription()">Show Less</span>
        </div>
        <hr>
       <!-- <div class="section clearfix">
            <h2>Teacher Information</h2>
            <div class="teacher-info">
                <img src="assets/teacher.jpg" alt="Teacher Photo">
                <p>
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
                </p>
            </div>
        </div>
        <hr> -->
        <div class="section">
            <h2>Syllabus</h2>
            <div class="syllabus">
                <i class="fas fa-file-pdf syllabus-icon"></i>
                <a href="assets/Syllabus_GenBio2.pdf" target="_blank">Syllabus Gen Bio 2.pdf</a>
            </div>
        </div>
    </div>
    <script>
        let isExpanded = true;
        function toggleDescription() {
            const description = document.getElementById('courseDescription');
            const toggleButton = document.querySelector('.toggle-description');
            if (isExpanded) {
                description.style.display = 'none';
                toggleButton.textContent = 'Show More';
            } else {
                description.style.display = 'block';
                toggleButton.textContent = 'Show Less';
            }
            isExpanded = !isExpanded;
        }
    </script>
</body>
</html>
