<?php

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$current_page = basename($_SERVER['PHP_SELF']);

// Database connection parameters
include "connection.php";

$conn = new mysqli($servername, $db_username, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Fetch user progress
$sql = "SELECT module1quiz_completed, module2quiz_completed, module3quiz_completed FROM progress WHERE user_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Preparation failed: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($module1quiz_completed, $module2quiz_completed, $module3quiz_completed);
$stmt->fetch();
$stmt->close();

// Fetch user role
$sql = "SELECT role FROM users WHERE user_id = ?"; // Adjust to your actual column name
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Preparation failed: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($role);
$stmt->fetch();
$stmt->close();

// Fetch the user's module1_lab_grade
$sql = "SELECT module1_lab_grade, module1_quiz_grade, module2_lab_grade, module2_quiz_grade, module3_lab_grade, module3_quiz_grade, module4_lab_grade, module4_quiz_grade, quarterly_quiz_grade FROM grades WHERE user_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Preparation failed: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($module1_lab_grade, $module1_quiz_grade, $module2_lab_grade, $module2_quiz_grade, $module3_lab_grade, $module3_quiz_grade, $module4_lab_grade, $module4_quiz_grade, $quarterly_quiz_grade);
$stmt->fetch();
$stmt->close();

$conn->close();

// Load the XML file
$xmlFileS = 'xmlfile/cms.xml';
if (!file_exists($xmlFileS)) {
    die('CMS XML file not found.');
}

$cmsXml = simplexml_load_file($xmlFileS);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GenB Distance Learning</title>
    <link rel="icon" type="image/x-icon" href="assets/icon.png">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">
    <style>
    @keyframes slideIn {
        from {
            left: -300px; /* Adjust according to your sidebar width */
            opacity: 0; /* Start fully transparent */
        }
        to {
            left: 0;
            opacity: 1; /* End fully visible */
        }
    }

    .sidebar {
        width: 300px;
        background-color: white;
        color: #818181;
        padding-top: 60px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: fixed;
        left: -300px; /* Start off-screen */
        top: 0;
        bottom: 0;
        opacity: 0; /* Start fully transparent */
        animation: slideIn 0.5s ease forwards; /* Apply animation */
        overflow-y: auto; /* Enable vertical scrolling */
        max-height: 100vh; /* Limit the height to viewport height */
    }

    .sidebar-content {
        padding-bottom: 16px; /* Adjust as needed */
        overflow-y: auto; /* Enable vertical scrolling inside sidebar content */
        max-height: calc(100vh - 60px); /* Adjust based on header height */
    }

    .sidebar a {
        display: block;
        padding: 15px; /* Increase padding */
        text-decoration: none;
        color: #333;
        border-radius: 5px;
        margin-bottom: 5px;
        font-size: 18px; /* Increase font size */
        font-family: 'Quicksand', sans-serif;
        font-weight: 700;
    }

    .sidebar a.active, .sidebar a:hover {
        background-color: #B9F4D8;
        color: #284290;
    }

    .sidebar a:hover {
        color: #B9F4D80;
        color: #284290;
    }

    .dropdown-content {
        display: none; /* Initially hide dropdown content */
    }

        .dropdown-btn.select {
            background-color: #B9F4D8; /* Dark yellow background color */
            color: #284290;
        }
        .dropdown-btn.select:hover {
            background-color: #B9F4D8; /* Darker shade on hover */
            color: #284290;
        }
        .sign-out-btn {
            padding: 15px; /* Increase padding */
            color: white;
            text-decoration: none;
            font-size: 18px; /* Increase font size */
            font-family: 'Quicksand', sans-serif;
            font-weight: 700;
        }
        .sign-out-btn:hover {
            text-decoration: underline;
        }
        .toggle-btn {
            position: absolute;
            top: 10px;
            left: 10px;
            cursor: pointer;
            z-index: 999;
        }

        .toggle-btn i {
            color: #000; /* Default hamburger color */
            font-size: 20px; /* Adjust icon size */
        }

        /* Additional style for the outside toggle button */
        .toggle-btn.outside {
            display: none; /* Hidden when sidebar is open */
        }
        .dummy-link {
            color: #999;
            background-color: #ddd;
            cursor: not-allowed;
            position: relative; /* Ensure relative positioning */
            font-family: 'Quicksand', sans-serif;
            font-weight: 700;
        }
        .lock-symbol {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            color: #999;
            font-size: 16px;
        }

        .sidebar span {
            display: block;
            padding: 15px; /* Increase padding */
            text-decoration: none;
            color: #333;
            border-radius: 5px;
            margin-bottom: 5px;
            font-size: 18px; /* Increase font size */
            font-family: 'Quicksand', sans-serif;
            font-weight: 700;
        }

        .header {
            display: flex;
            align-items: center;
            margin-bottom: 15px; /* Adjust space below the header */
            margin-left: 3px; /* Move header 3 pixels to the right */
        }

        .icon-circle {
            width: 50px; /* Adjusted size for a fatter circle */
            height: 50px; /* Adjusted size for a fatter circle */
            border-radius: 50%;
            background-color: #FFD700; /* Yellow color */
            display: flex;
            justify-content: center;
            align-items: center;
            margin-right: 10px; /* Space between circle and title */
        }

        .icon-circle img {
            width: 30px; /* Adjust icon size */
            height: 30px; /* Adjust icon size */
        }

        .title {
            font-family: 'Bebas Neue', sans-serif; /* Using the Bebas Neue font */
            font-size: 24px; /* Adjust title font size */
            color: #333; /* Title color */
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

      /* Centered Popup */
      .edit-popupS {
        display: none; /* Hidden by default */
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: auto;
        height: auto;
        z-index: 1000; /* High z-index to overlay everything */
      }

      /* Popup Content */
      .edit-popupS .popup-contentS {
        background-color: #fff;
        width: 800px; /* Set desired width */
        height: 500px; /* Set desired height */
        border-radius: 10px;
        position: relative;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3); /* Softer shadow */
        overflow: hidden; /* Ensure iframe stays within bounds */
        padding: 20px;
      }

      /* Close Button */
      .edit-popupS .popup-contentS .close-btnS {
        position: absolute;
        top: 10px;
        right: 15px;
        font-size: 24px; /* Smaller size */
        font-weight: bold;
        color: #333;
        cursor: pointer;
        z-index: 1001;
      }

      /* Iframe Adjustments */
      .edit-popupS .popup-contentS iframe {
        width: 100%;
        height: 100%;
        border: none;
      }


    </style>
</head>
<body>
<div class="sidebar" id="sidebar">
  <!--<div class="toggle-btn" onclick="toggleSidebar()">
      <i class="fas fa-bars fa-2x"></i>
  </div>-->


    <div class="sidebar-content">

      <div class="header">
          <div class="icon-circle">
              <img src="assets/icon.png" alt="Icon">
          </div>
          <div class="title">GENB DISTANCE LEARNING</div>
      </div><br><br><br><br>



      <?php
      if ($role == 0) {
          echo '<a href="profile.php" class="' . ($current_page == 'profile.php' ? 'active' : '') . '">Dashboard</a>';
      } elseif ($role == 1) {
          echo '<a href="advisory.php" class="' . ($current_page == 'advisory.php' ? 'active' : '') . '">Advisor Dashboard</a>';
      } elseif ($role == 2) {
          echo '<a href="admin.php" class="' . ($current_page == 'admin.php' ? 'active' : '') . '">Admin Dashboard</a>';
      }
      ?>


        <hr>
        <!-- Home Link -->
        <a href="home.php" class="<?php echo ($current_page == 'home.php') ? 'active' : ''; ?>"><img src="assets/home.png" alt="Dashboard Icon" style="width: 20px; height: 20px; margin-right: 5px;">Home</a>
        <hr>

        <?php
        if (isset($role) && ($role == 1 || $role == 2)) : ?>
        <a class="dropdown-btn" href="javascript:void(0)" onclick="toggleDropdown(this.parentElement)"><img src="assets/Plus.png" alt="Plus Icon" style="width: 20px; height: 20px; margin-right: 5px;">Manage Content</a>
        <div class="dropdown-content" style="display: none;">
            <a onclick="openModulesPage()" style="cursor: pointer;"><img src="assets/Minus.png" alt="Minus Icon" style="width: 10px; height: 10px; margin-right: 5px;">Manage Modules</a>
            <a onclick="openCreatePage()" style="cursor: pointer;"><img src="assets/Minus.png" alt="Minus Icon" style="width: 10px; height: 10px; margin-right: 5px;">Add Topic</a>
            <a onclick="openQuizPage()" style="cursor: pointer;"><img src="assets/Minus.png" alt="Minus Icon" style="width: 10px; height: 10px; margin-right: 5px;">Add Quiz</a>
          <!--  <a onclick="openLabPage()" style="cursor: pointer;"><img src="assets/Minus.png" alt="Minus Icon" style="width: 10px; height: 10px; margin-right: 5px;">Add Lab</a>-->
        </div>
        <hr>
        <?php endif; ?>

        <!-- Module Dropdown for all Modules -->
        <div class="dropdown">
            <a class="dropdown-btn <?php echo ($current_page == 'experiment1.php' || $current_page == 'experiment1_lab.php' || $current_page == 'experiment1_quiz.php' || $current_page == 'experiment2.php' || $current_page == 'experiment2_lab.php' || $current_page == 'experiment2_quiz.php' || $current_page == 'experiment3.php' || $current_page == 'experiment3_lab.php' || $current_page == 'experiment3_quiz.php' || $current_page == 'experiment4.php' || $current_page == 'experiment4_lab.php' || $current_page == 'experiment4_quiz.php' || $current_page == 'topicloader.php') ? 'active' : ''; ?>" href="javascript:void(0)" onclick="toggleModulesDropdown()"><img src="assets/modulesicon.png" alt="Modules Icon" style="width: 20px; height: 20px; margin-right: 5px;"> Modules</a>
            <div class="dropdown-content" id="modulesDropdownContent" style="display: none;">

              <!-- Module 1 Dropdown -->
              <div class="dropdown">
                  <a class="dropdown-btn <?php echo ($current_page == 'experiment1.php' || $current_page == 'experiment1_lab.php' || $current_page == 'experiment1_quiz.php') ? 'active' : ''; ?>" href="javascript:void(0)" onclick="toggleDropdown(this.parentElement)">Module 1</a>
                  <div class="dropdown-content" style="display: none;">
                    <a class="<?php echo ($current_page == 'experiment1.php') ? 'active' : ''; ?>" href="experiment1.php">&nbsp;&nbsp; Topic: Organismal Biolgy</a>

                   <?php
                   if (isset($cmsXml->Module1->link)) {
                       foreach ($cmsXml->Module1->link as $link) {
                           $linkTitle = (string)$link->title;
                           $linkAddress = (string)$link->address;

                           // Check if the current page matches this link
                           $current_file = isset($_GET['file']) ? $_GET['file'] : '';
                           $is_active = ($current_file === $linkAddress) ? 'active' : '';

                           // Generate a new link with "Topic: " prefix
                           echo '<a href="topicloader.php?file=' . urlencode($linkAddress) . '" class="' . $is_active . '">&nbsp;&nbsp; Subtopic: ' . htmlspecialchars($linkTitle) . '</a>';
                       }
                   }

                   if (isset($cmsXml->Module1->qlink)) {
                       foreach ($cmsXml->Module1->qlink as $qlink) {
                           $qlinkTitle = (string)$qlink->title;
                           $qlinkAddress = (string)$qlink->address;

                           // Check if the current page matches this link
                           $current_file = isset($_GET['file']) ? $_GET['file'] : '';
                           $is_active = ($current_file === $qlinkAddress) ? 'active' : '';

                           // Generate a new link with "Topic: " prefix
                           echo '<a href="quizloader.php?file=' . urlencode($qlinkAddress) . '" class="' . $is_active . '">&nbsp;&nbsp; Quiz: ' . htmlspecialchars($qlinkTitle) . '</a>';
                       }
                   }

                   if (isset($cmsXml->Module1->llink)) {
                       foreach ($cmsXml->Module1->llink as $llink) {
                           $llinkTitle = (string)$llink->title;
                           $llinkAddress = (string)$llink->address;

                           // Check if the current page matches this link
                           $current_file = isset($_GET['file']) ? $_GET['file'] : '';
                           $is_active = ($current_file === $llinkAddress) ? 'active' : '';

                           // Generate a new link with "Topic: " prefix
                           echo '<a href="labloader.php?file=' . urlencode($llinkAddress) . '" class="' . $is_active . '">&nbsp;&nbsp; Lab: ' . htmlspecialchars($llinkTitle) . '</a>';
                       }
                   }
                    ?>

                      <?php
                      // Check if the lab should be locked
                      if ($module1_lab_grade > 0 && !($role == 1 || $role == 2)) {
                          // Lab locked: display a dummy link with a lock symbol
                          echo '<a class="dummy-link">Lab Locked <span class="lock-symbol">&#128274;</span></a>';
                      } else {
                          // Lab accessible: show the lab link
                          echo '<a href="experiment1_lab.php" class="' . ($current_page == 'experiment1_lab.php' ? 'active' : '') . '">&nbsp;&nbsp; Module 1 Lab</a>';
                      }
                      ?>

                      <?php
                      // Check if the quiz should be locked
                      if ($module1_quiz_grade > 0 && !($role == 1 || $role == 2)) {
                          // Quiz locked: display a dummy link with a lock symbol
                          echo '<a class="dummy-link">&nbsp;&nbsp;Quiz Locked <span class="lock-symbol">&#128274;</span></a>';
                      } else {
                          // Quiz accessible: show the quiz link
                          echo '<a href="experiment1_quiz.php" class="' . ($current_page == 'experiment1_quiz.php' ? 'active' : '') . '">&nbsp;&nbsp; Module 1 Quiz</a>';
                      }
                      ?>
                  </div>
              </div>

                <!-- Module 2 Dropdown -->
                <div class="dropdown">
                    <?php if ($module1quiz_completed): ?>
                        <a class="dropdown-btn <?php echo ($current_page == 'experiment2.php' || $current_page == 'experiment2_lab.php' || $current_page == 'experiment2_quiz.php') ? 'active' : ''; ?>" href="javascript:void(0)" onclick="toggleDropdown(this.parentElement)">Module 2</a>
                        <div class="dropdown-content" style="display: none;">
                          <a class="<?php echo ($current_page == 'experiment2.php') ? 'active' : ''; ?>" href="experiment2.php">&nbsp;&nbsp; Topic: Genetics</a>

                          <?php
                          if (isset($cmsXml->Module2->link)) {
                              foreach ($cmsXml->Module2->link as $link) {
                                  $linkTitle = (string)$link->title;
                                  $linkAddress = (string)$link->address;

                                  // Check if the current page matches this link
                                  $current_file = isset($_GET['file']) ? $_GET['file'] : '';
                                  $is_active = ($current_file === $linkAddress) ? 'active' : '';

                                  // Generate a new link with "Topic: " prefix
                                echo '<a href="topicloader.php?file=' . urlencode($linkAddress) . '" class="' . $is_active . '">&nbsp;&nbsp; Subtopic: ' . htmlspecialchars($linkTitle) . '</a>';
                              }
                          }

                          if (isset($cmsXml->Module2->qlink)) {
                              foreach ($cmsXml->Module2->qlink as $qlink) {
                                  $qlinkTitle = (string)$qlink->title;
                                  $qlinkAddress = (string)$qlink->address;

                                  // Check if the current page matches this link
                                  $current_file = isset($_GET['file']) ? $_GET['file'] : '';
                                  $is_active = ($current_file === $qlinkAddress) ? 'active' : '';

                                  // Generate a new link with "Topic: " prefix
                                  echo '<a href="quizloader.php?file=' . urlencode($qlinkAddress) . '" class="' . $is_active . '">&nbsp;&nbsp; Quiz: ' . htmlspecialchars($qlinkTitle) . '</a>';
                              }
                          }

                          if (isset($cmsXml->Module2->llink)) {
                              foreach ($cmsXml->Module2->llink as $llink) {
                                  $llinkTitle = (string)$llink->title;
                                  $llinkAddress = (string)$llink->address;

                                  // Check if the current page matches this link
                                  $current_file = isset($_GET['file']) ? $_GET['file'] : '';
                                  $is_active = ($current_file === $llinkAddress) ? 'active' : '';

                                  // Generate a new link with "Topic: " prefix
                                  echo '<a href="labloader.php?file=' . urlencode($llinkAddress) . '" class="' . $is_active . '">&nbsp;&nbsp; Lab: ' . htmlspecialchars($llinkTitle) . '</a>';
                              }
                          }
                           ?>

                          <?php
                          // Check if the lab should be locked
                          if ($module2_lab_grade > 0 && !($role == 1 || $role == 2)) {
                              // Lab locked: display a dummy link with a lock symbol
                              echo '<a class="dummy-link">&nbsp;&nbsp; Lab Locked <span class="lock-symbol">&#128274;</span></a>';
                          } else {
                              // Lab accessible: show the lab link
                              echo '<a href="experiment2_lab.php" class="' . ($current_page == 'experiment2_lab.php' ? 'active' : '') . '">&nbsp;&nbsp; Module 2 Lab</a>';
                          }
                          ?>
                          <?php
                          // Check if the quiz should be locked
                          if ($module2_quiz_grade > 0 && !($role == 1 || $role == 2)) {
                              // Quiz locked: display a dummy link with a lock symbol
                              echo '<a class="dummy-link">&nbsp;&nbsp; Quiz Locked <span class="lock-symbol">&#128274;</span></a>';
                          } else {
                              // Quiz accessible: show the quiz link
                              echo '<a href="experiment2_quiz.php" class="' . ($current_page == 'experiment2_quiz.php' ? 'active' : '') . '">&nbsp;&nbsp; Module 2 Quiz</a>';
                          }
                          ?>
                        </div>
                    <?php else: ?>
                        <a class="dropdown-btn dummy-link">Module 2: Genetics <span class="lock-symbol">&#128274;</span></a>
                    <?php endif; ?>
                </div>

                <!-- Module 3 Dropdown -->
                <div class="dropdown">
                  <?php if ($module2quiz_completed): ?>
                    <a class="dropdown-btn <?php echo ($current_page == 'experiment3.php' || $current_page == 'experiment3_lab.php' || $current_page == 'experiment3_quiz.php') ? 'active' : ''; ?>" href="javascript:void(0)" onclick="toggleDropdown(this.parentElement)">Module 3</a>
                    <div class="dropdown-content" style="display: none;">
                      <a class="<?php echo ($current_page == 'experiment3.php') ? 'active' : ''; ?>" href="experiment3.php">&nbsp;&nbsp; Topic: Evolution and Origin of Biodiversity</a>

                      <?php
                      if (isset($cmsXml->Module3->link)) {
                          foreach ($cmsXml->Module3->link as $link) {
                              $linkTitle = (string)$link->title;
                              $linkAddress = (string)$link->address;

                              // Check if the current page matches this link
                              $current_file = isset($_GET['file']) ? $_GET['file'] : '';
                              $is_active = ($current_file === $linkAddress) ? 'active' : '';

                              // Generate a new link with "Topic: " prefix
                            echo '<a href="topicloader.php?file=' . urlencode($linkAddress) . '" class="' . $is_active . '">&nbsp;&nbsp; Subtopic: ' . htmlspecialchars($linkTitle) . '</a>';
                          }
                      }
                      if (isset($cmsXml->Module3->qlink)) {
                          foreach ($cmsXml->Module3->qlink as $qlink) {
                              $qlinkTitle = (string)$qlink->title;
                              $qlinkAddress = (string)$qlink->address;

                              // Check if the current page matches this link
                              $current_file = isset($_GET['file']) ? $_GET['file'] : '';
                              $is_active = ($current_file === $qlinkAddress) ? 'active' : '';

                              // Generate a new link with "Topic: " prefix
                              echo '<a href="quizloader.php?file=' . urlencode($qlinkAddress) . '" class="' . $is_active . '">&nbsp;&nbsp; Quiz: ' . htmlspecialchars($qlinkTitle) . '</a>';
                          }
                      }

                      if (isset($cmsXml->Module3->llink)) {
                          foreach ($cmsXml->Module3->llink as $llink) {
                              $llinkTitle = (string)$llink->title;
                              $llinkAddress = (string)$llink->address;

                              // Check if the current page matches this link
                              $current_file = isset($_GET['file']) ? $_GET['file'] : '';
                              $is_active = ($current_file === $llinkAddress) ? 'active' : '';

                              // Generate a new link with "Topic: " prefix
                              echo '<a href="labloader.php?file=' . urlencode($llinkAddress) . '" class="' . $is_active . '">&nbsp;&nbsp; Lab: ' . htmlspecialchars($llinkTitle) . '</a>';
                          }
                      }
                       ?>

                      <?php
                      // Check if the lab should be locked
                      if ($module3_lab_grade > 0 && !($role == 1 || $role == 2)) {
                          // Lab locked: display a dummy link with a lock symbol
                          echo '<a class="dummy-link">&nbsp;&nbsp; Lab Locked <span class="lock-symbol">&#128274;</span></a>';
                      } else {
                          // Lab accessible: show the lab link
                          echo '<a href="experiment3_lab.php" class="' . ($current_page == 'experiment3_lab.php' ? 'active' : '') . '">&nbsp;&nbsp; Module 3 Lab</a>';
                      }
                      ?>

                      <?php
                      // Check if the quiz should be locked
                      if ($module3_quiz_grade > 0 && !($role == 1 || $role == 2)) {
                          // Quiz locked: display a dummy link with a lock symbol
                          echo '<a class="dummy-link">&nbsp;&nbsp; Quiz Locked <span class="lock-symbol">&#128274;</span></a>';
                      } else {
                          // Quiz accessible: show the quiz link
                          echo '<a href="experiment3_quiz.php" class="' . ($current_page == 'experiment3_quiz.php' ? 'active' : '') . '">&nbsp;&nbsp; Module 3 Quiz</a>';
                      }
                      ?>
                    </div>
                  <?php else: ?>
                      <a class="dropdown-btn dummy-link">Module 3: Evolution and Origin of Biodiversity <span class="lock-symbol">&#128274;</span></a>
                  <?php endif; ?>
                </div>

                <!-- Module 4 Dropdown -->
                <div class="dropdown">
                  <?php if ($module3quiz_completed): ?>
                    <a class="dropdown-btn <?php echo ($current_page == 'experiment4.php' || $current_page == 'experiment4_lab.php' || $current_page == 'experiment4_quiz.php') ? 'active' : ''; ?>" href="javascript:void(0)" onclick="toggleDropdown(this.parentElement)">Module 4</a>
                    <div class="dropdown-content" style="display: none;">
                      <a class="<?php echo ($current_page == 'experiment4.php') ? 'active' : ''; ?>" href="experiment4.php">&nbsp;&nbsp; Topic: Systematics based on Evolutionary Relationships</a>

                      <?php
                      if (isset($cmsXml->Module4->link)) {
                          foreach ($cmsXml->Module4->link as $link) {
                              $linkTitle = (string)$link->title;
                              $linkAddress = (string)$link->address;

                              // Check if the current page matches this link
                              $current_file = isset($_GET['file']) ? $_GET['file'] : '';
                              $is_active = ($current_file === $linkAddress) ? 'active' : '';

                              // Generate a new link with "Topic: " prefix
                              echo '<a href="topicloader.php?file=' . urlencode($linkAddress) . '" class="' . $is_active . '">&nbsp;&nbsp; Subtopic: ' . htmlspecialchars($linkTitle) . '</a>';
                          }
                      }

                      if (isset($cmsXml->Module4->qlink)) {
                          foreach ($cmsXml->Module4->qlink as $qlink) {
                              $qlinkTitle = (string)$qlink->title;
                              $qlinkAddress = (string)$qlink->address;

                              // Check if the current page matches this link
                              $current_file = isset($_GET['file']) ? $_GET['file'] : '';
                              $is_active = ($current_file === $qlinkAddress) ? 'active' : '';

                              // Generate a new link with "Topic: " prefix
                              echo '<a href="quizloader.php?file=' . urlencode($qlinkAddress) . '" class="' . $is_active . '">&nbsp;&nbsp; Quiz: ' . htmlspecialchars($qlinkTitle) . '</a>';
                          }
                      }

                      if (isset($cmsXml->Module4->llink)) {
                          foreach ($cmsXml->Module4->llink as $llink) {
                              $llinkTitle = (string)$llink->title;
                              $llinkAddress = (string)$llink->address;

                              // Check if the current page matches this link
                              $current_file = isset($_GET['file']) ? $_GET['file'] : '';
                              $is_active = ($current_file === $llinkAddress) ? 'active' : '';

                              // Generate a new link with "Topic: " prefix
                              echo '<a href="labloader.php?file=' . urlencode($llinkAddress) . '" class="' . $is_active . '">&nbsp;&nbsp; Lab: ' . htmlspecialchars($llinkTitle) . '</a>';
                          }
                      }
                       ?>

                      <?php
                      // Check if the lab should be locked
                      if ($module4_lab_grade > 0 && !($role == 1 || $role == 2)) {
                          // Lab locked: display a dummy link with a lock symbol
                          echo '<a class="dummy-link">&nbsp;&nbsp; Lab Locked <span class="lock-symbol">&#128274;</span></a>';
                      } else {
                          // Lab accessible: show the lab link
                          echo '<a href="experiment4_lab.php" class="' . ($current_page == 'experiment4_lab.php' ? 'active' : '') . '">&nbsp;&nbsp; Module 4 Lab</a>';
                      }
                      ?>

                      <?php
                      // Check if the quiz should be locked
                      if ($module4_quiz_grade > 0 && !($role == 1 || $role == 2)) {
                          // Quiz locked: display a dummy link with a lock symbol
                          echo '<a class="dummy-link">&nbsp;&nbsp; Quiz Locked <span class="lock-symbol">&#128274;</span></a>';
                      } else {
                          // Quiz accessible: show the quiz link
                          echo '<a href="experiment4_quiz.php" class="' . ($current_page == 'experiment4_quiz.php' ? 'active' : '') . '">&nbsp;&nbsp; Module 4 Quiz</a>';
                      }
                      ?>
                    </div>
                  <?php else: ?>
                      <a class="dropdown-btn dummy-link">Module 4: Systematics based on Evolutionary Relationships <span class="lock-symbol">&#128274;</span></a>
                  <?php endif; ?>
                </div>

                <!-- ADDITIONAL MODULES -->
                <?php
                // Loop through each module in the XML
                foreach ($cmsXml->children() as $module) {
                    $moduleName = $module->getName(); // Get the module's tag name

                    // Check if the module name is Module5 or higher
                    if (preg_match('/^Module[5-9][0-9]*$/', $moduleName)) {
                        // Separate "Module" from the number (e.g., Module5 -> Module 5)
                        $displayName = preg_replace('/(Module)(\d+)/', '$1 $2', $moduleName);

                        // Check if the module is the currently active one
                        $isActive = ($current_page == strtolower($moduleName) . '.php') ? 'active' : '';

                        // Render the dropdown structure
                        echo '<div class="dropdown">';
                        echo '<a class="dropdown-btn ' . $isActive . '" href="javascript:void(0)" onclick="toggleDropdown(this.parentElement)">';
                        echo htmlspecialchars($displayName); // Display with space between "Module" and number
                        echo '</a>';
                        echo '<div class="dropdown-content" style="display: none;">';

                        // Check if the module contains links
                        if (isset($module->link)) {
                            foreach ($module->link as $link) {
                                $linkTitle = (string)$link->title;
                                $linkAddress = (string)$link->address;

                                // Check if the current page matches this link
                                $current_file = isset($_GET['file']) ? $_GET['file'] : '';
                                $is_active = ($current_file === $linkAddress) ? 'active' : '';

                                // Generate a new link with "Topic: " prefix
                                echo '<a href="topicloader.php?file=' . urlencode($linkAddress) . '" class="' . $is_active . '">';
                                echo '&nbsp;&nbsp; Topic: ' . htmlspecialchars($linkTitle); // Escape for safety
                                echo '</a>';
                            }
                        }

                        if (isset($module->qlink)) {
                            foreach ($module->qlink as $qlink) {
                                $qlinkTitle = (string)$qlink->title;
                                $qlinkAddress = (string)$qlink->address;

                                // Check if the current page matches this link
                                $current_file = isset($_GET['file']) ? $_GET['file'] : '';
                                $is_active = ($current_file === $qlinkAddress) ? 'active' : '';

                                // Generate a new link with "Quiz: " prefix
                                echo '<a href="quizloader.php?file=' . urlencode($qlinkAddress) . '" class="' . $is_active . '">';
                                echo '&nbsp;&nbsp; Quiz: ' . htmlspecialchars($qlinkTitle); // Escape for safety
                                echo '</a>';
                            }
                        }

                        if (isset($module->llink)) {
                            foreach ($module->llink as $llink) {
                                $llinkTitle = (string)$llink->title;
                                $llinkAddress = (string)$llink->address;

                                // Check if the current page matches this link
                                $current_file = isset($_GET['file']) ? $_GET['file'] : '';
                                $is_active = ($current_file === $llinkAddress) ? 'active' : '';

                                // Generate a new link with "Quiz: " prefix
                                echo '<a href="labloader.php?file=' . urlencode($llinkAddress) . '" class="' . $is_active . '">';
                                echo '&nbsp;&nbsp; Lab: ' . htmlspecialchars($llinkTitle); // Escape for safety
                                echo '</a>';
                            }
                        }

                        echo '</div>'; // Close dropdown-content
                        echo '</div>'; // Close dropdown
              }
          }

                ?>


                <!-- Quarterly Assessment Link -->
                <?php if ($quarterly_quiz_grade > 0 && !($role == 1 || $role == 2)): ?>
                    <a class="dummy-link">Quarterly Assessment Locked <span class="lock-symbol">&#128274;</span></a>
                <?php else: ?>
                    <a href="quarterly.php" class="<?php echo ($current_page == 'quarterly.php') ? 'active' : ''; ?>">Quarterly Assessment</a>
                <?php endif; ?>
            </div>
        </div>


        <hr>
            <a href="course.php" class="<?php echo ($current_page == 'course.php') ? 'active' : ''; ?>"><img src="assets/courseinfo.png" alt="CourseInfo Icon" style="width: 20px; height: 20px; margin-right: 5px;">Course Info</a>

        <hr>
        <a href="message.php" class="<?php echo ($current_page == 'message.php') ? 'active' : ''; ?>"><img src="assets/email.png" alt="email Icon" style="width: 30px; height: 25px; margin-right: 5px;">Messages</a>
        <hr>
    </div>
    <!-- Quarterly Assessment Link -->

    <a href="signout.php" class="sign-out-btn"><img src="assets/logout.png" alt="logout Icon" style="width: 30px; height: 30px; margin-right: 5px;">Log Out</a>
</div>

<div id="editPopup" class="edit-popup">
    <div class="popup-content">
        <span class="close-btn" onclick="closePopup()">&times;</span>
        <iframe id="editIframe" src="" frameborder="0"></iframe>
    </div>
</div>

<div id="editPopupS" class="edit-popupS">
    <div class="popup-contentS">
        <span class="close-btnS" onclick="closePopupS()">&times;</span>
        <iframe id="editIframeS" src="" frameborder="0"></iframe>
    </div>
</div>


<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Close all dropdowns initially
        var dropdowns = document.querySelectorAll('.dropdown-content');
        dropdowns.forEach(function(dropdown) {
            dropdown.style.display = 'none';
        });
    });



