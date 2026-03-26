<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Database connection parameters
include "connection.php";

// Establish connection
$conn = new mysqli($servername, $db_username, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['grade'])) {
    $user_id = $_SESSION['user_id'];
    $grade = $_POST['grade'];

    // Prepare SQL statement to update module3_quiz_grade
    $sql = "UPDATE grades SET quarterly_quiz_grade = ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("di", $grade, $user_id); // Bind parameters

    // Execute the update query
    if ($stmt->execute()) {
        echo "Grade updated successfully";
    } else {
        echo "Error updating grade: " . $stmt->error;
    }

    // Prepare SQL statement to update module2quiz_completed to 1
    $sql = "UPDATE progress SET module4quiz_completed = 1 WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id); // Bind parameters

    // Execute the update query
    if ($stmt->execute()) {
        echo "Quarterly quiz completion status updated successfully";
    } else {
        echo "Error updating completion status: " . $stmt->error;
    }

    // Close statement and connection
    $stmt->close();

    // Fetch user role from the database
    $sql = "SELECT role FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id); // Bind parameter
    $stmt->execute();
    $stmt->bind_result($role);
    $stmt->fetch();
    $stmt->close();

    $conn->close();
    exit();
}

// Prepare SQL statement to fetch username based on user_id
$user_id = $_SESSION['user_id'];
$sql = "SELECT username FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id); // Bind parameter
$stmt->execute();
$stmt->store_result();

// Check if user exists
if ($stmt->num_rows == 1) {
    // Bind result variables
    $stmt->bind_result($username);
    $stmt->fetch();
} else {
    // If user not found (though it should not happen if session user_id is valid)
    $username = "Unknown";
}

// Close statement
$stmt->close();

include 'sidebar.php'; // Include the sidebar


// Load quiz data from XML
$xml = simplexml_load_file('xmlfile/quarterlydata.xml') or die("Error: Cannot load quiz data");

