<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Database connection parameters
include "connection.php";

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

$stmt->close(); // Close statement
$conn->close(); // Close the database connection
include 'sidebar.php'; // Include the sidebar

$xmlFile = "xmlfile/announcements.xml";
$announcements = [];

if (file_exists($xmlFile)) {
    $xml = simplexml_load_file($xmlFile);
    if ($xml && isset($xml->msg)) {
        foreach ($xml->msg as $msg) {
            $announcements[] = (string) $msg;
        }
    }
}
// Ensure announcements.xml exists
if (!file_exists($xmlFile)) {
    $xml = new SimpleXMLElement('<announcements></announcements>');
    $xml->asXML($xmlFile);
}

// Function to add announcement
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_announcement'])) {
    $announcement = trim($_POST['announcement']);
    if (!empty($announcement)) {
        $xml = simplexml_load_file($xmlFile);
        $newMsg = $xml->addChild('msg', htmlspecialchars($announcement));
        $xml->asXML($xmlFile);
    }
    
        echo "<script>
        setTimeout(() => { window.location.href = 'home.php'; }, 100);
    </script>";
}

// Function to delete the last announcement
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_announcement'])) {
    $xml = new DOMDocument();
    $xml->load($xmlFile);
    $xml->preserveWhiteSpace = false;
    $xml->formatOutput = true;
    
    $announcements = $xml->getElementsByTagName("msg");
    
    if ($announcements->length > 0) {
        $lastMsg = $announcements->item($announcements->length - 1);
        $lastMsg->parentNode->removeChild($lastMsg); // Remove last <msg>
        $xml->save($xmlFile); // Save changes
    }
        // Force multiple reloads using JavaScript
     echo "<script>
        setTimeout(() => { window.location.href = 'home.php'; }, 100);
    </script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GenB Distance Learning</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300&display=swap" rel="stylesheet"> <!-- Add Quicksand font -->
    <style>
    body {
        font-family: 'Quicksand', sans-serif; /* Use Quicksand font */
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column; /* Stack elements vertically */
        min-height: 100vh; /* Full height for the body */
    }

    header {
        background: black;
        color: #fff;
        padding: 180px 20px; /* Adjusted padding for the header */
        text-align: center;
        width: 100%; /* Full width */
        overflow: hidden; /* Hide overflow to keep dots within the header */
        z-index: -1;
        position: relative;
    }

    header h1 {
        margin: 0;
    }

    .header-title {
      position: absolute; /* Set title to absolute positioning */
      top: 20px; /* Adjust the distance from the top */
      left: 400px; /* Adjust the distance from the left */
      font-size: 40px;
      color: #557DF6;
      margin: 0;
    }

    .header-subtitle {
      position: absolute; /* Set subtitle to absolute positioning */
      top: 80px; /* Position it below the header title (adjust as necessary) */
      left: 450px; /* Align with the header title */
      font-size: 70px; /* Set a larger font size for subtitle */
      color: white; /* Set the text color to white */
      margin: 0; /* Remove margin */
      text-align: left; /* Align text to the left */
    }

    .header-description {
      position: absolute; /* Set description to absolute positioning */
      top: 240px; /* Position it below both the title and subtitle (adjust as necessary) */
      left: 450px; /* Align with the header title */
      color: #C3C3C3; /* Set the text color */
      font-size: 24px; /* Increase font size to make it larger */
      margin: 10px 0; /* Reduced bottom margin */
      text-align: left; /* Align text to the left */
    }

    .header-image {
      position: absolute; /* Position the image absolutely */
      top: -30px; /* Adjust top position to align it properly */
    /*  left: 900px; /* Place the image on the right */
      right: 20px;
      width: 450px; /* Set a size for the atom image */
      height: auto; /* Maintain aspect ratio */
      z-index: 0; /* Ensure the image is behind the titles */
    }

    section {
      padding: 20px;
      margin: 20px; /* Space around the section */
      margin-left: 320px; /* Adjusted left margin to account for sidebar width and spacing */
      flex: 1; /* Allow the section to grow */
      text-align: center; /* Center align content in the section */
      z-index: 10;
      position: relative;
      overflow: visible; /* Ensure dropdowns are not cut off */
    }

    .section-title {
        color: #284290; /* Set section title color */
        font-size: 60px; /* Adjust font size for title */
        margin: 20px 0; /* Adjust margin for spacing */
        position: absolute;
        left: 480px;
        top: 0px;

    }

    .panel {
      background-color: #f9f9f9 /* Background color of the panel */
      border: none; /* Make the border invisible */
      padding: 20px; /* Inner padding */
      max-width: 500px; /* Maximum width of the panel */
      margin: 20px 0; /* Center the panel horizontally and keep top/bottom margins */
      text-align: center; /* Center the text */
      position: absolute; /* Ensure the panel can be positioned */
      left: 20px;
    }

    .panel2 {
      background-color: #f9f9f9 /* Background color of the panel */
      border: none; /* Make the border invisible */
      padding: 20px; /* Inner padding */
      max-width: 600px; /* Maximum width of the panel */
      margin: 20px 0; /* Center the panel horizontally and keep top/bottom margins */
      text-align: center; /* Center the text */
      position: absolute; /* Ensure the panel can be positioned */
      left: 580px;
    }

    .panel3 {
      background-color: #f9f9f9 /* Background color of the panel */
      border: none; /* Make the border invisible */
      padding: 20px; /* Inner padding */
      max-width: 800px; /* Maximum width of the panel */
      margin: 20px 0; /* Center the panel horizontally and keep top/bottom margins */
      text-align: center; /* Center the text */
      position: absolute; /* Ensure the panel can be positioned */
      left: 1380px;
    }

    .panel4 {
      background-color: #f9f9f9 /* Background color of the panel */
      border: none; /* Make the border invisible */
      padding: 20px; /* Inner padding */
      max-width: 500px; /* Maximum width of the panel */
      margin: 20px 0; /* Center the panel horizontally and keep top/bottom margins */
      text-align: center; /* Center the text */
      position: absolute; /* Ensure the panel can be positioned */
      left: 630px;
    }

    .panel-image {
        width: 200px; /* Set image width */
        height: auto; /* Maintain aspect ratio */
        margin-bottom: 15px; /* Space below the image */
    }


    .panel-title {
        color: black; /* Title color */
        font-size: 32px; /* Title font size */
        margin: 10px 0; /* Margin around the title */
    }

    .panel-description {
        color: black; /* Description text color */
        font-size: 16px; /* Description font size */
        line-height: 1.5; /* Line height for better readability */

    }
    .clearfix::after {
        content: "";
        clear: both;
        display: table;
    }
    .start-course-btn {
        display: block;
        margin: 20px auto; /* Center horizontally */
        background-color: #F3E06A; /* Original background color */
        color: #284290; /* Text color */
        padding: 15px 30px; /* Increased padding to make the button larger */
        border: none;
        border-radius: 5px;
        text-decoration: none;
        font-size: 20px; /* Increased font size for better visibility */
        font-family: 'Quicksand', sans-serif; /* Apply Quicksand font */
        cursor: pointer;
        text-align: center;
        position: absolute;
        left: 780px;
    }

    .start-course-btn:hover {
        background-color: yellow; /* Darker shade on hover */
    }


    footer {
        text-align: center;
        padding: 10px 0;
        background: #333;
        color: #fff;
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


    .dot {
        position: absolute;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background-color: #f39c12;
        animation: moveDot 5s infinite ease-in-out;
        pointer-events: none; /* Prevent interaction */
        z-index: -10;
    }

    @keyframes moveDot {
        0% {
            transform: translate(0, 0);
        }
        25% {
            transform: translate(200px, -200px);
        }
        50% {
            transform: translate(-200px, 200px);
        }
        75% {
            transform: translate(300px, 100px);
        }
        100% {
            transform: translate(0, 0);
        }
    }


    
#announcements-container {
    width: 100%;
    max-width: 1000px;
    margin: 20px auto;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 10px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
    height: 300px; /* Fixed height */
    overflow-y: auto; /* Enables vertical scrolling */
    scrollbar-width: thin; /* Makes scrollbar less obtrusive */
    scrollbar-color: #007bff #f8f9fa; /* Scrollbar styling for Firefox */
}

