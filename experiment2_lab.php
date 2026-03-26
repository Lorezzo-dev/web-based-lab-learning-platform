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
    $sql = "UPDATE progress SET module2lab_completed = 1 WHERE user_id = ?";
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
    h1, h2 {
        text-align: center;
    }
    .punnett-square {
        display: grid;
        grid-template-columns: repeat(3, 100px);
        grid-template-rows: repeat(3, 100px);
        gap: 5px;
        justify-content: center;
        margin: 20px 0;
        position: relative;
    }
    .cell {
        width: 100px;
        height: 100px;
        border: 2px solid #000;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 24px;
        background-color: #f9f9f9;
    }
    .legend {
        display: flex;
        justify-content: space-around;
        margin: 20px 0;
    }
    .legend div {
        padding: 10px;
        background-color: #ddd;
        border-radius: 20px;
    }
    .instructions {
        background-color: #e6f7ff;
        padding: 15px;
        border-left: 6px solid #2196F3;
        margin-bottom: 20px;
    }
    #punnett-square-container {
        margin-top: 20px;
        text-align: center;
    }
    .outer-square, .inner-square {
        width: 100px;
        height: 100px;
        border: 1px solid black;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 24px;
        position: relative;
    }
    #genotypes {
        margin-top: 10px;
    }
    .genotype {
        width: 50px;
        height: 50px;
        border: 1px solid black;
        display: inline-block;
        margin: 0 10px;
        line-height: 50px;
        font-size: 24px;
        cursor: grab;
    }
    #ratio-probability {
        margin-top: 20px;
    }
    .popup {
        position: absolute;
        background-color: #fff;
        border: 1px solid #000;
        padding: 5px;
        font-size: 12px;
        z-index: 10;
        display: none;
    }

    .popup-right {
        left: 110%; /* Adjust as needed */
        top: 20%;
        transform: translateY(-50%);
    }

    .popup-left {
        right: 110%; /* Adjust as needed */
        top: 80%;
        transform: translateY(-50%);
    }
    #ratio-probability-container {
        margin-top: 20px;
        padding: 10px;
        border: 2px solid black; /* Optional: Add a border color */
        border-radius: 8px; /* Optional: Round the corners */
        text-align: center; /* Center the text inside the container */
    }

    #instructions-container {
      position: absolute; /* Position it absolutely within the container */
      top: 1500px;          /* Adjust top position */
      left: 40px;         /* Adjust left position */
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
        <h2>Genetics Lab</h2>
        <h1>Understanding Punnett Squares</h1>

        <div class="instructions">
            <h2>Instructions</h2>
            <p>A Punnett square is a diagram used to predict the outcome of a particular cross or breeding experiment. It is named after Reginald C. Punnett, who devised the approach.</p>
            <p>In the square, the parent's alleles are written along the top and left sides. By filling in the square, you can determine the potential genotypes of the offspring.</p>
        </div>

        <h2>Punnett Square Visualization</h2>
        <p>Let's take a simple example: crossing two heterozygous individuals for a trait where the dominant allele is <strong>A</strong> and the recessive allele is <strong>a</strong>.</p>

        <div class="punnett-square">
            <div class="cell"></div>
            <div class="cell"><strong>A</strong></div>
            <div class="cell"><strong>a</strong></div>
            <div class="cell"><strong>A</strong></div>
            <div class="cell">AA</div>
            <div class="cell">Aa</div>
            <div class="cell"><strong>a</strong></div>
            <div class="cell">Aa</div>
            <div class="cell">aa</div>
        </div>

        <div class="legend">
            <div><strong>AA</strong>: Homozygous Dominant</div>
            <div><strong>Aa</strong>: Heterozygous</div>
            <div><strong>aa</strong>: Homozygous Recessive</div>
        </div>

        <h2>How It Works</h2>
        <p>Each parent contributes one allele to the offspring. In this example, one parent is <strong>Aa</strong> and the other is also <strong>Aa</strong>.</p>
        <ul>
            <li>The first cell in the Punnett square (top left) shows the combination <strong>AA</strong>, meaning the offspring would be homozygous dominant.</li>
            <li>The next two cells (top right and bottom left) show the combination <strong>Aa</strong>, meaning the offspring would be heterozygous.</li>
            <li>The last cell (bottom right) shows the combination <strong>aa</strong>, meaning the offspring would be homozygous recessive.</li>
        </ul>
        <p>This Punnett square tells us that there's a 25% chance of homozygous dominant (<strong>AA</strong>), a 50% chance of heterozygous (<strong>Aa</strong>), and a 25% chance of homozygous recessive (<strong>aa</strong>).</p>

        <h2>Interactive Punett Square</h2>
        <p>Punnett squares are a simple yet powerful tool for predicting the possible genotypes of offspring in genetic crosses. By understanding the basics of how alleles are passed from parents to offspring, you can use Punnett squares to explore a wide variety of genetic scenarios.</p>
        <p>Here is an example of a scenario, say we have a parent with <span style="color:brown;"><strong>Brown Eyes</strong></span> and another parent with <span style="color:skyblue;"><strong>Blue Eyes</strong></span>. For the alleles we will use "H" to represent <span style="color:brown;"><strong>Brown Eyes</strong></span> being the dominant gene and "h" to represent <span style="color:skyblue;"><strong>Blue Eyes</strong></span> as the recessive gene.
        <strong>Drag and drop</strong> the "H" and "h" alleles into the <strong>(-)</strong> of Punnet Square to makeup different scenarios of different combinations of the alleles. </p>

        <div id="punnett-square-container">
            <div class="punnett-square">
                <div class="outer-square"></div>
                <div class="outer-square" id="parent1-1" ondrop="drop(event)" ondragover="allowDrop(event)">-</div>
                <div class="outer-square" id="parent1-2" ondrop="drop(event)" ondragover="allowDrop(event)">-</div>
                <div class="outer-square" id="parent2-1" ondrop="drop(event)" ondragover="allowDrop(event)">-</div>
                <div class="inner-square" id="result1"></div>
                <div class="inner-square" id="result2"></div>
                <div class="outer-square" id="parent2-2" ondrop="drop(event)" ondragover="allowDrop(event)">-</div>
                <div class="inner-square" id="result3"></div>
                <div class="inner-square" id="result4"></div>

                <div class="popup popup-right" id="popup1"></div>
                <div class="popup popup-right" id="popup2"></div>
                <div class="popup popup-left" id="popup3"></div>
                <div class="popup popup-left" id="popup4"></div>
            </div>

            <div id="genotypes">
                <div class="genotype" draggable="true" ondragstart="drag(event)" id="H">H</div>
                <div class="genotype" draggable="true" ondragstart="drag(event)" id="h">h</div>
            </div>

            <div id="ratio-probability-container">
                <div id="ratio-probability">
                    <p id="brown-eye-ratio">Brown Eyes: 0%</p>
                    <p id="blue-eye-ratio">Blue Eyes: 0%</p>
                </div>
            </div>
        </div>

        <div id="instructions-container">
            <p>Study how the Punnet Square Works</p>
            <p>If you are ready, you can start lab test.</p>
            <button id="start-game-button">Start Lab</button>
        </div>

        <div id="confirmation-popup">
          <h2>Rubrics:</h2>
          <ul>
              <li>• 1 point correct answer</li>
              <li>• Get up to 10 points for 100%</li>
          </ul>
          <h3>Are you sure you want to proceed?</h3>
            <button id="confirm-yes">Yes</button>
            <button id="confirm-no">No</button>
        </div>

        <br><br><br>
        <button class="lab-btn" onclick="showCustomPrompt()" id="proceedQuizBtn" style="display:none;">Proceed to Quiz</button>
      </div>



    <!-- Custom prompt -->
    <div class="custom-prompt" id="customPrompt">
        <div class="prompt-content">
            <h2>Proceed to Quiz</h2>
            <p>Do you want to proceed to the quiz?</p>
            <div class="prompt-buttons">
                <button class="yes" onclick="proceedToLab()">Yes</button>
                <button class="no" onclick="hideCustomPrompt()">No</button>
            </div>
        </div>
    </div>


    <script>
    function allowDrop(ev) {
        ev.preventDefault();
    }

    function drag(ev) {
        ev.dataTransfer.setData("text", ev.target.id);
    }

    function drop(ev) {
        ev.preventDefault();
        const data = ev.dataTransfer.getData("text");
        ev.target.textContent = document.getElementById(data).textContent;
        calculateResults();
    }

    function calculateResults() {
        const parent1 = document.getElementById('parent1-1').textContent + document.getElementById('parent1-2').textContent;
        const parent2 = document.getElementById('parent2-1').textContent + document.getElementById('parent2-2').textContent;

        const results = [
            parent1.charAt(0) + parent2.charAt(0),
            parent1.charAt(0) + parent2.charAt(1),
            parent1.charAt(1) + parent2.charAt(0),
            parent1.charAt(1) + parent2.charAt(1)
        ];

        let brownEyeCount = 0;
        let blueEyeCount = 0;

        results.forEach((result, index) => {
            const resultElement = document.getElementById(`result${index + 1}`);
            resultElement.textContent = result;

            // Set text color based on allele combination
            if (result === 'HH' || result === 'Hh' || result === 'hH') {
                resultElement.style.color = 'brown'; // Brown color for 'HH', 'Hh', 'hH'
                brownEyeCount++;
            } else if (result === 'hh') {
                resultElement.style.color = 'skyblue'; // Sky blue color for 'hh'
                blueEyeCount++;
            } else {
                resultElement.style.color = ''; // Reset color if needed
            }

            // Add mini-labels to the inner squares
            const miniLabel = document.createElement('div');
            miniLabel.className = 'mini-label';
            if (result === 'HH' || result === 'Hh' || result === 'hH') {
                miniLabel.classList.add('brown-eye');

            } else if (result === 'hh') {
                miniLabel.classList.add('blue-eye');

            }
            resultElement.appendChild(miniLabel);
        });

        document.getElementById('brown-eye-ratio').textContent = `Brown Eyes: ${(brownEyeCount / 4) * 100}%`;
        document.getElementById('blue-eye-ratio').textContent = `Blue Eyes: ${(blueEyeCount / 4) * 100}%`;

        displayPopup('parent1-1', 'parent1-2', 'popup1');
        displayPopup('parent2-1', 'parent2-2', 'popup3');
    }

    function displayPopup(parent1Id, parent2Id, popupId) {
        const allele1 = document.getElementById(parent1Id).textContent;
        const allele2 = document.getElementById(parent2Id).textContent;
        const popup = document.getElementById(popupId);

        let text = '';
        if (allele1 === 'H' && allele2 === 'H') {
            text = '<strong>HH</strong>: Homozygous Dominant';
        } else if ((allele1 === 'H' && allele2 === 'h') || (allele1 === 'h' && allele2 === 'H')) {
            text = '<strong>Hh</strong>: Heterozygous';
        } else if (allele1 === 'h' && allele2 === 'h') {
            text = '<strong>hh</strong>: Homozygous Recessive';
        } else {
            popup.style.display = 'none';
            return;
        }

        popup.innerHTML = text;
        popup.style.display = 'block';
    }

    document.getElementById('start-game-button').onclick = startGame;
    function startGame() {
        document.getElementById('confirmation-popup').style.display = 'block';

    }
    document.getElementById('confirm-no').onclick = function() {
        document.getElementById('confirmation-popup').style.display = 'none';
    }

    document.getElementById('confirm-yes').onclick = function() {
        document.getElementById('confirmation-popup').style.display = 'none';
        document.getElementById('proceedQuizBtn').style.display = 'block';
        document.getElementById('overlay').style.display = 'block';

        // Open the URL in a new tab
        var gameWindow = window.open('punnetsquare.php', '_blank');

        // Function to hide the overlay when the new tab is closed (note: tabs generally cannot be detected if closed)
        var checkWindowClosed = setInterval(function() {
            if (gameWindow.closed) {
                document.getElementById('overlay').style.display = 'none';
                clearInterval(checkWindowClosed);
            }
        }, 1000); // Check every second
    };

        function showCustomPrompt() {
            document.getElementById("customPrompt").style.display = "block";
        }

        function hideCustomPrompt() {
            document.getElementById("customPrompt").style.display = "none";
        }

        function proceedToLab() {
            // Redirect to the lab page or perform any other action
            window.location.href = "experiment2_quiz.php";
        }

        // Path to the small image
        const imageSrc = 'assets/dna.png'; // Replace with the actual path to your image

        // Function to create multiple animated images
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