$timerValue = (int)$xml->timer; // Read the timer value in seconds
$formattedTimer = 0;
$quizTitle = (string)$xml->title;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GenB Distance Learning</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400&display=swap" rel="stylesheet">
    <style>
    body {
        font-family: 'Quicksand', sans-serif; /* Updated font */
        background-color: #f9f9f9;
        margin: 0;
        padding: 0;
        margin-left: 320px;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
    }

    header {
        background: #005f73;
        color: #fff;
        padding: 20px;
        text-align: center;
    }

    header h1 {
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

    .toggle-btn {
        position: absolute;
        top: 10px;
        left: 10px;
        cursor: pointer;
        z-index: 999;
    }

    .toggle-btn i {
        color: #000;
        font-size: 20px;
    }

    .lab-btn {
      background: #F3E06A; /* Button background color */
      color: #284290; /* Text color */
      padding: 15px 50px; /* Increased padding for a larger button */
      border: none; /* No border */
      border-radius: 5px; /* Rounded corners */
      cursor: pointer; /* Pointer on hover */
      margin-top: 50px; /* Margin above the button */
      transition: background 0.3s; /* Smooth background transition */
      font-size: 18px; /* Increased font size for thicker text */
      font-weight: bold; /* Make the font thicker */
      bottom: 0;
    }

    .lab-btn:hover {
        background-color: Yellow;
    }

    .custom-prompt {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 1000;
    }

    .prompt-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: #fff;
        padding: 20px;
        border-radius: 5px;
        text-align: center;
    }

    .prompt-buttons {
        margin-top: 20px;
    }

    .prompt-buttons button {
        display: inline-block;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        cursor: pointer;
        margin-right: 10px;
    }

    .prompt-buttons .yes {
        background-color: #28a745;
        color: white;
    }

    .prompt-buttons .no {
        background-color: #dc3545;
        color: white;
    }

    .question {
        margin: 20px 0;
    }

    .answers label {
        display: block;
        margin-bottom: 10px;
        text-align: left;
        font-weight: normal;
        cursor: pointer;
    }

    .answers input[type="radio"] {
        width: 20px; /* Make radio buttons bigger */
        height: 20px;
        margin-left: 100px;
        accent-color: #284290; /* Set selected color */
    }

    .button {
        background: #F3E06A; /* Button background color */
        color: #284290; /* Text color */
        padding: 15px 50px; /* Increased padding for a larger button */
        border: none; /* No border */
        border-radius: 5px; /* Rounded corners */
        cursor: pointer; /* Pointer on hover */
        margin-top: 20px; /* Margin above the button */
        transition: background 0.3s; /* Smooth background transition */
        font-size: 18px; /* Increased font size for thicker text */
        font-weight: bold; /* Make the font thicker */
        margin-left: 750px;
    }


    .button:hover {
        background: Yellow;
    }

    #results {
        margin-top: 20px;
        font-weight: bold;
    }

    h1 {
        color: #284290; /* Set h1 color */
    }

    .question-container {
        position: relative; /* For positioning the point container */
        margin-bottom: 20px; /* Space between questions */
    }

    .point-container {
        background: rgba(243, 224, 106, 0.33); /* 33% opacity */
        color: #284290; /* Text color */
        padding: 10px 20px; /* Increased padding for larger size */
        border-radius: 10px; /* Slightly larger rounded corners */
        position: absolute; /* Position it above the question */
        top: 20px; /* Adjust position as needed */
        left: 70%; /* Center above the question */
        transform: translateX(-50%); /* Centering using transform */
        font-weight: bold; /* Bold text */
        font-size: 1.2em; /* Increase font size */
    }

    .loading-bar-container {
        position: relative; /* Change to relative */
        width: 80%; /* Adjust width as needed */
        height: 30px;
        background-color: #eee;
        border-radius: 5px;
        overflow: hidden;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        z-index: 10;
        transform: rotate(180deg); /* Flip the container */
    }

    .loading-bar {
        position: relative; /* Set position relative */
        height: 100%;
        background-color: #3EF31396; /* Loading bar color */
        width: 100%; /* Start filled */
        transition: width 0.3s;
    }

    #results-container {
        margin-top: 20px;
        padding: 15px;
        background-color: #fff; /* Optional background color */
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        text-align: center; /* Center the text */
        font-weight: bold;
        font-size: 25px;
    }

    /* Add additional styles here as needed */
    .long-edit-btn {
        position: absolute;
        margin-left: 450px;
        background: #284290;
        color: #fff;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
        font-weight: bold;
        transition: background 0.3s;
    }

    .long-edit-btn:hover {
        background: #3a5ba0;
    }

    .quiz-popup {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.8); /* Dark overlay */
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }

    .popup-content {
        background-color: #fff;
        padding: 20px 40px;
        border-radius: 10px;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
    }

    .popup-content h1 {
        color: #284290;
        margin-bottom: 20px;
    }

    .popup-content p {
        font-size: 18px;
        margin: 10px 0;
    }

    .back-btn {
        background-color: #dc3545;
        color: white;
        padding: 10px 20px;
        font-size: 16px;
        cursor: pointer;
        border: none;
        border-radius: 5px;
        margin-top: 20px;
    }

    .back-btn:hover {
        background-color: #c82333;
    }

    .edit-popup {
    display: none; /* Hidden by default */
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.8); /* Black overlay */
    z-index: 1002; /* High z-index to overlay everything */
    justify-content: center;
    align-items: center;
}
.edit-popup .popup-content {
    background-color: #fff;
    width: 80%;
    height: 80%;
    border-radius: 10px;
    overflow: hidden; /* Ensure iframe stays within bounds */
    position: relative;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
}
.edit-popup .popup-content .close-btn {
    position: absolute;
    top: 10px;
    right: 15px;
    font-size: 30px;
    font-weight: bold;
    color: #333;
    cursor: pointer;
    z-index: 1001;
}
.edit-popup .popup-content iframe {
    width: 100%;
    height: 100%;
    border: none;
}
    </style>
