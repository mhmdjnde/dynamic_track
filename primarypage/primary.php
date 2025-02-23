<?php
session_start();

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// 1) Check user is logged in
if (!isset($_SESSION['userEmail'])) {
    header("Location: ../login.php");
    exit;
}
$userEmail = $_SESSION['userEmail'];

// 2) Connect to database
$servername  = "localhost";
$db_username = "jnde";
$db_password = "jnde1777";
$dbname      = "dynamictrackbackend";

try {
    $conn = new mysqli($servername, $db_username, $db_password, $dbname);
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}

//3) If request is POST with JSON, handle meal state update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST['addFoodSubmit'])) {
    // Attempt to read JSON from the request body
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data !== null) {
        // Store totals in session
        $_SESSION['totalCalsConsumed'] = $data['totalCalsConsumed'] ?? 0;
        $_SESSION['totalCarbs']       = $data['totalCarbs']       ?? 0;
        $_SESSION['totalProtein']     = $data['totalProtein']     ?? 0;
        $_SESSION['totalFat']         = $data['totalFat']         ?? 0;
        $_SESSION['totalSugar']       = $data['totalSugar']       ?? 0;
        $_SESSION['totalWaterIntake'] = $data['totalWaterIntake'] ?? 0;

        // Grab the total water from the JSON
        $waterTotal = floatval($data['totalWaterIntake'] ?? 0);

        // 1) Delete existing rows for this user
        $delStmt = $conn->prepare("DELETE FROM user_meals WHERE userEmail = ?");
        $delStmt->bind_param("s", $userEmail);
        $delStmt->execute();
        $delStmt->close();

        // 2) Re-insert all meal entries
        $mealTypes = ['breakfast', 'lunch', 'dinner', 'snack'];
        foreach ($mealTypes as $mt) {
            if (!empty($data['mealEntries'][$mt])) {
                foreach ($data['mealEntries'][$mt] as $item) {
                    $foodName = $item['name'];
                    $grams    = floatval($item['grams']);
                    $cal      = floatval($item['calories']);
                    $fat      = floatval($item['fat']);
                    $carbs    = floatval($item['carbs']);
                    $protein  = floatval($item['protein']);
                    $sugar    = floatval($item['sugar']);

                    $insStmt = $conn->prepare("
                        INSERT INTO user_meals
                        (userEmail, mealType, foodName, grams, calories, fat, carbs, protein, sugar, water)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0)
                    ");
                    $insStmt->bind_param(
                        'sssdddddd',
                        $userEmail,
                        $mt,
                        $foodName,
                        $grams,
                        $cal,
                        $fat,
                        $carbs,
                        $protein,
                        $sugar
                    );
                    $insStmt->execute();
                    $insStmt->close();
                }
            }
        }

        // 3) Also insert one row to store total water
        //    (Set mealType = 'WATER' or any label you like)
        $waterMeal = 'WATER';
        $insW = $conn->prepare("
            INSERT INTO user_meals
            (userEmail, mealType, foodName, grams, calories, fat, carbs, protein, sugar, water)
            VALUES (?, ?, 'Water', 0, 0, 0, 0, 0, 0, ?)
        ");
        $insW->bind_param('ssd', $userEmail, $waterMeal, $waterTotal);
        $insW->execute();
        $insW->close();

        echo json_encode(['status' => 'success', 'data' => $data]);
        $conn->close();
        exit;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No data provided']);
        $conn->close();
        exit;
    }
}

/**
 * 4) Process "Add New Food" form submission (for the fooddata table)
 */