/* Custom scrollbar for WebKit browsers (Chrome, Edge, Safari) */
#announcements-container::-webkit-scrollbar {
    width: 8px;
}

#announcements-container::-webkit-scrollbar-thumb {
    background-color: #007bff; /* Scrollbar color */
    border-radius: 10px;
}

#announcements-container::-webkit-scrollbar-track {
    background: #f8f9fa; /* Scrollbar track color */
}
#announcements-container h3 {
    text-align: center;
    color: #333;
    font-size: 1.5em;
    margin-bottom: 10px;
}

.announcement-box {
    background: white;
    padding: 10px;
    margin: 5px 0;
    border-left: 5px solid #007bff;
    border-radius: 5px;
    box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);
    font-size: 1.1em;
}

       .announcement-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .announcement-buttons button {
            padding: 12px 20px;
            font-size: 16px;
            font-weight: bold;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            transition: 0.3s;
        }
        .add-btn {
            background-color: #4CAF50; /* Green */
            color: white;
        }
        .add-btn:hover {
            background-color: #45a049;
        }
        .delete-btn {
            background-color: #f44336; /* Red */
            color: white;
        }
        .delete-btn:hover {
            background-color: #d32f2f;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            width: 700px;
        }
        .modal textarea {
            width: 80%;
            height: 100px;
            margin-bottom: 10px;
            padding: 8px;
            font-size: 14px;
        }
        .modal button {
            padding: 10px 15px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            font-size: 14px;
        }
        .save-btn {
            background-color: #008CBA; /* Blue */
            color: white;
        }
        .save-btn:hover {
            background-color: #007bb5;
        }
        .cancel-btn {
            background-color: #777; /* Gray */
            color: white;
        }
        .cancel-btn:hover {
            background-color: #666;
        }
    </style>
