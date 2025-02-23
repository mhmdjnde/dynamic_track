<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

$error_message = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $host = "localhost";
    $dbname = "dynamictrackbackend";
    $db_username = "jnde";
    $db_password = "jnde1777";

    // Create a new MySQLi connection
    $conn = new mysqli($host, $db_username, $db_password, $dbname);

    // Check for connection errors
    if ($conn->connect_error) {
        $error_message = "Database connection failed: " . $conn->connect_error;
    } else {
        // Prepare and execute the statement using MySQLi
        $stmt = $conn->prepare("SELECT * FROM usersecurity WHERE email = ? AND password = ?");
        $stmt->bind_param("ss", $username, $password);

        if ($stmt->execute()) {
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Login successful
                $_SESSION['userEmail'] = $username;

                // Set a cookie for 3 days
                setcookie("userEmail", $username, time() + (3 * 24 * 60 * 60), "/");
                header("Location: ../index.php");
                exit;
            } else {
                $error_message = "Invalid email or password.";
            }
        } else {
            $error_message = "Database error: " . $stmt->error;
        }

        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Simple Navigation Bar</title>
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
  />
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <h1 style="margin-top: 0; margin-bottom: 15px">
    <span style="color: #45a049">DynamicTrack</span>
  </h1>
  <nav>
    <ul>
      <li><a href="../index.php">Home</a></li>
      <li><a href="../ContactPage/contact.php">Contact</a></li>
    </ul>
  </nav>
  <div class="login-container">
    <h2>Login</h2>
    <?php if (!empty($error_message)): ?>
      <p style="color: red;"><?php echo $error_message; ?></p>
    <?php endif; ?>
    <?php if (!empty($success_message)): ?>
      <p style="color: green;"><?php echo $success_message; ?></p>
    <?php endif; ?>

    <form action="" method="POST">
      <label for="username">Email</label>
      <input
        type="text"
        id="username"
        name="username"
        required
        placeholder="Enter your Email"
        value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>"
      />

      <label for="password">Password</label>
      <div class="password-container">
        <input
          type="password"
          id="password"
          name="password"
          required
          placeholder="Enter your password"
        />
      </div>

      <button type="submit" class="login-btn">Login</button>

      <div class="forgot-password">
        <a href="#">Forgot Password?</a>
      </div>

      <div class="signup">
        Not a member yet? <a href="../signup/signup.html">Sign Up</a>
      </div>
    </form>
  </div>
</body>
</html>
