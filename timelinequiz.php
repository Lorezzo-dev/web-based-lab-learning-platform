<?php
session_start();
include('connection.php'); // Include your database connection file

// Establish connection
$conn = new mysqli($servername, $db_username, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id']; // Assuming you store the user's ID in the session

if (!$user_id) {
    die('User not authenticated.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['percentage'])) {
    $percentage = (float)$_POST['percentage'];

    // Debugging: Log the received percentage
    error_log("Received percentage: $percentage");

    // Prepare and bind
    $stmt = $conn->prepare("UPDATE grades SET module3_lab_grade = ? WHERE user_id = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param('di', $percentage, $user_id);

    // Execute
    if ($stmt->execute()) {
        echo "Grade updated successfully.";
    } else {
        echo "Error updating grade: " . $stmt->error;
    }

    // Close statement
    $stmt->close();
}

// Close connection
$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="assets/icon.png">
    <title>Timeline Lab Assessment</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400&display=swap" rel="stylesheet">
    <style>

    /* Fade-in animation for body */
    @keyframes fadeIn {
        0% { opacity: 0; transform: translateY(-10px); }
        100% { opacity: 1; transform: translateY(0); }
    }

    /* Scaling animation for container on load */
    @keyframes scaleIn {
        0% { transform: scale(0.8); opacity: 0; }
        100% { transform: scale(1); opacity: 1; }
    }

    /* Glow effect for images on hover */
    @keyframes glowEffect {
        0% { box-shadow: 0 0 10px rgba(0, 121, 107, 0.5); }
        50% { box-shadow: 0 0 20px rgba(0, 121, 107, 0.8); }
        100% { box-shadow: 0 0 10px rgba(0, 121, 107, 0.5); }
    }

    /* Button bounce animation */
    @keyframes bounce {
        0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
        40% { transform: translateY(-10px); }
        60% { transform: translateY(-5px); }
    }
    body {
        font-family: 'Quicksand', sans-serif;
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #a8c0ff 100%);
        background-attachment: fixed;
        background-size: cover;
        background-position: center;
        text-align: center;
        padding: 20px;
        animation: fadeIn 1.5s ease;
    }

    .container {
        position: relative;
        max-width: 2000px; /* Increased container width */
        margin: 0 auto;
        background-color: #fff;
        border: 1px solid #00796b;
        border-radius: 5px;
        padding: 40px; /* Increased padding */
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.3); /* Enhanced shadow */
        animation: scaleIn 1s ease-in-out;
    }

    h1 {
        margin-bottom: 40px;
        font-size: 2.5rem; /* Larger font size for the title */

    }

    h3 {
        font-size: 2.8rem; /* Larger font size for question titles */
        margin-bottom: 10px;
        color: #004d40;
    }

    p {
        font-size: 1.8rem; /* Increased paragraph font size */
        line-height: 1.6; /* Better line spacing for readability */
        color: #333;
    }

    .question {
        margin-bottom: 20px;
        display: none; /* Initially hide all questions */
    }

    .question.active {
        display: block; /* Show the active question */
    }

    .bottom-images {
        display: flex;
        justify-content: center;
        margin-top: 20px;
    }

    .bottom-images img {
        width: 150px;
        height: auto;
        margin: 0 10px;
        border: 2px solid #00796b;
        border-radius: 5px;
        cursor: pointer;
    }

    .popup-text {
        font-size: 20px;
        margin-bottom: 20px;
    }

    .popup button {
        padding: 10px 20px;
        border: none;
        color: white;
        background-color: #00796b;
        cursor: pointer;
        border-radius: 5px;
        font-size: 16px;
    }

    .popup button:hover {
        background-color: #004d40;
    }

    .question .image-container {
        display: flex;
        justify-content: space-evenly; /* Evenly spaced images */
        gap: 20px; /* Added spacing between images */
        margin-top: 20px;
    }

    .image-wrapper {
        position: relative; /* Ensure the label is positioned relative to the image */
        width: 300px;
        height: 300px;
        border: 2px solid #00796b;
        border-radius: 5px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        cursor: pointer;
    }

    .image-wrapper img {
        max-width: 100%;
        max-height: 100%;
        object-fit: cover;
    }

    .image-wrapper:hover .label {
        opacity: 1; /* Show the label on hover */
    }

    .label {
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        background-color: rgba(0, 0, 0, 0.7); /* Semi-transparent background */
        color: white;
        padding: 5px;
        border-radius: 5px;
        font-size: 16px;
        text-align: center;
        opacity: 0; /* Hide the label by default */
        transition: opacity 0.3s ease; /* Smooth transition */
    }

    /* Glowing effect for correct and incorrect answers */
    .image-wrapper.correct {
        box-shadow: 0 0 20px 5px green; /* Green glow for correct answer */
    }

    .image-wrapper.incorrect {
        box-shadow: 0 0 20px 5px red; /* Red glow for incorrect answer */
    }

    /* Modal container styles */
    .modal {
        display: none;
        position: absolute;
        top: 80%; /* Position it just below the container */
        left: 50%;
        transform: translateX(-50%);
        width: 100%;
        max-width: 800px;
        background-color: rgba(255, 255, 255, 0.9); /* Slight transparency */
        border: 2px solid #00796b;
        border-radius: 10px;
        padding: 10px;
        z-index: 1000;
    }

    .modal-content {
        margin: 0 auto;
        width: 80%;
        max-width: 500px;
        text-align: center;
    }

    .modal-content.correct {
        border: 2px solid green;
    }

    .modal-content.incorrect {
        border: 2px solid red;
    }

    #modalOverlay {
        display: none; /* Hidden by default */
        position: fixed; /* Fixed position to cover the entire screen */
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent black */
        z-index: 999; /* Behind the modal but above other content */
    }

    /* Updated CSS for the Next Question button */
    .next-button {
        padding: 10px 20px;
        border: none;
        color: white;
        background-color: #00796b;
        cursor: pointer;
        border-radius: 5px;
        font-size: 16px;
        margin-top: 20px;
        transition: background-color 0.3s ease, transform 0.3s ease;
    }

    .next-button:hover {
        background-color: #004d40;
        transform: scale(1.05); /* Slight scale effect on hover */
        animation: bounce 1s;
    }

    .completion-popup {
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: #ffffff;
        border: 2px solid #00796b;
        border-radius: 10px;
        padding: 30px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
        z-index: 1100;
        text-align: center;
    }

  .completion-popup.active {
      display: block;
  }

  #exit-game-button {
      background-color: #f44336; /* Red background color */
      color: white; /* White text color */
      padding: 15px 30px; /* Larger padding for a bigger button */
      font-size: 18px; /* Larger font size */
      border: none; /* Remove border */
      border-radius: 5px; /* Rounded corners */
      cursor: pointer; /* Pointer cursor on hover */
  }

  #exit-game-button:hover {
      background-color: #c62828; /* Darker red on hover */
  }
  @keyframes slideUp {
      0% { transform: translateY(10px); opacity: 0; }
      100% { transform: translateY(0); opacity: 1; }
  }
  
        #progress-bar-container {
            width: 80%;
            max-width: 800px;
            background-color: #e0e0e0;
            border-radius: 10px;
            margin: 10px auto;
            height: 20px;
            overflow: hidden;
            border: 2px solid #4caf50;
            position: fixed;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
        }

        #progress-bar {
            width: 0%;
            height: 100%;
            background-color: #4caf50;
            text-align: center;
            line-height: 20px;
            color: white;
            font-weight: bold;
            transition: width 0.5s ease-in-out;
        }
        

    </style>
