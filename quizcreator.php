<?php
$xmlFile = 'xmlfile/placeholderq.xml';

// Check if the file exists
if (!file_exists($xmlFile)) {
    die("Error: The specified XML file does not exist.");
}

// Save changes if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Directory to save the quiz XML files
    $directory = 'xmlfile/';
    if (!is_dir($directory)) {
        mkdir($directory, 0777, true); // Create directory if it doesn't exist
    }

    // Generate a random file name
    $randomFileName = uniqid('quiz_', true) . '.xml';
    $filePath = $directory . $randomFileName;

    // Collect data from the form
    $quizTitle = $_POST['quizTitle'] ?? 'Default Quiz Title';
    $quizTimer = $_POST['quizTimer'] ?? '300';

    // Collect quiz questions
    $questions = isset($_POST['questions']) ? json_decode($_POST['questions'], true) : [];

    // Create a new XML document
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->formatOutput = true;

    // Root element
    $root = $dom->createElement('quiz');
    $dom->appendChild($root);

    // Title element
    $titleElement = $dom->createElement('title', htmlspecialchars($quizTitle));
    $root->appendChild($titleElement);

    // Timer element
    $timerElement = $dom->createElement('timer', htmlspecialchars($quizTimer));
    $root->appendChild($timerElement);

    // Add questions
    foreach ($questions as $question) {
        $questionElement = $dom->createElement('question');

        $textElement = $dom->createElement('text', htmlspecialchars($question['text']));
        $questionElement->appendChild($textElement);

        foreach ($question['options'] as $option) {
            $optionElement = $dom->createElement('option', htmlspecialchars($option['text']));
            $optionElement->setAttribute('id', htmlspecialchars($option['id']));
            $questionElement->appendChild($optionElement);
        }

        if (!empty($question['correct'])) {
            $correctElement = $dom->createElement('correct', htmlspecialchars($question['correct']));
            $questionElement->appendChild($correctElement);
        }

        $root->appendChild($questionElement);
    }

    // Save the quiz XML file
    if ($dom->save($filePath)) {
        // Update cms.xml
        $cmsFile = 'xmlfile/cms.xml';
        if (file_exists($cmsFile)) {
            $cmsDom = new DOMDocument('1.0', 'UTF-8');
            $cmsDom->formatOutput = true;
            $cmsDom->preserveWhiteSpace = false;
            $cmsDom->load($cmsFile);

            $selectedModule = $_POST['module'] ?? '';

            // Locate the selected module
            $module = $cmsDom->getElementsByTagName($selectedModule)->item(0);
            if ($module) {
                // Create new <qlink> element
                $qlink = $cmsDom->createElement('qlink');
                $title = $cmsDom->createElement('title', htmlspecialchars($quizTitle));
                $address = $cmsDom->createElement('address', htmlspecialchars($filePath));
                $qlink->appendChild($title);
                $qlink->appendChild($address);

                // Append <qlink> to the selected module
                $module->appendChild($qlink);

                // Save changes to cms.xml
                if ($cmsDom->save($cmsFile)) {

                    echo "success"; // Success message
                    exit;
                } else {
                    echo "Failed to update cms.xml.";
                    exit;
                }
            } else {
                echo "Error: Please select a Module!";
                exit;
            }
        } else {
            echo "Error: cms.xml file does not exist.";
            exit;
        }
    } else {
        echo "Failed to create the quiz XML file.";
        exit;
    }


  }

// Load the XML file for display
$xml = simplexml_load_file($xmlFile) or die("Error: Cannot load the XML file.");

// Extract quiz content
$quizTitle = (string)$xml->title;
$quizTimer = (string)$xml->timer;
$questions = [];
foreach ($xml->question as $question) {
    $quizQuestion = [
        'text' => (string)$question->text,
        'options' => [],
        'correct' => isset($question->correct) ? (string)$question->correct : ''
    ];

    foreach ($question->option as $option) {
        $quizQuestion['options'][] = [
            'text' => (string)$option,
            'id' => (string)$option['id']
        ];
    }

    $questions[] = $quizQuestion;
}