$successMessage = "";
$errorMessage   = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addFoodSubmit'])) {
    $foodName = trim($_POST['foodName'] ?? '');
    $calories = floatval($_POST['calories'] ?? 0);
    $fat      = floatval($_POST['fat']      ?? 0);
    $carbs    = floatval($_POST['carbs']    ?? 0);
    $protein  = floatval($_POST['protein']  ?? 0);
    $sugar    = floatval($_POST['sugar']    ?? 0);

    // Basic validation
    if (!empty($foodName) && $calories >= 0 && $fat >= 0 && $carbs >= 0 && $protein >= 0 && $sugar >= 0) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO fooddata (FoodName, Calories, Fat, Carbs, Protein, Sugar)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("sddddd", $foodName, $calories, $fat, $carbs, $protein, $sugar);
            $stmt->execute();
            $stmt->close();

            // Redirect (Post/Redirect/Get) to avoid re-submitting on refresh
            header("Location: primary.php?successMsg=" . urlencode("Food '$foodName' was added!"));
            $conn->close();
            exit;
        } catch (mysqli_sql_exception $ex) {
            if ($ex->getCode() == 1062) {
                $errorMessage = "That food name already exists. Please use a different name.";
            } else {
                $errorMessage = "Database error: " . $ex->getMessage();
            }
        }
    } else {
        $errorMessage = "Invalid input. Please check your entries.";
    }
}

// If redirected here, fetch any successMsg from the URL
if (isset($_GET['successMsg'])) {
    $successMessage = $_GET['successMsg'];
}

/**
 * 5) Fetch user's nutrition data (daily goal, macros, etc.)
 */
$sql1 = "SELECT * FROM nutrirequirements WHERE Email = ?";
$stmt1 = $conn->prepare($sql1);
$stmt1->bind_param("s", $userEmail);
$stmt1->execute();
$result1 = $stmt1->get_result();

$userWeight     = 0;
$userHeight     = 0;
$userActivity   = "Sedentary";
$userWeightGoal = "Maintain Weight";
$userDietGoal   = "Modify my diet";

if ($result1 && $result1->num_rows > 0) {
    $row = $result1->fetch_assoc();
    $userWeight     = (float)$row['Weight'];
    $userHeight     = (float)$row['Height'];
    $userActivity   = $row['Activity'];
    $userWeightGoal = $row['WeightGoal'];
    $userDietGoal   = $row['DietGoal'];
}
$stmt1->close();

/**
 * 6) Fetch user's basic info (age, gender) from userinfo
 */
$sql2 = "SELECT * FROM userinfo WHERE Email = ?";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("s", $userEmail);
$stmt2->execute();
$result2 = $stmt2->get_result();

$age    = 25;
$gender = "male";

if ($result2 && $result2->num_rows > 0) {
    $row    = $result2->fetch_assoc();
    $age    = (int)$row['Age'];
    $gender = strtolower($row['gender']);
}
$stmt2->close();

/**
 * 7) Calculate daily calorie goal (Mifflin-St Jeor + activity factor)
 */
$bmr = ($gender === "male")
    ? (10 * $userWeight + 6.25 * $userHeight - 5 * $age + 5)
    : (10 * $userWeight + 6.25 * $userHeight - 5 * $age - 161);

$activityFactor = 1.2;
switch ($userActivity) {
    case "Lightly active":    $activityFactor = 1.375; break;
    case "Moderately active": $activityFactor = 1.55;  break;
    case "Very active":       $activityFactor = 1.725; break;
    case "Extra active":      $activityFactor = 1.9;   break;
    // default: "Sedentary" => 1.2
}

$tdee = $bmr * $activityFactor;

if ($userWeightGoal === "Gain Weight") {
    $tdee += 500;
} elseif ($userWeightGoal === "Lose Weight") {
    $tdee -= 500;
}

$dailyGoal = max(1200, round($tdee));

// Basic macro targets (example)
$macroCarbs   = round(($dailyGoal * 0.50) / 4);
$macroProtein = round(($dailyGoal * 0.25) / 4);
$macroFat     = round(($dailyGoal * 0.25) / 9);
$macroSugar   = round(($dailyGoal * 0.10) / 4);

/**
 * 8) Fetch full food database (fooddata table)
 */
$sqlFoods = "SELECT FoodName, Calories, Fat, Carbs, Protein, Sugar FROM fooddata";
$resultFoods = $conn->query($sqlFoods);

$foods = [];
if ($resultFoods && $resultFoods->num_rows > 0) {
    while ($row = $resultFoods->fetch_assoc()) {
        $foods[] = [
            "name"     => $row["FoodName"],
            "calories" => (float)$row["Calories"],
            "fat"      => (float)$row["Fat"],
            "carbs"    => (float)$row["Carbs"],
            "protein"  => (float)$row["Protein"],
            "sugar"    => (float)$row["Sugar"],
        ];
    }
}

