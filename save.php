<?php
session_start();

// Ensure the user is logged in or has the right permissions
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit();
}

// Check if content is provided
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['content'])) {
        $content = $data['content'];

        // Write content back to the XML file
        $filePath = 'xmlfile/experiment1.xml';
        if (file_put_contents($filePath, $content) !== false) {
            echo json_encode(['success' => true, 'message' => 'Changes saved successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error saving changes to XML file.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No content provided.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
