
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
    $sql = "UPDATE progress SET module1lab_completed = 1 WHERE user_id = ?";
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
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        background-image: url('assets/hexbg.jpg'); /* Replace with your hexagon background */
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
        position: relative; /* Make the container a positioning context */
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
        display: block;
        margin-top: 20px;
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

    /*--------------------------*/
    #game-container {
        margin-top: 20px;
    }

    #frog-wrapper {
        display: none
    }

    #frog-image, #dissected-frog-image {
        width: 600px;
        height: 600px;
    }

    #info-box {
        display: none;
        position: absolute;
        background-color: #fff;
        border: 1px solid #ccc;
        padding: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        z-index: 1000;
    }


    #dissected-frog-wrapper {
        display: block;
        position: relative;
    }

    .clickable-area {
        cursor: pointer;
    }

    #label-box {
        position: absolute;
        background-color: #fff;
        border: 1px solid #00796b;
        padding: 5px;
        border-radius: 3px;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.3);
        display: none;
    }

    #label-box.show {
        display: block;
    }

    #instructions-container {
        position: absolute; /* Position it absolutely within the container */
        top: 20px;          /* Adjust top position */
        left: 20px;         /* Adjust left position */
        background-color: #fff;
        border: 1px solid #00796b;
        padding: 15px;
        border-radius: 5px;
        display: none;      /* Hide by default */
        width: fit-content; /* Adjust width based on content */
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
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

    #initial-instructions-container {
    position: absolute; /* Position it absolutely within the container */
    top: 20px;          /* Adjust top position */
    left: 20px;         /* Adjust left position */
    background-color: #fff;
    border: 1px solid #00796b;
    padding: 15px;
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
    display: block;     /* Show by default */
    width: fit-content; /* Adjust width based on content */
    z-index: 1000;      /* Ensure it is above other content */

}

#initial-instructions-container.hide {
    display: none;      /* Hide the container */
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

.clickable-area:hover {
    outline: 2px solid #00796b;
    background-color: rgba(0, 255, 0, 0.2);
}

.instructions {
    background-color: #e6f7ff;
    padding: 15px;
    border-left: 6px solid #2196F3;
    margin-bottom: 20px;
}
    </style>
