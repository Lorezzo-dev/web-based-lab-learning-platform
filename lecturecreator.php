<?php

$xmlFile = 'xmlfile/placeholder.xml';

// Check if the file exists
if (!file_exists($xmlFile)) {
    die("Error: The specified XML file does not exist.");
}

// Save changes if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Directory to save the XML files
    $directory = 'xmlfile/';
    if (!is_dir($directory)) {
        mkdir($directory, 0777, true); // Create directory if it doesn't exist
    }

    // Generate a random file name
    $randomFileName = uniqid('lecture_', true) . '.xml';
    $filePath = $directory . $randomFileName;

    // Collect data from the form
    $mainTitle = $_POST['mainTitle'] ?? 'Default Title';
    $description = $_POST['description'] ?? 'Default Description';

    // Collect subtopics and their paragraphs
    $subtopics = [];
    foreach ($_POST as $key => $value) {
        if (preg_match('/^subtopic_title_(\d+)$/', $key, $matches)) {
            $index = $matches[1];
            $subtopics[$index]['title'] = $value;
        } elseif (preg_match('/^subtopic_paragraph_(\d+)_(\d+)$/', $key, $matches)) {
            $subtopicIndex = $matches[1];
            $paragraphIndex = $matches[2];
            $subtopics[$subtopicIndex]['paragraphs'][$paragraphIndex] = $value;
        }
    }

    // Collect video data
    $videoEnabled = isset($_POST['videoisOn']) ? 'true' : 'false';
    $videoSource = $_POST['video_source'] ?? '';
    $videoDescription = $_POST['video_description'] ?? '';

    // Collect quiz data
    $quizData = isset($_POST['quiz_data']) ? json_decode($_POST['quiz_data'], true) : [];

    // Create a new XML document
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->formatOutput = true;

    // Root element
    $root = $dom->createElement('lecture');
    $dom->appendChild($root);

    // Title element
    $titleElement = $dom->createElement('title', htmlspecialchars($mainTitle));
    $root->appendChild($titleElement);

    // Description element
    $descriptionElement = $dom->createElement('description', htmlspecialchars($description));
    $root->appendChild($descriptionElement);

    // Subtopics element
    $subtopicsElement = $dom->createElement('subtopics');
    foreach ($subtopics as $subtopic) {
        $subtopicElement = $dom->createElement('subtopic');
        $titleElement = $dom->createElement('title', htmlspecialchars($subtopic['title']));
        $subtopicElement->appendChild($titleElement);

        if (!empty($subtopic['paragraphs'])) {
            foreach ($subtopic['paragraphs'] as $paragraph) {
                $paragraphElement = $dom->createElement('paragraph', htmlspecialchars($paragraph));
                $subtopicElement->appendChild($paragraphElement);
            }
        }
        $subtopicsElement->appendChild($subtopicElement);
    }
    $root->appendChild($subtopicsElement);

    // Video element
    $videoElement = $dom->createElement('video');
    $videoElement->appendChild($dom->createElement('videoisOn', $videoEnabled));
    $videoElement->appendChild($dom->createElement('source', htmlspecialchars($videoSource)));
    $videoElement->appendChild($dom->createElement('description', htmlspecialchars($videoDescription)));
    $root->appendChild($videoElement);

    // Quiz element
    if (!empty($quizData)) {
        $quizElement = $dom->createElement('quiz');
        foreach ($quizData as $question) {
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

            $quizElement->appendChild($questionElement);
        }
        $root->appendChild($quizElement);
    }

    // Save the XML document to the file
    if ($dom->save($filePath)) {

    } else {
        echo "Failed to create the XML file.";
    }

    // Update cms.xml
     $cmsFile = 'xmlfile/cms.xml';
     if (file_exists($cmsFile)) {
         $cmsDom = new DOMDocument('1.0', 'UTF-8');
         $cmsDom->formatOutput = true;
         $cmsDom->preserveWhiteSpace = false;
         $cmsDom->load($cmsFile);

         $selectedModule = isset($_POST['module']) ? $_POST['module'] : '';

         // Locate the selected module
         $module = $cmsDom->getElementsByTagName($selectedModule)->item(0);
         if ($module) {
             // Create new <link> element
             $link = $cmsDom->createElement('link');
             $title = $cmsDom->createElement('title', htmlspecialchars($mainTitle));
             $address = $cmsDom->createElement('address', htmlspecialchars($filePath));
             $link->appendChild($title);
             $link->appendChild($address);

             // Append <link> to the selected module
             $module->appendChild($link);

             // Save changes to cms.xml
             if ($cmsDom->save($cmsFile)) {
                   $saveMessage = "Topic Added! Reload Webpage";
             } else {
                 echo "Failed to update cms.xml.";
             }
         } else {
             echo "Error: pls select a Module!";
         }
     } else {
         echo "Error: cms.xml file does not exist.";

}
}


