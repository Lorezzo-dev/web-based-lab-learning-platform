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
    $stmt = $conn->prepare("UPDATE grades SET module2_lab_grade = ? WHERE user_id = ?");
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
    <title>Punnett Square Game</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400&display=swap" rel="stylesheet">
    <style>
    body {
      font-family: 'Quicksand', sans-serif;
      background: linear-gradient(to bottom, rgba(255, 255, 255, 0.7), rgba(150, 200, 255, 0.8)),
                  url('https://images.unsplash.com/photo-1614007638572-a6c33e3030e9?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080');
      background-size: cover;
      background-attachment: fixed;
      background-position: center;
      text-align: left;
      padding: 20px;
      animation: fadeIn 1.5s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    h1 {
        margin-bottom: 20px;
        font-size: 3em;
        color: #00796b;
        animation: slideIn 1s ease;
    }

    @keyframes slideIn {
        from { transform: translateY(-100px); }
        to { transform: translateY(0); }
    }

    .description {
        font-size: 1.5em;
        margin-bottom: 20px;
        color: #004d40;
    }

    .container {
        position: absolute;
        left: 1000px;
        background: white;
        border-radius: 10px;
        padding: 30px;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        width: 20%;
        z-index: 200;
    }

    .container p {
        margin: 0 0 10px;
        font-size: 20px; /* Adjust the size as needed */
    }

    .punnett-square {
        display: grid;
        grid-template-columns: repeat(3, 150px);
        grid-template-rows: repeat(3, 150px);
        gap: 10px;
        margin: 50px 0 50px 150px; /* Added margin-left of 50px */
        margin-left: 100px
        justify-content: left;
        transform: scale(1);
        transition: transform 0.5s ease;
    }

    .punnett-square:hover {
        transform: scale(1.05);
    }

    .cell {
        width: 150px;
        height: 150px;
        border: 2px solid #004d40;
        background: #e0f2f1;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 36px;
        transition: background 0.3s ease;
    }

    .cell.correct {
        background: lightgreen;
    }

    .cell.incorrect {
        background: lightcoral;
    }

    .allele-container {
        display: flex;
        justify-content: left;
        margin-top: 20px;
        animation: popIn 1.5s ease;
    }

    @keyframes popIn {
        from { transform: scale(0); }
        to { transform: scale(1); }
    }

    .allele {
        width: 100px;
        height: 60px;
        background: #b2dfdb;
        border: 2px solid #004d40;
        margin: 10px;
        text-align: center;
        line-height: 60px;
        font-size: 28px;
        cursor: grab;
        transition: transform 0.2s;
    }

    .allele:active {
        cursor: grabbing;
        transform: scale(1.2);
    }

    label {
        font-size: 1.8em;
        margin: 10px 0;
        display: block;
        color: #00796b;
    }

    input[type="text"] {
        width: 50%;
        padding: 15px;
        font-size: 1.5em;
        margin: 10px 0;
        border: 2px solid #004d40;
        border-radius: 5px;
    }

    .submit-button {
        padding: 15px 30px;
        background: #00796b;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 1.5em;
        transition: background 0.3s ease;

    }

    .submit-button:hover {
        background: #004d40;
    }

    .confirmation-popup {
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        padding: 40px;
        border: 3px solid #00796b;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
        animation: fadeIn 1s ease;

    }

    .completion-popup {
        display: none;
        position: fixed;
        bottom: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        padding: 40px;
        border: 3px solid #00796b;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
        animation: fadeIn 1s ease;
        z-index: 300;

    }

    .confirmation-popup, .completion-popup p {
      font-size: 20px;
    }

    .confirmation-popup.active, .completion-popup.active {
        display: block;
    }

    .confirmation-popup button, .completion-popup button {
        padding: 15px 30px;
        margin: 10px;
        border: none;
        color: white;
        border-radius: 5px;
        font-size: 18px;
        cursor: pointer;
    }

    .confirmation-popup #yes-btn {
        background: #4caf50;
    }

    .confirmation-popup #no-btn, #exit-game-button {
        background: #f44336;
    }

    .confirmation-popup #yes-btn:hover, #exit-game-button:hover {
        background: #388e3c;
    }

    .confirmation-popup #no-btn:hover {
        background: #c62828;
    }

        #exit-game-button {
            background-color: #f44336;
            margin-left: 130px;
        }
        #exit-game-button:hover {
            background-color: #c62828;

        }

        .probability-container {
            animation: fadeIn 1.5s ease;
            float: right;
            margin-top: -300px;
            margin-right: 1000px; /* Added margin-right to move left */
            position: absolute;
            left: 1000px;
        }
        .textfield {
            width: 60px;
            text-align: center;
        }
        .check-symbol {
            display: inline-block;
            font-size: 24px;
            margin-left: 10px;
        }
        .check-symbol.correct {
            color: green;
        }
        .x-symbol.incorrect {
            color: red;
        }
        .textfield.correct {
            border: 2px solid green;
            background-color: #eaffea; /* Light green background */
        }
        .textfield.incorrect {
            border: 2px solid red;
            background-color: #ffe6e6; /* Light red background */
        }
        