</head>
<body>

  <!-- Page content -->
  <header>
      <h1 class="header-title">GEN B DISTANCE LEARNING</h1>
      <h2 class="header-subtitle">LEARN, DISCOVER,<br> GROW </h2>
      <h3 class="header-description">Understand Life Sciences With Us </h3>
      <img src="assets/atom.png" alt="Atom Icon" class="header-image">
  </header>

  <section>
      <h2 class="section-title">What Can You Learn From Us?</h2>
      <br><br><br><br><br><br><br>
      <div class="panel">
          <img src="assets/bacteriaicon.png" alt="Organismal Biology" class="panel-image">
          <h3 class="panel-title">Organismal Biology</h3>
          <p class="panel-description">Explore the functions of plant and animal organ systems while uncovering the interconnectedness of these systems and the workings of feedback mechanisms. Experience hands-on analysis of biological processes, enhancing your understanding of how organisms respond to internal and external changes.</p>
      </div>

      <div class="panel2">
          <img src="assets/dnaicon.png" alt="Genetics" class="panel-image">
          <h3 class="panel-title">Genetics</h3>
          <p class="panel-description">Study Mendel’s Laws of Inheritance and learn how traits are passed through generations. Explore sex linkage to understand how genes on sex chromosomes affect inheritance patterns. Examine the Central Dogma of Molecular Biology to grasp how DNA is transcribed into RNA and translated into proteins. Discover recombinant DNA techniques and their role in genetic engineering.</p>
      </div>

      <div class="panel3">
          <img src="assets/evoicon.png" alt="Evolution" class="panel-image">
          <h3 class="panel-title">Evolution and Origin of Biodiversity</h3>
          <p class="panel-description">Examine the relevance of evolution in understanding the diversity of life on Earth. Explore the mechanisms driving evolutionary change, such as natural selection and genetic drift. Review the evidence supporting evolutionary theories, including fossil records and genetic data. Learn about the foundational theories of evolution and how they explain the origins of biodiversity.</p>
      </div>
      <br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>

      <div class="panel4">
          <img src="assets/treeicon.png" alt="Phylogenetic" class="panel-image">
          <h3 class="panel-title">Systematics Based on Evolutionary Relationships</h3>
          <p class="panel-description">Study basic taxonomic concepts and principles to understand how organisms are classified based on evolutionary relationships. Learn the methods for describing species, including nomenclature rules for naming them. Gain skills in identification and classification techniques that help organize biodiversity in a systematic way.</p>
      </div>

      <br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
              <button class="start-course-btn" onclick="showCustomPrompt()"><strong>START COURSE</strong></button>
      <br><br><br><br>

