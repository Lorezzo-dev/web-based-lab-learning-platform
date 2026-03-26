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
$stmt_username->bind_param("i", $user_id); // Bind parameter
$stmt_username->execute();
$stmt_username->store_result();

// Check if user exists
if ($stmt_username->num_rows == 1) {
    // Bind result variables
    $stmt_username->bind_result($username, $role);
    $stmt_username->fetch();

    // Check if the user has an unauthorized role (role = 0)
    if ($role === 0) {
        header('Location: unauthorized.php'); // Redirect to an unauthorized access page
        exit();
    }
} else {
    // If user not found (though it should not happen if session user_id is valid)
    $username = "Unknown";
}


$stmt_username->close(); // Close username statement

// Prepare SQL statement to fetch accounts
$sql_accounts = "SELECT user_id, username, email FROM users WHERE role NOT IN (1, 2)";
$result_accounts = $conn->query($sql_accounts);

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
    p {
        margin: 15px 0;
    }
    hr {
        border: none;
        height: 2px;
        background: #ddd;
    }
    ul {
        padding: 0;
    }
    ul li {
        list-style: none;
        padding: 5px 0;
    }
    footer {
        text-align: center;
        padding: 10px 0;
        background: #333;
        color: #fff;
    }

    .content {
        padding: 16px;
    }

    .admin-buttons {
        display: flex;
        justify-content: space-between;
        gap: 20px;
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
    .dashboard-label {
        position: absolute; /* Position label in top left */
        left: 20px;
        top: 20px;
        font-size: 20px; /* Font size for the label */
        font-weight: bold;
        color: #284290;
    }
    .course-progress-label {
        margin-top: 40px; /* Space above the progress bar */
        font-size: 48px; /* Large font size for the course progress label */
        font-weight: bold;
        color: #284290;
        text-shadow: 4px 4px 8px rgba(0, 0, 0, 0.5); /* More pronounced shadow effect */
    }
    .profile-icon {
        position: absolute; /* Positioning for the profile icon */
        right: 20px; /* Aligns to the right */
        top: 20px; /* Aligns to the top */
        display: flex;
        align-items: center; /* Center the icon and text vertically */
        font-size: 16px; /* Font size for the username */
        color: #284290; /* Color for the username */
        font-size: 18px; /* Increased font size for the username */
        font-family: 'Quicksand', sans-serif; /* Use Quicksand font */
        font-weight: bold;
    }
    .profile-icon img {
        width: 50px; /* Width of the profile icon */
        height: 50px; /* Height of the profile icon */
        margin-right: 8px; /* Space between icon and username */
    }


    .action-btn {
        padding: 5px 10px;
        font-size: 14px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }
    .action-btn.modify {
        background-color: #4caf50;
        color: #fff;
    }
    .action-btn.modify:hover {
        background-color: #388e3c;
    }
    .action-btn.remove {
        background-color: #f44336;
        color: #fff;
    }
    .action-btn.remove:hover {
        background-color: #d32f2f;
    }

    .add-account-btn {
        margin-bottom: 10px;
        padding: 10px 20px;
        background-color: #4caf50; /* Green color */
        color: #fff;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    .add-account-btn:hover {
        background-color: #388e3c; /* Darker green color on hover */
    }

    /* Remove Account Popup Style */
    .remove-popup {
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 50%;
        background: #fff;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        padding: 20px;
        border-radius: 5px;
        z-index: 1001;
    }
    .remove-popup h3 {
        margin-top: 0;
    }
    .remove-popup button {
        margin-top: 10px;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }
    .remove-popup .confirm-btn {
        background-color: #f44336;
        color: #fff;
    }
    .remove-popup .confirm-btn:hover {
        background-color: #d32f2f;
    }
    .remove-popup .cancel-btn {
        background-color: #ccc;
        color: #333;
    }
    .remove-popup .cancel-btn:hover {
        background-color: #bbb;
    }

    /* Edit Accounts Popup Style */
    .popup-edit {
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 50%;
        background: #fff;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        padding: 20px;
        border-radius: 5px;
        z-index: 1001;
    }
    .popup-edit h3 {
        margin-top: 0;
    }
    .popup-edit button {
        margin-top: 10px;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        background-color: #ff5722;
        color: #fff;
    }
    .popup-edit button:hover {
        background-color: #e64a19;
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
    </style>

</head>
<body>

    <!-- Main Content -->
    <header>
        <div class="dashboard-label">TEACHER DASHBOARD</div> <!-- Label in the top left -->
        <div class="profile-icon">
            <img src="assets/profileicon.png" alt="Profile Icon"> <!-- Profile icon -->
            Teacher <?php echo $username; ?> <!-- Current user's name -->
        </div>
        <br>
        <p>Percentage of Students who are: </p>
        <?php include 'progressbar.php'; ?>
        <p>Welcome, <?php echo $username; ?>! This is the Teacher Dashboard.</p><br><br>
        <div class="admin-buttons">
            <button onclick="window.location.href='changepass.php'">Change Password</button>
        </div>
    </header>



        <div class="content">
            <div class="admin-buttons">
                <button onclick="window.location.href='advisor.php'">View Grades</button>
            </div>

            <h1>Accounts</h1>
            <a href="add_accountadvisor.php" onclick="openSmallWindow('add_accountadvisor.php', 'Add Account', 500, 400); return false;" class="add-account-btn">Add Student</a>
              <table>
                  <tr>
                      <th>User ID</th>
                      <th>Username</th>
                      <th>Section</th>
                      <th>Actions</th>
                  </tr>
                  <?php if ($result_accounts->num_rows > 0): ?>
                      <?php while ($row = $result_accounts->fetch_assoc()): ?>
                          <tr>
                              <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                              <td><?php echo htmlspecialchars($row['username']); ?></td>
                              <td><?php echo htmlspecialchars($row['email']); ?></td>
                              <td>
                                  <button class="action-btn remove" onclick="showRemovePopup(<?php echo $row['user_id']; ?>)">Remove</button>
                              </td>
                          </tr>
                      <?php endwhile; ?>
                  <?php else: ?>
                      <tr>
                          <td colspan="4">No accounts found.</td>
                      </tr>
                  <?php endif; ?>
              </table>



            <!-- Edit Accounts Popup -->
            <div id="popup-overlayedit" class="popup-overlay" onclick="closeEditPopup()"></div>
            <div id="popup-edit" class="popup-edit">
                <h3>Edit Accounts</h3>
                <button onclick="closeEditPopup()">Close</button>
            </div>

            <!-- Remove Account Popup -->
            <div id="remove-popup-overlay" class="popup-overlay" onclick="closeRemovePopup()"></div>
            <div id="remove-popup" class="remove-popup">
                <h3>Confirm Removal</h3>
                <p>Are you sure you want to remove this account?</p>
                <button id="confirm-remove-btn" class="confirm-btn">Confirm</button>
                <button class="cancel-btn" onclick="closeRemovePopup()">Cancel</button>
            </div>
        </div>


    <script>
    function openSmallWindow(url, title, width, height) {
    const left = (screen.width / 2) - (width / 2);
    const top = (screen.height / 2) - (height / 2);
    window.open(url, title, `width=${width},height=${height},top=${top},left=${left}`);
}

function showPopup() {
    document.getElementById('popup-overlay').style.display = 'block';
    document.getElementById('popup').style.display = 'block';
}
function closePopup() {
    document.getElementById('popup-overlay').style.display = 'none';
    document.getElementById('popup').style.display = 'none';
}

function showEditPopup() {
    document.getElementById('popup-overlayedit').style.display = 'block';
    document.getElementById('popup-edit').style.display = 'block';
}
function closeEditPopup() {
    document.getElementById('popup-overlayedit').style.display = 'none';
    document.getElementById('popup-edit').style.display = 'none';
}

function showRemovePopup(userId) {
    document.getElementById('remove-popup-overlay').style.display = 'block';
    document.getElementById('remove-popup').style.display = 'block';
    document.getElementById('confirm-remove-btn').onclick = function() {
        removeAccount(userId);
    };
}
function closeRemovePopup() {
    document.getElementById('remove-popup-overlay').style.display = 'none';
    document.getElementById('remove-popup').style.display = 'none';
}

function removeAccount(userId) {
    if (confirm('Are you sure you want to remove this account?')) {
        console.log('Attempting to remove user ID:', userId);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'remove_account.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                console.log('Response:', xhr.responseText);
                if (xhr.responseText.includes('successfully')) {
                    location.reload();
                } else {
                    alert('Error: ' + xhr.responseText);
                }
            }
        };
        xhr.send('user_id=' + encodeURIComponent(userId));
    }
}
</script>

</body>
</html>