</head>
<body>


    <!-- Page content -->
    <div class="container">\
          <h2>Organismal Biology Lab</h2>
          <h1>Frog Dissection</h1>
          <div id="initial-instructions-container">
            <p>Click on the frog's body part for information.</p>
            <p> Double click the popup to make it disappear</p>
            <p>If you are ready, you can start the Lab Assessment.</p>
            <button id="start-game-button">Start Lab</button>
          </div>

        <!--  <div class="instructions">
              <h2>Instructions</h2>
              <p>The diagram below shows the body parts of a dissected frog</p>
              <p>Click a body part to show its info </p>
          </div> -->



          <div id="game-container">
              <div id="frog-wrapper">
                  <img id="frog-image" src="assets/frogscalpel.jpg" alt="Frog" usemap="#frogmap1">
                  <map name="frogmap1">
                    <area class="clickable-area" shape="poly" coords="536,217,527,177,529,149,530,101,527,45,527,12,535,9,545,11,542,59,539,84,542,118,541,127,537,151,536,164,542,172,545,195" alt="Scalpel" title="Scalpel" onclick="dissectFrog()">
                  </map>
              </div>
              <div id="dissected-frog-wrapper">
                  <img id="dissected-frog-image" src="assets/frog.jpeg" alt="Dissected Frog" usemap="#frogmap">
                  <map name="frogmap">
                      <!-- Area tags from froglab.html -->
                      <area class="clickable-area" shape="rect" coords="279,123,293,141" alt="Heart" title="Heart" onclick="showOrganInfo('Heart', 'The heart of the frog is responsible for pumping blood throughout the body.', 123, 279)">
                      <area class="clickable-area" shape="rect" coords="299,209,311,301" alt="Kidney" title="Kidney" onclick="showOrganInfo('Kidney', 'The kidney is responsible for filtering waste from the frog\'s blood.', 200, 400)">
                      <area class="clickable-area" shape="rect" coords="267,215,284,306" alt="Kidney" title="Kidney" onclick="showOrganInfo('Kidney', 'The kidney is responsible for filtering waste from the frog\'s blood.', 200, 400)">
                      <area class="clickable-area" shape="rect" coords="329,199,343,261" alt="Stomach" title="Stomach" onclick="showOrganInfo('Stomach', 'The stomach helps in the digestion of food.', 400, 400)">
                      <area class="clickable-area" shape="poly" coords="252,135,267,132,289,145,312,135,338,155,330,176,289,153,252,166" alt="Liver" title="Liver" onclick="showOrganInfo('Liver', 'The liver is responsible for detoxification and metabolism.', 200, 200)">
                      <area class="clickable-area" shape="poly" coords="254,265,248,271,253,281,263,289,271,291,278,295,288,300,284,308,291,305,296,297,288,290,277,285,266,281,259,275" alt="Small Intestines" title="Small Intestines" onclick="showOrganInfo('Small Intestines', 'The small intestines are responsible for nutrient absorption.', 200, 400)">
                      <area class="clickable-area" shape="poly" coords="325,271,325,284,321,294,315,305,307,309,314,312,321,303,327,331,329,267" alt="Small Intestines" title="Small Intestines" onclick="showOrganInfo('Small Intestines', 'The small intestines are responsible for nutrient absorption.', 200, 400)">
                      <area class="clickable-area" shape="poly" coords="268,303,259,310,257,320,262,326,279,327,291,324,310,318,327,314,326,309,316,309,302,314,279,321,264,320,272" alt="Small Intestines" title="Small Intestines" onclick="showOrganInfo('Small Intestines', 'The small intestines are responsible for nutrient absorption.', 200, 400)">
                      <area class="clickable-area" shape="poly" coords="291,159,293,171,317,185,332,172,305,159" alt="Lungs" title="Lungs" onclick="showOrganInfo('Lungs', 'The lungs are responsible for respiration and gas exchange.', 159, 291)">
                      <area class="clickable-area" shape="poly" coords="291,330,287,350,297,356,307,336,322,328,307,317,295,321" alt="Large Intestine" title="Large Intestine" onclick="showOrganInfo('Large Intestine', 'The large intestine absorbs water and forms feces.', 250, 500)">
                      <area class="clickable-area" shape="poly" coords="250,179,250,204,275,214,297,191,293,170" alt="Fat Bodies" title="Fat Bodies" onclick="showOrganInfo('Fat Bodies', 'The fat bodies store energy for the frog.', 200, 282)">
                      <area class="clickable-area" shape="poly" coords="282,211,291,190,303,177,318,195,306,212,290,213" alt="Fat Bodies" title="Fat Bodies" onclick="showOrganInfo('Fat Bodies', 'The fat bodies store energy for the frog.', 200, 282)">
                      <area class="clickable-area" shape="poly" coords="259,168,261,176,274,176,284,162" alt="Gall Bladder" title="Gall Bladder" onclick="showOrganInfo('Gall Bladder', 'The gall bladder stores bile produced by the liver.', 168, 259)">
                      <area class="clickable-area" shape="rect" coords="288,168,290,264" alt="Posterior Vena Cava" title="Posterior Vena Cava" onclick="showOrganInfo('Posterior Vena Cava', 'The posterior vena cava is a large vein that carries deoxygenated blood from the lower body back to the heart.', 168, 288)">
                      <area class="clickable-area" shape="poly" coords="288,120,278,110,272,123,270,113,263,116,272,106,278,105,285,109" alt="Anterior Vena Cava" title="Carotid Arteries" onclick="showOrganInfo('Carotid Arteries', 'The carotid arteries in frogs carry oxygenated blood from the heart to the head and neck regions.', 120, 288)">
                  </map>
              </div>
          </div>

          <div id="info-box">
              <p id="organ-info"></p>
          </div>

          <div id="instructions-container">

          </div>

          <div id="confirmation-popup">
              <h2>Rubrics:</h2>
              <ul>
                  <li>• 1 points for correct identification of body parts</li>
                  <li>• Get up to 10 points for 100%</li>
              </ul>
              <h3>Are you sure you want to proceed?</h3>
              <button id="confirm-yes">Yes</button>
              <button id="confirm-no">No</button>
          </div>

        <!--  <button id="dissect-button">Dissect Frog</button>-->

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

    <!-- Script to open and close sidebar -->
    <script>
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


        function showCustomPrompt() {
            document.getElementById("customPrompt").style.display = "block";
        }

        function hideCustomPrompt() {
            document.getElementById("customPrompt").style.display = "none";
        }

        function proceedToLab() {
            // Redirect to the lab page or perform any other action
            window.location.href = "experiment1_quiz.php";
        }

        let identifiedOrgans = new Set();

        function showOrganInfo(organ, description, x, y) {
            if (!identifiedOrgans.has(organ)) {
                identifiedOrgans.add(organ);
                document.getElementById("info-box").innerHTML = `<strong>${organ}</strong>: ${description}`;
                document.getElementById("info-box").style.top = y + "px";
                document.getElementById("info-box").style.left = x + "px";
                document.getElementById("info-box").style.display = "block";

                if (identifiedOrgans.size === totalOrgans) {
                    alert("Congratulations! You've identified all the organs!");
                }
            }
        }


        // Add an event listener to the info-box for click events to toggle visibility
        document.getElementById('info-box').addEventListener('click', function() {
            this.style.display = 'none';
        });


        function startGame() {
            document.getElementById('confirmation-popup').style.display = 'block';


        }

        document.getElementById('confirm-yes').onclick = function() {
            document.getElementById('confirmation-popup').style.display = 'none';
            document.getElementById('proceedQuizBtn').style.display = 'block';
            document.getElementById('overlay').style.display = 'block';

            // Open the URL in a new tab
            var gameWindow = window.open('FrogLab/index.html', '_blank');

            // Function to hide the overlay when the new tab is closed (note: tabs generally cannot be detected if closed)
            var checkWindowClosed = setInterval(function() {
                if (gameWindow.closed) {
                    document.getElementById('overlay').style.display = 'none';
                    clearInterval(checkWindowClosed);
                }
            }, 1000); // Check every second
        };

        document.getElementById('confirm-no').onclick = function() {
            document.getElementById('confirmation-popup').style.display = 'none';
        }

        document.getElementById('start-game-button').onclick = startGame;

            document.getElementById('dissect-button').onclick = function() {
            document.getElementById('frog-wrapper').style.display = 'none';
            document.getElementById('dissected-frog-wrapper').style.display = 'block';
            document.getElementById('instructions-container').style.display = 'block';
            document.getElementById('dissect-button').style.display = 'none';
        }

        document.addEventListener('DOMContentLoaded', function() {
    // Show the initial instructional container
    document.getElementById('initial-instructions-container').style.display = 'block';
});
        function dissectFrog() {
           document.getElementById('initial-instructions-container').style.display = 'none';
           document.getElementById('frog-wrapper').style.display = 'none';
           document.getElementById('dissected-frog-wrapper').style.display = 'block';
           document.getElementById('instructions-container').style.display = 'block';
           document.getElementById('dissect-button').style.display = 'none';
         }


    </script>
    <div id="overlay"></div>
</body>
</html>
