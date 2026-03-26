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
             font-weight: bold;
             font-size: 15px;
             color: #000; /* Text color */
         }
         .label2 {
             position: absolute;
             padding: 2px 5px;
             font-weight: bold;
             font-size: 15px;
             color: #000; /* Text color */
         }

         .vertical-label {
             position: absolute;
             padding: 2px 5px;
             font-size: 15px;
             font-weight: bold;
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
             animation: scaleIn 1s ease-in-out;
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

         /* Hover effect for draggable items */
         .draggable {
             width: 100px;
             height: 100px;
             cursor: move;
             transition: transform 0.3s ease, box-shadow 0.3s ease;
         }

         .draggable:hover {
             transform: scale(1.1); /* Slightly increase size on hover */
             box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3); /* Add shadow for depth */
         }

         /* Dragging effect */
         .draggable:active {
             transform: scale(1.2); /* Increase size while being dragged */
             opacity: 0.8; /* Make it semi-transparent */
             transition: transform 0.1s ease, opacity 0.1s ease;
         }

         /* Drop area pop animation */
         @keyframes pop {
             0% {
                 transform: scale(0.9);
             }
             50% {
                 transform: scale(1.1);
             }
             100% {
                 transform: scale(1);
             }
         }

         .drop-area {
    width: 100px;
    height: 100px;
    border: 2px dashed black;
    background-color: white; /* Added black color */
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 10px;
    position: relative;
    transition: background-color 0.3s ease, border-color 0.3s ease;
}


         /* Highlight drop area when an item is hovering over */
         .drop-area.hovered {
             border-color: #4caf50; /* Green border when hovered */
             background-color: rgba(76, 175, 80, 0.1);
         }

         /* Animate when an item is dropped */
         .drop-area.filled {
             animation: pop 0.3s ease-out; /* Play the "pop" animation */
             border-color: #4caf50; /* Green border when filled */
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
        <div id="progress-bar">0%</div>
    </div>
    
<h1>Phylogenetic Tree Lab</h1>

<div class="container">
    <p style="font-weight: bold; font-size: 20px;">Fill in the Phylogenetic Tree with the correct species, based on their Scientific Classification</p>
    <p style="font-weight: bold; font-size: 20px;">Click Submit if you are done</p>
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
        <div class="lablel" style="top: 350px; left: 1010px;">Euarchontoglires</div>
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
            <img src="assets/lab4/EColi.png" alt="E. Coli">
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
            <img src="assets/lab4/MJan.jpg" alt="M. jannaschii">
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
        <button id="exit-game-button">Exit Lab</button>
    </div>

    <script>
     document.body.style.transform = "scale(0.8)";
     const submitButton = document.getElementById('submit-btn');
     const confirmationPopup = document.getElementById('confirmation-popup');
     const completionPopup = document.getElementById('completion-popup');
     const confirmationYes = document.getElementById('yes-btn');
     const confirmationNo = document.getElementById('no-btn');
     const exitButton = document.getElementById('exit-game-button');

        document.addEventListener("DOMContentLoaded", () => {
            const dropAreas = document.querySelectorAll('.drop-area');
            const progressBar = document.getElementById('progress-bar');
            const totalDropZones = dropAreas.length;

            function updateProgressBar() {
                let filledDropZones = 0;
                dropAreas.forEach(area => {
                    if (area.querySelector('.draggable')) {
                        filledDropZones++;
                    }
                });
                
                let progressPercentage = (filledDropZones / totalDropZones) * 100;
                progressBar.style.width = progressPercentage + '%';
                progressBar.textContent = Math.round(progressPercentage) + '%';
            }

            dropAreas.forEach(area => {
                area.addEventListener('drop', event => {
                    setTimeout(updateProgressBar, 100); // Delay to ensure the draggable is detected
                });

                area.addEventListener('dragover', event => {
                    event.preventDefault();
                });
            });
        });

     // Store the original container reference for each draggable element
     const originalContainer = document.getElementById('original-container');
     const dropAreas = document.querySelectorAll('.drop-area');
     const popup = document.getElementById('popup');

     // Descriptions for each image
     const descriptions = {
         "Chicken": "Chicken:<br> Kingdom: Animalia<br> Phylum: Chordata<br> Class: Aves<br> Order: Galliformes<br> Family: Phasianidae<br> Genus:Gallus<br> Species: Gallus gallus",
         "Ameoba": "Ameoba<br> Kingdom: Protista<br> Phylum: Amoebozoa<br> Class: Tubulinea<br> Order: Amoebida<br> Family: Amoebidae",
         "Snake": "Snake<br> Kingdom: Animalia<br> Phylum: Chordata<br> Class: Reptilia<br> Order: Squamata<br> Suborder: Serpentes",
         "Ecoli": "Escherichia coli<br> Kingdom: Bacteria<br>  Phylum: Proteobacteria<br> Class: Gammaproteobacteria<br> Order: Enterobacterales<br> Family: Enterobacteriaceae<br> Genus: Escherichia",
         "Tomato": "Tomato<br> Kingdom: Plantae<br> Phylum: Angiosperms<br> Class: Eudicots<br> Order: Solanales<br> Family: Solanaceae<br> Genus: Solanum<br> Species: Solanum lycopersicum",
         "Hamster": "Hamster<br> Kingdom: Animalia<br> Phylum: Chordata<br> Class: Mammalia<br> Order: Rodentia<br> Family: Cricetidae<br>",
         "Mushroom": "Beech Mushroom<br> Kingdom: Fungi<br> Phylum: Basidiomycota<br> Class: Agaricomycetes<br> Order: Agaricales<br> Family: Hypsizygaceae<br> Genus: Hypsizygus",
         "Jannaschii": "Methanocaldococcus jannaschii<br> Domain: Archaea<br> Kingdom:Euryarchaeota<br> Phylum Euryarchaeota<br> Class: Methanococci<br> Order: Methanococcales<br> Family: Methanocaldococcaceae<br> Genus: Methanocaldococcus",
         "Aureus": "Staphylococcus aureus<br> Domain: Bacteria<br> Phylum: Firmicutes<br> Class: Bacilli<br> Order: Bacillales<br> Family: Staphylococcaceae<br> Genus: Staphylococcus ",
         "Horse": "Horse<br> Kingdom: Animalia<br> Phylum: Chordata<br> Class: Mammalia<br> Order: Perissodactyla<br> Family: Equidae<br> Genus: Equus<br>",
         "Moss": "Moss<br> Kingdom: Plantae<br> Phylum: Bryophyta<br> Class: Bryopsida",
         "Earthworm": "Earthworm<br> Domain: Eukaryota<br> Kingdom: Animalia<br> Phylum: Annelida<br> Clade: Pleistoannelida<br> Clade: Sedentaria<br> Class: Clitellata<br> Order: Opisthosoma<br> Suborder: Lumbricina",
         "Whale": "Blue Whale<br> Kingdom: Animalia<br> Phylum: Chordata<br> Class: Mammalia<br> Order: Cetacea<br> Suborder: Mysticeti<br> Family: Balaenopteridae<br> Genus: Balaenoptera<br> Species: Balaenoptera musculus<br>",
         "Butterfly": "Monarch Butterfly<br> Kingdom: Animalia<br> Phylum: Arthropoda<br> Class: Insecta<br> Order: Lepidoptera<br> Family: Nymphalidae<br> Genus: Danaus",
         "Frog": "Bullfrog<br> Kingdom: Animalia<br> Phylum: Chordata<br> Class: Amphibia<br> Order: Anura<br> Family: Ranidae<br> Genus: Rana<br> Species: Rana catesbeiana",
         "Human": "Humans<br> Kingdom: Animalia<br> Phylum: Chordata<br> Class: Mammalia<br> Order: Primates<br> Family: Hominidae<br> Genus: Homo<br> Species: Homo sapiens",
         "Goldfish": "Goldfish<br> Kingdom: Animalia<br> Phylum: Chordata<br> Class: Actinopterygii<br> Order: Cypriniformes<br> Family: Cyprinidae<br> Genus: Carassius<br> Species: Carassius auratus",

     };

     // Handle drag start
     document.querySelectorAll('.draggable').forEach(item => {
         item.addEventListener('dragstart', dragStart);
         item.addEventListener('click', showDescription); // Add click event
     });

     function dragStart(event) {
         event.dataTransfer.setData('text/plain', event.target.id);
     }

     function showDescription(event) {
         const id = event.currentTarget.id;
         const description = descriptions[id];
         const rect = event.currentTarget.getBoundingClientRect();

         popup.innerHTML = description;
         popup.style.left = `${rect.left + 50}px`; // Adjust horizontal position
         popup.style.top = `${rect.bottom + 250}px`; // Adjust vertical position
         popup.classList.add('active');
     }
     // Handle drag over for each drop area
     dropAreas.forEach(area => {
         area.addEventListener('dragover', event => {
             event.preventDefault();
             setTimeout(updateProgressBar, 100); // Delay to ensure the draggable is detected
         });

         // Handle drag enter to hide text
         area.addEventListener('dragenter', () => {
             const text = area.querySelector('.drop-text');
             if (text) {
                 text.style.visibility = 'hidden';
             }
         });

         // Handle drag leave to show text again if not dropped
         area.addEventListener('dragleave', () => {
             const text = area.querySelector('.drop-text');
             if (text) {
                 text.style.visibility = 'visible';
             }
         });
     });

     // Handle drop for each drop area
     dropAreas.forEach(area => {
         area.addEventListener('drop', event => {
             event.preventDefault();
             const data = event.dataTransfer.getData('text/plain');
             const draggableElement = document.getElementById(data);
             area.appendChild(draggableElement);

             // Ensure the text stays hidden after drop
             const text = area.querySelector('.drop-text');
             if (text) {
                 text.style.visibility = 'hidden';
             }
         });
     });

     // Handle drag over for the original container to allow items to return
     originalContainer.addEventListener('dragover', event => {
         event.preventDefault();
     });

     // Handle drop for the original container
     originalContainer.addEventListener('drop', event => {
         event.preventDefault();
         const data = event.dataTransfer.getData('text/plain');
         const draggableElement = document.getElementById(data);
         originalContainer.appendChild(draggableElement);
     });

     // Hide popup when clicking outside
     document.addEventListener('click', event => {
         if (!popup.contains(event.target) && !event.target.classList.contains('draggable')) {
             popup.classList.remove('active');
         }
     });


     submitButton.addEventListener('click', () => {
         confirmationPopup.classList.add('active');
     });

     confirmationYes.addEventListener('click', () => {
         confirmationPopup.classList.remove('active');
         evaluateDraggables()
     });

     confirmationNo.addEventListener('click', () => {
       confirmationPopup.classList.remove('active');
       });


       function evaluateDraggables() {
           // Correct IDs mapped to drop areas
           const correctMappings = {
               drop1: 'Jannaschii',
               drop2: 'Aureus',
               drop3: 'Ecoli',
               drop4: 'Ameoba',
               drop5: 'Moss',
               drop6: 'Tomato',
               drop7: 'Mushroom',
               drop8: 'Earthworm',
               drop9: 'Butterfly',
               drop10: 'Goldfish',
               drop11: 'Frog',
               drop12: 'Snake',
               drop13: 'Chicken',
               drop14: 'Hamster',
               drop15: 'Human',
               drop16: 'Horse',
               drop17: 'Whale',
           };

           let score = 0;
           const totalPoints = 17;

           // Loop through each drop area
           for (let dropId in correctMappings) {
               const dropArea = document.getElementById(dropId);
               const draggableId = dropArea.querySelector('.draggable')?.id;
               const correctId = correctMappings[dropId];

               // Remove existing correct/incorrect classes
               dropArea.classList.remove('correct', 'incorrect');

               // Check if the drop area is correct
               if (Array.isArray(correctId)) {
                   if (correctId.includes(draggableId)) {
                       score++;
                       dropArea.classList.add('correct');  // Apply green glow
                   } else {
                       dropArea.classList.add('incorrect');  // Apply red glow
                   }
               } else {
                   if (draggableId === correctId) {
                       score++;
                       dropArea.classList.add('correct');  // Apply green glow
                   } else {
                       dropArea.classList.add('incorrect');  // Apply red glow
                   }
               }
           }

           const percentage = ((score / totalPoints) * 100).toFixed(2);

           // Display completion popup
           const completionPopup = document.getElementById('completion-popup');
           const completionMessage = document.getElementById('completion-message');
           completionMessage.textContent = `Lab Test Completed: Your score is: ${score}/17 points: ${percentage}%`;
           completionPopup.style.display = 'block';

          submitScoreToDatabase('module4_lab_grade', percentage);

           // Add event listener to exit game button
           document.getElementById('exit-game-button').addEventListener('click', function() {
               // Close or redirect as needed
                window.close(); // Close the window
           });
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

       document.addEventListener("DOMContentLoaded", () => {
           const draggables = document.querySelectorAll('.draggable');
           const dropAreas = document.querySelectorAll('.drop-area');

           draggables.forEach(draggable => {
               draggable.addEventListener('dragstart', () => {
                   setTimeout(() => draggable.classList.add('invisible'), 0);
               });

               draggable.addEventListener('dragend', () => {
                   draggable.classList.remove('invisible');
               });
           });

           dropAreas.forEach(area => {
               area.addEventListener('dragover', (e) => {
                   e.preventDefault();
                   area.classList.add('hovered');
               });

               area.addEventListener('dragleave', () => {
                   area.classList.remove('hovered');
               });

               area.addEventListener('drop', (e) => {
                   e.preventDefault();
                   const draggable = document.querySelector('.invisible');
                   area.appendChild(draggable);
                   area.classList.remove('hovered');
                   area.classList.add('filled'); // Play pop animation
               });
           });
       });

    </script>

</body>
</html>
