<?php
// Initialize variables for error handling and success message
$password_error = "";
$confirm_password_error = "";
$success_message = "";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $username = $_POST["username"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    // Validate form data
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

        // Check for existing username or email
        $check_sql = "SELECT * FROM users WHERE username='$username' OR email='$email'";
        $result = $conn->query($check_sql);

        if ($result->num_rows > 0) {
            $success_message = "Username or email already registered. Please pick a different one.";
        } else {
            // Insert into users table
            $sql_users = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password')";

            if ($conn->query($sql_users) === TRUE) {
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
                $success_message = "User registered successfully";
            } else {
                echo "Error inserting into users table: " . $conn->error;
            }
        }

        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GenB Distance Learning</title>
    <link rel="icon" type="image/x-icon" href="assets/icon.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            display: flex;
            height: 100vh;
            font-family: 'Poppins', sans-serif;
            overflow: hidden;
            background: linear-gradient(to right, #f3f3f3 50%, #ffffff 50%);
        }

        .left-container {
            width: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #B9F4D8;
            padding: 20px; /* Add some padding to the left container */
        }

        .right-container {
            width: 50%;
            position: relative;
            background-image: url('assets/hexbg.jpg'); /* Replace with your hexagon background */
            background-size: cover;
            background-position: center;
            z-index: -1;
        }

        form {
            width: 100%; /* Make form take full width */
            text-align: center;
        }

        h2 {
          font-size: 2.2rem; /* Increase the font size */
          text-align: center;
          color: #2d6a93;
          margin-bottom: 10px;
          font-weight: 70; /* Make the text bold */
        }

        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        input[type="submit"] {
            width: 30%; /* Shorter width */
            padding: 12px;
            background-color: #26547c;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            font-weight: 500;
            display: block;
            margin: 0 auto; /* Center the button */
        }

        input[type="submit"]:hover {
            background-color: #1b4a6e;
        }

        input[type="submit"]:hover {
            background-color: #ff9800;
        }

        .login-link {
            margin-top: 15px;
            font-size: 0.9em;
        }

        .login-link a {
            color: #007bff;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .error-message {
            color: red;
            font-size: 0.8em;
        }

        /* DNA animation */
        .dna-dot {
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

        .popup-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent black */
            z-index: 9998; /* Ensure it's behind the popup */
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .popup-message {
            text-align: center;
            color: #ffffff; /* Change the text color for better visibility */
            font-size: 1.2em;
            padding: 20px;
            border-radius: 10px;
            background-color: rgba(0, 0, 0, 0.7); /* Slightly transparent black for background */
        }
    </style>
</head>
<body>
    <div class="left-container">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <h2>Register for Virtual Lab</h2>
            <input type="text" name="username" placeholder="Your Full Name" required>
            <input type="email" name="email" placeholder="Your Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <input type="submit" value="Register">
            <div class="error-message"><?php echo $password_error; ?></div>
            <div class="error-message"><?php echo $confirm_password_error; ?></div>
            <div class="login-link">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </form>
    </div>

    <div class="right-container">
        <!-- DNA animation -->
        <script>
            const dnaImageSrc = 'assets/dna.png'; // Replace with the actual path to your DNA image

            function createDnaDot() {
                const img = document.createElement('img');
                img.classList.add('dna-dot');
                img.src = dnaImageSrc;

                const x = Math.random() * window.innerWidth / 2 + window.innerWidth / 2;
                const size = Math.random() * 40 + 20;

                img.style.left = `${x}px`;
                img.style.width = `${size}px`;
                img.style.height = `${size}px`;

                const duration = Math.random() * 8 + 4;
                img.style.animationDuration = `${duration}s`;

                document.body.appendChild(img);
            }

            for (let i = 0; i < 100; i++) {
                createDnaDot();
            }
        </script>
    </div>

    <?php if (!empty($success_message)) : ?>
    <div class="popup-overlay" id="popupOverlay">
        <div class="popup-message"><?php echo $success_message; ?></div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.getElementById("popupOverlay").style.display = "flex";
            setTimeout(function() {
                window.location.href = "login.php";
            }, 3000);
        });
    </script>
    <?php endif; ?>
</body>
</html>
