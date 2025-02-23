<?php
session_start();

// If "logout" parameter is set, end the session and refresh
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

$loggedIn = isset($_SESSION['userEmail']);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dynamic Track</title>
    <link rel="stylesheet" href="style.css" />

    <style>
      /* Simple dropdown styling for the profile icon */
      .profile-menu {
        position: relative;
        display: inline-block;
      }

      .profileDropdown {
        display: none;
        position: absolute;
        right: 0;
        background-color: #f1f1f1;
        min-width: 120px;
        box-shadow: 0px 8px 16px rgba(0,0,0,0.2);
        z-index: 1000;
      }

      .profileDropdown a {
        color: black;
        padding: 10px 16px;
        text-decoration: none;
        display: block;
      }

      .profileDropdown a:hover {
        background-color: #ddd;
      }

      .profileDropdown.show {
        display: block;
      }

      .profileLogo {
        cursor: pointer;
      }
    </style>
  </head>

  <body>
    <header class="top-bar">
      <div class="top-bar-left">
        <div class="Text1">dynamictrack</div>
      </div>
      <div class="top-bar-right">
        <!-- If logged in, show a dropdown when clicking the profile icon.
             Otherwise, link directly to login page. -->
        <?php if ($loggedIn): ?>
          <div class="profile-menu">
            <div class="profileLogo" onclick="toggleProfileMenu()"></div>
            <div id="profileDropdown" class="profileDropdown">
              <!-- Pass the email as GET param to edit page -->
              <a href="editprofile/editpage.php?email=<?php echo urlencode($_SESSION['userEmail']); ?>">Settings</a>
              <a href="?logout=1" onclick="return confirm('Are you sure you want to logout?');">Logout</a>
            </div>
          </div>
        <?php else: ?>
          <a href="login/login.php">
            <div class="profileLogo"></div>
          </a>
        <?php endif; ?>
      </div>
    </header>

    <nav class="navbar hide-navbar" id="navbar">
      <ul class="nav-list">
        <li class="nav-item"><a href="#section-home">Home</a></li>
        <li class="nav-item"><a href="#section-features">Features</a></li>
        <li class="nav-item"><a href="#section-qa">Q&A</a></li>
        <li class="nav-item2">
          <a href="ContactPage/contact.php" class="nav-item-contact">Contact Us</a>
        </li>
      </ul>
    </nav>

    <div class="halfpage" id="section-home">
      <div class="container2">
        <div class="text-button-wrapper">
          <div class="Text2">Track nutrition<br />effortlessy</div>
          <a style="text-decoration: none"
             href="<?php echo $loggedIn ? 'primarypage/primary.php' : 'login/login.php'; ?>">
            <button class="start-button">
              <?php echo $loggedIn ? 'TRACK YOUR DIET >' : 'START TODAY >'; ?>
            </button>
          </a>
        </div>
        <div class="PhoneImage"></div>
      </div>
    </div>

    <div class="black-section" id="section-motto">
      <div class="black-content">
        <div class="motto-text">
          If it's healthy and delicious, it's in here
        </div>
        <div class="sub-motto-text">
          Every rep is a step toward a stronger you
        </div>
      </div>
    </div>

    <div class="spacer"></div>

    <!-- FEATURES SECTION -->
    <section class="features-section" id="section-features">
      <div class="features-container">
        <div class="feature-item">
          <div class="feature-content">
            <div class="feature-number">1</div>
            <h2 class="feature-title">Set Your Fitness Targets</h2>
            <p class="feature-description">
              Define your goals—whether it’s building strength, increasing
              stamina, or losing weight—and we’ll help you create a personalized
              plan.
            </p>
          </div>
          <img
            src="Images/TrackingGoals.jpg"
            alt="Tracking feature"
            class="feature-image"
          />
        </div>

        <div class="feature-item">
          <div class="feature-content">
            <div class="feature-number">2</div>
            <h2 class="feature-title">Track Your Progress</h2>
            <p class="feature-description">
              Monitor workouts, steps, and overall activity with easy-to-use
              tools and real-time feedback to keep you motivated.
            </p>
          </div>
          <img
            src="Images/TrackingProgress.webp"
            alt="Learning feature"
            class="feature-image"
          />
        </div>

        <div class="feature-item">
          <div class="feature-content">
            <div class="feature-number">3</div>
            <h2 class="feature-title">Transform Your Lifestyle</h2>
            <p class="feature-description">
              Build lasting habits with expert guidance and resources designed
              to keep you moving and feeling your best every day.
            </p>
          </div>
          <img
            src="Images/image2.webp"
            alt="Habits feature"
            class="feature-image"
          />
        </div>
      </div>
    </section>
    <!-- QUOTE SECTION -->
    <section class="quote-section" id="section-quote">
      <div class="quote-content">
        <p class="quote-text">
          Strong body, strong mind—build a lifestyle that fuels your greatness
        </p>
        <p class="quote-author">
          Start today, every step counts toward a healthier you
        </p>
      </div>
    </section>
    <!-- Q&A SECTION -->
    <section class="qa-section" id="section-qa">
      <div class="qa-container">
        <h2 class="qa-title">Q&A</h2>

        <div class="qa-item">
          <button class="qa-question">
            What is Dynamictrack?
            <svg
              class="qa-arrow"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <path d="M19 9l-7 7-7-7"></path>
            </svg>
          </button>
          <div class="qa-answer">
            <p>
              Dynamictrack is a comprehensive website designed to help you track
              your calories, monitor your food intake, and manage various
              aspects of your nutrition and health. By signing up, you gain
              access to personalized tools and insights to support your wellness
              goals.
            </p>
          </div>
        </div>

        <div class="qa-item">
          <button class="qa-question">
            How does Dynamictrack help with calorie tracking?
            <svg
              class="qa-arrow"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <path d="M19 9l-7 7-7-7"></path>
            </svg>
          </button>
          <div class="qa-answer">
            <p>
              Dynamictrack provides an intuitive calorie tracker where you can
              log your meals and snacks. The platform calculates your daily
              intake, compares it to your goals, and offers insights to help you
              stay on track with your nutritional needs.
            </p>
          </div>
        </div>

        <div class="qa-item">
          <button class="qa-question">
            Is Dynamictrack free to use?
            <svg
              class="qa-arrow"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <path d="M19 9l-7 7-7-7"></path>
            </svg>
          </button>
          <div class="qa-answer">
            <p>
              Dynamictrack offers a free version with essential calorie tracking
              and health monitoring features. Additional premium tools and
              services are available through a subscription plan.
            </p>
          </div>
        </div>

        <div class="qa-item">
          <button class="qa-question">
            Can I track my macros with Dynamictrack?
            <svg
              class="qa-arrow"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <path d="M19 9l-7 7-7-7"></path>
            </svg>
          </button>
          <div class="qa-answer">
            <p>
              Yes! Dynamictrack includes tools to track your macronutrients—
              proteins, carbohydrates, and fats—allowing you to better
              understand your diet and achieve your fitness or wellness goals.
            </p>
          </div>
        </div>

        <div class="qa-item">
          <button class="qa-question">
            Does Dynamictrack provide meal suggestions?
            <svg
              class="qa-arrow"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <path d="M19 9l-7 7-7-7"></path>
            </svg>
          </button>
          <div class="qa-answer">
            <p>
              Yes, Dynamictrack offers meal suggestions based on your dietary
              preferences, calorie goals, and health requirements, making it
              easier to plan balanced and nutritious meals.
            </p>
          </div>
        </div>

        <div class="qa-item">
          <button class="qa-question">
            Can I set weight loss or gain goals on Dynamictrack?
            <svg
              class="qa-arrow"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <path d="M19 9l-7 7-7-7"></path>
            </svg>
          </button>
          <div class="qa-answer">
            <p>
              Absolutely! Dynamictrack allows you to set weight loss,
              maintenance, or gain goals. It provides personalized
              recommendations to help you reach your targets efficiently and
              sustainably.
            </p>
          </div>
        </div>

        <div class="qa-item">
          <button class="qa-question">
            Does Dynamictrack sync with fitness devices or apps?
            <svg
              class="qa-arrow"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <path d="M19 9l-7 7-7-7"></path>
            </svg>
          </button>
          <div class="qa-answer">
            <p>
              Yes, Dynamictrack integrates with popular fitness devices and
              apps, allowing you to seamlessly track your activity, calories
              burned, and overall progress in one place.
            </p>
          </div>
        </div>
      </div>
    </section>
    <!-- FOOTER -->
    <footer class="main-footer">
      <div class="footer-top">
        <div class="footer-left">
          <div class="footer-logo">Dynamic Track</div>
          <p class="footer-text">Find your healthy, and your happy.</p>
          <a style="text-decoration: none"
             href="<?php echo $loggedIn ? 'primarypage/primary.php' : 'login/login.php'; ?>">
            <button class="start-button">
              <?php echo $loggedIn ? 'TRACK YOUR DIET >' : 'START TODAY >'; ?>
            </button>
          </a>
        </div>
      </div>
      <div class="footer-bottom">
        <p>
          ©2025 Dynamic Track, Inc. Community Guidelines &nbsp; Feedback &nbsp;
          Terms &nbsp; Privacy &nbsp; API &nbsp; Cookie Preferences
        </p>
        <div class="social-icons">
        </div>
      </div>
    </footer>
    <!-- Link to your external JS (put at the bottom) -->
    <script src="script.js"></script>

    <script>
      function toggleProfileMenu() {
        document.getElementById('profileDropdown').classList.toggle('show');
      }
      // Close the dropdown if clicked outside
      window.onclick = function(event) {
        if (!event.target.matches('.profileLogo')) {
          var dropdowns = document.getElementsByClassName("profileDropdown");
          for (var i = 0; i < dropdowns.length; i++) {
            var openDropdown = dropdowns[i];
            if (openDropdown.classList.contains('show')) {
              openDropdown.classList.remove('show');
            }
          }
        }
      }
    </script>
  </body>
</html>