</head>
<body>

  <div class="quiz-popup" id="quizPopup">
    <div class="popup-content">
      <?php if ($role == 1 || $role == 2): ?>
          <button class="long-edit-btn" onclick="openEditPage()">Edit</button>
      <?php endif; ?>

       <h1><?php echo $quizTitle; ?></h1>
      <p id="total-items"></p>
      <p>Passing Score: 60%</p>
      <p id="total-time"></p>
      <p>You can't go back once the quiz starts</p>
      <button class="back-btn" onclick="goBack()">Back</button>
      <button class="button" id="startQuizButton">Start Quiz</button>

    </div>
  </div>

  <div id="editPopup" class="edit-popup">
      <div class="popup-content">
          <span class="close-btn" onclick="closeEditPopup()">&times;</span>
          <iframe id="editIframe" src="" frameborder="0"></iframe>
      </div>
  </div>

        <div id="quiz-container">
            <h1>Gen2 Quarterly Assessment</h1>
            <div class="loading-bar-container">
                <div class="loading-bar" id="loadingBar"></div> <!-- Loading bar -->

            </div>

            <div id="timer-label">Time Remaining: <span id="time"><?php echo $formattedTimer; ?></span></div> <!-- Timer display -->
                    <div id="quiz"></div>

            <div id="results-container" style="display: none;">
                <div id="grade-result"></div>
                <div id="status-result"></div>
            </div>
            <button id="submit" class="button">SUBMIT</button>
            <div id="results"><button id="proceed-button" class="lab-btn" onclick="proceedToLab()" style="display: none;">Complete Course</button></div>
        </div>


    <div class="custom-prompt" id="customPrompt">
        <div class="prompt-content">
            <p>Do you want to complete course?</p>
            <div class="prompt-buttons">
                <button class="yes" onclick="proceedToLab()">Yes</button>
                <button class="no" onclick="hideCustomPrompt()">No</button>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const quizPopup = document.getElementById('quizPopup');
        const startQuizButton = document.getElementById('startQuizButton');
        const totalItems = document.getElementById('total-items');
        const totalTime = document.getElementById('total-time');
        const quizContainer = document.getElementById('quiz');
        const submitButton = document.getElementById('submit');
        const resultsContainer = document.getElementById('results');
        const proceedButton = document.getElementById('proceed-button');
        const loadingBar = document.getElementById('loadingBar');
        const timerLabel = document.getElementById('time');

        let timer;
        let timeRemaining = <?php echo $timerValue; ?>; // Timer value from PHP
        let quizSubmitted = false; // Track if quiz has already been submitted

        // Load quiz data from PHP as JSON
        const quizData = <?php
            $questions = [];
            foreach ($xml->question as $question) {
                $options = [];
                foreach ($question->option as $option) {
                    $options[(string) $option['id']] = (string) $option;
                }
                $questions[] = [
                    'question' => (string) $question->text,
                    'options' => $options,
                    'correct' => (string) $question->correct
                ];
            }
            echo json_encode($questions);
        ?>;

        // Display total items and time from XML data
        const totalQuestions = <?php echo count($xml->question); ?>;
        totalItems.textContent = `Total Items: ${totalQuestions}`;
        const minutes = Math.floor(timeRemaining / 60);
        totalTime.textContent = `Total Time: ${minutes} minutes`;

        // Show the popup
        quizPopup.style.display = 'flex';

        // Start quiz on button click
        startQuizButton.addEventListener('click', () => {
            quizPopup.style.display = 'none'; // Hide the popup
            startTimer(); // Start the timer
            buildQuiz(); // Build the quiz
        });

        // Function to start the timer
        function startTimer() {
            const totalTime = timeRemaining;
            timer = setInterval(() => {
                const minutes = Math.floor(timeRemaining / 60);
                const seconds = timeRemaining % 60;
                timerLabel.textContent = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
                const widthPercentage = (timeRemaining / totalTime) * 100;
                loadingBar.style.width = `${widthPercentage}%`;

                if (timeRemaining <= 0) {
                    clearInterval(timer);
                    showResults(); // Automatically show results when time runs out
                }
                timeRemaining--;
            }, 1000);
        }

        // Build the quiz HTML dynamically
        function buildQuiz() {
            const output = [];
            quizData.forEach((currentQuestion, questionNumber) => {
                const answers = [];
                for (let letter in currentQuestion.options) {
                    answers.push(
                        `<label>
                            <input type="radio" name="question${questionNumber}" value="${letter}">
                            ${letter} : ${currentQuestion.options[letter]}
                        </label>`
                    );
                }
                output.push(
                    `<div class="question-container">
                        <div class="point-container">1 Point</div>
                        <div class="question">${currentQuestion.question}</div>
                        <div class="answers">${answers.join('')}</div>
                    </div>`
                );
            });

            quizContainer.innerHTML = output.join('');
            submitButton.style.display = 'block';
        }

        // Function to show results
        function showResults() {
          const answerContainers = quizContainer.querySelectorAll('.answers');
          let numCorrect = 0;

          quizData.forEach((currentQuestion, questionNumber) => {
              const answerContainer = answerContainers[questionNumber];
              const selector = `input[name=question${questionNumber}]:checked`;
              const selectedInput = answerContainer?.querySelector(selector);

              if (selectedInput) {
                  const userAnswer = selectedInput.value;
                  const selectedLabel = selectedInput.closest('label'); // Find the label around the selected input

                  if (userAnswer === currentQuestion.correct) {
                      numCorrect++;
                      selectedLabel.style.color = 'lightgreen'; // Correct answer in green
                  } else {
                      selectedLabel.style.color = 'red'; // Incorrect answer in red
                  }
              }
          });

          const scorePercentage = (numCorrect / quizData.length) * 100;
          const gradeResult = document.getElementById('grade-result');
          const statusResult = document.getElementById('status-result');

          gradeResult.innerHTML = `GRADE | ${scorePercentage.toFixed(2)}%`;
          statusResult.innerHTML = scorePercentage >= 60
              ? '<span style="color: lightgreen;">PASSED</span>'
              : '<span style="color: red;">FAILED</span>';

          document.getElementById('results-container').style.display = 'block';
          submitButton.style.display = 'none';
          proceedButton.style.display = 'block';
          clearInterval(timer);

          // Remove the saved timer value
          localStorage.removeItem('timeRemaining');

          // Send the score to the server
          fetch('', {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/x-www-form-urlencoded'
              },
              body: `grade=${scorePercentage.toFixed(2)}`
          })
          .then(response => response.text())
          .then(data => console.log(data))
          .catch(error => console.error('Error:', error));
      }

        // Function to calculate score
        function calculateScore() {
            const answerContainers = quizContainer.querySelectorAll('.answers');
            let numCorrect = 0;

            quizData.forEach((currentQuestion, questionNumber) => {
                const answerContainer = answerContainers[questionNumber];
                const selector = `input[name=question${questionNumber}]:checked`;
                const selectedInput = answerContainer?.querySelector(selector);

                if (selectedInput) {
                    const userAnswer = selectedInput.value;
                    if (userAnswer === currentQuestion.correct) {
                        numCorrect++;
                    }
                }
            });

            return (numCorrect / quizData.length) * 100;
        }

        // Submit quiz on button click
        submitButton.addEventListener('click', () => showResults());



        // Redirect to home.php on unload
        window.addEventListener('unload', () => {
                window.location.href = 'home.php';
        });
    });

    // JavaScript function for the "Back" button
    function goBack() {
        window.location.href = 'home.php'; // Redirect to the home page
    }

    // Function for proceeding to the next page
    function proceedToLab() {
        window.location.href = "profile.php"; // Replace with your module 2 page
    }

    function openEditPage() {
        const editPopup = document.getElementById('editPopup');
        const editIframe = document.getElementById('editIframe');
        const xmlFilePath = 'xmlfile/quarterlydata.xml'; // The path to the current XML file

        // Set the iframe source to load the editor
        editIframe.src = `quizeditor.php?file=${encodeURIComponent(xmlFilePath)}`;

        // Show the popup
        editPopup.style.display = 'flex';
    }

    function closeEditPopup() {
        const editPopup = document.getElementById('editPopup');
        const editIframe = document.getElementById('editIframe');

        // Hide the popup and clear the iframe source
        editPopup.style.display = 'none';
        editIframe.src = '';
    }
    </script>
</body>
</html>
