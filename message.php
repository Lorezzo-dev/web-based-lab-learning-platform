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

$xmlFile = "xmlfile/messagelist.xml";


// Handle message sending
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["to"], $_POST["from"], $_POST["subject"], $_POST["message"])) {
    $to = trim($_POST["to"]);
    $from = trim($_POST["from"]);
    $subject = trim($_POST["subject"]);
    $message = trim($_POST["message"]);

    if (empty($to) || empty($from) || empty($subject) || empty($message)) {
        echo json_encode(["status" => "error", "message" => "All fields are required."]);
        exit();
    }

    // Create XML structure if the file does not exist
    if (!file_exists($xmlFile)) {
        $xmlContent = "<?xml version=\"1.0\"?>\n<messagelist>\n</messagelist>";
        file_put_contents($xmlFile, $xmlContent);
    }

    // Load the existing XML file
    $xml = simplexml_load_file($xmlFile);

    // Create new letter node
    $letter = $xml->addChild("letter");
    $letter->addChild("to", htmlspecialchars($to));
    $letter->addChild("from", htmlspecialchars($from));
    $letter->addChild("subject", htmlspecialchars($subject));
    $letter->addChild("message", htmlspecialchars($message));

    // Save the updated XML
    $xml->asXML($xmlFile);

    echo json_encode(["status" => "success", "message" => "Message saved successfully!"]);
    exit();
}

// Handle message deletion
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "delete") {
    if (!isset($_POST["subject"])) {
        echo json_encode(["status" => "error", "message" => "Subject is required for deletion."]);
        exit();
    }

    $subjectToDelete = trim($_POST["subject"]);

    if (!file_exists($xmlFile)) {
        echo json_encode(["status" => "error", "message" => "No messages found."]);
        exit();
    }

    // Load XML
    $xml = simplexml_load_file($xmlFile);
    $found = false;

    // Iterate over letters and remove the matching one
    for ($i = 0; $i < count($xml->letter); $i++) {
        if ((string) $xml->letter[$i]->subject === $subjectToDelete) {
            unset($xml->letter[$i]); // Remove the node
            $found = true;
            break;
        }
    }

    if ($found) {
        $xml->asXML($xmlFile); // Save updated XML
        echo json_encode(["status" => "success", "message" => "Message deleted successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Message not found."]);
    }
    exit();
}




?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message Center</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300&display=swap" rel="stylesheet">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Quicksand', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin-left: 300px;
            background-color: rgba(247, 238, 231, 0.47);
        }

        header {
            background-color: #F7EEE7;
            padding: 20px;
            text-align: center;
        }
        h2 {
            color: #284290;
        }
        #composeButton, #sendButton {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #ffc107;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        #composeButton:hover, #sendButton:hover {
            background-color: #ff9800;
        }

/* Popup Styling */
#editor-section {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    padding: 30px;
    width: 60%;
    max-width: 700px;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
    border-radius: 10px;
    z-index: 1000;
}

/* Header Styling */
.editor-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

/* Close Button Styling */
#closeButton {
    position: absolute;
    top: 15px;
    right: 15px;
    font-size: 24px;
    background: none;
    border: none;
    cursor: pointer;
    color: #555;
}

#closeButton:hover {
    color: red;
}

/* Larger Input Fields */
#to,
#from,
#subject {
    width: 100%;
    padding: 12px;
    font-size: 16px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

/* Larger Editor Container */
#editor-container {
    height: 400px !important;
    border: 1px solid #ccc;
    padding: 15px;
    background: #f9f9f9;
    border-radius: 5px;
}

/* Larger Send Button */
#sendButton {
    width: 100%;
    padding: 15px;
    font-size: 18px;
    background-color: #ffc107;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

#sendButton:hover {
    background-color: #ff9800;
}

#message-list {
    padding: 20px;
    background: white;
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    margin: 20px;
}

.message-panel {
    padding: 10px;
    border-bottom: 1px solid #ddd;
    margin-bottom: 10px;
    background: #f9f9f9;
    border-radius: 5px;
}

.message-panel p {
    margin: 5px 0;
}

.viewButton, .editButton, .deleteButton {
    margin-right: 5px;
    padding: 5px 10px;
    font-size: 14px;
    border: none;
    border-radius: 3px;
    cursor: pointer;
}

