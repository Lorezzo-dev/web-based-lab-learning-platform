<?php
// Initialize variables for error handling and success message
$password_error = "";
$confirm_password_error = "";
$success_message = "";
$role = 0; // Default role for student

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $username = $_POST["username"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    $role = $_POST["role"]; // Get the selected role

    // Validate form data
    // (You should add more validation here as needed)

    // Check if passwords match and are at least 8 characters long
    if ($password !== $confirm_password) {
        $confirm_password_error = "Passwords do not match";
    } elseif (strlen($password) < 8) {
        $password_error = "Password must be at least 8 characters long";
    } else {
        // Database connection parameters
        include "connection.php";

        // Connect to MySQL database
        $conn = new mysqli($servername, $db_username, $db_password, $db_name);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Prepare SQL statement to insert data into users table
        $sql_users = "INSERT INTO users (username, email, password, role) VALUES ('$username', '$email', '$password', '$role')";

        if ($conn->query($sql_users) === TRUE) {
            // Get the auto-generated user_id
            $user_id = $conn->insert_id;

            // Insert into progress table
            $sql_progress = "INSERT INTO progress (user_id) VALUES ('$user_id')";
            if ($conn->query($sql_progress) !== TRUE) {
                echo "Error inserting into progress table: " . $conn->error;
            }

            // Insert into grades table
            $sql_grades = "INSERT INTO grades (user_id) VALUES ('$user_id')";
            if ($conn->query($sql_grades) !== TRUE) {
                echo "Error inserting into grades table: " . $conn->error;
            }

            // Set success message
            $success_message = "User added successfully";
        } else {
            echo "Error inserting into users table: " . $conn->error;
        }

        // Close database connection
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Account - Virtual Lab</title>
    <style>
        /* Your existing styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .register-container {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 400px; /* Increased width */
        }

        h2 {
            text-align: center;
            color: #333;
        }

        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        input[type="submit"] {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: #ffc107;
            color: white;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #ff9800;
        }

        .error-message {
            color: red;
            font-size: 0.8em;
            display: block;
            margin-top: 10px;
        }

        /* Radio button styles */
        .role-container {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
        }

        /* Popup styles */
         .popup-overlay {
             display: none;
             position: fixed;
             top: 0;
             left: 0;
             width: 100%;
             height: 100%;
             background-color: rgba(0, 0, 0, 0.5);
             z-index: 9998;
         }

         .popup-container {
             display: none;
             position: fixed;
             top: 50%;
             left: 50%;
             transform: translate(-50%, -50%);
             background-color: #ffffff;
             padding: 20px;
             border-radius: 10px;
             box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
             z-index: 9999;
             width: 300px; /* Adjust width as needed */
             text-align: center;
         }

         .popup-message {
             color: #333333;
             margin-bottom: 20px;
         }

         .popup-button {
             padding: 10px 20px;
             border: none;
             border-radius: 5px;
             background-color: #ffc107;
             color: white;
             cursor: pointer;
         }

         .popup-button:hover {
             background-color: #ff9800;
         }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Add New Account</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <input type="text" name="username" placeholder="Username" required>
            <input type="text" name="email" placeholder="Section" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>

            <!-- Radio buttons for user role -->
            <div class="role-container">
                <label>
                    <input type="radio" name="role" value="0" checked> Student
                </label>
                <label>
                    <input type="radio" name="role" value="1"> Teacher
                </label>
                <label>
                    <input type="radio" name="role" value="2"> Admin
                </label>
            </div>

            <input type="submit" value="Add Account">
            <span class="error-message"><?php echo $password_error; ?></span>
            <span class="error-message"><?php echo $confirm_password_error; ?></span>
        </form>
    </div>

    <!-- Popup overlay -->
    <div class="popup-overlay" id="popupOverlay"></div>

    <!-- Popup container -->
    <?php if (!empty($success_message)) : ?>
    <div class="popup-container" id="popupContainer">
        <div class="popup-message">
            <?php echo $success_message; ?>
        </div>
        <button class="popup-button" onclick="closePopup()">Close</button>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Show popup if success message is set
            if (document.getElementById("popupContainer")) {
                document.getElementById("popupOverlay").style.display = "block";
                document.getElementById("popupContainer").style.display = "block";
                document.body.style.overflow = "hidden"; // Prevent scrolling
            }
        });

        function closePopup() {
            document.getElementById("popupOverlay").style.display = "none";
            document.getElementById("popupContainer").style.display = "none";
            document.body.style.overflow = "auto"; // Re-enable scrolling
        }
    </script>
    <?php endif; ?>
</body>
</html>
