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

// Fetch all users
$sql = "SELECT * FROM users";
$result = $conn->query($sql);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Update all user rows based on the submitted form data
    foreach ($_POST['user'] as $user_id => $user_data) {
        $username = $user_data['username'];
        $email = $user_data['email'];
        $password = $user_data['password'];
        $role = $user_data['role'];

        // Update user details in the database
        $update_sql = "UPDATE users SET username = ?, email = ?, password = ?, role = ? WHERE user_id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("sssii", $username, $email, $password, $role, $user_id);
        $stmt->execute();
        $stmt->close();
    }

    // Reload the page with a success message
    header("Location: editaccounts.php?success=1");
    exit();
}

include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GenB Distance Learning</title>
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
        width: 150px; /* Set a fixed width for text fields */
        padding: 5px;
        border: 1px solid #ddd;
        border-radius: 3px;
        font-size: 14px; /* Adjust font size as needed */
    }

    input[type="password"] {
        width: 150px; /* Set a fixed width for text fields */
        padding: 5px;
        border: 1px solid #ddd;
        border-radius: 3px;
        font-size: 14px; /* Adjust font size as needed */
    }

        .btn {
            padding: 10px 20px;
            background-color: #4caf50;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }
        .btn:hover {
            background-color: #45a049;
        }
        .success-message {
            color: green;
            margin-bottom: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>Edit User Accounts</h1>
    <?php if (isset($_GET['success'])): ?>
        <p class="success-message">Changes have been successfully saved.</p>
    <?php endif; ?>

    <form method="POST">
        <table>
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Password</th>
                    <th>Role</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['user_id']; ?></td>
                            <td>
                                <input type="text" name="user[<?php echo $row['user_id']; ?>][username]" value="<?php echo $row['username']; ?>" required>
                            </td>
                            <td>
                                <input type="text" name="user[<?php echo $row['user_id']; ?>][email]" value="<?php echo $row['email']; ?>" required>
                            </td>
                            <td>
                              <input type="password"
                                     name="user[<?php echo $row['user_id']; ?>][password]"
                                     value="<?php echo $row['password']; ?>"
                                     required
                                     onfocus="togglePasswordVisibility(this, true)"
                                     onblur="togglePasswordVisibility(this, false)">
                            </td>
                            <td>
                                <input type="text" name="user[<?php echo $row['user_id']; ?>][role]" value="<?php echo $row['role']; ?>" required>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">No users found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <button type="submit" class="btn" onclick="confirmSubmission(event)">OK</button>
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

        function togglePasswordVisibility(input, show) {
    if (show) {
        input.type = 'text'; // Show password as plain text
    } else {
        input.type = 'password'; // Hide password as dots
    }
}
    </script>

</body>
</html>