function toggleDropdown(dropdown) {
  var content = dropdown.querySelector('.dropdown-content');
  if (content.style.display === 'block') {
      content.style.display = 'none';
  } else {
      content.style.display = 'block';
  }
}

function toggleModulesDropdown() {
    var modulesDropdownContent = document.getElementById('modulesDropdownContent');
    if (modulesDropdownContent.style.display === 'block') {
        modulesDropdownContent.style.display = 'none';
    } else {
        modulesDropdownContent.style.display = 'block';
    }
}

function openCreatePage() {
    const editPopup = document.getElementById('editPopup');
    const editIframe = document.getElementById('editIframe');

    // Set the iframe source to load the editor
    editIframe.src = `lecturecreator.php`;

    // Show the popup
    editPopup.style.display = 'flex';
}

function openModulesPage() {
    const editPopup = document.getElementById('editPopupS');
    const editIframe = document.getElementById('editIframeS');

    // Set the iframe source to load the editor
    editIframe.src = `managemodules.php`;

    // Show the popup
    editPopup.style.display = 'flex';
}

function openQuizPage() {
    const editPopup = document.getElementById('editPopup');
    const editIframe = document.getElementById('editIframe');

    // Set the iframe source to load the editor
    editIframe.src = `quizcreator.php`;

    // Show the popup
    editPopup.style.display = 'flex';
}

function openLabPage() {
    const editPopup = document.getElementById('editPopupS');
    const editIframe = document.getElementById('editIframeS');

    // Set the iframe source to load the editor
    editIframe.src = `labcreator.php`;

    // Show the popup
    editPopup.style.display = 'flex';
}


function closePopup() {
    const editPopup = document.getElementById('editPopup');
    const editIframe = document.getElementById('editIframe');

    // Hide the popup and clear the iframe source
    editPopup.style.display = 'none';
    editIframe.src = '';
}

function closePopupS() {
    const editPopup = document.getElementById('editPopupS');
    const editIframe = document.getElementById('editIframeS');

    // Hide the popup and clear the iframe source
    editPopup.style.display = 'none';
    editIframe.src = '';
}
</script>
</body>
</html>
