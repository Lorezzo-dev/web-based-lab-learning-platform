<?php

$xmlFile = 'xmlfile/placeholderl.xml';

if (!file_exists($xmlFile)) {
    die("Error: The specified XML file does not exist.");
}

$xml = simplexml_load_file($xmlFile) or die("Error: Cannot load the XML file.");

$labTitle = (string)$xml->title;
$src = (string)$xml->src;

function getModules($filePath) {
    if (file_exists($filePath)) {
        $xml = simplexml_load_file($filePath);
        $modules = [];
        foreach ($xml->children() as $module) {
            $modules[] = $module->getName();
        }
        return $modules;
    }
    return [];
}

$modules = getModules('xmlfile/cms.xml');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $selectedModule = $_POST['module'];
    $labTitle = $_POST['labTitle'];
    $src = $_POST['src'];

    $randomFileName = "xmlfile/lab_" . uniqid() . ".xml";

    $newLabXML = simplexml_load_file($xmlFile);
    $newLabXML->title = $labTitle;
    $newLabXML->src = $src;
    $newLabXML->asXML($randomFileName);

    $cmsXML = simplexml_load_file('xmlfile/cms.xml');
    foreach ($cmsXML->children() as $moduleNode) {
        if ($moduleNode->getName() == $selectedModule) {
            $newLab = $moduleNode->addChild('llink');
            $newLab->addChild('title', $labTitle);
            $newLab->addChild('address', $randomFileName);
            break;
        }
    }
    $cmsXML->asXML('xmlfile/cms.xml');

    echo "<p class='success-message'>Lab successfully added!</p>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Lab</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body, html {
            height: 100%;
            font-family: 'Quicksand', sans-serif;
            background-color: #f8f9fa;
        }
        .container {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            background: #fff;
        }
        form {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            max-width: 1000px;
            width: 100%;
        }
        h1 {
            color: #343a40;
            font-size: 2em;
            margin-bottom: 10px;
        }
        label {
            font-weight: bold;
            color: #343a40;
        }
        .input-field, select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .input-field:focus, select:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }
        .submit-button {
            padding: 10px 20px;
            background-color: #007bff;
            color: #ffffff;
            font-size: 1em;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .submit-button:hover {
            background-color: #0056b3;
        }
        .success-message {
            color: #28a745;
            font-size: 1.2em;
            font-weight: bold;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="container">
    <form method="POST">
        <h1>Add New Lab</h1>
        <label for="moduleDropdown">Select Module:</label><br>
        <select id="moduleDropdown" name="module" required>
            <option value="">-- Select a Module --</option>
            <?php foreach ($modules as $module): ?>
                <option value="<?php echo htmlspecialchars($module); ?>">
                    <?php echo htmlspecialchars($module); ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="labTitle">Lab Title:</label><br>
        <input class="input-field" type="text" name="labTitle" id="labTitle" value="<?php echo htmlspecialchars($labTitle); ?>" required><br><br>

        <label for="src">Lab Source:</label><br>
        <input class="input-field" type="text" name="src" id="src" value="<?php echo htmlspecialchars($src); ?>" required><br><br>

        <input type="submit" class="submit-button" value="Add Lab">
    </form>
</div>
</body>
</html>
