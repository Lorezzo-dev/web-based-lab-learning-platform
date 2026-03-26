<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if the user_id is set in session
if (isset($_SESSION['user_id'])) {
    // Database connection parameters
include "connection.php";

    // Establish connection
    $conn = new mysqli($servername, $db_username, $db_password, $db_name);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
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


    // Prepare SQL statement to update module1_completed to 1
    $user_id = $_SESSION['user_id'];
    $sql = "UPDATE progress SET module4lab_completed = 1 WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id); // Bind parameter

    // Execute the update query
    if ($stmt->execute()) {
        // Update successful
    } else {
        // Error occurred
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
}

include 'sidebar.php'; // Include the sidebar
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
        font-family: 'Quicksand', sans-serif;
        background-color: #f9f9f9;
        margin: 0;
        padding: 0;
        display: flex;
        min-height: 100vh;
        background-image: url('assets/hexbg.jpg'); /* Replace with your hexagon background */
    }
    .container {
      width: 85%;
      margin: auto;
      overflow: hidden;
      padding: 20px;
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 0 10px #ccc;
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
    .container {
        display: flex;          /* Enable flexbox */
        flex-direction: column; /* Stack children vertically */
        justify-content: center;/* Center vertically */
        align-items: center;    /* Center horizontally */
        width: 65%;             /* Adjust width as necessary */
        margin: auto;           /* Center the container itself horizontally */
        overflow: hidden;
        padding: 30px;
        background-color: #fff;
        border-radius: 20px;
        box-shadow: 0 0 20px #ccc;
        min-height: 80vh;      /* Optional: full height of viewport */
        position: relative;
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
    /* Style for the Proceed to Lab button */
    .lab-btn {
        display: inline-block;
        background-color: #28a745; /* Green background color */
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        text-decoration: none;
        font-size: 16px;
        cursor: pointer;
    }
    .lab-btn:hover {
        background-color: #218838; /* Darker shade on hover */
    }
    /* Custom prompt */
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
    /* Style for the prompt buttons */
    .prompt-buttons button {
        display: inline-block;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        cursor: pointer;
        margin-right: 10px; /* Adjust spacing between buttons */
    }
    .prompt-buttons .yes {
        background-color: #28a745; /* Green background color */
        color: white;
    }
    .prompt-buttons .no {
        background-color: #dc3545; /* Red background color */
        color: white;
    }

    #instructions-container {
      position: absolute; /* Position it absolutely within the container */
      top: 2650px;          /* Adjust top position */
      left: 300px;         /* Adjust left position */
      background-color: #fff;
      border: 1px solid #00796b;
      padding: 15px;
      border-radius: 5px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
      display: block;     /* Show by default */
      width: fit-content; /* Adjust width based on content */
      z-index: 1000;      /* Ensure it is above other content */
    }
    #start-game-button {
        padding: 10px 15px;
        background-color: #4caf50;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        margin-top: 10px;
    }

    #start-game-button:hover {
        background-color: #388e3c;
    }

    #confirmation-popup {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: white;
        border: 2px solid #00796b;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        display: none;
         z-index: 1000;
    }

    #confirmation-popup button {
        padding: 10px 15px;
        margin: 5px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    #confirm-yes {
        background-color: #4caf50;
        color: white;
    }

    #confirm-no {
        background-color: #f44336;
        color: white;
    }

    #overlay {
        display: none; /* Hidden by default */
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.7); /* Dark overlay with transparency */
        z-index: 999; /* High z-index to cover all other content */
        pointer-events: all; /* Ensure overlay captures all clicks */
    }

    #confirmation-popup ul,
    #confirmation-popup p {
        font-weight: normal; /* Ensure normal weight for list and paragraph text */
    }

    .dot {
        position: absolute;
        animation: moveUp infinite linear;
        z-index: -1;
    }

    @keyframes moveUp {
        0% {
            transform: translateY(100vh);
        }
        100% {
            transform: translateY(-100vh);
        }
    }




    </style>
