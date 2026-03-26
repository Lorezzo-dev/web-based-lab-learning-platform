<?php
session_start();
include "connection.php"; // Database connection

$error_message = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];

    // Check if email exists in database
    $conn = new mysqli($servername, $db_username, $db_password, $db_name);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT user_id FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        // Generate a unique token
        $token = bin2hex(random_bytes(50));
        $stmt->bind_result($user_id);
        $stmt->fetch();

        // Save token in database
        $expires_at = date("Y-m-d H:i:s", strtotime("+1 hour"));
        $insert = $conn->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
        $insert->bind_param("iss", $user_id, $token, $expires_at);
        $insert->execute();

        // Send email with reset link (Update with actual email sending function)
        $reset_link = "https://genbdistancelearning.online/reset_password.php?token=$token";
        mail($email, "Password Reset", "Click this link to reset your password: $reset_link");

        $success_message = "A reset link has been sent to your email.";
    } else {
        $error_message = "No account found with that email.";
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Forgot Password - GenB Distance Learning</title>
    <link rel="icon" type="image/x-icon" href="assets/icon.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #B9F4D8;
        }

        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .container h2 {
            color: #2d6a93;
            font-size: 1.8rem;
            margin-bottom: 10px;
        }

        .container p {
            color: #6a7c8f;
            font-size: 1rem;
            margin-bottom: 20px;
        }

        input[type="email"] {
            width: 80%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
            background-image: url('assets/mail.png');
            background-repeat: no-repeat;
            background-position: 15px center;
            background-size: 20px 20px;
            padding-left: 45px;
        }

        input[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #26547c;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            font-weight: 500;
        }

        input[type="submit"]:hover {
            background-color: #1b4a6e;
        }

        .message {
            margin-top: 15px;
            font-size: 0.9rem;
        }

        .error-message {
            color: red;
        }

        .success-message {
            color: green;
        }

        .back-link {
            display: block;
            margin-top: 15px;
            font-size: 0.9rem;
        }

        .back-link a {
            color: #26547c;
            text-decoration: none;
        }

        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Forgot Password</h2>
        <p>Enter your email to receive a password reset link</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <input type="email" name="email" placeholder="Enter your email" required>
            <input type="submit" value="Send Reset Link">
        </form>

        <div class="message <?php echo !empty($error_message) ? 'error-message' : (!empty($success_message) ? 'success-message' : ''); ?>">
            <?php echo $error_message . $success_message; ?>
        </div>

        <div class="back-link">
            <a href="login.php">Back to Login</a>
        </div>
    </div>
</body>
</html>