
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['score'])) {
    $score = (int)$_POST['score'];

    if ($score >= 20) {
        $stmt = $conn->prepare("UPDATE grades SET module1_lab_grade = 100.00 WHERE user_id = ?");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $stmt->close();
    }
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Frog Dissection LabTest</title>
    <link rel="icon" type="image/x-icon" href="assets/icon.png">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #e0f7fa;
            text-align: center;
            margin: 0;
            padding: 0;
        }

        #game-container {
            margin-top: 20px;
        }

        #frog-wrapper {
            display: none; /* Hide the initial frog image by default */
        }

        #frog-image,
        #dissected-frog-image {
            width: 600px;
            height: 600px;
        }

        #info-box {
            margin-top: 20px;
            padding: 10px;
            background-color: #b2dfdb;
            display: none;
        }

        #dissected-frog-wrapper {
            display: none;
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

        #popup {
            position: absolute;
            top: 20px;
            left: 20px;
            background-color: #fff;
            border: 1px solid #00796b;
            border-radius: 5px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            display: none;
            z-index: 1000;
        }

        #popup h2 {
            margin: 0;
            font-size: 20px;
        }

        #popup p {
            margin: 10px 0;
        }

        #popup .message {
            color: red;
            font-weight: bold;
        }

        #popup .correct-message {
            color: green;
            font-weight: bold;
        }

        #completion-popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #ffffff;
            border: 2px solid #00796b;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
            display: none;
            z-index: 1100;
            text-align: center;
        }

        #completion-popup h2 {
            font-size: 24px;
            color: #00796b;
            margin-bottom: 20px;
        }

        #exit-game-button {
            padding: 10px 20px;
            border: none;
            background-color: #f44336;
            color: white;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
        }

        #exit-game-button:hover {
            background-color: #c62828;
        }
    </style>
</head>

<body>
    <h1>Frog Dissection Identification Game</h1>

    <div id="game-container">
        <div id="frog-wrapper">
            <img id="frog-image" src="assets/frog.jpeg" alt="Frog">
        </div>
        <div id="dissected-frog-wrapper">
            <img id="dissected-frog-image" src="assets/frog.jpeg" alt="Dissected Frog" usemap="#frogmap">
            <map name="frogmap">
                <!-- Define clickable areas here -->
                <area class="clickable-area" shape="rect" coords="279,123,293,141" alt="Heart" onclick="checkAnswer('Heart')">
                <area class="clickable-area" shape="rect" coords="299,209,311,301" alt="Kidney" onclick="checkAnswer('Kidney')">
                <area class="clickable-area" shape="rect" coords="267,215,284,306" alt="Kidney" onclick="checkAnswer('Kidney')">
                <area class="clickable-area" shape="rect" coords="329,199,343,261" alt="Stomach" onclick="checkAnswer('Stomach')">
                <area class="clickable-area" shape="poly" coords="252,135,267,132,289,145,312,135,338,155,330,176,289,153,252,166" alt="Liver" onclick="checkAnswer('Liver')">
                <area class="clickable-area" shape="poly" coords="254,265,248,271,253,281,263,289,271,291,278,295,288,300,284,308,291,305,296,297,288,290,277,285,266,281,259,275" alt="Small Intestines" onclick="checkAnswer('Small Intestines')">
                <area class="clickable-area" shape="poly" coords="325,271,325,284,321,294,315,305,307,309,314,312,321,303,327,331,329,267" alt="Small Intestines" onclick="checkAnswer('Small Intestines')">
                <area class="clickable-area" shape="poly" coords="268,303,259,310,257,320,262,326,279,327,291,324,310,318,327,314,326,309,316,309,302,314,279,321,264,320,272" alt="Small Intestines" onclick="checkAnswer('Small Intestines')">
                <area class="clickable-area" shape="poly" coords="291,159,293,171,317,185,332,172,305,159" alt="Lungs" onclick="checkAnswer('Lungs')">
                <area class="clickable-area" shape="poly" coords="291,330,287,350,297,356,307,336,322,328,307,317,295,321" alt="Large Intestine" onclick="checkAnswer('Large Intestine')">
                <area class="clickable-area" shape="poly" coords="250,179,250,204,275,214,297,191,293,170" alt="Fat Bodies" onclick="checkAnswer('Fat Bodies')">
                <area class="clickable-area" shape="poly" coords="282,211,291,190,303,177,318,195,306,212,290,213" alt="Fat Bodies" onclick="checkAnswer('Fat Bodies')">
                <area class="clickable-area" shape="poly" coords="259,168,261,176,274,176,284,162" alt="Gall Bladder" onclick="checkAnswer('Gall Bladder')">
                <area class="clickable-area" shape="rect" coords="288,168,290,264" alt="Posterior Vena Cava" onclick="checkAnswer('Posterior Vena Cava')">
                <area class="clickable-area" shape="poly" coords="288,120,278,110,272,123,270,113,263,116,272,106,278,105,285,109,282,99,285,101,292,112,299,106,303,103,299,111,311,112,308,117,317,125,313,127,302,0" alt="Carotid Arteries" onclick="checkAnswer('Carotid Arteries')">
            </map>
            <div id="label-box">
                <h3 id="organ-name">Organ Name</h3>
                <p id="organ-info">Organ Information</p>
            </div>
        </div>
        <div id="popup">
            <p>You must reach score of 20</p>
            <h2>Score: <span id="score">0</span></h2>
            <p id="question">Identify the <span id="body-part"></span>!</p>
            <p id="feedback" class="message"></p>
            <p id="correct-feedback" class="correct-message"></p>
        </div>
    </div>

    <div id="completion-popup">
        <h2>Lab Test Completed: 100% Score!</h2>
        <button id="exit-game-button">Exit Game</button>
    </div>

    <script>
        let score = 0;
        let currentBodyPart = '';

        function startGame() {
            document.getElementById('frog-wrapper').style.display = 'none'; // Hide initial frog image
            document.getElementById('dissected-frog-wrapper').style.display = 'block'; // Show dissected frog image
            document.getElementById('popup').style.display = 'block'; // Show game popup
            nextQuestion(); // Start the game
        }

        function nextQuestion() {
            const bodyParts = ['Heart', 'Kidney', 'Stomach', 'Liver', 'Small Intestines', 'Lungs', 'Large Intestine', 'Fat Bodies', 'Gall Bladder', 'Posterior Vena Cava', 'Carotid Arteries'];
            currentBodyPart = bodyParts[Math.floor(Math.random() * bodyParts.length)];
            document.getElementById('body-part').innerText = currentBodyPart;
            document.getElementById('feedback').innerText = '';
            document.getElementById('correct-feedback').innerText = '';
        }

        function checkAnswer(selectedBodyPart) {
            if (selectedBodyPart === currentBodyPart) {
                score++;
                score++;
                document.getElementById('score').innerText = score;
                document.getElementById('correct-feedback').innerText = 'You are correct!';
                if (score >= 20) {
                  fetch('froggame.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `score=${score}`
                }).then(() => {
                    showCompletionPopup();
                });
                } else {
                    setTimeout(nextQuestion, 1000);
                }
            } else {
                document.getElementById('feedback').innerText = 'Wrong body part!';
            }
        }

        function showCompletionPopup() {
            document.getElementById('popup').style.display = 'none';
            document.getElementById('completion-popup').style.display = 'block';
        }

        document.getElementById('exit-game-button').addEventListener('click', function() {
            window.close(); // Close the window
        });

        // Start the game automatically when the page loads
        window.onload = startGame;
    </script>

</body>

</html>