</head>
<body>
    <!-- Page content -->
    <div class="container">
        <h2>Systematics based on Evolutionary Relationships Lab</h2>
        <h1>Understanding Phylogenetic Tree</h1>
        <p>A phylogenetic tree is a diagram that represents the evolutionary relationships among various biological species or entities based on similarities and differences in their physical or genetic characteristics. The tree is composed of branches, each representing a lineage, and nodes, which indicate common ancestors from which different lineages diverged. The tips of the tree represent current species or groups, while the branches trace their evolutionary history back to a common point.</p>
        <p>Understanding a phylogenetic tree involves recognizing how closely related different species are, based on their shared ancestry. The closer two species are on the tree, the more recent their common ancestor. The length of the branches can also provide insights into the amount of evolutionary change or the time that has passed since the species diverged.</p>
        <p>Phylogenetic trees are essential tools in systematics, the study of classification based on evolutionary relationships, helping scientists understand biodiversity, the origin of species, and their historical development.</p>

        <p><strong>Here is an example of a Phlyogenetic Tree:</strong></p>
        <img src="assets/PhylogeneticTree.jpg" alt="Phylogenetic Tree" style="max-width: 100%; height: auto;"><br><br>

        <p><strong>Another Example showing the Reptile Family Tree</strong></p>
        <img src="assets/ReptileTree.png" alt="Reptile Tree" style="max-width: 100%; height: auto;"><br><br>

        <p><strong>Another Phylogenetic Tree but showing only the Mammals Family Tree</strong></p>
        <img src="assets/MammalFamilyTree.png" alt="Mammal Tree" style="max-width: 100%; height: auto;"><br><br><br><br><br><br><br><br><br><br><br><br><br>


        <div id="instructions-container">
            <p>Now that you have an idea what a Phylogenetic Tree looks like procees to Lab test:</p>
            <button id="start-game-button">Start Lab</button>
        </div>

        <div id="confirmation-popup">
          <h2>Rubrics:</h2>
          <ul>
              <li>• 1 point each for correct identification</li>
              <li>• Get 17/17 points for 100%</li>
          </ul>
          <h3>Are you sure you want to proceed?</h3>
            <button id="confirm-yes">Yes</button>
            <button id="confirm-no">No</button>
        </div>


        <!-- Proceed to Lab button -->
        <button class="lab-btn" onclick="showCustomPrompt()" id="proceedQuizBtn" style="display:none;">Proceed to Quiz</button>
    </div>

    <!-- Custom prompt -->
    <div class="custom-prompt" id="customPrompt">
        <div class="prompt-content">
            <h2>Proceed to Quiz</h2>
            <p>Do you want to proceed to the Module Quiz?</p>
            <div class="prompt-buttons">
                <button class="yes" onclick="proceedToLab()">Yes</button>
                <button class="no" onclick="hideCustomPrompt()">No</button>
            </div>
        </div>
    </div>


    <script>

        function showCustomPrompt() {
            document.getElementById("customPrompt").style.display = "block";
        }

        function hideCustomPrompt() {
            document.getElementById("customPrompt").style.display = "none";
        }

        function proceedToLab() {
            // Redirect to the lab page or perform any other action
            window.location.href = "experiment4_quiz.php";
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Bind the start game button click event
            document.getElementById('start-game-button').onclick = startGame;

            // Function to display the confirmation popup
            function startGame() {
                document.getElementById('confirmation-popup').style.display = 'block';
            }

            // Bind the 'No' button click event
            document.getElementById('confirm-no').onclick = function() {
                hideConfirmationPopup();
            };

            // Bind the 'Yes' button click event
            document.getElementById('confirm-yes').onclick = function() {
                handleConfirmYes();
            };

            // Function to hide the confirmation popup
            function hideConfirmationPopup() {
                document.getElementById('confirmation-popup').style.display = 'none';
            }

            // Function to handle the 'Yes' button click
            function handleConfirmYes() {
                hideConfirmationPopup();
                document.getElementById('proceedQuizBtn').style.display = 'block';
                document.getElementById('overlay').style.display = 'block';

                // Open the URL in a new tab
                var gameWindow = window.open('phylogenetictree.php', '_blank');

                // Monitor the closure of the tab
                monitorWindowClosure(gameWindow);
            }

            // Function to monitor the closure of the new window
            function monitorWindowClosure(windowRef) {
                var checkWindowClosed = setInterval(function() {
                    if (windowRef.closed) {
                        document.getElementById('overlay').style.display = 'none';
                        clearInterval(checkWindowClosed);
                    }
                }, 1000); // Check every second
            }
        });

        // Path to the small image
        const imageSrc = 'assets/dna.png'; // Replace with the actual path to your image

        function createDot() {
            const img = document.createElement('img');
            img.classList.add('dot');
            img.src = imageSrc;

            // Randomize the x position and size
            const x = Math.random() * window.innerWidth;
            const size = Math.random() * 30 + 20; // Image size between 20px and 50px

            img.style.left = `${x}px`;
            img.style.width = `${size}px`;
            img.style.height = `${size}px`;

            // Set the vertical position near the bottom of the viewport
            const y = window.innerHeight - size; // Start from the bottom edge
            img.style.top = `${y}px`;

            // Randomize animation duration for varied speed
            const duration = Math.random() * 5 + 3; // between 3 and 8 seconds
            img.style.animationDuration = `${duration}s`;

            // Append image to the body
            document.body.appendChild(img);
        }

        // Create multiple image dots
        for (let i = 0; i < 100; i++) {
            createDot();
        }

    </script>
    <div id="overlay"></div>
</body>
</html>
