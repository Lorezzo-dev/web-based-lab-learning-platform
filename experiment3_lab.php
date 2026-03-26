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
    $sql = "UPDATE progress SET module3lab_completed = 1 WHERE user_id = ?";
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
    .ruler-wrapper {
        position: relative;
        width: 80%;  /* Adjusted width of the ruler line */
        height: 50px;
        margin: 20px 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .ruler-line {
        position: absolute;
        width: 102.2%;  /* Full width of the wrapper */
        height: 4px;
        background-color: #333;
        top: 50%;
        transform: translateY(-50%);
    }

    .ruler-dot {
        position: absolute;
        width: 20px;
        height: 20px;
        background-color: #28a745;
        border-radius: 50%;
        cursor: grab;
        left: 0; /* Start the dot at the beginning of the ruler line */
        top: 50%;
        transform: translate(-50%, -50%);
        z-index: 2; /* Higher z-index to place it on top of the ruler line and marks */

    }

    .ruler-mark {
        position: absolute;
        width: 8px; /* Adjust width as needed */
        height: 8px; /* Adjust height as needed */
        background-color: #000; /* Black color */
        border-radius: 50%; /* Make it a circle */
        top: 50%;
        transform: translate(-50%, -50%);
        z-index: 1; /* Lower z-index to place it behind the dot */
    }

    .label {
        position: absolute;
        top: -30px; /* Adjust this value to position the label above the ruler */
        font-size: 12px;
        text-align: center;
        width: 100%;
        display: none;
    }

    .lab-image {
      display: block; /* Ensure it behaves as a block-level element */
      width: 500px; /* Fixed width */
      height: 500px; /* Fixed height */
      object-fit: contain; /* Fit the image within the container without cropping */
      margin: -80px 0;
    }
    .info-box {
      margin: 10px 0;
      padding: 60px; /* Add padding to the info box */
      background-color: #e9ecef;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      width: 100%;
      text-align: center;
      position: relative; /* Ensure the info box remains within the flow of the page */
    }

    .info-box h3 {
        margin: 0 0 10px;
    }

    .info-box p {
        margin: 0;
    }
    .paragraph-container {
    width: 100%;             /* Make sure it takes full width */
    padding: 20px;           /* Add some padding around the paragraphs */
    background-color: #f0f0f0; /* Light gray background for contrast */
    border-radius: 10px;      /* Rounded corners */
    margin-bottom: 20px;      /* Space between paragraph container and the next element (image) */
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); /* Soft shadow for a subtle 3D effect */
    text-align: justify;      /* Justify the text for better readability */
    }

    .vertical-line {
      position: absolute;
      width: 2px;
      background-color: #333;
      height: 50px; /* Adjust height to match ruler */
      top: 50%;
      transform: translateY(-50%);
  }

  .start-line {
      left: 0;
  }

  .end-line {
      right: 0;
  }

  .start-label, .end-label {
      position: absolute;
      font-size: 12px;
      color: #333;
  }

  .start-label {
      left: 0;
      top: 60px; /* Adjust position below the line */
  }

  .end-label {
      right: 0;
      top: 60px; /* Adjust position below the line */
  }

  #instructions-container {
    position: absolute; /* Position it absolutely within the container */
    top: 1100px;          /* Adjust top position */
    left: 200px;         /* Adjust left position */
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
      <h2>Evolution and Origin of Biodiversity Lab</h2>
      <h1>Interactive Timeline: From Cells to Humans</h1>
      <!-- Paragraph Container -->
      <div class="paragraph-container">
        <p>Biodiversity has evolved over billions of years, starting with simple, single-celled organisms in the oceans. As some of these organisms began producing oxygen, Earth's atmosphere changed, allowing more complex life forms to develop. Eukaryotic cells with nuclei eventually evolved, leading to plants, animals, and fungi. Over time, life became more diverse, with multicellular organisms appearing and more complex ecosystems forming. Around 540 million years ago, the Cambrian Explosion brought a burst of new species. Life then moved from water to land, with plants, animals, and insects adapting to different environments. While mass extinctions wiped out many species, they also paved the way for new life forms to evolve, leading to the rich diversity of life we see today. Humans, emerging only recently, have played a major role in shaping—and sometimes threatening—this biodiversity.</p>

        <p><strong>The interactive timeline below visualizes how life went from single-cell organisms to humans.</strong></p>
      </div>
       <img id="dynamicImage" src="assets/lab3/blank.jpg" alt="Dynamic Image" class="lab-image">

       <!-- New Info Box Container -->
       <div class="info-box" id="infoBox">
         <h3 id="infoTitle">Start</h3>
         <p id="infoDescription">Description of the species will appear here.<br><strong>BYA</strong> = Billion years Ago <br> <strong>MYA</strong> = Million years ago</p>
       </div>


      <div class="ruler-wrapper">
      <div class="ruler-line"></div>
      <div class="ruler-dot" draggable="true"></div>
      <div class="ruler-mark" style="left: 0%;"></div>
      <div class="ruler-mark" style="left: 3.45%;"></div>
      <div class="ruler-mark" style="left: 6.90%;"></div>
      <div class="ruler-mark" style="left: 10.34%;"></div>
      <div class="ruler-mark" style="left: 13.79%;"></div>
      <div class="ruler-mark" style="left: 17.24%;"></div>
      <div class="ruler-mark" style="left: 20.69%;"></div>
      <div class="ruler-mark" style="left: 24.14%;"></div>
      <div class="ruler-mark" style="left: 27.59%;"></div>
      <div class="ruler-mark" style="left: 31.03%;"></div>
      <div class="ruler-mark" style="left: 34.48%;"></div>
      <div class="ruler-mark" style="left: 37.93%;"></div>
      <div class="ruler-mark" style="left: 41.38%;"></div>
      <div class="ruler-mark" style="left: 44.83%;"></div>
      <div class="ruler-mark" style="left: 48.28%;"></div>
      <div class="ruler-mark" style="left: 51.72%;"></div>
      <div class="ruler-mark" style="left: 55.17%;"></div>
      <div class="ruler-mark" style="left: 58.62%;"></div>
      <div class="ruler-mark" style="left: 62.07%;"></div>
      <div class="ruler-mark" style="left: 65.52%;"></div>
      <div class="ruler-mark" style="left: 68.97%;"></div>
      <div class="ruler-mark" style="left: 72.41%;"></div>
      <div class="ruler-mark" style="left: 75.86%;"></div>
      <div class="ruler-mark" style="left: 79.31%;"></div>
      <div class="ruler-mark" style="left: 82.76%;"></div>
      <div class="ruler-mark" style="left: 86.21%;"></div>
      <div class="ruler-mark" style="left: 89.66%;"></div>
      <div class="ruler-mark" style="left: 93.10%;"></div>
      <div class="ruler-mark" style="left: 96.55%;"></div>
      <div class="ruler-mark" style="left: 100%;"></div>
      <div class="label" id="rulerLabel"></div>
      <div class="vertical-line start-line"></div>
      <div class="vertical-line end-line"></div>
      <div class="start-label">Start</div>
      <div class="end-label">End</div>
