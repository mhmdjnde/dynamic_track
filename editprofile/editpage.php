<?php
session_start();

$email = "";
if (isset($_GET['email']) && !empty($_GET['email'])) {
    $email = $_GET['email'];
} elseif (isset($_SESSION['userEmail'])) {
    $email = $_SESSION['userEmail'];
}

if (empty($email)) {
    die("No email provided. Please log in first.");
}

$dbname = "dynamictrackbackend";
$server = "localhost";
$username = "jnde";
$password = "jnde1777";

$conn = new mysqli($server, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$query = $conn->prepare("
    SELECT * 
    FROM userinfo u
    JOIN nutrirequirements n ON u.Email = n.Email
    JOIN usersecurity us ON u.Email = us.Email
    WHERE u.Email = ?
");
$query->bind_param("s", $email);
$query->execute();
$userinfo = $query->get_result();
$query->close();

$row = $userinfo->fetch_assoc();
if (!$row) {
    die("User with email '$email' not found in the database.");
}

$fname      = $row["FName"];
$lname      = $row["LName"];
$weight     = $row["Weight"];
$height     = $row["Height"];
$gender     = $row["gender"];
$pnum       = $row["PhoneNumber"];
$age        = $row["Age"];
$dbPassword = $row["Password"];

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["submit"])) {

    if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email address format.";
    }

    $currentPasswordInput = isset($_POST["currentPassword"]) ? $_POST["currentPassword"] : "";
    if ($currentPasswordInput !== $dbPassword) {
        $errors[] = "WRONG CURRENT PASSWORD";
    }

    $newPassword      = isset($_POST["password"])        ? $_POST["password"]        : "";
    $confirmPassword  = isset($_POST["confirmPassword"]) ? $_POST["confirmPassword"] : "";

    if (!empty($newPassword)) {
        if (
            strlen($newPassword) < 8 ||
            !preg_match('/[A-Z]/', $newPassword) ||
            !preg_match('/\d/', $newPassword) ||
            !preg_match('/\W/', $newPassword)
        ) {
            $errors[] = "New password must be at least 8 chars long and contain at least 1 uppercase letter, 1 number, and 1 symbol.";
        }

        if ($newPassword !== $confirmPassword) {
            $errors[] = "New Password and Confirm Password do not match.";
        }
    }

    if (empty($errors)) {
        $updateQuery = $conn->prepare("
            UPDATE userinfo 
            SET FName = ?, LName = ?, gender = ?, Age = ?, PhoneNumber = ?
            WHERE Email = ?
        ");
        $updateQuery->bind_param(
            "sssiss",
            $_POST["fname"],
            $_POST["lname"],
            $_POST["gender"],
            $_POST["age"],
            $_POST["phone"],
            $email
        );
        $updateQuery->execute();
        $updateQuery->close();

        $updateNutriQuery = $conn->prepare("
            UPDATE nutrirequirements 
            SET Weight = ?, Height = ? 
            WHERE Email = ?
        ");
        $updateNutriQuery->bind_param("dds", $_POST["weight"], $_POST["height"], $email);
        $updateNutriQuery->execute();
        $updateNutriQuery->close();

        if (!empty($newPassword)) {
            $updatePasswordQuery = $conn->prepare("
                UPDATE usersecurity 
                SET Password = ? 
                WHERE Email = ?
            ");
            $updatePasswordQuery->bind_param("ss", $newPassword, $email);
            $updatePasswordQuery->execute();
            $updatePasswordQuery->close();
        }

        echo json_encode(["success" => true, "message" => "Profile updated successfully!"]);
        exit;
    } else {
        echo json_encode(["success" => false, "errors" => $errors]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Edit Profile - FitLife</title>
  <link
    href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600&family=Roboto:wght@300;400;500&display=swap"
    rel="stylesheet"
  />
  <link href="style.css" rel="stylesheet" />
</head>
<body>
  <nav class="navbar">
    <a href="#" class="logo">DynamicTrack</a>
    <div class="menu-toggle" onclick="toggleMenu()">
      <span></span>
      <span></span>
      <span></span>
    </div>
    <div class="nav-links">
      <a href="../index.php">Home</a>
      <a href="../ExercisePage/index.html">Workouts</a>
      <a href="../primarypage/Primary.php">Nutrition</a>
      <a href="../ContactPage/contact.php">Contact</a>
    </div>
  </nav>

  <div class="container">
    <h2>Edit Profile</h2>
    
    <div id="errorContainer" style="color:red; margin-bottom:20px;"></div>

    <!-- Display success message -->
    <div id="successMessage" style="color:green; margin-bottom:20px;"></div>
    
    <form method="POST" id="editProfileForm">
      <input type="hidden" name="submit" value="1" />

      <!-- First & Last Name -->
      <div class="form-row">
        <div class="form-group">
          <label for="fname">First Name</label>
          <input
            type="text"
            id="fname"
            name="fname"
            placeholder="Your first name"
            value="<?= htmlspecialchars($fname, ENT_QUOTES, 'UTF-8') ?>"
            required
          />
        </div>
        <div class="form-group">
          <label for="lname">Last Name</label>
          <input
            type="text"
            id="lname"
            name="lname"
            placeholder="Your last name"
            value="<?= htmlspecialchars($lname, ENT_QUOTES, 'UTF-8') ?>"
            required
          />
        </div>
      </div>

      <!-- Email Address -->
      <div class="form-group">
        <label for="email">Email Address</label>
        <input
          type="email"
          id="email"
          name="email"
          placeholder="Your email address"
          value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
          required
        />
      </div>

      <!-- Phone Number -->
      <div class="form-group">
        <label for="phone">Phone Number</label>
        <input
          type="tel"
          id="phone"
          name="phone"
          placeholder="Your phone number"
          value="<?= htmlspecialchars($pnum, ENT_QUOTES, 'UTF-8') ?>"
          required
        />
      </div>

      <!-- Age & Gender -->
      <div class="form-row">
        <div class="form-group">
          <label for="age">Age</label>
          <input
            type="number"
            id="age"
            name="age"
            placeholder="Your age"
            value="<?= htmlspecialchars($age, ENT_QUOTES, 'UTF-8') ?>"
            required
          />
        </div>
        <div class="form-group">
          <label for="gender">Gender</label>
          <select id="gender" name="gender" required>
            <option value="" disabled <?= empty($gender) ? 'selected' : '' ?>>Select gender</option>
            <option value="male"   <?= ($gender === 'male')   ? 'selected' : '' ?>>Male</option>
            <option value="female" <?= ($gender === 'female') ? 'selected' : '' ?>>Female</option>
            <option value="other"  <?= ($gender === 'other')  ? 'selected' : '' ?>>Other</option>
          </select>
        </div>
      </div>

      <!-- Weight & Height -->
      <div class="form-row">
        <div class="form-group">
          <label for="weight">Weight (kg)</label>
          <input
            type="number"
            id="weight"
            name="weight"
            placeholder="Your weight"
            step="any"
            value="<?= htmlspecialchars($weight, ENT_QUOTES, 'UTF-8') ?>"
            required
          />
        </div>
        <div class="form-group">
          <label for="height">Height (cm)</label>
          <input
            type="number"
            id="height"
            name="height"
            placeholder="Your height"
            step="any"
            value="<?= htmlspecialchars($height, ENT_QUOTES, 'UTF-8') ?>"
            required
          />
        </div>
      </div>

      <!-- Current Password, New Password & Confirm Password -->
      <div class="form-row">
        <div class="form-group">
          <label for="currentPassword">Current Password</label>
          <input
            type="password"
            id="currentPassword"
            name="currentPassword"
            placeholder="Enter current password"
            required
          />
          <p class="help-block">Note: if you don't want to change your password, leave the new password empty</p>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="password">New Password</label>
          <input
            type="password"
            id="password"
            name="password"
            placeholder="Enter new password"
          />
        </div>
        <div class="form-group">
          <label for="confirmPassword">Confirm Password</label>
          <input
            type="password"
            id="confirmPassword"
            name="confirmPassword"
            placeholder="Confirm new password"
          />
        </div>
      </div>

      <!-- Client-side password mismatch check (optional) -->
      <div id="passwordError" class="error" style="color:red;"></div>

      <button name="submit" type="submit" class="btn">Save Changes</button>
    </form>
  </div>

  <!-- Scripts -->
  <script>
    document.getElementById("editProfileForm").addEventListener("submit", function (event) {
      event.preventDefault();

      const formData = new FormData(this);

      fetch(window.location.href, {
        method: "POST",
        body: formData,
        headers: {
          "Accept": "application/json"
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          document.getElementById("successMessage").textContent = data.message;
          document.getElementById("errorContainer").textContent = "";
        } else {
          document.getElementById("errorContainer").textContent = data.errors.join("\n");
          document.getElementById("successMessage").textContent = "";
        }
      })
      .catch(error => {
        console.error("Error:", error);
      });
    });

    // Toggle mobile menu visibility
    function toggleMenu() {
      const navLinks = document.querySelector(".nav-links");
      navLinks.classList.toggle("active");
    }
  </script>
</body>
</html>