#progress-bar-container {
    width: 100%;
    height: 10px;
    background-color: #ddd;
    border-radius: 5px;
    margin-bottom: 20px;
    overflow: hidden;
}

#progress-bar {
    width: 0%;
    height: 100%;
    background-color: #4caf50;
    transition: width 0.5s ease-in-out;
}
    </style>
</head>
<body>
    
<div id="progress-bar-container">
    <div id="progress-bar"></div>
</div>


<h1>Punnett Square Lab Assessment</h1>
<p class="description">Brown hair is dominant to red hair. Dad is heterozygous, Mom is homozygous recessive.</p>

<div class="container">
    <p>Drag and drop to fill the Punnett square.</p>
    <p>Click Submit button if you are done</p>
    <button class="submit-button" id="submit-btn">Submit</button>
</div>

<div class="punnett-square">
    <!-- Top row -->
    <div></div>
    <div class="cell dropzone" data-correct-allele="H"></div>
    <div class="cell dropzone" data-correct-allele="h"></div>

    <!-- Left column -->
    <div class="cell dropzone" data-correct-allele="h"></div>
    <div class="cell dropzone" data-correct-genotype="hH"></div>
    <div class="cell dropzone" data-correct-allele="hh"></div>

    <!-- Bottom row -->
    <div class="cell dropzone" data-correct-allele="h"></div>
    <div class="cell dropzone" data-correct-genotype="hH"></div>
    <div class="cell dropzone" data-correct-allele="hh"></div>
</div>

<div class="allele-container">
    <div class="allele draggable" draggable="true" data-allele="H">H</div>
    <div class="allele draggable" draggable="true" data-allele="h">h</div>
    <div class="allele draggable" draggable="true" data-allele="hH">hH</div>
    <div class="allele draggable" draggable="true" data-allele="Hh">Hh</div>
    <div class="allele draggable" draggable="true" data-allele="HH">HH</div>
    <div class="allele draggable" draggable="true" data-allele="hh">hh</div>
</div>

<div class="probability-container">
    <label for="brown-hair-prob">Probability of Brown Hair:</label>
    <input type="text" id="brown-hair-prob" class="textfield" maxlength="3">
    <span class="check-symbol" id="check-brown"></span>

    <label for="red-hair-prob">Probability of Red Hair: &nbsp&nbsp&nbsp</label>
    <input type="text" id="red-hair-prob" class="textfield" maxlength="3">
    <span class="check-symbol" id="check-red"></span>
</div>

<div class="confirmation-popup" id="confirmation-popup">
    <p>Are you sure your answers are correct?</p>
    <button id="yes-btn">Yes</button>
    <button id="no-btn">No</button>
</div>

<div class="completion-popup" id="completion-popup">
    <h2 id="completion-message">Lab Test Completed: 100% Score!</h2>
    <button id="exit-game-button">Exit Lab</button>
</div>

<script>
const dropzones = document.querySelectorAll('.dropzone');
const draggables = document.querySelectorAll('.draggable');
const brownInput = document.getElementById('brown-hair-prob');
const redInput = document.getElementById('red-hair-prob');
const submitButton = document.getElementById('submit-btn');
const confirmationPopup = document.getElementById('confirmation-popup');
const completionPopup = document.getElementById('completion-popup');
const confirmationYes = document.getElementById('yes-btn');
const confirmationNo = document.getElementById('no-btn');
const exitButton = document.getElementById('exit-game-button');
const progressBar = document.getElementById('progress-bar');
let progress = 0;

