<?php
include "connection.php";
$error_message = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST["token"];
    $new_password = $_POST["new_password"]; // Store as plain text

    $conn = new mysqli($servername, $db_username, $db_password, $db_name);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if token is valid and not expired
    $stmt = $conn->prepare("SELECT user_id FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($user_id);
        $stmt->fetch();

        // Update user password
        $update = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $update->bind_param("si", $new_password, $user_id);
        $update->execute();

        // Delete the reset token
        $delete = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
        $delete->bind_param("s", $token);
        $delete->execute();

        $success_message = "Password successfully updated. <a href='login.php'>Login</a>";
    } else {
        $error_message = "Invalid or expired token.";
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Reset Password - GenB Distance Learning</title>
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

        input[type="password"] {
            width: 80%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
            background-image: url('assets/lock.png');
            background-repeat: no-repeat;
            background-position: 15px center;
            background-size: 20px 20px;
            padding-left: 45px;
        }

        input[type="submit"] {
            width: 80%;
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
        <h2>Set New Password</h2>
        <p>Enter a new password for your account</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token'] ?? ''); ?>">
            <input type="password" name="new_password" placeholder="New Password" required>
            <input type="submit" value="Reset Password">
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