// Load the XML file for display
$xml = simplexml_load_file($xmlFile) or die("Error: Cannot load the XML file.");

// Extract video section
$videoSource = (string)$xml->video->source;
$videoDescription = (string)$xml->video->description;
$videoisOn = (string)$xml->video->videoisOn;

// Prepare quiz data as JSON
$quizArray = [];
foreach ($xml->quiz->question as $question) {
    $quizQuestion = [
        'text' => (string)$question->text,
        'options' => [],
        'correct' => isset($question->correct) ? (string)$question->correct : ''
    ];

    if (isset($question->option)) {
        foreach ($question->option as $option) {
            $quizQuestion['options'][] = [
                'text' => (string)$option,
                'id' => isset($option['id']) ? (string)$option['id'] : ''
            ];
        }
    }

    $quizArray[] = $quizQuestion;
}

$quizData = json_encode($quizArray, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

// Extract lecture content
$mainTitle = (string)$xml->title;
$description = (string)$xml->description;
$subtopics = [];
foreach ($xml->subtopics->subtopic as $subtopic) {
    $title = (string)$subtopic->title;
    $paragraphs = [];
    foreach ($subtopic->paragraph as $paragraph) {
        $paragraphs[] = (string)$paragraph;
    }
    $subtopics[] = ['title' => $title, 'paragraphs' => $paragraphs];
}

$cmsFile = 'xmlfile/cms.xml';

// Function to parse XML and retrieve module names
function getModules($filePath) {
    if (file_exists($filePath)) {
        $xml = simplexml_load_file($filePath);
        $modules = [];
        foreach ($xml->children() as $module) {
            $modules[] = $module->getName(); // Get the tag name (e.g., Module1, Module2)
        }
        return $modules;
    } else {
        return [];
    }
}

// Get modules for the dropdown
$modules = getModules($cmsFile);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400&display=swap" rel="stylesheet">
    <title>Edit Lecture Content</title>
    <style>
    body {
        font-family: 'Quicksand', sans-serif;
        margin: 0;
        padding: 20px;
        background-color: #f9f9f9;
    }
    .container {
        margin: 0 auto;
        max-width: 1500px;
        background: #fff;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
    textarea, input[type="text"] {
        width: 100%;
        padding: 10px;
        margin: 10px 0;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-family: monospace;
    }
    button {
        background-color: #4CAF50;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
        margin-top: 10px;
    }
    button:hover {
        background-color: #45a049;
    }
    .message {
        color: green;
        margin-bottom: 20px;
    }
    /* Quiz Editor Styles */
    .question-container {
        background-color: #ffffff;
        border: 1px solid #ddd;
        border-radius: 5px;
        margin-bottom: 15px;
        padding: 15px;
    }
    .question-container ul {
        padding: 0;
        margin: 10px 0;
        list-style-type: none;
    }
    .question-container ul li {
        padding: 5px;
        border: 1px solid #ddd;
        border-radius: 5px;
        margin-bottom: 5px;
    }
    label {
        display: block;
        margin-bottom: 5px;
    }
    .temp-input {
        width: calc(100% - 20px);
        padding: 5px;
        margin: 5px 0;
        font-family: monospace;
    }

    .question-container {
        background-color: #ffffff;
        border: 1px solid #ddd;
        border-radius: 5px;
        margin-bottom: 15px;
        padding: 15px;
        position: relative; /* Needed for positioning the delete button */
    }
    .delete-question {
        background-color: #e74c3c; /* Red color */
        color: white;
        border: none;
        border-radius: 5px;
        padding: 5px 10px;
        font-size: 14px;
        cursor: pointer;
        position: absolute; /* Place it relative to the container */
        top: 15px; /* Adjust based on your layout */
        right: 15px; /* Adjust based on your layout */
    }
    .delete-question:hover {
        background-color: #c0392b; /* Darker red for hover */
    }

    details.subtopic {
        margin: 10px 0;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        background-color: #fefefe;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    summary {
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        outline: none;
    }

    summary:hover {
        color: #4CAF50;
    }


    .toolbar {
        display: flex;
        align-items: center;
        background-color: #f0f0f0; /* Light gray background */
        border: 1px solid #ddd;
        padding: 5px;
        border-radius: 5px 5px 0 0; /* Rounded corners on the top */
        margin-bottom: -1px; /* Overlap with the textarea */
        position: relative; /* Required to position on top of textarea */
        z-index: 1; /* Ensure it's above the textarea */
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1); /* Subtle shadow */
    }

    textarea {
        margin-top: 0; /* Remove margin to align with toolbar */
        border-top-left-radius: 0; /* Remove top-left corner radius */
        border-top-right-radius: 0; /* Remove top-right corner radius */
    }

    .toolbar button {
        background-color: #e0e0e0;
        border: none;
        border-radius: 3px;
        margin-right: 5px;
        padding: 5px 10px;
        font-size: 14px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .toolbar button:hover {
        background-color: #d0d0d0; /* Slightly darker gray on hover */
    }

    .toolbar button:focus {
        outline: none;
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.5); /* Highlight focus */
    }

    .delete-paragraph {
        color: #ff4d4d; /* Red color for delete button */
        font-weight: bold;
        background-color: transparent;
        padding: 5px 10px;
        border-radius: 3px;
        border: 1px solid #ff4d4d; /* Red border for better visibility */
    }

    .delete-paragraph:hover {
        background-color: #ff4d4d; /* Red background on hover */
        color: white; /* White text on hover */
    }

     button.delete-subtopic {
      background-color: #e74c3c; /* Red */
      color: white;
      border: none;
      border-radius: 5px;
      padding: 5px 10px;
      font-size: 14px;
      cursor: pointer;
      margin-top: 5px;
  }

     button.delete-subtopic:hover {
      background-color: #c0392b; /* Darker Red */
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
      border-color: #4CAF50;
      outline: none;
      box-shadow: 0 0 5px rgba(76, 175, 80, 0.5);
  }

    </style>
</head>
<body>
    <div class="container">
        <h1>Add Topic</h1>
        <?php if (isset($saveMessage)): ?>
            <div class="message"><?php echo $saveMessage; ?></div>
        <?php endif; ?>
        <form id="lecture-form" method="post" onsubmit="return validateForm()">
          <button type="submit">Add Topic</button><br>
          <!-- Dropdown to select module -->
           <label for="moduleDropdown">Select Module:</label>
           <select id="moduleDropdown" name="module">
               <option value="">-- Select a Module --</option>
               <?php foreach ($modules as $module): ?>
                   <option value="<?php echo htmlspecialchars($module); ?>">
                       <?php echo htmlspecialchars($module); ?>
                   </option>
               <?php endforeach; ?>
           </select>
          <!-- Lecture Content -->
        <h2>Content</h2>
        <details class="subtopic">
             <summary id="lecture-summary"><?php echo htmlspecialchars($mainTitle); ?></summary>
            <div>
                <label for="mainTitle">Main Title:</label>
                <input type="text" id="mainTitle" name="mainTitle" value="<?php echo htmlspecialchars($mainTitle); ?>">
            </div>
            <div>
                <label for="description">Description:</label>
                <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($description); ?></textarea>

            </div>
        </details>

        <!-- Subtopics -->
        <div id="subtopics-container">
            <?php foreach ($subtopics as $index => $subtopic): ?>
                <details class="subtopic" data-index="<?php echo $index; ?>">
                    <summary><?php echo htmlspecialchars($subtopic['title']); ?></summary>
                    <div>
                        <label>Section Title:</label>
                        <input type="text" name="subtopic_title_<?php echo $index; ?>" value="<?php echo htmlspecialchars($subtopic['title']); ?>">
                        <label>Paragraphs:</label>
                        <div class="paragraphs-container">
                            <?php foreach ($subtopic['paragraphs'] as $pIndex => $paragraph): ?>
                                <div class="paragraph">
                                  <div class="toolbar">
                                      <button type="button" onclick="applyFormat(this, 'bold')"><b>B</b></button>
                                      <button type="button" onclick="applyFormat(this, 'italic')"><i>I</i></button>
                                      <button type="button" onclick="applyFormat(this, 'underline')"><u>U</u></button>
                                      <button type="button" onclick="applyFormat(this, 'h1')">H1</button>
                                      <button type="button" onclick="applyFormat(this, 'h2')">H2</button>
                                      <button type="button" onclick="removeTags(this)">Remove Tags</button>
                                      <button type="button" class="delete-paragraph" onclick="deleteParagraph(this)">🗑️</button>

                                  </div>
                                  <textarea name="subtopic_paragraph_<?php echo $index; ?>_<?php echo $pIndex; ?>" rows="3"><?php echo htmlspecialchars($paragraph); ?></textarea>


                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" onclick="addParagraph(<?php echo $index; ?>)">Add Paragraph</button>
                    </div>
                    <button type="button" class="delete-subtopic" onclick="deleteSubtopic(this)">Delete Section</button>
                </details>
            <?php endforeach; ?>
        </div>
        <button type="button" onclick="addSubtopic()">Add Section</button>

            <!-- Video Section -->
            <h2>Video Section</h2>
            <label for="videoisOn">Enable Video:</label>
                   <input type="checkbox" name="videoisOn" <?php echo ($videoisOn == 'true') ? 'checked' : ''; ?>><br><br>

            <label for="video_source">Video Link</label>
            <input type="text" id="video_source" name="video_source" value="<?php echo htmlspecialchars($videoSource); ?>">

            <label for="video_description">Video Description</label>
            <textarea id="video_description" name="video_description" rows="3"><?php echo htmlspecialchars($videoDescription); ?></textarea>

            <!-- Quiz Section -->
            <h2>Quiz Section</h2>
            <label for="quizIsOn">Enable Quiz:</label>
            <input type="checkbox" name="quizIsOn" <?php echo (isset($xml->quiz->quizIsOn) && $xml->quiz->quizIsOn == 'true') ? 'checked' : ''; ?>><br><br>
            <button type="button" onclick="addQuestion()">Add Question</button>
            <div id="quiz-container"></div>

            <!-- Hidden Input -->
            <input type="hidden" name="quiz_data" id="quiz-data">
            <input type="hidden" name="xml_content" id="xml-content" value="<?php echo htmlspecialchars($xml->asXML()); ?>">


        </form>
    </div>

    <script>
    let quizData = <?php echo $quizData; ?>;

    const quizContainer = document.getElementById('quiz-container');
    const subtopicsContainer = document.getElementById('subtopics-container');

    // Render Quiz
    function renderQuiz() {
        quizContainer.innerHTML = '';
        quizData.forEach((question, index) => {
            const questionHtml = `
                <div class="question-container" data-index="${index}">
                    <button type="button" class="delete-question" onclick="deleteQuestion(${index})">Delete</button>
                    <label class="editable" data-type="text" data-index="${index}">${question.text}</label>
                    <label>Options:</label>
                    <ul>
                        ${question.options.map((option, optIndex) => `
                            <li>
                                <label class="editable" data-type="option" data-index="${index}" data-optindex="${optIndex}">
                                    ${option.text}
                                </label>
                            </li>
                        `).join('')}
                    </ul>
                    <label>Correct Answer:</label>
                    <input type="text" class="correct-answer" value="${question.correct}" oninput="updateCorrectAnswer(${index}, this.value)">
                </div>
            `;
            quizContainer.insertAdjacentHTML('beforeend', questionHtml);
        });

        document.querySelectorAll('.editable').forEach(label => {
            label.addEventListener('click', turnIntoTextField);
        });
    }

    function turnIntoTextField(event) {
        const label = event.target;
        const text = label.textContent.trim();
        const type = label.getAttribute('data-type');
        const index = parseInt(label.getAttribute('data-index'));
        const optIndex = label.getAttribute('data-optindex');

        const input = document.createElement('input');
        input.type = 'text';
        input.value = text;
        input.className = 'temp-input';

        label.replaceWith(input);
        input.focus();

        function saveChanges() {
            const newValue = input.value.trim();
            if (type === 'text') {
                quizData[index].text = newValue;
            } else if (type === 'option') {
                quizData[index].options[optIndex].text = newValue;
            }

            const updatedLabel = document.createElement('label');
            updatedLabel.className = 'editable';
            updatedLabel.setAttribute('data-type', type);
            updatedLabel.setAttribute('data-index', index);
            if (type === 'option') updatedLabel.setAttribute('data-optindex', optIndex);
            updatedLabel.textContent = newValue;
            updatedLabel.addEventListener('click', turnIntoTextField);
            input.replaceWith(updatedLabel);
        }

        input.addEventListener('blur', saveChanges);
        input.addEventListener('keydown', event => {
            if (event.key === 'Enter') saveChanges();
        });
    }

    function addQuestion() {
        const newQuestion = {
            text: "New Question",
            options: [
                { text: "Option A", id: "a" },
                { text: "Option B", id: "b" },
                { text: "Option C", id: "c" },
                { text: "Option D", id: "d" }
            ],
            correct: ""
        };
        quizData.push(newQuestion);
        renderQuiz();
    }

    function deleteQuestion(index) {
        quizData.splice(index, 1);
        renderQuiz();
    }

    function updateCorrectAnswer(index, value) {
        quizData[index].correct = value;
    }


    // Form Submission
    document.getElementById('lecture-form').addEventListener('submit', () => {
        document.getElementById('quiz-data').value = JSON.stringify(quizData);
    });

    // Initial Rendering
    renderQuiz();

    // Function to add a new subtopic
    function addSubtopic() {
        const container = document.getElementById('subtopics-container');
        const subtopicCount = container.querySelectorAll('.subtopic').length;

        const subtopicDetails = document.createElement('details');
        subtopicDetails.classList.add('subtopic');
        subtopicDetails.setAttribute('data-index', subtopicCount);

        subtopicDetails.innerHTML = `
            <summary>New Subtopic</summary>
            <div>
                <label>Subtopic Title:</label>
                <input type="text" name="subtopic_title_${subtopicCount}" value="">
                <label>Paragraphs:</label>
                <div class="paragraphs-container">
                    <div class="paragraph">
                    <div class="toolbar">
                        <button type="button" onclick="applyFormat(this, 'bold')"><b>B</b></button>
                        <button type="button" onclick="applyFormat(this, 'italic')"><i>I</i></button>
                        <button type="button" onclick="applyFormat(this, 'underline')"><u>U</u></button>
                        <button type="button" onclick="applyFormat(this, 'h1')">H1</button>
                        <button type="button" onclick="applyFormat(this, 'h2')">H2</button>
                        <button type="button" onclick="removeTags(this)">Remove Tags</button>
                        <button type="button" class="delete-paragraph" onclick="deleteParagraph(this)">🗑️</button>
                    </div>
                        <textarea name="subtopic_paragraph_${subtopicCount}_0" rows="3"></textarea>
                    </div>
                </div>
                <button type="button" onclick="addParagraph(${subtopicCount})">Add Paragraph</button>
            </div>
            <button type="button" class="delete-subtopic" onclick="deleteSubtopic(this)">Delete Subtopic</button>
        `;

        container.appendChild(subtopicDetails);
    }

    // Function to add a paragraph to a specific subtopic
    function addParagraph(subtopicIndex) {
        const subtopic = document.querySelector(`[data-index="${subtopicIndex}"]`);
        const container = subtopic.querySelector('.paragraphs-container');

        const paragraphCount = container.querySelectorAll('.paragraph').length;
        const paragraphDiv = document.createElement('div');
        paragraphDiv.classList.add('paragraph');

        paragraphDiv.innerHTML = `
        <div class="toolbar">
            <button type="button" onclick="applyFormat(this, 'bold')"><b>B</b></button>
            <button type="button" onclick="applyFormat(this, 'italic')"><i>I</i></button>
            <button type="button" onclick="applyFormat(this, 'underline')"><u>U</u></button>
            <button type="button" onclick="applyFormat(this, 'h1')">H1</button>
            <button type="button" onclick="applyFormat(this, 'h2')">H2</button>
            <button type="button" onclick="removeTags(this)">Remove Tags</button>
            <button type="button" class="delete-paragraph" onclick="deleteParagraph(this)">🗑️</button>

        </div>
            <textarea name="subtopic_paragraph_${subtopicIndex}_${paragraphCount}" rows="3"></textarea>

        `;

        container.appendChild(paragraphDiv);
    }

    // Function to delete a specific paragraph
    function deleteParagraph(button) {
        // Find the parent '.paragraph' element
        const paragraph = button.closest('.paragraph');
        if (paragraph) {
            paragraph.remove(); // Remove the entire paragraph container
        } else {
            console.error('Delete button is not inside a paragraph container.');
        }
    }

    // Function to delete a specific subtopic
    function deleteSubtopic(button) {
        const subtopicDiv = button.closest('.subtopic');
        subtopicDiv.remove();
    }

    function updateSummary(newTitle) {
    const summaryElement = document.getElementById('lecture-summary');
    summaryElement.textContent = newTitle || 'Lecture Content'; // Fallback if empty
}

function applyFormat(button, format) {
    const paragraph = button.closest('.paragraph');
    const textarea = paragraph.querySelector('textarea');
    if (!textarea) return;

    // Apply formatting by wrapping text with HTML tags
    const selectedText = textarea.value.substring(
        textarea.selectionStart,
        textarea.selectionEnd
    );

    let formattedText;
    switch (format) {
        case 'bold':
            formattedText = `<b>${selectedText}</b>`;
            break;
        case 'italic':
            formattedText = `<i>${selectedText}</i>`;
            break;
        case 'underline':
            formattedText = `<u>${selectedText}</u>`;
            break;
        case 'h1':
            formattedText = `<h1>${selectedText}</h1>`;
            break;
        case 'h2':
            formattedText = `<h2>${selectedText}</h2>`;
            break;
        default:
            console.warn('Unknown format:', format);
            return;
    }

    // Replace selected text with formatted text
    textarea.setRangeText(formattedText, textarea.selectionStart, textarea.selectionEnd);
}

function removeTags(button) {
    const paragraph = button.closest('.paragraph');
    const textarea = paragraph.querySelector('textarea');
    if (!textarea) return;

    const selectedText = textarea.value.substring(
        textarea.selectionStart,
        textarea.selectionEnd
    );

    if (!selectedText.trim()) {
        alert('Please select text to remove tags from.');
        return;
    }

    // Remove HTML tags using a regular expression
    const plainText = selectedText.replace(/<\/?[^>]+(>|$)/g, '');

    // Replace selected text with plain text
    textarea.setRangeText(plainText, textarea.selectionStart, textarea.selectionEnd);
}

// Helper function to wrap selected text with tags
function wrapText(textarea, openTag, closeTag) {
    const { selectionStart, selectionEnd, value } = textarea;
    const selectedText = value.slice(selectionStart, selectionEnd);
    const before = value.slice(0, selectionStart);
    const after = value.slice(selectionEnd);

    textarea.value = before + openTag + selectedText + closeTag + after;
    textarea.focus();
    textarea.setSelectionRange(selectionStart + openTag.length, selectionEnd + openTag.length);
}

function validateForm() {
    const mainTitle = document.getElementById('mainTitle').value.trim();
    const description = document.getElementById('description').value.trim();

    if (!mainTitle || !description) {
        alert('Please fill out the main title and description.');
        return false;
    }

    return true;
}
    </script>
</body>
</html>