// Get available modules for the dropdown
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400&display=swap" rel="stylesheet">
    <title>Quiz Editor</title>
    <style>
    body {
        font-family: 'Quicksand', sans-serif;
        margin: 0;
        padding: 20px;
        background-color: #f8f9fa;
    }
    h1, h2 {
        color: #343a40;
    }
    h1 {
        font-size: 2em;
        margin-bottom: 10px;
    }
    h2 {
        font-size: 1.5em;
        margin-bottom: 20px;
    }
    .submit-button {
        display: inline-block;
        margin-bottom: 20px;
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
    .add-button {
        display: block;
        margin: 20px auto;
        padding: 10px 20px;
        background-color: #28a745;
        color: #ffffff;
        font-size: 1em;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        text-align: center;
    }
    .add-button:hover {
        background-color: #218838;
    }
    .question-container {
        position: relative;
        background-color: #ffffff;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        margin-bottom: 15px;
        padding: 15px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    .question-container h3 {
        margin: 0;
        color: #495057;
        font-size: 1.25em;
    }
    .question-container ul {
        padding: 10px 20px;
        margin: 10px 0;
        list-style-type: none;
    }
    .question-container ul li {
        color: #6c757d;
        font-size: 1em;
        margin: 5px 0;
        counter-increment: list-item;
        cursor: pointer;
    }
    .question-container ul li::before {
        content: counter(list-item, lower-alpha) ". ";
        color: #495057;
        font-weight: bold;
    }
    .delete-button {
        position: absolute;
        top: 10px;
        right: 10px;
        padding: 5px 10px;
        background-color: #dc3545;
        color: #ffffff;
        font-size: 0.9em;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }
    .delete-button:hover {
        background-color: #c82333;
    }
    .editable {
        border: 1px solid #dee2e6;
        padding: 5px;
        width: 100%;
        border-radius: 5px;
    }
    .correct-answer {
        margin-top: 10px;
    }
    .correct-answer label {
        display: inline-block;
        margin-bottom: 5px;
        font-weight: bold;
        color: #495057;
    }
    .correct-answer input {
        width: 100%;
        padding: 8px;
        font-size: 1em;
        border: 1px solid #dee2e6;
        border-radius: 5px;
    }
    .editable-field {
        border: 1px solid #ced4da; /* Light gray border */
        background-color: #f8f9fa; /* Subtle background for better contrast */
        padding: 8px; /* Add some padding for a better feel */
        border-radius: 5px; /* Rounded corners */
        font-size: 1em; /* Consistent font size */
        display: inline-block; /* Inline-block allows proper sizing */
        width: 100%; /* Ensure it spans the width of the container */
        box-sizing: border-box; /* Includes padding in width calculation */
        cursor: text; /* Show text cursor to indicate it's editable */
    }

    .editable-field:focus {
        outline: none; /* Remove default outline */
        border: 1px solid #007bff; /* Blue border on focus */
        background-color: #e9ecef; /* Slightly lighter background on focus */
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.5); /* Add subtle glow */
        transition: all 0.3s ease; /* Smooth transition for focus state */
    }

    .input-field {
        border: 1px solid #ced4da; /* Light gray border */
        background-color: #f8f9fa; /* Subtle background for better contrast */
        padding: 10px; /* Add padding for better feel */
        border-radius: 5px; /* Rounded corners */
        font-size: 1em; /* Consistent font size */
        width: 100%; /* Ensure it spans the width of its container */
        box-sizing: border-box; /* Include padding in width calculation */
        color: #495057; /* Darker text for better readability */
        transition: border-color 0.3s, box-shadow 0.3s; /* Smooth transitions for interactions */
    }

    .input-field:focus {
        outline: none; /* Remove default outline */
        border: 1px solid #007bff; /* Highlighted border color on focus */
        background-color: #ffffff; /* Slightly brighter background on focus */
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.5); /* Add a subtle blue glow */
    }

    .input-container label {
        display: block; /* Ensure the label is on its own line */
        margin-bottom: 5px; /* Spacing between the label and the input */
        font-weight: bold; /* Emphasize the label text */
        color: #343a40; /* Slightly darker gray for better visibility */
    }

    .timer-input {
        max-width: 150px; /* Limit the width for timer inputs */
        margin-bottom: 20px; /* Add spacing below the input */
    }

    .correct-answer {
        margin-top: 15px;
    }

    .correct-answer label {
        font-weight: bold;
        margin-bottom: 5px;
        color: #495057;
        display: block;
    }

    .correct-input {
        border: 1px solid #ced4da; /* Light gray border */
        padding: 10px; /* Add padding for better usability */
        font-size: 1em; /* Consistent font size */
        background-color: #f8f9fa; /* Slightly off-white for contrast */
        border-radius: 5px; /* Rounded corners */
        width: 100%; /* Span the entire available width */
        box-sizing: border-box; /* Include padding in width */
        color: #495057; /* Darker text for readability */
        transition: border-color 0.3s ease, box-shadow 0.3s ease; /* Smooth transitions */
    }

    .correct-input:focus {
        border-color: #007bff; /* Highlight border on focus */
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.5); /* Subtle blue glow */
        outline: none; /* Remove default outline */
        background-color: #ffffff; /* Slightly lighter background on focus */
    }

    select {
        width: 100%;
        padding: 10px;
        margin: 10px 0;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-family: 'Quicksand', sans-serif;
        font-size: 16px;
        background-color: #ffffff;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        cursor: pointer;
    }
    select:focus {
        border-color: #007bff;
        outline: none;
        box-shadow: 0 0 5px rgba(76, 175, 80, 0.5);
    }


    </style>
