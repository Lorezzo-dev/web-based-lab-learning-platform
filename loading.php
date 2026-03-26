<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/x-icon" href="assets/icon.png">
  <title>GenB Distance Learning</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      background-color: white;
      overflow: hidden;
      background-image: url('assets/hexbg.jpg'); /* Replace with the actual path to your background image */
      background-size: cover; /* Cover the entire viewport */
      background-repeat: no-repeat; /* Prevent tiling */
      background-position: center; /* Center the background image */
    }

    .loading-bar-container {
      position: fixed;
      top: 50%; /* Center vertically */
      left: 50%; /* Center horizontally */
      transform: translate(-50%, -50%); /* Adjust to center */
      width: 80%;
      height: 30px;
      background-color: #eee;
      border-radius: 5px;
      overflow: hidden;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
      z-index: 10; /* Ensure it appears above other elements */
    }

    .loading-bar {
      height: 100%;
      background-color: #B9F4D8;
      width: 0; /* Start with 0 width */
      transition: width 0.3s; /* Smooth transition */
    }

    .percentage {
      position: absolute;
      width: 100%;
      text-align: center;
      line-height: 30px; /* Center vertically */
      font-weight: bold;
      color: black; /* Change the percentage label color to black */
      z-index: 11; /* Ensure it appears above other elements */
    }

    .dot {
      position: absolute;
      z-index: 0; /* Keep dots behind the loading bar and label */
      animation: moveDiagonal infinite linear; /* Infinite diagonal animation */
    }

    @keyframes moveDiagonal {
      0% {
        transform: translate(0, 100vh);
      }
      100% {
        transform: translate(100vw, -100vh);
      }
    }
  </style>
</head>
<body>
  <div class="loading-bar-container">
    <div class="loading-bar" id="loadingBar"></div>
    <div class="percentage" id="percentageLabel">0%</div>
  </div>

  <script>
    // Path to the small image
    const imageSrc = 'assets/dna.png'; // Replace with the actual path to your image
    const loadingBar = document.getElementById('loadingBar');
    const percentageLabel = document.getElementById('percentageLabel');

    let loadingPercentage = 0;

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

      // Randomize animation duration for varied speed
      const duration = Math.random() * 5 + 3; // between 3 and 8 seconds
      img.style.animationDuration = `${duration}s`;

      // Append image to the body
      document.body.appendChild(img);
    }

    // Create dots randomly across the screen
    for (let i = 0; i < 100; i++) { // Increased to 100 for more dots
      createDot();
    }

    // Function to redirect based on user role after loading
    function redirectToHome() {
      const role = "<?php echo isset($_SESSION['role']) ? $_SESSION['role'] : 0; ?>"; // Get the role from session

      // Set the delay before redirecting
      setTimeout(() => {
        // Redirect based on role
        if (role == 1) {
          window.location.href = "advisory.php";
        } else if (role == 2) {
          window.location.href = "admin.php";
        } else {
          window.location.href = "home.php"; // Default redirection
        }
      }, 2000); // Delay of 2000 milliseconds (2 seconds)
    }

    // Function to update loading bar
    function updateLoadingBar() {
      loadingPercentage++;
      loadingBar.style.width = `${loadingPercentage}%`; // Use template literals correctly
      percentageLabel.textContent = `${loadingPercentage}%`; // Use template literals correctly

      // Stop updating at 100%
      if (loadingPercentage < 100) {
        requestAnimationFrame(updateLoadingBar);
      } else {
        redirectToHome(); // Redirect after loading is complete
      }
    }

    // Start updating the loading bar
    updateLoadingBar();
  </script>
</body>
</html>
