<?php
$xmlFile = 'xmlfile/cms.xml';
$message = "";

// Load the XML file
if (file_exists($xmlFile)) {
    $xml = simplexml_load_file($xmlFile);
} else {
    $message = "XML file not found.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'add') {
        // Add a new module
        $lastModuleNumber = count($xml->children());
        $newModuleNumber = $lastModuleNumber + 1;

        $xml->addChild("Module$newModuleNumber");
        file_put_contents($xmlFile, $xml->asXML());
        $message = "Module$newModuleNumber added successfully!";
    } elseif ($_POST['action'] === 'remove') {
        // Remove the last module (not Modules 1–4)
        $modules = $xml->children();
        $lastModule = end($modules);
        $lastModuleName = $lastModule->getName();

        if ((int)substr($lastModuleName, 6) > 4) {
            $linkedFile = isset($lastModule->link->address) ? (string)$lastModule->link->address : null;

            if ($linkedFile && file_exists($linkedFile)) {
                unlink($linkedFile);
            }

            unset($xml->$lastModuleName);
            file_put_contents($xmlFile, $xml->asXML());
            $message = "$lastModuleName removed successfully!";
        } else {
            $message = "Cannot remove Module1 to Module4.";
        }
    }
    // Use JavaScript to refresh the parent and current windows after the operation
    echo "<script>
        if (window.parent) {
            window.parent.location.reload(); // Refresh the parent window
            window.location.href = window.location.href; // Refresh the current window to clear the form
        } else {
            window.location.href = window.location.href; // Fallback for standalone use
        }
    </script>";
    exit; // Prevent further execution

}





?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">
    <title>Manage Modules</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body, html {
            height: 100%;
            font-family: 'Quicksand', sans-serif;
            background-color: #f9f9f9;
        }
        .container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
            background: #fff;
        }
        .module-container {
            width: 100%;
            max-width: 800px;
            border: 1px solid #ddd;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            background-color: #fff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        button:hover {
            background-color: #45a049;
        }
        .remove-button {
            background-color: #e74c3c;
            padding: 10px 15px;
        }
        .remove-button:hover {
            background-color: #c0392b;
        }
        .message {
            margin-bottom: 15px;
            font-size: 14px;
            color: green;
        }
    </style>
    <script>
        function confirmRemove() {
            return confirm("Are you sure you want to remove the last module? This action cannot be undone.");
        }
    </script>
</head>
<body>
<div class="container">
    <h1>Manage Modules</h1>
    <p class="message"><?php echo htmlspecialchars($message); ?></p>

    <?php if (!empty($xml)) : ?>
        <?php foreach ($xml->children() as $module) : ?>
            <div class="module-container">
                <span><?php echo htmlspecialchars($module->getName()); ?></span>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <form method="post" style="display: flex; gap: 10px;">
        <button type="submit" name="action" value="add">Add Module</button>
        <button type="submit" name="action" value="remove" class="remove-button" onclick="return confirmRemove();">Remove Last Module</button>
    </form>
</div>
</body>
</html>