<div id="announcements-container">
    <h3>📢 Announcements</h3>
    <?php if (!empty($announcements)): ?>
        <?php foreach ($announcements as $announcement): ?>
            <div class="announcement-box">
                <?php echo htmlspecialchars($announcement); ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="announcement-box">No announcements available.</div>
    <?php endif; ?>
    
          <?php if (isset($_SESSION['role']) && ($_SESSION['role'] == 1 || $_SESSION['role'] == 2)): ?>
<div class="announcement-buttons">
    <button class="add-btn" onclick="openModal()">Add Announcement</button>
    <form method="post">
        <button type="submit" name="delete_announcement" class="delete-btn">Delete Announcement</button>
    </form>
</div>

<!-- Modal for Adding Announcement -->
<div id="announcementModal" class="modal">
    <div class="modal-content">
        <h3>Add Announcement</h3>
        <form method="post">
            <textarea name="announcement" placeholder="Type your announcement here..." required></textarea>
            <br>
            <button type="submit" name="add_announcement" class="save-btn">Save</button>
            <button type="button" class="cancel-btn" onclick="closeModal()">Cancel</button>
        </form>
    </div>
</div>


          <?php endif; ?>
</div>
  </section>

  <footer>
      <p>© 2024 General Biology 2 Virtual Lab. All rights reserved.</p>
  </footer>

  <!-- Custom prompt -->
  <div class="custom-prompt" id="customPrompt" style="display:none;">
      <div class="prompt-content">
          <h2>Start Course</h2>
          <p>Do you want to start the course?</p>
          <div class="prompt-buttons">
              <button class="yes" onclick="proceedToCourse()">Yes</button>
              <button class="no" onclick="hideCustomPrompt()">No</button>
          </div>
      </div>
  </div>

    <!-- Custom prompt Script -->
    <script>
        function showCustomPrompt() {
            document.getElementById("customPrompt").style.display = "block"; // Show the prompt
        }

        function hideCustomPrompt() {
            document.getElementById("customPrompt").style.display = "none"; // Hide the prompt
        }

        function proceedToCourse() {
            // Redirect to the first module or any other action
            window.location.href = "experiment1.php"; // Redirect to the first module
        }

        function createDot() {
            const dot = document.createElement('div');
            dot.classList.add('dot');

            // Randomize starting positions within the header
            const header = document.querySelector('header');
            const x = Math.random() * (header.clientWidth - 20); // Adjust for dot width
            const y = Math.random() * (header.clientHeight - 20); // Adjust for dot height

            dot.style.left = `${x}px`;
            dot.style.top = `${y}px`;

            // Append to header
            header.appendChild(dot);

            // Randomize animation duration and size
            const randomDuration = Math.random() * 5 + 3; // between 3 and 8 seconds
            const randomSize = Math.random() * 20 + 10; // between 10px and 30px
            dot.style.animationDuration = `${randomDuration}s`;
            dot.style.width = `${randomSize}px`;
            dot.style.height = `${randomSize}px`;
        }

        // Create multiple dots
        for (let i = 0; i < 30; i++) {
            createDot();
        }

    function openModal() {
        document.getElementById('announcementModal').style.display = 'flex';
    }
    
    function closeModal() {
        document.getElementById('announcementModal').style.display = 'none';
    }
    </script>
</body>
</html>
