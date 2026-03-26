<?php
// Get the user_id from the query parameter
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 'Not provided';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modify Account</title>
    <style>
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
    .container {
        background-color: #fff;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        text-align: center;
    }
    </style>
</head>
<body>
    <div class="container">
        <h1>Modify Account</h1>
        <p>User ID received: <?php echo htmlspecialchars($user_id); ?></p>
    </div>
</body>
</html>
