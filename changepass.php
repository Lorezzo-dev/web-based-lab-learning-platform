<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include "connection.php";
$conn = new mysqli($servername, $db_username, $db_password, $db_name);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Fetch username
$sql_username = "SELECT username FROM users WHERE user_id = ?";
$stmt_username = $conn->prepare($sql_username);
$stmt_username->bind_param("i", $user_id);
$stmt_username->execute();
$stmt_username->bind_result($username);
$stmt_username->fetch();
$stmt_username->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $old_password = trim($_POST['old_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validation: Ensure all fields are filled
    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        echo "<script>alert('Please fill in all fields.'); window.location.href='changepass.php';</script>";
        exit();
    }

    // Validation: Check new password length and complexity
    if (strlen($new_password) < 8 || !preg_match('/[A-Za-z]/', $new_password) || !preg_match('/[0-9]/', $new_password)) {
        echo "<script>alert('Password must be at least 8 characters long and include both letters and numbers.'); window.location.href='changepass.php';</script>";
        exit();
    }

    // Check if new password matches confirm password
    if ($new_password !== $confirm_password) {
        echo "<script>alert('New password and confirm password do not match.'); window.location.href='changepass.php';</script>";
        exit();
    }

    // Fetch the stored plaintext password
    $sql_fetch_password = "SELECT password FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql_fetch_password);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($stored_password);
    $stmt->fetch();
    $stmt->close();

    // Compare the plaintext old password directly with the stored password (plaintext comparison)
    if ($old_password !== $stored_password) {
        echo "<script>alert('Old password is incorrect.'); window.location.href='changepass.php';</script>";
        exit();
    }

    // Check if new password is different from the old password
    if ($new_password === $stored_password) {
        echo "<script>alert('New password cannot be the same as the old password.'); window.location.href='changepass.php';</script>";
        exit();
    }

    // Update the password in the database with the new plaintext password
    $sql_update_password = "UPDATE users SET password = ? WHERE user_id = ?";
    $stmt_update = $conn->prepare($sql_update_password);
    $stmt_update->bind_param("si", $new_password, $user_id);

    if ($stmt_update->execute()) {
        echo "<script>alert('Password successfully updated. Please log in again.');</script>";
        session_destroy(); // Log out user after password change
        header('Location: login.php');
        exit();
    } else {
        echo "<script>alert('Error updating password. Please try again.'); window.location.href='changepass.php';</script>";
    }

    $stmt_update->close();
}