</div>

<div id="instructions-container">
    <p>Drag the Green dot on the ruler to see the different species in Earth's history that will eventually evolve into humans </p>
    <p>If you are ready, you can start lab test.</p>
    <button id="start-game-button">Start Lab</button>
</div>

<div id="confirmation-popup">
  <h2>Rubrics:</h2>
  <ul>
      <li>• 1 point correct answer</li>
      <li>• Get up to 5 points for 100%</li>
  </ul>
  <h3>Are you sure you want to proceed?</h3>
    <button id="confirm-yes">Yes</button>
    <button id="confirm-no">No</button>
</div>

    <br><br><br><br><br><br><br><br><br><br><br>
    <button class="lab-btn" onclick="showCustomPrompt()" id="proceedQuizBtn" style="display:none;">Proceed to Quiz</button>

    </div>


    <!-- Custom prompt -->
    <div class="custom-prompt" id="customPrompt">
        <div class="prompt-content">
            <h2>Proceed to Quiz</h2>
            <p>Do you want to proceed to the Module Quiz</p>
            <div class="prompt-buttons">
                <button class="yes" onclick="proceedToLab()">Yes</button>
                <button class="no" onclick="hideCustomPrompt()">No</button>
            </div>
        </div>
    </div>

    <!-- Script to open and close sidebar -->
    <script>

        function showCustomPrompt() {
            document.getElementById("customPrompt").style.display = "block";
        }

        function hideCustomPrompt() {
            document.getElementById("customPrompt").style.display = "none";
        }

        function proceedToLab() {
            // Redirect to the lab page or perform any other action
            window.location.href = "experiment3_quiz.php";
        }

        function updateLabel(index) {
            const periods = [
                "Start", "Cells (4.3BYA Hadean Eon)", "Prokaryotes (4.0BYA Hadean Eon)", "Eukaryotes (2.2BYA Proterozoic Eon)", "Dickinsonia (800MYA Ediacaran Period)", "Urbilaterian (650MYA Ediacaran Period)", "Platyhelminthes (550MYA Ediacaran Period)",
                "Pikaia (540MYA Cambrian Period)", "Haikouichthys (525MYA Cambrian Period)", "Agnatha (520MYA Cambrian Period)", "Placodermi (460MYA Silurian Period)", "Coelacanth (420MYA Silurian Period)", "Panderichthys (415MYA Devonian Period)", "Tiktaalik (375MYA Devonian Period)", "Ichthyostega (360MYA Devonian Period)", "Pederpes (358MYA Devonian period)",
                "Hylonomus (330MYA Carboniferous Period)", "Phthinosuchus (300MYA Carboniferous Period)", "Cynognathus (260MYA Permian Period)", "Repenomamus (230MYA Triassic Period)","Juramaia (170MYA Jurassic Period)", "Carpolestes (65MYA Cretaceous Period)", "Aegyptophitecus (50MYA Oligocene Epoch)", "Proconsul (35MYA Eocene Epoch)",
                "Pierolapithecus (15MYA Miocene Epoch)", "Ardiphitecus (5.7MYA Miocene Epoch)", "Australopithecus (4MYA Pliocene Epoch)", "Homo Erectus (2MYA Pleistocene Epoch)","Neanderthals (300,000 Years ago Pleistocene Epoch)", "Humans (200,000 Years ago to Present Day Holocene Epoch)"
            ];

            document.getElementById("rulerLabel").innerText = periods[index];

            const imageMap = {
                "Start": "assets/lab3/blank.jpg",
                "Cells (4.3BYA Hadean Eon)": "assets/lab3/cells.jpg",
                "Prokaryotes (4.0BYA Hadean Eon)": "assets/lab3/Prokaryote.jpg",
                "Eukaryotes (2.2BYA Proterozoic Eon)": "assets/lab3/eukaryotes.jpg",
                "Dickinsonia (800MYA Ediacaran Period)": "assets/lab3/Dickinsonia.jpg",
                "Urbilaterian (650MYA Ediacaran Period)": "assets/lab3/Hallucigenia.jpg",
                "Platyhelminthes (550MYA Ediacaran Period)": "assets/lab3/Planarians.jpg",
                "Pikaia (540MYA Cambrian Period)": "assets/lab3/Pikaia.jpg",
                "Haikouichthys (525MYA Cambrian Period)": "assets/lab3/Haikouichthys.jpg",
                "Agnatha (520MYA Cambrian Period)": "assets/lab3/Agnatha.jpg",
                "Placodermi (460MYA Silurian Period)": "assets/lab3/Placodermi.jpg",
                "Coelacanth (420MYA Silurian Period)": "assets/lab3/Coelacanth.jpg",
                "Panderichthys (415MYA Devonian Period)": "assets/lab3/Panderichthys.jpg",
                "Tiktaalik (375MYA Devonian Period)": "assets/lab3/Tiktaalik.png",
                "Ichthyostega (360MYA Devonian Period)": "assets/lab3/Ichthyostega.jpg",
                "Pederpes (358MYA Devonian period)": "assets/lab3/Pederpes.jpg",
                "Hylonomus (330MYA Carboniferous Period)": "assets/lab3/Hylonomus.jpeg",
                "Phthinosuchus (300MYA Carboniferous Period)": "assets/lab3/Phthinosuchus.jpg",
                "Cynognathus (260MYA Permian Period)": "assets/lab3/Cynognathus.jpg",
                "Repenomamus (230MYA Triassic Period)": "assets/lab3/Repenomamus.jpg",
                "Juramaia (170MYA Jurassic Period)": "assets/lab3/Juramaia.jpg",
                "Carpolestes (65MYA Cretaceous Period)": "assets/lab3/Carpolestes.jpg",
                "Aegyptophitecus (50MYA Oligocene Epoch)": "assets/lab3/Aegyptophitecus.jpg",
                "Proconsul (35MYA Eocene Epoch)": "assets/lab3/Proconsul.jpg",
                "Pierolapithecus (15MYA Miocene Epoch)": "assets/lab3/Pierolapithecus.jpg",
                "Ardiphitecus (5.7MYA Miocene Epoch)": "assets/lab3/Ardiphitecus.jpg",
                "Australopithecus (4MYA Pliocene Epoch)": "assets/lab3/Australophitecus.jpg",
                "Homo Erectus (2MYA Pleistocene Epoch)": "assets/lab3/Homo-erectus.png",
                "Neanderthals (300,000 Years ago Pleistocene Epoch)": "assets/lab3/Neanderthals.png",
                "Humans (200,000 Years ago to Present Day Holocene Epoch)": "assets/lab3/Humans.png"
            };

            const descriptions = {
                "Cells (4.3BYA Hadean Eon)": "Cells, the basic units of life, emerged around 4.3 billion years ago in the Hadean Eon. Early cells were simple, likely using self-replicating RNA molecules to transmit genetic information. These cells laid the foundation for all life forms, evolving into complex organisms over billions of years.",
                "Prokaryotes (4.0BYA Hadean Eon)": "Prokaryotes are single-celled organisms without a nucleus. Their genetic material is free-floating in the cell and consists of circular DNA. In early prokaryotes, RNA molecules stabilized into the double-helix structure of DNA, allowing for better genetic storage and replication. They are the simplest and earliest forms of life, including bacteria and archaea.",
                "Eukaryotes (2.2BYA Proterozoic Eon)": "Eukaryotes are complex cells that contain membrane-bound organelles such as nuclei, mitochondria, and a cytoskeleton, which allow for compartmentalized functions. These cells evolved to support more advanced biological processes, including sexual reproduction. Sexual reproduction provided genetic diversity, enhancing adaptability and evolutionary success. Eukaryotes are the building blocks of plants, animals, fungi, and protists.",
                "Dickinsonia (800MYA Ediacaran Period)": "Dickinsonia was an ancient organism that had soft, flat bodies with bilateral symmetry, meaning its left and right sides were mirror images. Dickinsonia showed early features of complex animals, including epithelia (protective outer tissue), muscle, and connective tissues. It likely had simple structures like a mouth and possibly photoreceptive eye-spots to detect light. This organism is considered an early step toward the evolution of more complex animals.",
                "Urbilaterian (650MYA Ediacaran Period)": "An early bilateral organism characterized by the emergence of a single-layered tube that functions as a single-chambered heart. This simple body plan is a precursor to more complex bilateral animals, featuring symmetry along a central axis.",
                "Platyhelminthes (550MYA Ediacaran Period)": "These are flatworms characterized by their flattened bodies and bilateral symmetry. They exhibit a simple structure with a single opening that functions as both mouth and anus. Notably, pharyngeal slits, which later evolve into gills in other organisms, first appear in these early worms, marking an important step in the evolution of more complex respiratory structures.",
                "Pikaia (540MYA Cambrian Period)": "An early chordate from the Cambrian period, Pikaia is notable for its primitive spine and notochord, which provide support and structure. It also had a post-anal tail, an important feature in the evolution of vertebrates. These characteristics make Pikaia a key ancestor in the development of more complex vertebrate structures.",
                "Haikouichthys (525MYA Cambrian Period)": "An early Cambrian chordate, Haikouichthys had a rudimentary brain, branched blood vessels, and a pharynx. It also possessed the ability to smell, marking an important step in sensory development. These features reflect its early advancement in the evolution of vertebrate-like traits.",
                "Agnatha (520MYA Cambrian Period)": "Agnatha are jawless fish that appeared in the Cambrian Period. They feature ears, camera-type eyes, a pineal gland (often referred to as a third eye), and skin layers. They possess a two-chambered heart and can taste. Notably, their pharyngeal slits evolved into gills, facilitating respiration",
                "Placodermi (460MYA Silurian Period)": "An early class of armored fish with jaws, scales, and teeth. They had specialized digestive organs such as the stomach and spleen, and their blood contained hemoglobin. Placodermi also exhibited early forms of adaptive immunity",
                "Coelacanth (420MYA Silurian Period)": "An ancient fish with lungs, preliminary ribs, and other developing bones. Coelacanths are known for their lobed pectoral fins and are considered living fossils, providing insights into the evolution of vertebrates.",
                "Panderichthys (415MYA Devonian Period)": "An early lobe-finned fish with protolimbs, which are evolutionary precursors to legs. Panderichthys represents a crucial stage in the transition from aquatic to terrestrial life, showcasing adaptations that led to the development of limbs in vertebrates.",
                "Tiktaalik (375MYA Devonian Period)": "An ancient vertebrate from around 375 million years ago, Tiktaalik is considered the first to transition from water to land. It had both fish-like and early tetrapod features, including robust fins capable of supporting its body on land. This crucial evolutionary step marks the beginning of vertebrate terrestrial life.",
                "Ichthyostega (360MYA Devonian Period)": "An early vertebrate with eyelids, tear glands, stronger bones, and a complete rib cage. It also features the first recognizable legs, marking a significant step in the evolution of land-dwelling animals.",
                "Pederpes (358MYA Devonian period)": " An early vertebrate from around 360 million years ago, Pederpes had a tongue, salivary glands, a three-chambered heart, glottis, and bladder. It marked a significant evolutionary step as dorsal, anal, and tail fins disappeared, indicating adaptation to a more terrestrial lifestyle.",
                "Hylonomus (330MYA Carboniferous Period)": "Hylonomus (about 330 million years ago) is one of the earliest known reptiles. It had keratinized skin, shelled eggs, and adrenal glands. It also had cranial nerves for sensory information, and gills had disappeared, marking its adaptation to a fully terrestrial life.",
                "Phthinosuchus (300MYA Carboniferous Period)": "Phthinosuchus was an early reptile with a diaphragm, specialized teeth, and a secondary palate. It marked a significant evolutionary step with the disappearance of scales, indicating an adaptation to a more advanced mode of life.",
                "Cynognathus (260MYA Permian Period)": "Cynognathus, from the Permian period, was a warm-blooded reptile with thicker skin and advanced lungs and lymphatic system. It had erect hindlimbs, contributing to a more upright posture. While it possessed a pineal gland (third eye) that would later disappear, it lacked lumbar ribs.",
                "Repenomamus (230MYA Triassic Period)": "Was an early mammal-like creature featuring milk glands, sweat glands, and hairy skin. It had internal penises, specialized teeth, and a four-chambered heart for maintaining a constant body temperature. Adapted for nocturnality, it experienced a loss of cervical ribs and a shift from tetrachromatic to dichromatic vision.",
                "Juramaia (170MYA Jurassic Period)": "This early mammal, dating to about 160 million years ago in the Late Jurassic, is notable for its advancements in reproductive features. It exhibited live (eggless) birth, had marsupial pouches, nipples, and a placenta. Additionally, Juramaia possessed external penises and separate anal and urogenital openings. These features indicate a significant evolutionary step towards modern mammalian reproduction.",
                "Carpolestes (65MYA Cretaceous Period)": "Carpolestes was an early primate with forward-facing eyes and grasping digits, adaptations that facilitated arboreal life. This species also marks a shift in diet, as it required the introduction of Vitamin C into its diet, reflecting changes in its metabolic needs.",
                "Aegyptophitecus (50MYA Oligocene Epoch)": "Aegyptopithecus was an early primate characterized by its menstrual cycle, diurnality, and the presence of paranasal sinuses. During this time, claws evolved into nails, and the mammary glands reduced to a single pair on the chest.",
                "Proconsul (35MYA Eocene Epoch)": "Proconsul was an early primate. It is characterized by trichromatic vision and larger brains compared to earlier primates. During this period, tails disappeared as primates evolved more advanced sensory and cognitive abilities.",
                "Pierolapithecus (15MYA Miocene Epoch)": "Pierolapithecus lived around 15 million years ago during the Miocene Epoch. It featured a flat and widened rib cage, a stiff lower spine, and flexible wrists, along with a shoulder blade adapted for climbing and swinging, indicating an advanced arboreal lifestyle.",
                "Ardiphitecus (5.7MYA Miocene Epoch)": "Ardipithecus (around 5.7 million years ago) is one of the earliest known hominids. It is likely bipedal, meaning it walked on two legs, and marks the beginning of brain enlargement in human evolution. This species also shows early signs of tool construction.",
                "Australopithecus (4MYA Pliocene Epoch)": "Australopithecus (around 4 to 2 million years ago) is a key early hominid known for being completely bipedal, walking exclusively on two legs. This adaptation is crucial in human evolution, reflecting significant changes in locomotion and posture.",
                "Homo Erectus (2MYA Pleistocene Epoch)": "Homo erectus is an early human species known for introducing fire and using clothing. This species exhibited a more advanced tool culture and larger brain compared to earlier hominins, marking a significant step in human evolution.",
                "Neanderthals (300,000 Years ago Pleistocene Epoch)": "Neanderthals had a brain size that stabilized, with elongated trunks and extremities compared to earlier humans. They also showed a reduction in body hair, adapting to cooler climates. Their physical features and advanced tool use demonstrate their close relation to modern humans.",
                "Humans (200,000 Years ago to Present Day Holocene Epoch)": "Humans are characterized by a protruding nose, advanced language skills, and complex cultural practices. They have a reduced brow ridge and snout, along with smaller jaws and teeth compared to earlier hominins. Their unique features include a high forehead and the development of sophisticated tools and symbolic communication."
            };

            let imageUrl = "assets/lab3/blank.jpg";
            let description = "Description of the species will appear here.";
            if (periods[index] in imageMap) {
                imageUrl = imageMap[periods[index]];
                description = descriptions[periods[index]] || description;
            }


                const dynamicImage = document.getElementById("dynamicImage");
                dynamicImage.src = imageUrl;
                dynamicImage.style.width = '500px'; // Set fixed width
                dynamicImage.style.height = '500px'; // Set fixed height
                document.getElementById("infoTitle").innerText = periods[index];
                document.getElementById("infoDescription").innerText = description;
        }

         document.addEventListener('DOMContentLoaded', function() {
             const rulerDot = document.querySelector('.ruler-dot');
             const rulerLine = document.querySelector('.ruler-line');
             const rulerMarks = document.querySelectorAll('.ruler-mark');
             const labels = document.querySelector('#rulerLabel');

             rulerDot.addEventListener('dragstart', function(event) {
                 event.preventDefault();
             });

             rulerDot.addEventListener('mousedown', function(event) {
                 const startX = event.clientX;
                 const startLeft = rulerDot.getBoundingClientRect().left;

                 function onMouseMove(event) {
                     const deltaX = event.clientX - startX;
                     let newLeft = startLeft + deltaX - rulerLine.getBoundingClientRect().left;

                     const maxLeft = rulerLine.offsetWidth - rulerDot.offsetWidth;
                     if (newLeft < 0) newLeft = 0;
                     if (newLeft > maxLeft) newLeft = maxLeft;

                     rulerDot.style.left = `${newLeft}px`;
                 }

                 function onMouseUp() {
                     document.removeEventListener('mousemove', onMouseMove);
                     document.removeEventListener('mouseup', onMouseUp);

                     let closestMark = null;
                     let closestDistance = Infinity;

                     rulerMarks.forEach((mark, index) => {
                         const markPosition = mark.getBoundingClientRect().left - rulerLine.getBoundingClientRect().left + (mark.offsetWidth / 2);
                         const dotPosition = rulerDot.getBoundingClientRect().left - rulerLine.getBoundingClientRect().left + (rulerDot.offsetWidth / 2);
                         const distance = Math.abs(markPosition - dotPosition);

                         if (distance < closestDistance) {
                             closestDistance = distance;
                             closestMark = mark;
                             updateLabel(index);
                         }
                     });

                     if (closestMark) {
                         const markPosition = closestMark.getBoundingClientRect().left - rulerLine.getBoundingClientRect().left - (rulerDot.offsetWidth / 2) + (closestMark.offsetWidth / 2);
                         rulerDot.style.left = `${markPosition}px`;
                     }
                 }

                 document.addEventListener('mousemove', onMouseMove);
                 document.addEventListener('mouseup', onMouseUp);
             });
         });

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
                 var gameWindow = window.open('timelinequiz.php', '_blank');

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
