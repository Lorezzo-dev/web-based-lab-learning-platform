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
    $stmt = $conn->prepare("UPDATE grades SET module4_lab_grade = ? WHERE user_id = ?");
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
    <title>Phylogenetic Tree Labtest</title>
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

    }

    h1 {
        margin-bottom: 10px;
    }
        /* Drop area with the same size as draggables */
        .drop-area {
            width: 100px;
            height: 100px;
            border: 2px dashed #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 10px;
            position: relative;
        }


        /* Original container */
        #original-container {
          display: grid;
          grid-template-columns: repeat(9, 1fr); /* 9 columns, each taking up 1 fraction of the available space */
          gap: 10px;
          background-color: #fff;
          padding: 20px;
          border: 2px solid #6a1b9a; /* Visible border */
          box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Shadow effect */
          position: fixed;
          bottom: -100px;
          left: 0;
          width: 80%; /* Full width */
          justify-content: start; /* Align items to the left */
        }

        /* Draggable items */
        .draggable {
            width: 100px;
            height: 100px;
            cursor: move;
        }

        /* Image inside draggable items */
        .draggable img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            pointer-events: none; /* Disable image pointer events to allow drag on the parent div */
        }

        /* Invisible image */
        .invisible {
            visibility: hidden; /* Makes the image invisible */
        }

        /* Text within drop areas */
        .drop-text {
            position: absolute;
            z-index: 1;
        }

        .horizontal-line {
            border: 0;
            height: 2px; /* Thickness */
            background: #000; /* Color */
            width: 100%; /* Full width */
        }
        .vertical-line {
            width: 2px; /* Thickness */
            height: 200px; /* Height */
            background: #000; /* Color */
            margin: 20px; /* Space around the line */
        }

        .tree-container {
            position: relative;
            width: 600px; /* Adjust width as needed */
            height: 800px; /* Adjust height as needed */
        }

        .line {
            position: absolute;
            background: #000;
            animation: draw-line 2s forwards; /* 2 seconds duration */
        }

        @keyframes draw-line {
    0% {
        width: 0;
        height: 0;
    }
}

        .horizontal-line {
            height: 2px;
        }

        /* Vertical lines */
        .vertical-line {
            width: 2px;
        }

        /* Popup styles */
         .popup {
             position: absolute;
             background-color: #fff;
             border: 2px solid #6a1b9a;
             padding: 10px;
             box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
             display: none;
             z-index: 1000;
         }

         .popup.active {
             display: block;
         }

         .label {
             position: absolute;
             background-color: rgba(255, 255, 255, 0.8); /* Light background for readability */
             padding: 2px 5px;
             border: 1px solid #000; /* Border for visibility */
             font-size: 12px;
             color: #000; /* Text color */
         }
         .label2 {
             position: absolute;
             padding: 2px 5px;
             font-size: 12px;
             color: #000; /* Text color */
         }

         .vertical-label {
             position: absolute;
             padding: 2px 5px;
             font-size: 12px;
             color: #000; /* Text color */
             transform: rotate(-90deg); /* Rotate text 90 degrees counter-clockwise */
             transform-origin: left bottom; /* Adjust origin if necessary */
             white-space: nowrap; /* Prevent text from wrapping */
         }
         .container {
             position: absolute;
             top: 100px;
             left: 20px;
             background-color: #fff;
             border: 1px solid #00796b;
             border-radius: 5px;
             padding: 20px;
             box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
             display: flex;
             flex-direction: column;
             align-items: center;
             z-index: 1000; /* Ensure it is above other elements */
         }
         .container p {
             margin: 0 0 10px;
         }
         .submit-button {
             padding: 10px 15px; /* Adjust padding */
             background-color: #4caf50; /* Green background color */
             color: white; /* White text color */
             border: none; /* Remove border */
             border-radius: 5px; /* Rounded corners */
             cursor: pointer; /* Pointer cursor on hover */
             margin-top: 10px; /* Top margin */
             font-size: 16px; /* Font size */
         }
         .submit-button:hover {
             background-color: #388e3c; /* Darker green on hover */
         }
         .confirmation-popup,
         .completion-popup {
             display: none;
             position: fixed;
             top: 30%;
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
         .confirmation-popup.active
          {
             display: block;
         }
         .completion-popup.active
         {
            display: block;
        }
         .confirmation-popup button,
         .completion-popup button {
             padding: 10px 20px;
             border: none;
             color: white;
             cursor: pointer;
             border-radius: 5px;
             font-size: 16px;
             margin: 0 10px;
         }
         .confirmation-popup #yes-btn {
             background-color: #4caf50; /* Green background color */
         }
         .confirmation-popup #no-btn {
             background-color: #f44336; /* Red background color */
         }
         .confirmation-popup #yes-btn:hover {
             background-color: #388e3c; /* Darker green on hover */
         }
         .confirmation-popup #no-btn:hover {
             background-color: #c62828; /* Darker red on hover */
         }
         #exit-game-button {
             background-color: #f44336;
         }
         #exit-game-button:hover {
             background-color: #c62828;
         }

         .correct {
             border: 2px solid green;
             box-shadow: 0 0 15px green;
         }

         .incorrect {
             border: 2px solid red;
             box-shadow: 0 0 15px red;
         }

    </style>