.viewButton { background-color: #2196F3; color: white; }
.editButton { background-color: #FFC107; color: white; }
.deleteButton { background-color: #F44336; color: white; }

.viewButton:hover { background-color: #1976D2; }
.editButton:hover { background-color: #FFA000; }
.deleteButton:hover { background-color: #D32F2F; }

.popup {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    padding: 30px; /* Increased padding */
    border-radius: 10px; /* Larger border radius for a smoother look */
    box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.3); /* Stronger shadow for depth */
    z-index: 1000;
    width: 600px; /* Increased width */
    max-height: 80vh; /* Ensure it doesn’t exceed screen height */
    overflow-y: auto; /* Enable scrolling if content is too long */
}

.popup-content {
    position: relative;
    font-size: 18px; /* Increase overall font size */
}

.close-btn {
    position: absolute;
    top: 15px; /* Adjusted position */
    right: 20px;
    font-size: 22px; /* Larger close button */
    cursor: pointer;
}

.formatted-message {
    border: 2px solid #ccc; /* Slightly thicker border */
    padding: 15px; /* More padding for readability */
    background: #f5f5f5; /* Softer background color */
    font-size: 17px; /* Larger message text */
    white-space: pre-wrap; /* Preserve line breaks */
    max-height: 300px; /* Limit message height */
    overflow-y: auto; /* Scroll if message is long */
}
    </style>
</head>
<body>
    <header>
        <h2>Message Center</h2>
        <p>Welcome,   <?php echo $username; ?> This is you inbox!</p>
    </header>
<div id="message-list">
    <h2>Inbox</h2>
    <?php
    $xmlFile = "xmlfile/messagelist.xml";

    if (file_exists($xmlFile)) {
        $xml = simplexml_load_file($xmlFile);
        $hasMessages = false;


        foreach ($xml->letter as $letter) {
        $to = (string) $letter->to;
        $from = (string) $letter->from;

        // Display only messages where the user is the sender or recipient
        if (stripos($to, $username) !== false || stripos($from, $username) !== false){
            $hasMessages = true;

            echo '<div class="message-panel">';
            echo '<p><strong>To:</strong> ' . $letter->to . '</p>';
            echo '<p><strong>From:</strong> ' . $letter->from . '</p>';
            echo '<p><strong>Subject:</strong> ' . $letter->subject . '</p>';
            $formattedMessage = htmlspecialchars($letter->message);
            echo '<button class="viewButton"
                data-to="' . htmlspecialchars($letter->to) . '"
                data-from="' . htmlspecialchars($letter->from) . '"
                data-subject="' . htmlspecialchars($letter->subject) . '"
                data-message="' . $formattedMessage . '">View</button>';
            echo '<button class="deleteButton" onclick="deleteMessage(\'' . addslashes($letter->subject) . '\')">Delete</button>';
            echo '</div>';
        }
    }

    if (!$hasMessages) {
        echo "<p>No messages found.</p>";
    }
} else {
    echo "<p>No messages found.</p>";
}
    ?>
</div>
    <button id="composeButton">Compose</button>

    <div id="editor-section">
        <h2>Compose Message</h2>
        <button id="closeButton">&times;</button>
        <label for="to">To:</label>
        <input type="text" id="to" name="to" style="width: 100%; margin-bottom: 10px;" autocomplete="off">
        <div id="userDropdown" style="display: none; position: absolute; background: white; border: 1px solid #ccc; max-height: 200px; overflow-y: auto; z-index: 1000;"></div>


        <label for="from">From:</label>
        <input type="text" id="from" name="from" value="<?php echo $username; ?>" readonly style="width: 100%; margin-bottom: 10px; background-color: #f0f0f0;">

        <label for="subject">Subject:</label>
        <input type="text" id="subject" name="subject" style="width: 100%; margin-bottom: 10px;">

        <div id="toolbar">
            <button class="ql-bold"></button>
            <button class="ql-italic"></button>
            <button class="ql-underline"></button>
            <button class="ql-list" value="ordered"></button>
            <button class="ql-list" value="bullet"></button>
            <select class="ql-header">
                <option selected></option>
                <option value="1"></option>
                <option value="2"></option>
            </select>
            <button class="ql-align" value=""></button>
            <button class="ql-align" value="center"></button>
            <button class="ql-align" value="right"></button>
            <button class="ql-align" value="justify"></button>
        </div>
        <div id="editor-container"></div>
        <button id="sendButton" style="margin-top: 10px;">Send</button>
    </div>

<div id="messagePopup" class="popup">
    <div class="popup-content">
        <span id="closePopup" class="close-btn">&times;</span>
        <h3>Message Details</h3>
        <p><strong>From:</strong> <span id="popup-from"></span></p>
        <p><strong>To:</strong> <span id="popup-to"></span></p>
        <p><strong>Subject:</strong> <span id="popup-subject"></span></p>
        <div id="popupMessage" class="formatted-message"></div>
    </div>
</div>

    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script>
        var quill = new Quill('#editor-container', {
            theme: 'snow',
            modules: {
                toolbar: '#toolbar'
            }
        });

document.getElementById("to").addEventListener("click", function () {
    fetch("fetch_users.php")
        .then(response => response.json())
        .then(data => {
            let dropdown = document.getElementById("userDropdown");
            dropdown.innerHTML = "";
            data.forEach(user => {
                let option = document.createElement("div");
                option.textContent = user.username;
                option.style.padding = "5px";
                option.style.cursor = "pointer";
                option.addEventListener("click", function () {
                    document.getElementById("to").value = user.username;
                    dropdown.style.display = "none";
                });
                dropdown.appendChild(option);
            });
            dropdown.style.display = "block";
        })
        .catch(error => console.error("Error fetching users:", error));
});

// Hide dropdown when clicking outside
document.addEventListener("click", function (event) {
    let dropdown = document.getElementById("userDropdown");
    if (!event.target.closest("#to") && !event.target.closest("#userDropdown")) {
        dropdown.style.display = "none";
    }
});

document.getElementById("sendButton").addEventListener("click", function () {
    let to = document.getElementById("to").value.trim();
    let from = document.getElementById("from").value.trim();
    let subject = document.getElementById("subject").value.trim();
    let message = document.querySelector(".ql-editor").innerHTML.trim(); // Gets formatted Quill content

    if (to === "" || subject === "" || message === "") {
        alert("Please fill in all fields.");
        return;
    }

    let formData = new FormData();
    formData.append("to", to);
    formData.append("from", from);
    formData.append("subject", subject);
    formData.append("message", message);

    console.log("Sending data:", { to, from, subject, message });

    fetch("message.php", {
        method: "POST",
        body: formData
    })
.then(response => response.text())
.then(data => {
    console.log("Server response:", data);
    if (data.includes("Message saved successfully")) {
        alert("Message Sent Successfully!"); // Display a clean success message
        location.reload(); // Refresh the page after success
    } else {
        alert("Error sending message. Please try again.");
    }
})
    .catch(error => console.error("Fetch error:", error));
});

document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".viewButton").forEach(button => {
        button.addEventListener("click", function () {
            // Retrieve data attributes
            let to = this.getAttribute("data-to");
            let from = this.getAttribute("data-from");
            let subject = this.getAttribute("data-subject");
            let message = this.getAttribute("data-message");

            // Populate the popup
            document.getElementById("popup-from").textContent = from;
            document.getElementById("popup-to").textContent = to;
            document.getElementById("popup-subject").textContent = subject;
            document.getElementById("popup-message").innerHTML = message; // Preserve HTML formatting

            // Show the popup
            document.getElementById("messagePopup").style.display = "block";
        });
    });

    // Close popup
    document.querySelector(".close-btn").addEventListener("click", function () {
        document.getElementById("messagePopup").style.display = "none";
    });
});

document.querySelectorAll(".editButton").forEach(button => {
    button.addEventListener("click", function () {
        let newSubject = prompt("Edit Subject:", this.parentElement.querySelector("p:nth-child(3)").textContent);
        if (newSubject) {
            fetch("edit_message.php", {
                method: "POST",
                body: JSON.stringify({ subject: this.dataset.id, newSubject }),
                headers: { "Content-Type": "application/json" }
            }).then(response => response.text()).then(data => alert(data));
        }
    });
});

function deleteMessage(subject) {
    if (confirm("Are you sure you want to delete this message?")) {
        let formData = new FormData();
        formData.append("action", "delete");
        formData.append("subject", subject);

        fetch("message.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            console.log("Server response:", data);  // Debugging
            alert(data.message);  // Show success or error message
            if (data.status === "success") {
                location.reload(); // Refresh if successful
            }
        })
        .catch(error => console.error("Error:", error));
    }
}

document.addEventListener("DOMContentLoaded", function () {
    let composeButton = document.getElementById("composeButton");
    let editorSection = document.getElementById("editor-section");
    let closeButton = document.getElementById("closeButton");

    // Initially hide the editor section
    editorSection.style.display = "none";

    // Show the editor section when Compose is clicked
    composeButton.addEventListener("click", function () {
        editorSection.style.display = "block";
    });

    // Hide the editor section when Close is clicked
    closeButton.addEventListener("click", function () {
        editorSection.style.display = "none";
    });

    // Close the popup if clicking outside the editor
    window.addEventListener("click", function (event) {
        if (event.target === editorSection) {
            editorSection.style.display = "none";
        }


    });
});

document.addEventListener("DOMContentLoaded", function () {
    const popup = document.getElementById("messagePopup");
    const popupContent = document.getElementById("popupMessage");
    const closeBtn = document.getElementById("closePopup");

    document.querySelectorAll(".viewButton").forEach(button => {
        button.addEventListener("click", function () {
            let message = this.getAttribute("data-message");
            popupContent.innerHTML = message; // Display message
            popup.style.display = "block"; // Show popup
        });
    });

    closeBtn.addEventListener("click", function () {
        popup.style.display = "none"; // Hide popup
    });

    // Close popup when clicking outside it
    window.addEventListener("click", function (event) {
        if (event.target === popup) {
            popup.style.display = "none";
        }
    });
});
    </script>

</body>
</html>
