<?php
session_start(); // Start the PHP session

// Initialize the variable to store the error message
$error_message = "";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Database connection parameters
    include "connection.php";

    // Connect to MySQL database
    $conn = new mysqli($servername, $db_username, $db_password, $db_name);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare SQL statement to fetch user data including role
    $sql = "SELECT user_id, username, role FROM users WHERE username = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password); // Bind parameters
    $stmt->execute();
    $stmt->store_result();

    // Check if user exists
    if ($stmt->num_rows == 1) {
        // Bind result variables
        $stmt->bind_result($user_id, $fetched_username, $role);
        $stmt->fetch();

        // Store user_id in session
        $_SESSION["user_id"] = $user_id;
        // Store role in session to use it in loading.php
        $_SESSION["role"] = $role;

        // Redirect to loading.php
        header("Location: loading.php");
        exit; // Ensure script stops here
    } else {
        // If user is not found, display error message
        $error_message = "Invalid username or password";
    }

    $stmt->close(); // Close statement
    $conn->close(); // Close the database connection
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


        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            overflow: hidden;
        }

        /* Flexbox container for login and animated background */
        .main-container {
            display: flex;
            width: 100%;
        }

        /* Left section for the login form */
        .left-container {
            width: 40%;
            background-color: #B9F4D8;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 50px;
            z-index: 2;
            overflow: hidden; /* Ensure no scrollbars appear */
        }

        .login-box {
            background-color: transparent; /* Removed background */
            padding: 40px;
            border-radius: 10px;
            box-shadow: none; /* Removed shadow */
            width: 100%;
            max-width: 350px;
        }

        .login-box h2 {
            font-size: 2.2rem; /* Increase the font size */
            text-align: center;
            color: #2d6a93;
            margin-bottom: 10px;
            font-weight: 70; /* Make the text bold */
        }

        .login-box p {
            font-size: 1.2rem; /* Increase the font size */
            text-align: center;
            color: #6a7c8f;
            margin-bottom: 30px;
        }

        input[type="text"] {
          background-image: url('assets/user.png');
          background-repeat: no-repeat;
          background-position: 15px center;
          background-size: 20px 20px;
          padding-left: 45px; /* Add padding for the icon */
          width: 100%; /* Increase input width */
        }


        input[type="password"] {
          background-image: url('assets/lock.png');
          background-repeat: no-repeat;
          background-position: 15px center;
          background-size: 20px 20px;
          padding-left: 45px; /* Add padding for the icon */
          width: 100%; /* Increase input width */
        }

        input[type="text"], input[type="password"] {
            padding: 12px 15px 12px 45px; /* Adjust for icons */
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
            color: #333;
            font-weight: 400;
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


        .register-link {
            text-align: center;
            margin-top: 10px;
        }

        .register-link a {
            color: #26547c;
            text-decoration: none;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        .error-message {
            color: red;
            font-size: 0.9em;
            margin-bottom: 10px;
            text-align: center;
            display: <?php echo empty($error_message) ? 'none' : 'block'; ?>;
        }

        /* Right section for animated DNA */
        .right-container {
            width: 60%;
            position: relative;
            background-image: url('assets/hexbg.jpg'); /* Replace with your hexagon background */
            background-size: cover;
            background-position: center;
            overflow: hidden;
            z-index: -2;
        }

        .dot {
            position: absolute;
            animation: moveUp infinite linear;
            z-index: 1;
        }

        @keyframes moveUp {
            0% {
                transform: translateY(100vh);
            }
            100% {
                transform: translateY(-100vh);
            }
        }

    </style>
</head>
<body>
    <div class="main-container">
        <div class="left-container">
            <div class="login-box">
                <h2>Welcome Learner!</h2>
                <p>Please Login to your account</p>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <input type="text" name="username" placeholder="you@example.com" required>
                    <input type="password" name="password" placeholder="Password" required>

                    <br><br>
                    <input type="submit" value="Login">
                    <div class="error-message">
                        <?php echo $error_message; ?>
                    </div>

                </form>
                <div class="register-link">
                    Don't have an account yet? <a href="register.php">Register Here</a>
                    <a href="forgot_password.php">Forgot Password?</a>
                </div>
            </div>
        </div>
        <div class="right-container">
            <script>
                // Path to the small image
                const imageSrc = 'assets/dna.png'; // Replace with the actual path to your image

                // Function to create multiple animated images
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

                // JavaScript to refresh the page only on first load
                window.onload = function() {
                    if (!sessionStorage.getItem('firstLoadDone')) {
                        // Refresh the page once on first load
                        sessionStorage.setItem('firstLoadDone', 'true');
                        location.reload();
                    }
                };
            </script>
        </div>
    </div>
</body>
</html>