</head>
<body>
<h1>Phylogenetic Tree LabTest</h1>

<div class="container">
    <p>Fill in the Phylogenetic Tree with the correct species, based on their Scientific Classification</p>
    <p>Click Submit if you are done</p>
    <button class="submit-button" id="submit-btn">Submit</button>
</div>

<br><br><br><br><br><br><br><br><br><br>
    <!-- Phylogenetic Tree -->
    <div class="tree-container">
        <div class="line vertical-line" style="top: 500px; left: 500px; height: 100px; background-color: #6a1b9a;"></div>
        <div class="line horizontal-line" style="top: 520px; left: 120px; width: 500px; background-color: #6a1b9a;"></div>
        <div class="line vertical-line" style="top: 300px; left: 100px; height: 200px; background-color: #6a1b9a;"></div>
        <div class="line vertical-line" style="top: 482px; left: 600px; height: 20px; background-color: #6a1b9a;"></div>

        <div class="line vertical-line" style="top: 300px; left: 350px; height: 200px; background-color: #0000FF;"></div>
        <div class="line horizontal-line" style="top: 320px; left: 320px; width: 100px; background-color: #0000FF;"></div>
        <div class="line vertical-line" style="top: 280px; left: 300px; height: 20px; background-color: #0000FF;"></div>
        <div class="line vertical-line" style="top: 152px; left: 400px; height: 150px; background-color: #0000FF;"></div>

        <div class="line horizontal-line" style="top: 500px; left: 570px; width: 100px; background-color: #FFFF00;"></div>
        <div class="line vertical-line" style="top: 430px; left: 550px; height: 50px; background-color: #FFFF00;"></div>
        <div class="line vertical-line" style="top: 280px; left: 650px; height: 200px; background-color: #FFFF00;"></div>

        <div class="line horizontal-line" style="top: 300px; left: 540px; width: 230px; background-color: #00FF00;"></div>
        <div class="line vertical-line" style="top: 260px; left: 520px; height: 20px; background-color: #00FF00;"></div>
        <div class="line horizontal-line" style="top: 280px; left: 520px; width: 130px; background-color: #00FF00;"></div>
        <div class="line vertical-line" style="top: 240px; left: 500px; height: 20px; background-color: #00FF00;"></div>
        <div class="line vertical-line" style="top: 112px; left: 630px; height: 150px; background-color: #00FF00;"></div>

        <div class="line vertical-line" style="top: 262px; left: 750px; height: 20px; background-color: #FFA500;"></div>
        <div class="line horizontal-line" style="top: 280px; left: 750px; width: 130px; background-color: #FFA500;"></div>
        <div class="line vertical-line" style="top: 220px; left: 730px; height: 50px; background-color: #FFA500;"></div>
        <div class="line vertical-line" style="top: 62px; left: 860px; height: 200px; background-color: #FFA500;"></div>

        <div class="line horizontal-line" style="top: 80px; left: 880px; width: 50px; background-color: #FFC0CB;"></div>
        <div class="line vertical-line" style="top: 30px; left: 908px; height: 150px; background-color: #FFC0CB;"></div>
        <div class="line horizontal-line" style="top: 50px; left: 930px; width: 50px; background-color: #FFC0CB;"></div>
        <div class="line vertical-line" style="top: -10px; left: 960px; height: 80px; background-color: #FFC0CB;"></div>
        <div class="line horizontal-line" style="top: 10px; left: 980px; width: 50px; background-color: #FFC0CB;"></div>
        <div class="line horizontal-line" style="top: 90px; left: 980px; width: 180px; background-color: #FFC0CB;"></div>

        <div class="line horizontal-line" style="top: 200px; left: 928px; width: 100px; background-color: #FFC0CB;"></div>
        <div class="line vertical-line" style="top: 140px; left: 1008px; height: 80px; background-color: #FFC0CB;"></div>
        <div class="line horizontal-line" style="top: 160px; left: 1030px; width: 260px; background-color: #FFC0CB;"></div>

        <div class="line horizontal-line" style="top: 240px; left: 1028px; width: 50px; background-color: #FFC0CB;"></div>
        <div class="line vertical-line" style="top: 200px; left: 1058px; height: 80px; background-color: #FFC0CB;"></div>
        <div class="line horizontal-line" style="top: 220px; left: 1080px; width: 80px; background-color: #FFC0CB;"></div>

        <div class="line horizontal-line" style="top: 298px; left: 1080px; width: 220px; background-color: #FFC0CB;"></div>
        <div class="line vertical-line" style="top: 240px; left: 1280px; height: 130px; background-color: #FFC0CB;"></div>
        <div class="line horizontal-line" style="top: 260px; left: 1300px; width: 50px; background-color: #FFC0CB;"></div>
        <div class="line vertical-line" style="top: 200px; left: 1330px; height: 100px; background-color: #FFC0CB;"></div>
        <div class="line horizontal-line" style="top: 220px; left: 1350px; width: 80px; background-color: #FFC0CB;"></div>
        <div class="line horizontal-line" style="top: 320px; left: 1350px; width: 80px; background-color: #FFC0CB;"></div>

        <div class="line horizontal-line" style="top: 390px; left: 1252px; width: 50px; background-color: #FFC0CB;"></div>
        <div class="line vertical-line" style="top: 340px; left: 1230px; height: 150px; background-color: #FFC0CB;"></div>
        <div class="line horizontal-line" style="top: 360px; left: 1050px; width: 200px; background-color: #FFC0CB;"></div>
        <div class="line vertical-line" style="top: 280px; left: 1030px; height: 120px; background-color: #FFC0CB;"></div>
        <div class="line horizontal-line" style="top: 300px; left: 1000px; width: 50px; background-color: #FFC0CB;"></div>
        <div class="line horizontal-line" style="top: 418px; left: 870px; width: 180px; background-color: #FFC0CB;"></div>

        <div class="line horizontal-line" style="top: 508px; left: 1200px; width: 50px; background-color: #FFC0CB;"></div>
        <div class="line vertical-line" style="top: 450px; left: 1180px; height: 100px; background-color: #FFC0CB;"></div>
        <div class="line horizontal-line" style="top: 470px; left: 1150px; width: 50px; background-color: #FFC0CB;"></div>
        <div class="line horizontal-line" style="top: 570px; left: 1002px; width: 200px; background-color: #FFC0CB;"></div>


        <div class="drop-area" id="drop1" style="top: 217px; left: 65px;"></div>
        <div class="drop-area" id="drop2" style="top: 80px; left: 260px;"> </div>
        <div class="drop-area" id="drop3" style="top: -160px; left: 355px;"></div>
        <div class="drop-area" id="drop4" style="top: 5px; left: 510px;"></div>
        <div class="drop-area" id="drop5" style="top: -300px; left: 480px;"></div>
        <div class="drop-area" id="drop6" style="top: -540px; left: 590px;"></div>
        <div class="drop-area" id="drop7" style="top: -540px; left: 720px;"></div>
        <div class="drop-area" id="drop8" style="top: -850px; left: 1020px;"></div>
        <div class="drop-area" id="drop9" style="top: -880px; left: 1150px;"></div>
        <div class="drop-area" id="drop10" style="top: -950px; left: 1280px;"></div>
        <div class="drop-area" id="drop11" style="top: -960px; left: 1150px;"></div>
        <div class="drop-area" id="drop12" style="top: -1100px; left: 1420px;"></div>
        <div class="drop-area" id="drop13" style="top: -1080px; left: 1420px;"></div>
        <div class="drop-area" id="drop14" style="top: -1200px; left: 888px;"></div>
        <div class="drop-area" id="drop15" style="top: -1220px; left: 758px;"></div>
        <div class="drop-area" id="drop16" style="top: -1270px; left: 1038px;"></div>
        <div class="drop-area" id="drop17" style="top: -1320px; left: 890px;"></div>


        <div class="label" style="top: 190px; left: 1000px;">Vertebrates</div>
        <div class="label" style="top: 230px; left: 1050px;">Tetrapods</div>
        <div class="label" style="top: 290px; left: 1250px;">Amniotes</div>
        <div class="label" style="top: 250px; left: 1330px;">Sauropsids</div>
        <div class="label" style="top: 380px; left: 1200px;">Synapsids</div>
        <div class="label" style="top: 500px; left: 1180px;">Artiodactyla</div>
        <div class="label" style="top: 350px; left: 1010px;">Euarchontoglires</div>
        <div class="label" style="top: 70px; left: 900px;">Animalia</div>
        <div class="label" style="top: 40px; left: 940px;">Invertebrates</div>
        <div class="label" style="top: 490px; left: 590px;">Eukaryotes</div>
        <div class="label" style="top: 510px; left: 250px;">Archaea</div>
        <div class="label" style="top: 310px; left: 350px;">Bacteria</div>
        <div class="label" style="top: 280px; left: 750px;">Opisthokont</div>
        <div class="label" style="top: 290px; left: 520px;">Plantae</div>
        <div class="vertical-label" style="top: 440px; left: 120px;">Methanococcales</div>
        <div class="vertical-label" style="top: 270px; left: 420px;">Enterobacterales</div>
        <div class="label2" style="top: 300px; left: 260px;">Bacillales</div>
        <div class="vertical-label" style="top: 490px; left: 570px;">Protista</div>
        <div class="vertical-label" style="top: 230px; left: 650px;">Solanales</div>
        <div class="label2" style="top: 260px; left: 460px;">Bryopsida</div>
        <div class="label2" style="top: 250px; left: 710px;">Fungi</div>
        <div class="label2" style="top: -10px; left: 970px;">Annelids</div>
        <div class="label2" style="top: 70px; left: 1030px;">Arthropods</div>
        <div class="label2" style="top: 70px; left: 1030px;">Arthropods</div>
        <div class="label2" style="top: 140px; left: 1070px;">Cyprinoformes</div>
        <div class="label2" style="top: 200px; left: 1090px;">Amphibia</div>
        <div class="label2" style="top: 200px; left: 1350px;">Reptilia</div>
        <div class="label2" style="top: 300px; left: 1370px;">Aves</div>
        <div class="label2" style="top: 280px; left: 1000px;">Rodentia</div>
        <div class="label2" style="top: 400px; left: 900px;">Primates</div>
        <div class="label2" style="top: 450px; left: 1150px;">Perissodactyla</div>
        <div class="label2" style="top: 550px; left: 1050px;">Cetacea</div>


    </div>

    <!-- Original Container for Items -->
    <div id="original-container">
        <div class="draggable" draggable="true" id="Chicken">
            <img src="assets/lab4/Chicken.png" alt="Chicken">
        </div>
        <div class="draggable" draggable="true" id="Ameoba">
            <img src="assets/lab4/Ameoba.png" alt="Ameoba">
        </div>
        <div class="draggable" draggable="true" id="Snake">
            <img src="assets/lab4/Snake.jpg" alt="Snake">
        </div>
        <div class="draggable" draggable="true" id="Ecoli">
            <img src="assets/lab4/Ecoli.png" alt="E. Coli">
        </div>
        <div class="draggable" draggable="true" id="Tomato">
            <img src="assets/lab4/Tomato.jpg" alt="Tomato">
        </div>
        <div class="draggable" draggable="true" id="Hamster">
            <img src="assets/lab4/Hamster.png" alt="Hamster">
        </div>
        <div class="draggable" draggable="true" id="Mushroom">
            <img src="assets/lab4/Mushroom.png" alt="Mushroom">
        </div>
        <div class="draggable" draggable="true" id="Jannaschii">
            <img src="assets/lab4/Mjan.jpg" alt="M. jannaschii">
        </div>
        <div class="draggable" draggable="true" id="Aureus">
            <img src="assets/lab4/Staph-aureus.jpg" alt="S. Aureus">
        </div>
        <div class="draggable" draggable="true" id="Horse">
            <img src="assets/lab4/Horse.jpeg" alt="Horse">
        </div>
        <div class="draggable" draggable="true" id="Moss">
            <img src="assets/lab4/Moss.png" alt="Moss">
        </div>
        <div class="draggable" draggable="true" id="Earthworm">
            <img src="assets/lab4/Earthworm.jpg" alt="Earthworm">
        </div>
        <div class="draggable" draggable="true" id="Whale">
            <img src="assets/lab4/Whale.jpg" alt="Whale">
        </div>
        <div class="draggable" draggable="true" id="Butterfly">
            <img src="assets/lab4/Butterfly.png" alt="Butterfly">
        </div>
        <div class="draggable" draggable="true" id="Frog">
            <img src="assets/lab4/Frog.png" alt="Frog">
        </div>
        <div class="draggable" draggable="true" id="Human">
            <img src="assets/lab4/Humans.jpg" alt="Human">
        </div>
        <div class="draggable" draggable="true" id="Goldfish">
            <img src="assets/lab4/Goldfish.jpg" alt="Goldfish">
        </div>
        <div class="draggable invisible" draggable="true" id="drag6">
     <img src="https://via.placeholder.com/100?text=Invisible" alt="Invisible Image">
 </div>

    </div>

    <!-- Popup for Scientific Class -->
    <div id="popup-container">
        <div id="popup" class="popup"></div>
    </div>

    <div class="confirmation-popup" id="confirmation-popup">
        <p>Are you sure your answers are correct?</p>
        <button id="yes-btn">Yes</button>
        <button id="no-btn">No</button>
    </div>

    <div class="completion-popup" id="completion-popup" style="top: 120px;">
        <h2 id="completion-message">Lab Test Completed: 100% Score!</h2>
        <button id="exit-game-button">Exit Game</button>
    </div>

    <script>


    </script>

</body>
</html>