</head>
<body>
    
<div id="progress-bar-container">
    <div id="progress-bar"></div>
</div>

<h1>Timeline Quiz</h1>

<div class="container">
    <!-- Question 1 -->
    <div id="question1" class="question active">
        <h3>Question 1</h3>
        <p>These are complex cells that contain membrane-bound organelles such as nuclei, mitochondria, and a cytoskeleton</p>
        <div class="image-container">
            <div class="image-wrapper">
                <img src="assets/lab3/cells.jpg" alt="Option A" id="Cells" onclick="checkAnswer('A')">
                <span class="label">Cells</span>
            </div>
            <div class="image-wrapper">
                <img src="assets/lab3/Prokaryote.jpg" alt="Option B" id="Prokaryote" onclick="checkAnswer('B')">
                <span class="label">Prokaryote</span>
            </div>
            <div class="image-wrapper">
                <img src="assets/lab3/eukaryotes.jpg" alt="Option C" id="Eukaryote" onclick="checkAnswer('C')">
                <span class="label">Eukaryotes</span>
            </div>
        </div>
    </div>

    <!-- Question 2 -->
    <div id="question2" class="question">
        <h3>Question 2</h3>
        <p>This is an early human species from the pleistocene that are known to have first use fire</p>
        <div class="image-container">
            <div class="image-wrapper">
                <img src="assets/lab3/Australophitecus.jpg" alt="Option A" id="Australophitecus" onclick="checkAnswer('A')">
                <span class="label">Australophitecus</span>
            </div>
            <div class="image-wrapper">
                <img src="assets/lab3/Homo-erectus.png" alt="Option B" id="Homo-Erectus" onclick="checkAnswer('B')">
                <span class="label">Homo Erectus</span>
            </div>
            <div class="image-wrapper">
                <img src="assets/lab3/Neanderthals.png" alt="Option C" id="Neanderthals" onclick="checkAnswer('C')">
                <span class="label">Neanderthals</span>
            </div>
        </div>
    </div>

    <!-- Question 3 -->
    <div id="question3" class="question">
        <h3>Question 3</h3>
        <p>Is a Devonian fish thought to be the first vertebrate to walk on land</p>
        <div class="image-container">
            <div class="image-wrapper">
                <img src="assets/lab3/Panderichthys.jpg" alt="Option A" id="Panderichthys" onclick="checkAnswer('A')">
                <span class="label">Panderichthys</span>
            </div>
            <div class="image-wrapper">
                <img src="assets/lab3/Tiktaalik.png" alt="Option B" id="Tiktaalik" onclick="checkAnswer('B')">
                <span class="label">Tiktaalik</span>
            </div>
            <div class="image-wrapper">
                <img src="assets/lab3/Ichthyostega.jpg" alt="Option C" id="Ichthyostega" onclick="checkAnswer('C')">
                <span class="label">Ichthyostega</span>
            </div>
        </div>
    </div>

        <!-- Question 4 -->
        <div id="question4" class="question">
            <h3>Question 4</h3>
            <p>Which of this species is present in the Jurassic Period?</p>
            <div class="image-container">
                <div class="image-wrapper">
                    <img src="assets/lab3/Juramaia.jpg" alt="Option A" id="Juramaia" onclick="checkAnswer('A')">
                    <span class="label">Juramaia</span>
                </div>
                <div class="image-wrapper">
                    <img src="assets/lab3/Repenomamus.jpg" alt="Option B" id="Repenomamus" onclick="checkAnswer('B')">
                    <span class="label">Repenomamus</span>
                </div>
                <div class="image-wrapper">
                    <img src="assets/lab3/Carpolestes.jpg" alt="Option C" id="Carpolestes" onclick="checkAnswer('C')">
                    <span class="label">Carpolestes</span>
                </div>
            </div>
        </div>

        <!-- Question 5 -->
        <div id="question5" class="question">
            <h3>Question 5</h3>
            <p>A warm-blooded reptile from the Permian period considered as a precursor to all mammals</p>
            <div class="image-container">
                <div class="image-wrapper">
                    <img src="assets/lab3/Gorgonopsid.png" alt="Option A" id="Gorgonpsid" onclick="checkAnswer('A')">
                    <span class="label">Gorgonpsid</span>
                </div>
                <div class="image-wrapper">
                    <img src="assets/lab3/Dimetrodon.jpeg" alt="Option B" id="Dimetrodon" onclick="checkAnswer('B')">
                    <span class="label">Dimetrodon</span>
                </div>
                <div class="image-wrapper">
                    <img src="assets/lab3/Cynognathus.jpg" alt="Option C" id="Cynognathus" onclick="checkAnswer('C')">
                    <span class="label">Cynognathus</span>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal for correct/incorrect answers positioned outside the container -->
