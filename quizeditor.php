<?php
if (isset($_GET['file'])) {
    $filePath = $_GET['file'];

    if (file_exists($filePath)) {
        $xml = simplexml_load_file($filePath) or die("Error: Cannot load quiz data");
    } else {
        die("Error: File not found");
    }
} else {
    die("Error: No file specified");
}

// Handle form submission to update the XML file
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['questions'], $_POST['timer'], $_POST['quizTitle'])) {
    $questionsData = json_decode($_POST['questions'], true);
    $timerMinutes = intval($_POST['timer']) * 60;
    $quizTitle = $_POST['quizTitle'];

    // Create a new XML object
    $newXml = new SimpleXMLElement('<quiz></quiz>');

    // Add the title and updated timer
    $newXml->addChild('title', htmlspecialchars($quizTitle));
    $newXml->addChild('timer', htmlspecialchars($timerMinutes));

    // Add questions to the XML
    foreach ($questionsData as $q) {
        $question = $newXml->addChild('question');
        $question->addChild('text', htmlspecialchars($q['text']));
        foreach ($q['options'] as $option) {
            $opt = $question->addChild('option', htmlspecialchars($option['text']));
            $opt->addAttribute('id', htmlspecialchars($option['id']));
        }
        $question->addChild('correct', htmlspecialchars($q['correct']));
    }

    // Save the updated XML to the file
    $newXml->asXML($filePath);
    echo json_encode(['success' => true, 'message' => 'Quiz updated successfully!']);
    exit;
}

$exceptionFiles = ['quiz1data.xml', 'quiz2data.xml', 'quiz3data.xml', 'quiz4data.xml', 'quarterlydata.xml'];
$currentFile = basename($filePath);

// Check if current file is not an exception
$showDeleteButton = !in_array($currentFile, $exceptionFiles);


// Check if it's a delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete']) && $_POST['delete'] === 'true') {
    $fileToDelete = $_POST['xml_file'];

    if (file_exists($fileToDelete)) {
        // Delete the XML file
        if (unlink($fileToDelete)) {
            // Update cms.xml
            $cmsFile = 'xmlfile/cms.xml';
            if (file_exists($cmsFile)) {
                $cmsDom = new DOMDocument('1.0', 'UTF-8');
                $cmsDom->formatOutput = true;
                $cmsDom->preserveWhiteSpace = false;
                $cmsDom->load($cmsFile);

                $xpath = new DOMXPath($cmsDom);
                $linkNodes = $xpath->query("//qlink[address[text() = '$fileToDelete']]");

                foreach ($linkNodes as $linkNode) {
                    $linkNode->parentNode->removeChild($linkNode); // Remove the <link> element
                }

                // Save the updated cms.xml
                if ($cmsDom->save($cmsFile)) {
                    echo " File Deletion Succesful! Please exit and reload the page";
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to update cms.xml.']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'cms.xml file does not exist.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete the XML file.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'The specified XML file does not exist.']);
    }
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

    .button-delete-lecture {
        background-color: #e74c3c; /* Red background color */
        color: white; /* White text color */
        padding: 10px 20px; /* Button padding */
        border: none; /* Remove border */
        border-radius: 5px; /* Rounded corners */
        cursor: pointer; /* Pointer cursor on hover */
        font-size: 16px; /* Font size */
        margin-top: 10px; /* Spacing above the button */
        transition: background-color 0.3s ease; /* Smooth hover effect */
    }

    .button-delete-lecture:hover {
        background-color: #c0392b; /* Darker red on hover */
    }

    </style>
</head>
<body>
    <h1>Quiz Editor</h1>
    <label for="quizTitle">Quiz Title:</label>
    <input class="input-field" type="text" id="quizTitle" name="quizTitle" value="<?php echo htmlspecialchars($xml->title); ?>" required>
    <div>
        <label for="timer-minutes">Timer (minutes):</label>
        <input type="number" id="timer-minutes" class="input-field" value="<?php echo htmlspecialchars($xml->timer / 60); ?>">
    </div>
    <br>
    <button class="submit-button">Submit</button>
    <?php if ($showDeleteButton): ?>
        <button class="button-delete-lecture" type="button" onclick="deleteFile()">Delete Quiz</button>
    <?php endif; ?>

    <h2>Editing File: <?php echo htmlspecialchars($filePath); ?></h2>
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
             const quizTitle = document.getElementById('quizTitle').value;
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
             formData.append('timer', timer);
             formData.append('questions', JSON.stringify(questions));

             try {
                 const response = await fetch('', { // Current page URL
                     method: 'POST',
                     body: formData
                 });

                 const result = await response.json();

                 if (result.success) {
                     alert(result.message); // Show popup message
                     if (window.parent) {
                         window.parent.location.reload(); // Refresh the parent window
                         window.location.href = window.location.href; // Refresh the current window to clear the form
                     } else {
                         window.location.href = window.location.href; // Fallback for standalone use
                     }
                 } else {
                     alert('Failed to save the quiz file. Please try again.');
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


     function deleteFile() {
         if (confirm('Are you sure you want to delete this quiz and remove it from the module?')) {
             // Create a form to submit the delete request
             let form = document.createElement('form');
             form.method = 'post';
             form.action = '';

             // Add hidden inputs to indicate the delete action
             let deleteInput = document.createElement('input');
             deleteInput.type = 'hidden';
             deleteInput.name = 'delete';
             deleteInput.value = 'true';
             form.appendChild(deleteInput);

             // Add the current file name as hidden input
             let fileInput = document.createElement('input');
             fileInput.type = 'hidden';
             fileInput.name = 'xml_file';
             fileInput.value = '<?php echo $filePath;; ?>';
             form.appendChild(fileInput);

             // Submit the form
             document.body.appendChild(form);
             form.submit();
         }
     }
    </script>
</body>
</html>
