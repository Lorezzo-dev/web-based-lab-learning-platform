<?php
include "connection.php";

$conn = new mysqli($servername, $db_username, $db_password, $db_name);

if ($conn->connect_error) {
    die(json_encode([]));
}

// Modify SQL to include the role and order by role (Admin & Teacher on top)
$sql = "SELECT username, role FROM users ORDER BY FIELD(role, 1, 2) DESC, username ASC";
$result = $conn->query($sql);

$users = [];
while ($row = $result->fetch_assoc()) {
    // Prefix 'Teacher - ' for role 2
    if ($row['role'] == 2 || $row['role'] == 1) {
        $row['username'] = "Teacher - " . $row['username'];
    }
    $users[] = $row;
}

echo json_encode($users);

$conn->close();
?>