<div id="answerModal" class="modal">
    <div class="modal-content" id="modalContent">
        <p id="answerText" class="popup-text"></p>
        <button class="next-button" onclick="closeModal()">Next Question</button>

    </div>
</div>

<div class="completion-popup" id="completion-popup">
    <h2 id="completion-message">Quiz completed! Your score is 0/5</h2>
    <button id="exit-game-button">Exit Lab</button>
</div>


<div id="modalOverlay"></div>

<script>
let currentQuestion = 1;
let score = 0; // Variable to store the score

function checkAnswer(option) {
    var modal = document.getElementById("answerModal");
    var overlay = document.getElementById("modalOverlay");
    var modalContent = document.getElementById("modalContent");
    var answerText = document.getElementById("answerText");

    // Get all the images for the current question
    var images = document.querySelectorAll('#question' + currentQuestion + ' .image-wrapper');

    // Reset the glow for all images
    images.forEach(function(image) {
        image.classList.remove("correct", "incorrect");
    });

    // Check if the answer is correct or not for each question
    if (currentQuestion === 1) {
        if (option === 'C') {
            answerText.textContent = "Correct! Eukaryotes have membrane-bound organelles.";
            modalContent.classList.add("correct");
            modalContent.classList.remove("incorrect");

            document.getElementById('Eukaryote').parentElement.classList.add("correct");
            score++;
        } else {
            answerText.textContent = "Wrong answer. See correct answer.";
            modalContent.classList.add("incorrect");
            modalContent.classList.remove("correct");

            if (option === 'A') {
                document.getElementById('Cells').parentElement.classList.add("incorrect");
            } else if (option === 'B') {
                document.getElementById('Prokaryote').parentElement.classList.add("incorrect");
            }

            document.getElementById('Eukaryote').parentElement.classList.add("correct");
        }
    } else if (currentQuestion === 2) {
        if (option === 'B') {
            answerText.textContent = "Correct! Homo-Erectus lived in the Pleistocene and are known to have first invented fire.";
            modalContent.classList.add("correct");
            modalContent.classList.remove("incorrect");

            document.getElementById('Homo-Erectus').parentElement.classList.add("correct");
            score++;
        } else {
            answerText.textContent = "Wrong answer. See correct answer.";
            modalContent.classList.add("incorrect");
            modalContent.classList.remove("correct");

            if (option === 'C') {
                document.getElementById('Neanderthals').parentElement.classList.add("incorrect");
            } else if (option === 'A') {
                document.getElementById('Australophitecus').parentElement.classList.add("incorrect");
            }

            document.getElementById('Homo-Erectus').parentElement.classList.add("correct");
        }
    } else if (currentQuestion === 3) {
        if (option === 'B') {
            answerText.textContent = "Correct! Tiktaalik is known to be the first vertebrate animal to walk on land.";
            modalContent.classList.add("correct");
            modalContent.classList.remove("incorrect");

            document.getElementById('Tiktaalik').parentElement.classList.add("correct");
            score++;
        } else {
            answerText.textContent = "Wrong answer. See correct answer.";
            modalContent.classList.add("incorrect");
            modalContent.classList.remove("correct");

            if (option === 'A') {
                document.getElementById('Panderichthys').parentElement.classList.add("incorrect");
            } else if (option === 'C') {
                document.getElementById('Ichthyostega').parentElement.classList.add("incorrect");
            }

            document.getElementById('Tiktaalik').parentElement.classList.add("correct");
        }
    } else if (currentQuestion === 4) {
        if (option === 'A') {
            answerText.textContent = "Correct! Juramaia is the small mammal that live during the Jurassic Period";
            modalContent.classList.add("correct");
            modalContent.classList.remove("incorrect");

            document.getElementById('Juramaia').parentElement.classList.add("correct");
            score++;
        } else {
            answerText.textContent = "Wrong answer. See correct answer.";
            modalContent.classList.add("incorrect");
            modalContent.classList.remove("correct");

            if (option === 'B') {
                document.getElementById('Repenomamus').parentElement.classList.add("incorrect");
            } else if (option === 'C') {
                document.getElementById('Carpolestes').parentElement.classList.add("incorrect");
            }

            document.getElementById('Juramaia').parentElement.classList.add("correct");
          }
        } else if (currentQuestion === 5) {
            if (option === 'C') {
                answerText.textContent = "Correct! Cynognathus is the Permian reptile that's likely the ancestor to all mammals";
                modalContent.classList.add("correct");
                modalContent.classList.remove("incorrect");

                document.getElementById('Cynognathus').parentElement.classList.add("correct");
                score++;
            } else {
                answerText.textContent = "Wrong answer. See correct answer.";
                modalContent.classList.add("incorrect");
                modalContent.classList.remove("correct");

                if (option === 'A') {
                    document.getElementById('Gorgonpsid').parentElement.classList.add("incorrect");
                } else if (option === 'B') {
                    document.getElementById('Dimetrodon').parentElement.classList.add("incorrect");
                }

                document.getElementById('Cynognathus').parentElement.classList.add("correct");
            }

    }


    // Show the overlay and modal
    overlay.style.display = "block";
    modal.style.display = "block";
}