include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Page</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300&display=swap" rel="stylesheet"> <!-- Add Quicksand font -->
    <style>
    body {
        font-family: 'Quicksand', sans-serif; /* Use Quicksand font */
            margin: 0;
            padding: 0;
            background: #f5f5f5;
            margin-left: 300px; /* Move the whole page to the right by 350px */

        }
        .header {
            background-image: url('assets/hexbg.jpg'); no-repeat center center / cover;
            text-align: center;
            color: #1a1a1a;
            padding: 50px 20px;
            position: relative;
            z-index: -2;
            overflow: hidden; /* Ensures dots stay inside the header */
        }
        .header h1 {
            font-family: Arial, sans-serif;
            margin: 0;
            font-size: 56px;
            color: #284290;
            margin-bottom: 20px; /* Adds space below the h1 */
        }
        .header p {
          font-family: Arial, sans-serif;
            margin: 10px 0 0;
            font-size: 28px;
            color: #284290;
            margin-top: 0; /* Removes extra space above the paragraph */
            margin-bottom: 10px; /* Optional, adds space below the paragraph */
            text-align: left;
        }
        .profile-icon {
            background-color: #ffdf4d;
            border-radius: 50%;
            width: 100px;
            height: 100px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 50px;
            color: #1a1a1a;
            position: absolute;
            top: 20px;
            right: 20px;
        }
        .account-section {
            background-color: #ffdf4d;
            padding: 15px;
            text-align: left;
            font-size: 20px;
            font-weight: bold;
        }
        .form-container {
            padding: 20px;
            max-width: 500px;
            margin: 30px auto;
            background: #ffffff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        .form-container h2 {
            text-align: left;
            font-size: 18px;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-size: 24px;
            color: #666;
            margin-left: 30px;
        }
        input {
            width: 100%;
            font-size: 14px;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            max-width: 500px;
            margin-left: 30px;
        }
        input:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 3px rgba(0, 123, 255, 0.25);
        }
         button {
            background-color: #284290;
            color: #fff;
            border: none;
            padding: 10px 15px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            max-width: 500px;
            margin-left: 30px;
        }
        button:hover {
            background-color: #0056b3;
        }

        h2 {
          margin-left: 30px;
        }

        .dot {
            position: absolute;
            animation: moveUp infinite linear;
            z-index: -1;
        }

        @keyframes moveUp {
            0% {
                transform: translateY(100vh);
            }
            100% {
                transform: translateY(-100vh);
            }
        }

        .password-container {
            position: relative; /* Ensures container is positioned relative to the page */
            z-index: 1; /* Higher z-index than dots to cover them */
            background-color: #ffffff; /* Solid background to block dots */
            width: 100%; /* Full width of the page */
            min-height: calc(100vh - 300px); /* Full height minus header height */
            padding: 50px 20px; /* Add some padding for spacing */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Optional: Add a shadow for aesthetics */

        }

        .password-field {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-field input {
            flex: 1;
            padding-right: 40px; /* Add space for the icon inside the field */
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            font-size: 14px;
        }

        .toggle-password {
            position: absolute;
            left: 580px;
            cursor: pointer;
            font-size: 20px; /* Bigger eye icon for better visibility */
            color: #666; /* Neutral color for the icon */
        }

        .toggle-password:hover {
            color: #000; /* Highlight the icon on hover */
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Hi <?php echo $username; ?><br></h1>
        <p><br><br>This is your profile page. You can customize your profile the way you like and change password.</p>
        <div class="profile-icon">👤</div>
    </div>

    <div class="account-section">My Account</div>

    <div class="password-container">
        <h2>Change Password</h2>
        <form action="changepass.php" method="POST">
            <div class="form-group">
                <label for="old-password">Old Password</label>
                <div class="password-field">
                    <input type="password" id="old-password" name="old_password" required>
                    <span class="toggle-password" onclick="togglePassword('old-password')">👁️</span>
                </div>
            </div>

            <div class="form-group">
                <label for="new-password">New Password</label>
                <div class="password-field">
                    <input type="password" id="new-password" name="new_password" required>
                    <span class="toggle-password" onclick="togglePassword('new-password')">👁️</span>
                </div>
            </div>

            <div class="form-group">
                <label for="confirm-password">Confirm Password</label>
                <div class="password-field">
                    <input type="password" id="confirm-password" name="confirm_password" required>
                    <span class="toggle-password" onclick="togglePassword('confirm-password')">👁️</span>
                </div>
            </div>

            <button type="submit" name="change_password">Confirm Password</button>
        </form>
</div>

<script>
// Path to the small image
const imageSrc = 'assets/dna.png'; // Replace with the actual path to your image

function createDot() {
    const img = document.createElement('img');
    img.classList.add('dot');
    img.src = imageSrc;

    // Randomize the x position and size
    const x = Math.random() * window.innerWidth;
    const size = Math.random() * 30 + 20; // Image size between 20px and 50px

    img.style.left = `${x}px`;
    img.style.width = `${size}px`;
    img.style.height = `${size}px`;

    // Set the vertical position near the bottom of the viewport
    const header = document.querySelector('.header');
    const y = header.offsetHeight; // Set y to header height
    img.style.top = `${y}px`;
    header.appendChild(img); // Append dots to the header instead of body

    // Randomize animation duration for varied speed
    const duration = Math.random() * 5 + 3; // between 3 and 8 seconds
    img.style.animationDuration = `${duration}s`;

    // Append image to the body
    document.body.appendChild(img);
}

// Create multiple image dots
for (let i = 0; i < 100; i++) {
    createDot();
}


function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const type = field.getAttribute('type') === 'password' ? 'text' : 'password';
    field.setAttribute('type', type);
}
</script>
</body>
</html>
