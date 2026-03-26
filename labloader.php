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
    // Close statement and connection
    $stmt->close();
    $conn->close();
}



// Load XML file
// Check if the 'file' query parameter is set
if (isset($_GET['file'])) {
    // Sanitize the file parameter to prevent directory traversal attacks
    $file = basename($_GET['file']);
    $baseDir = 'xmlfile/'; // Directory where XML files are stored
    $filePath = $baseDir . $file;

    // Check if the file exists and is readable
    if (file_exists($filePath) && is_readable($filePath)) {
        // Load the specified XML file
        $xml = simplexml_load_file($filePath) or die("Error: Cannot load the specified XML file.");
    } else {
        header("Location: home.php");
        die("Error: The requested file does not exist or is not accessible.");
        exit;

    }
} else {
    // Default file to load if no file parameter is provided
    $xml = simplexml_load_file("xmlfile/placeholderl.xml") or die("Error: Cannot load the default XML file.");
}
$title = (string) $xml->title;
$src = (string) $xml->src;


// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    // Delete XML file
    if (file_exists($filePath)) {
        unlink($filePath);
    }

    // Remove <llink> reference from cms.xml
    $cmsFile = 'xmlfile/cms.xml';
    if (file_exists($cmsFile)) {
        $dom = new DOMDocument();
        $dom->load($cmsFile);

        $xpath = new DOMXPath($dom);

        // Find <llink> with matching <address>
        foreach ($xpath->query("//llink[address[text()='$filePath']]") as $llinkNode) {
            $llinkNode->parentNode->removeChild($llinkNode); // Remove the <llink>
        }

        // Save changes
        $dom->save($cmsFile);
    }

    // Redirect after deletion
    header("Location: home.php");
    exit();
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
        width: 70%;             /* Adjust width as necessary */
        margin: auto;           /* Center the container itself horizontally */
        overflow: hidden;
        padding: 30px;
        background-color: #fff;
        border-radius: 20px;
        box-shadow: 0 0 20px #ccc;
        min-height: 80vh;      /* Optional: full height of viewport */
        position: relative;
        left: 80px
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

      .delete-btn {
          position: absolute;
          top: 10px;
          right: 10px;
          background-color: #dc3545;
          color: white;
          border: none;
          padding: 10px 15px;
          border-radius: 5px;
          cursor: pointer;
          font-size: 16px;
      }
      .delete-btn:hover {
          background-color: #c82333;
      }


    </style>
</head>
<body>
    <!-- Page content -->
    <div class="container" id="labContainer">
      <h2>LAB LOADER</h2>
      <h2 id="labTitle"><?php echo htmlspecialchars($title); ?></h2>
          <button class="lab-btn" id="openLabBtn">Open Lab</button>

          <!-- Delete Button -->
          <form method="POST" onsubmit="return confirmDelete();">
              <button type="submit" name="delete" class="delete-btn">Delete</button>
          </form>

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

        }



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

         const labSrc = "<?php echo $src; ?>";

         function isValidURL(url) {
             // Ensure the URL has the protocol
             if (!url.match(/^https?:\/\//i)) {
                 url = "http://" + url; // Add http:// if missing
             }
             try {
                 new URL(url);
                 return true;
             } catch (e) {
                 return false;
             }
         }

         document.getElementById('openLabBtn').addEventListener('click', () => {
             if (isValidURL(labSrc)) {
                 // Open the URL in a new tab or window
                 window.open(labSrc, "_blank");
             } else {
                 alert("Invalid URL. Cannot load lab content.");
             }
         });

         function confirmDelete() {
           return confirm("Are you sure you want to delete this file? This action cannot be undone.");
       }
    </script>
    <div id="overlay"></div>
</body>
</html>