function closeModal() {
    var modal = document.getElementById("answerModal");
    var overlay = document.getElementById("modalOverlay");

    modal.style.display = "none";
    overlay.style.display = "none";

    // Move to next question if the current question is answered
    if (currentQuestion === 1) {
        currentQuestion++;
        showNextQuestion();
    } else if (currentQuestion === 2) {
        currentQuestion++;
        showNextQuestion();
    } else if (currentQuestion === 3) {
        currentQuestion++;
        showNextQuestion();
    } else if (currentQuestion === 4) {
          currentQuestion++;
          showNextQuestion();
    } else if (currentQuestion === 5) {
          currentQuestion++;
          showNextQuestion();
    }else if (currentQuestion === 6) {
        // No more questions, show the score popup
        alert("Quiz completed! Your score is " + score);
    }
}

function updateProgressBar() {
    let progressBar = document.getElementById("progress-bar");
    let progressPercentage = ((currentQuestion - 1) / 4) * 100; // Scale from 0% to 100%
    progressBar.style.width = progressPercentage + "%";
}


function showNextQuestion() {
    // Hide the current question
    var currentQuestionDiv = document.getElementById("question" + (currentQuestion - 1));
    currentQuestionDiv.classList.remove("active");

    // Show the next question
    var nextQuestionDiv = document.getElementById("question" + currentQuestion);
    if (nextQuestionDiv) {
        nextQuestionDiv.classList.add("active");
        updateProgressBar(); // Update progress when moving to the next question
    } else {
        // If no more questions, handle completion
        var totalQuestions = 5; // Update with the actual number of questions if different
        var percentage = (score / totalQuestions) * 100;
        showCompletionPopup(score, percentage);
        submitScoreToDatabase('module3_lab_grade', percentage);

        // Hide the container
var containerDiv = document.querySelector(".container");
if (containerDiv) {
    containerDiv.style.display = "none";
}

    }
}

function showCompletionPopup(score, percentage) {
    const completionMessage = document.getElementById('completion-message');
    completionMessage.textContent = `Quiz completed! Your score is ${score}/5: ${percentage.toFixed(2)}%`;
    document.getElementById('completion-popup').classList.add('active');
}

document.getElementById('exit-game-button').addEventListener('click', function() {
    window.close(); // Close the window
});


// Prevent the overlay from closing the modal when clicked
document.getElementById("modalOverlay").addEventListener("click", function(event) {
    event.stopPropagation(); // Prevent clicks on the overlay from closing it
});

// Close the modal only when the close button is clicked
window.onclick = function(event) {
    var modal = document.getElementById("answerModal");
    var overlay = document.getElementById("modalOverlay");
    if (event.target === document.getElementById("nextButton")) {
        closeModal();
    }
}

function submitScoreToDatabase(module, percentage) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'update_grade.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function () {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                console.log("Score submitted successfully.");
            } else {
                console.error("Failed to submit score.");
            }
        }
    };
    xhr.send('module=' + encodeURIComponent(module) + '&grade=' + encodeURIComponent(percentage));
}



</script>


</body>
</html>
