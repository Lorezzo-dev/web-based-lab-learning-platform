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

    // Fetch user data
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT username, role FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows == 1) {
        $stmt->bind_result($username, $role);
        $stmt->fetch();
    } else {
        $username = "Unknown";
        $role = null;
    }

    // Fetch grade
    $sql = "SELECT module3_lab_grade FROM grades WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->store_result();
    $module1_lab_grade = $stmt->num_rows == 1 ? $stmt->fetch() : 0;

    // Mark module as completed
    $sql = "UPDATE progress SET module3_completed = 1 WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $stmt->close();
    $conn->close();
}

// Load XML file
$xml = simplexml_load_file("xmlfile/experiment3.xml") or die("Error: Cannot load experiment1.xml");

// Extract content
$title = $xml->title;
$description = $xml->description;
$subtopics = $xml->subtopics;
$video_src = $xml->video->source;
$video_description = $xml->video->description;
$quiz = $xml->quiz;
$video_is_on = (string) $xml->video->videoisOn;
$quiz_is_on = (string) $xml->quiz->quizIsOn;

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
        margin-left: 320px;
    }
    header {
        padding: 20px;
    }
    header h1 {
        margin: 0;
    }
    section {
        padding: 20px;
    }
    p {
        margin: 15px 0;
    }
    hr {
        border: none;
        height: 2px;
        background: #ddd;
    }
    footer {
        text-align: center;
        padding: 10px 0;
        background: #333;
        color: #fff;
    }
    .lab-btn {
        background: #F3E06A;
        color: #284290;
        padding: 15px 50px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        margin-top: 50px;
        margin-left: 1000px;
        transition: background 0.3s;
        font-size: 18px;
        font-weight: bold;
        bottom: 0;
    }

    .lab-btn:hover {
        background-color: Yellow;
    }
    .quiz-container {
        margin-top: 30px;
        text-align: left;
        padding: 20px;
        background: #fff;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .question {
        margin-bottom: 20px;
    }
    .option {
        margin: 5px 0;
    }
    .submit-btn {
        background-color: #4CAF50;
        color: white;
        padding: 15px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }
    .submit-btn:hover {
        background-color: #45a049;
    }
    .correct {
        color: lightgreen;
    }

    .incorrect {
        color: red;
    }

    .long-edit-btn {
        position: absolute;
        margin-left: 1500px;
        margin-top: 10px;
        background: #284290;
        color: #fff;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
        font-weight: bold;
        transition: background 0.3s;
    }

    .long-edit-btn:hover {
        background: #3a5ba0;
    }


    .popup-content {
        background-color: #fff;
        padding: 20px 40px;
        border-radius: 10px;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
    }

    .popup-content h1 {
        color: #284290;
        margin-bottom: 20px;
    }

    .popup-content p {
        font-size: 18px;
        margin: 10px 0;
    }

    .back-btn {
        background-color: #dc3545;
        color: white;
        padding: 10px 20px;
        font-size: 16px;
        cursor: pointer;
        border: none;
        border-radius: 5px;
        margin-top: 20px;
    }

    .back-btn:hover {
        background-color: #c82333;
    }

    .edit-popup {
    display: none; /* Hidden by default */
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.8); /* Black overlay */
    z-index: 1000; /* High z-index to overlay everything */
    justify-content: center;
    align-items: center;
  }
  .edit-popup .popup-content {
    background-color: #fff;
    width: 80%;
    height: 80%;
    border-radius: 10px;
    overflow: hidden; /* Ensure iframe stays within bounds */
    position: relative;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
  }
  .edit-popup .popup-content .close-btn {
    position: absolute;
    top: 10px;
    right: 15px;
    font-size: 30px;
    font-weight: bold;
    color: #333;
    cursor: pointer;
    z-index: 1001;
  }
  .edit-popup .popup-content iframe {
    width: 100%;
    height: 100%;
    border: none;
  }
    </style>