</head>
<body>
    <h1>Add Quiz</h1>
    <label for="moduleDropdown">Select Module:</label>
    <select id="moduleDropdown" name="module">
        <option value="">-- Select a Module --</option>
        <?php foreach ($modules as $module): ?>
            <option value="<?php echo htmlspecialchars($module); ?>">
                <?php echo htmlspecialchars($module); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <label for="quizTitle">Quiz Title:</label>
    <input class="input-field" type="text" id="quizTitle" name="quizTitle" value="<?php echo htmlspecialchars($quizTitle); ?>" required>
    <div>
        <label for="timer-minutes">Timer (minutes):</label>
        <input type="number" id="timer-minutes" class="input-field" value="<?php echo htmlspecialchars($xml->timer / 60); ?>">
    </div>
    <br>
    <button class="submit-button">Submit</button><br>
    <div id="questions-container">
        <?php
        $index = 0;
        foreach ($xml->question as $question): ?>
            <div class="question-container" data-index="<?php echo $index; ?>">
                <button class="delete-button">Delete</button>
                <h3>Question <?php echo ++$index; ?>:</h3>
                <p class="editable-field" contenteditable="true" data-type="question"><?php echo htmlspecialchars($question->text); ?></p>
                <ul>
                    <?php foreach ($question->option as $option): ?>
                        <li class="editable-field" contenteditable="true" data-type="option" data-option-id="<?php echo $option['id']; ?>">
                            <?php echo htmlspecialchars($option); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="correct-answer">
                    <label for="correct-<?php echo $index; ?>">Correct Answer:</label>
                    <input type="text" id="correct-<?php echo $index; ?>" value="<?php echo htmlspecialchars($question->correct); ?>">
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <button class="add-button">Add Question</button>
    <form id="submit-form" method="POST" style="display:none;">
        <input type="hidden" name="timer" id="timer-data">
        <input type="hidden" name="questions" id="questions-data">
    </form>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
         const questionsContainer = document.getElementById('questions-container');

         // Convert editable fields back to text when losing focus or pressing Enter
         questionsContainer.addEventListener('focusout', function (e) {
             if (e.target.matches('.editable-field')) {
                 e.target.textContent = e.target.textContent.trim();
             }
         });

         questionsContainer.addEventListener('keypress', function (e) {
             if (e.target.matches('.editable-field') && e.key === 'Enter') {
                 e.preventDefault();
                 e.target.blur();
             }
         });

         // Add new question
         document.querySelector('.add-button').addEventListener('click', function () {
             const questionHTML = `
                 <div class="question-container">
                     <button class="delete-button">Delete</button>
                     <h3>Question:</h3>
                     <p class="editable-field" contenteditable="true" data-type="question">New Question</p>
                     <ul>
                         <li class="editable-field" contenteditable="true" data-type="option" data-option-id="a">Option A</li>
                         <li class="editable-field" contenteditable="true" data-type="option" data-option-id="b">Option B</li>
                         <li class="editable-field" contenteditable="true" data-type="option" data-option-id="c">Option C</li>
                         <li class="editable-field" contenteditable="true" data-type="option" data-option-id="d">Option D</li>
                     </ul>
                     <div class="correct-answer">
                         <label>Correct Answer:</label>
                         <input type="text">
                     </div>
                 </div>`;
             questionsContainer.insertAdjacentHTML('beforeend', questionHTML);
             attachDeleteListeners();
         });

         // Submit updated quiz data
         document.querySelector('.submit-button').addEventListener('click', async function () {
             const timer = document.getElementById('timer-minutes').value.trim();
             const selectedModule = document.getElementById('moduleDropdown').value;
             const quizTitle = document.getElementById('quizTitle').value;

             if (!selectedModule) {
                 alert("Please select a module.");
                 return;
             }

             const questions = Array.from(document.querySelectorAll('.question-container')).map(container => ({
                 text: container.querySelector('[data-type="question"]').textContent.trim(),
                 options: Array.from(container.querySelectorAll('[data-type="option"]')).map(option => ({
                     text: option.textContent.trim(),
                     id: option.getAttribute('data-option-id')
                 })),
                 correct: container.querySelector('input[type="text"]').value.trim()
             }));

             const formData = new FormData();
             formData.append('quizTitle', quizTitle);
             formData.append('quizTimer', timer * 60);
             formData.append('module', selectedModule);
             formData.append('questions', JSON.stringify(questions));

             try {
                 const response = await fetch('', {
                     method: 'POST',
                     body: formData
                 });

                 const result = await response.text();

                 if (result.trim() === "success") {
                     alert("Quiz added successfully!");
                     if (window.parent) {
                         window.parent.location.reload(); // Refresh the parent window
                         window.location.href = window.location.href; // Refresh the current window to clear the form
                     } else {
                         window.location.href = window.location.href; // Fallback for standalone use
                     }
                 } else {
                     alert("Error: " + result);
                 }
             } catch (error) {
                 console.error('Error saving quiz:', error);
                 alert('An error occurred while saving the quiz.');
             }
         });

         // Add delete button functionality to existing questions
         function attachDeleteListeners() {
             document.querySelectorAll('.delete-button').forEach(button => {
                 button.removeEventListener('click', deleteQuestion);
                 button.addEventListener('click', deleteQuestion);
             });
         }

         function deleteQuestion(event) {
             const questionContainer = event.target.closest('.question-container');
             questionContainer.remove();
         }

         // Initialize delete button listeners for existing questions
         attachDeleteListeners();
     });
    </script>
</body>
</html>