function updateProgress() {
    progress = Math.min(100, progress + 10);
    progressBar.style.width = progress + "%";
}

brownInput.addEventListener("input", () => updateProgress());
redInput.addEventListener("input", () => updateProgress());


draggables.forEach(draggable => {
    draggable.addEventListener('dragstart', dragStart);
});

dropzones.forEach(dropzone => {
    dropzone.addEventListener('dragover', dragOver);
    dropzone.addEventListener('drop', drop);
});

submitButton.addEventListener('click', () => {
    confirmationPopup.classList.add('active');
});

confirmationYes.addEventListener('click', () => {
    confirmationPopup.classList.remove('active');
    evaluateAnswers();
});

confirmationNo.addEventListener('click', () => {
  confirmationPopup.classList.remove('active');
  });

  exitButton.addEventListener('click', () => {
      window.location.reload();
  });

  function dragStart(e) {
      e.dataTransfer.setData('text/plain', e.target.dataset.allele);
  }

  function dragOver(e) {
      e.preventDefault();
  }

  function drop(e) {
      e.preventDefault();
      const allele = e.dataTransfer.getData('text/plain');
      const correctAllele = e.target.dataset.correctAllele;
      const correctGenotype = e.target.dataset.correctGenotype;

      if (correctAllele) {
          if (allele === correctAllele) {
              e.target.innerHTML = allele;
              e.target.style.backgroundColor = 'lightgreen';
              updateProgress();
          } else {
              e.target.innerHTML = allele;
              e.target.style.backgroundColor = 'lightcoral';
          }
      } else if (correctGenotype) {
          if (allele === correctGenotype) {
              e.target.innerHTML = allele;
              e.target.style.backgroundColor = 'lightgreen';
              updateProgress();
          } else {
              e.target.innerHTML = allele;
              e.target.style.backgroundColor = 'lightcoral';
          }
      } else {
          e.target.innerHTML = allele;
          e.target.style.backgroundColor = 'lightcoral';
      }
  }

  function evaluateAnswers() {
      const allCells = [...dropzones];
      const brownHairProb = document.getElementById('brown-hair-prob').value;
      const redHairProb = document.getElementById('red-hair-prob').value;

      const brownCheck = document.getElementById('check-brown');
      const redCheck = document.getElementById('check-red');

      // Define correct values
      const correctBrownProb = '50';
      const correctRedProb = '50';

      // Check if probabilities are correct
      const isBrownCorrect = brownHairProb === correctBrownProb;
      const isRedCorrect = redHairProb === correctRedProb;

      // Update the styling of input fields based on correctness
      brownInput.className = isBrownCorrect ? 'textfield correct' : 'textfield incorrect';
      redInput.className = isRedCorrect ? 'textfield correct' : 'textfield incorrect';

      // Update the check symbols based on correctness
      brownCheck.className = isBrownCorrect ? 'check-symbol correct' : 'x-symbol incorrect';
      redCheck.className = isRedCorrect ? 'check-symbol correct' : 'x-symbol incorrect';

      // Check if all cells are correct
      const allCorrectCells = allCells.filter(cell => cell.style.backgroundColor === 'lightgreen').length;
      const totalCells = allCells.length;

      // Calculate the score based on correct cells
      let score = (allCorrectCells / totalCells) * 10;

      // Adjust percentage based on score and penalties
      let percentage = (score / 10) * 100;

      // Apply penalties for incorrect probabilities
      if (!isBrownCorrect) percentage -= 10; // Example penalty
      if (!isRedCorrect) percentage -= 10; // Example penalty

      // Ensure percentage is between 0 and 100
      percentage = Math.max(0, Math.min(100, percentage));

      // Ensure score is correctly rounded
      score = Math.round(score);

      showCompletionPopup(score, percentage);
      submitScoreToDatabase('module2_lab_grade', percentage); // Pass module name and percentage
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

  function showCompletionPopup(score, percentage) {
      const completionMessage = document.getElementById('completion-message');
      completionMessage.textContent = `Lab Test Completed: (${percentage.toFixed(2)}% Score!)`;
      completionPopup.classList.add('active');
  }
  document.getElementById('exit-game-button').addEventListener('click', function() {
      window.close(); // Close the window
  });


  </script>

  </body>
  </html>