</head>
<body>

  <?php if ($role == 1 || $role == 2): ?>
        <button class="long-edit-btn" onclick="openEditPage()">Edit</button>
  <?php endif; ?>

    <header>
        <h1><?php echo $title; ?></h1>
    </header>

    <section>
        <p><?php echo $description; ?></p>
    </section>

    <hr>

    <section>
        <?php foreach ($subtopics->subtopic as $subtopic): ?>
            <h3><?php echo $subtopic->title; ?></h3>
            <?php foreach ($subtopic->paragraph as $paragraph): ?>
                <p><?php echo $paragraph; ?></p>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </section>

    <?php if ($video_is_on == "true"): ?>
        <hr>
        <section style="text-align: center; margin-top: 20px;">
            <h3>Watch the Video</h3>
            <iframe width="800" height="450" src="<?php echo $video_src; ?>" frameborder="0" allowfullscreen style="display: block; margin: 0 auto;"></iframe>
            <p style="margin-top: 10px;"><?php echo $video_description; ?></p>
        </section>
    <?php endif; ?>



    <!-- Quiz Section -->

      <hr>
      <div class="quiz-container" id="quizContainer" <?php echo ($quiz_is_on == "true") ? '' : 'style="display: none;"'; ?>>
            <h3>Quiz</h3>
            <form id="quizForm">
              <?php
              $quiz_data = []; // Initialize the array for quiz data

              $question_counter = 0; // Counter for unique question names
              foreach ($quiz->question as $question):
                  $question_counter++;
                  $unique_name = "q" . $question_counter; // Generate a unique name

                  // Collect data for correct answers
                  $quiz_data[] = [
                      'id' => $unique_name, // Use unique_name to match with form elements
                      'correct' => (string)$question->correct // Get the correct answer from the <correct> tag
                  ];
              ?>
                  <div class="question">
                      <p><strong><?php echo $question->text; ?></strong></p>
                      <?php foreach ($question->option as $option): ?>
                          <label class="option">
                              <input type="radio" name="<?php echo $unique_name; ?>" value="<?php echo htmlspecialchars($option['id']); ?>">
                              <?php echo htmlspecialchars($option); ?>
                          </label><br>
                      <?php endforeach; ?>
                  </div>
              <?php endforeach; ?>

                <button type="button" class="submit-btn" onclick="submitQuiz(event)">Submit Quiz</button>
            </form>
            <div id="quizResults"></div>
        </div>

    <hr><br><br><br>

    <?php if ($module3_lab_grade <= 0): ?>
        <a href="javascript:void(0);" class="lab-btn" onclick="proceedToLab()">Proceed to Lab</a>
    <?php endif; ?>


        <div id="editPopup" class="edit-popup">
            <div class="popup-content">
                <span class="close-btn" onclick="closeEditPopup()">&times;</span>
                <iframe id="editIframe" src="" frameborder="0"></iframe>
            </div>
        </div>

    <script>
        function proceedToLab() {
            window.location.href = "experiment3_lab.php";
        }

        var correctAnswers = <?php echo json_encode($quiz_data); ?>;

        function submitQuiz(event) {
            event.preventDefault();
            let form = document.getElementById('quizForm');
            let resultDiv = document.getElementById('quizResults');
            let score = 0;
            let totalQuestions = correctAnswers.length; // Automatically adapts to the number of questions

            // Clear previous results
            resultDiv.innerHTML = '';

            correctAnswers.forEach(function (question) {
                let questionName = question.id; // Match with radio button names
                let selected = form.querySelector(`input[name="${questionName}"]:checked`);

                // Reset styles for all options of this question
                let options = form.querySelectorAll(`input[name="${questionName}"]`);
                options.forEach(function (option) {
                    let label = option.parentElement;
                    label.classList.remove('correct', 'incorrect');
                });

                // Check selected answer
                if (selected) {
                    if (selected.value === question.correct) {
                        score++;
                        selected.parentElement.classList.add('correct'); // Highlight correct answer in green
                    } else {
                        selected.parentElement.classList.add('incorrect'); // Highlight incorrect answer in red
                    }
                }
            });

            // Calculate percentage
            let percentage = ((score / totalQuestions) * 100).toFixed(2);

            // Display final score with percentage
            resultDiv.innerHTML = `
                <p>You scored ${score} out of ${totalQuestions} (${percentage}%).</p>
            `;
        }

        function openEditPage() {
            const editPopup = document.getElementById('editPopup');
            const editIframe = document.getElementById('editIframe');
            const xmlFilePath = 'xmlfile/experiment3.xml'; // The path to the current XML file

            // Set the iframe source to load the editor
            editIframe.src = `lectureeditor.php?file=${encodeURIComponent(xmlFilePath)}`;

            // Show the popup
            editPopup.style.display = 'flex';
        }

        function closeEditPopup() {
            const editPopup = document.getElementById('editPopup');
            const editIframe = document.getElementById('editIframe');

            // Hide the popup and clear the iframe source
            editPopup.style.display = 'none';
            editIframe.src = '';
        }


    </script>
</body>
</html>