/**
 * 9) Fetch existing meal entries from user_meals for this user
 */
$mealEntries = [
    'breakfast' => [],
    'lunch'     => [],
    'dinner'    => [],
    'snack'     => [],
];

$waterIntakeFromDB = 0;

$sqlMeal = "SELECT mealType, foodName, grams, calories, fat, carbs, protein, sugar
            FROM user_meals
            WHERE userEmail = ?";
$stmtMeal = $conn->prepare($sqlMeal);
$stmtMeal->bind_param("s", $userEmail);
$stmtMeal->execute();
$resultMeal = $stmtMeal->get_result();
while ($rowMeal = $resultMeal->fetch_assoc()) {
    $mt = $rowMeal['mealType'];
    $mealEntries[$mt][] = [
        'name'     => $rowMeal['foodName'],
        'grams'    => (float)$rowMeal['grams'],
        'calories' => (float)$rowMeal['calories'],
        'fat'      => (float)$rowMeal['fat'],
        'carbs'    => (float)$rowMeal['carbs'],
        'protein'  => (float)$rowMeal['protein'],
        'sugar'    => (float)$rowMeal['sugar'],
    ];
}
$stmtMeal->close();

// Close DB connection
$conn->close();

/**
 * 10) Now pass data to the front-end
 */
$totalCalsConsumed = $_SESSION['totalCalsConsumed'] ?? 0;
$totalCarbs        = $_SESSION['totalCarbs']       ?? 0;
$totalProtein      = $_SESSION['totalProtein']     ?? 0;
$totalFat          = $_SESSION['totalFat']         ?? 0;
$totalSugar        = $_SESSION['totalSugar']       ?? 0;
$totalWaterIntake  = $_SESSION['totalWaterIntake'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>DynamicTrack</title>
  <link rel="stylesheet" href="Primary.css" />
</head>
<body>
  <!-- ====== NAVIGATION BAR ====== -->
  <nav class="navbar">
    <div class="nav-brand">
      <div style="display: flex">
        <img src="../apple_logo.png" style="height: 60px; margin-top: -4.5%" />
        <h1 class="site-title">DynamicTrack</h1>
      </div>
    </div>
    <button class="hamburger">☰</button>
    <ul class="nav-links">
    <li><a href="../index.php">Home</a></li>
    <li><a href="#" id="foodLink">Food</a></li>
      <li><a href="../ExercisePage/index.html">Workouts</a></li>
      <li><a href="CommingSoonPage.html" class="premium-btn">Premium Planning</a></li>
      <li><a href="../ContactPage/contact.php">Contact Us</a></li>
    </ul>
  </nav>

  <!-- ====== MESSAGES (success / error) ====== -->
  <?php if (!empty($successMessage)): ?>
    <div class="message success"><?php echo htmlspecialchars($successMessage); ?></div>
  <?php endif; ?>
  <?php if (!empty($errorMessage)): ?>
    <div class="message error"><?php echo htmlspecialchars($errorMessage); ?></div>
  <?php endif; ?>

  <!-- ====== QUANTITY MODAL ====== -->
  <div class="quantity-modal" id="quantityModal">
    <div class="quantity-modal-content">
      <div class="quantity-modal-header">
        <h3>Enter Quantity</h3>
        <span class="close-btn" id="closeQuantityModal">&times;</span>
      </div>
      <div class="quantity-modal-body">
        <p>Please specify how many grams of this item you’ve eaten:</p>
        <input type="number" id="quantityInput" placeholder="Enter grams consumed" min="1" step="1" />
        <button id="confirmQuantityBtn">Confirm</button>
      </div>
    </div>
  </div>

  <div class="container">
    <!-- ====== CALORIE/WATER TRACKER BOX ====== -->
    <div class="tracker-box">
      <!-- Top: "Left" heading & leftover number -->
      <div class="calorie-header">
        <h2>Left</h2>
        <p class="budget-number"></p>
      </div>

      <div class="tracker-content">
        <!-- Left Side Stats (macros) -->
        <div class="side-stats">
          <div class="stat-item">
            <span class="stat-label">Carbs</span>
            <span class="stat-number">0 of ???g</span>
          </div>
          <div class="stat-item">
            <span class="stat-label">protein</span>
            <span class="stat-number">0 of ???g</span>
          </div>
          <div class="stat-item">
            <span class="stat-label">Fat</span>
            <span class="stat-number">0 of ???g</span>
          </div>
          <div class="stat-item">
            <span class="stat-label">Sugar</span>
            <span class="stat-number">0 of ???g</span>
          </div>
        </div>

        <!-- Center Circle Visualization -->
        <div class="apple-visual">
          <div class="apple-circle">
            <div class="apple-content">
              <span class="calories-left">0%</span>
            </div>
          </div>
        </div>

        <!-- Right Side Stats (meal totals) -->
        <div class="side-stats">
          <div class="stat-item">
            <span class="stat-label">Breakfast</span>
            <span class="stat-number">0</span>
          </div>
          <div class="stat-item">
            <span class="stat-label">Lunch</span>
            <span class="stat-number">0</span>
          </div>
          <div class="stat-item">
            <span class="stat-label">Dinner</span>
            <span class="stat-number">0</span>
          </div>
          <div class="stat-item">
            <span class="stat-label">Snacks</span>
            <span class="stat-number">0</span>
          </div>
        </div>
      </div>

      <!-- Water Tracker -->
      <div class="water-tracker">
        <div class="water-emoji">💧</div>
        <div class="water-progress-bar">
          <div class="water-progress-fill"></div>
        </div>
        <div class="water-info">
          <span id="currentWaterText">0L</span>
          <span class="water-goal"> / 4L goal</span>
        </div>
        <div class="water-input">
          <input type="number" id="waterInput" placeholder="Add water (mL)" min="0"/>
          <button id="addWaterBtn">Add</button>
        </div>
      </div>
    </div>

    <!-- ====== 7-DAY CALORIE SUMMARY ====== -->
    <div class="summary-container">
      <h2>This Week's Calorie Summary</h2>
      <div class="summary-days" id="summaryDays">
        <!-- Populated by JS -->
      </div>
    </div>
  </div>

  <!-- ====== 4 MEAL BOXES ====== -->
  <div class="meal-windows-container">
    <div class="meal-box" data-meal="breakfast">
      <div class="meal-header">
        <span class="meal-title">Breakfast</span>
        <span class="meal-total-cals">0 kcal</span>
        <button class="add-food-btn">Add Food</button>
      </div>
      <div class="meal-entries"></div>
    </div>

    <div class="meal-box" data-meal="lunch">
      <div class="meal-header">
        <span class="meal-title">Lunch</span>
        <span class="meal-total-cals">0 kcal</span>
        <button class="add-food-btn">Add Food</button>
      </div>
      <div class="meal-entries"></div>
    </div>

    <div class="meal-box" data-meal="dinner">
      <div class="meal-header">
        <span class="meal-title">Dinner</span>
        <span class="meal-total-cals">0 kcal</span>
        <button class="add-food-btn">Add Food</button>
      </div>
      <div class="meal-entries"></div>
    </div>

    <div class="meal-box" data-meal="snack">
      <div class="meal-header">
        <span class="meal-title">Snack</span>
        <span class="meal-total-cals">0 kcal</span>
        <button class="add-food-btn">Add Food</button>
      </div>
      <div class="meal-entries"></div>
    </div>
  </div>

  <!-- ====== SEARCH POPUP (MODAL) ====== -->
  <div class="search-modal" id="searchModal">
    <div class="search-modal-content">
      <div class="search-modal-header">
        <h3>Search Food</h3>
        <span class="close-btn" id="closeSearchModal">&times;</span>
      </div>
      <div class="search-modal-body">
        <p>Type at least one letter to find matching foods:</p>
        <input type="text" id="foodSearchInput" placeholder="e.g. Apple..." />
        <div class="search-results" id="searchResults"></div>

        <!-- Button to open the custom-food modal -->
        <div style="text-align: center; margin-top: 20px;">
          <button id="openAddFoodModal">Add Custom Food to DB</button>
        </div>
      </div>
    </div>
  </div>

  <!-- ====== CUSTOM FOOD (ADD NEW FOOD) MODAL ====== -->
  <div id="customFoodModal">
    <div class="custom-modal-content">
      <div class="custom-modal-header">
        <h3>Add a New Food (values per 100g)</h3>
        <span class="close-btn" id="closeCustomFoodModal">&times;</span>
      </div>
      <div class="custom-modal-body">
        <form method="POST" action="">
          <label for="foodName">Food Name:</label>
          <input
            type="text"
            id="foodName"
            name="foodName"
            placeholder="e.g. Homemade Soup"
            required
          />

          <label for="calories">Calories (per 100g):</label>
          <input
            type="number"
            step="1"
            min="0"
            id="calories"
            name="calories"
            required
          />

          <label for="fat">Fat (g / 100g):</label>
          <input
            type="number"
            step="0.1"
            min="0"
            id="fat"
            name="fat"
            required
          />

          <label for="carbs">Carbs (g / 100g):</label>
          <input
            type="number"
            step="0.1"
            min="0"
            id="carbs"
            name="carbs"
            required
          />

          <label for="protein">Protein (g / 100g):</label>
          <input
            type="number"
            step="0.1"
            min="0"
            id="protein"
            name="protein"
            required
          />

          <label for="sugar">Sugar (g / 100g):</label>
          <input
            type="number"
            step="0.1"
            min="0"
            id="sugar"
            name="sugar"
            required
          />

          <button type="submit" name="addFoodSubmit" value="1">Add Food</button>
        </form>
      </div>
    </div>
  </div>

  <!-- ====== PASS PHP DATA INTO JS ====== -->
  <script>
    // Some global config from the server
    var dailyGoal     = <?php echo $dailyGoal; ?>;
    var macroCarbs    = <?php echo $macroCarbs; ?>;
    var macroProtein  = <?php echo $macroProtein; ?>;
    var macroFat      = <?php echo $macroFat; ?>;
    var macroSugar    = <?php echo $macroSugar; ?>;
    // Pass user email to JS so we can use it for user-specific cookie
    var userEmail     = "<?php echo urlencode($userEmail); ?>";

    // The entire foods database from your DB
    var foodsDatabase = <?php echo json_encode($foods, JSON_PRETTY_PRINT); ?>;

    // The saved meal-entries from DB (plus water/macros from session if you want)
    var savedState = {
      totalCalsConsumed: <?php echo json_encode($totalCalsConsumed); ?>,
      totalCarbs:        <?php echo json_encode($totalCarbs);        ?>,
      totalProtein:      <?php echo json_encode($totalProtein);      ?>,
      totalFat:          <?php echo json_encode($totalFat);          ?>,
      totalSugar:        <?php echo json_encode($totalSugar);        ?>,
      totalWaterIntake:  <?php echo json_encode($totalWaterIntake);  ?>,
      mealEntries:       <?php echo json_encode($mealEntries);       ?>
    };
  </script>

  <!-- ====== Your main JS code (external file) ====== -->
  <script src="https://cdn.jsdelivr.net/npm/js-cookie@3.0.1/dist/js.cookie.min.js"></script>
  <script src="javaScript.js"></script>

  <!-- ====== Additional JS for controlling Add-Food Modal ====== -->
  <script>
    const openAddFoodBtn = document.getElementById("openAddFoodModal");
    const customFoodModal = document.getElementById("customFoodModal");
    const closeCustomFoodModalBtn = document.getElementById("closeCustomFoodModal");

    openAddFoodBtn.addEventListener("click", () => {
      customFoodModal.style.display = "block";
    });

    closeCustomFoodModalBtn.addEventListener("click", () => {
      customFoodModal.style.display = "none";
    });

    window.addEventListener("click", (e) => {
      if (e.target === customFoodModal) {
        customFoodModal.style.display = "none";
      }
    });
  </script>
</body>
</html>
