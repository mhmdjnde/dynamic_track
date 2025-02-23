<?php
session_start();

header("Content-Type: application/json");

// Adjust DB credentials as needed
$dbHost = "localhost";
$dbName = "dynamictrackbackend";
$dbUser = "jnde";
$dbPass = "jnde1777";

// Create a new MySQLi connection
$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

// Check for connection errors
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed: " . $mysqli->connect_error
    ]);
    exit;
}

// Set charset to UTF-8
$mysqli->set_charset("utf8mb4");

// Enable MySQLi exceptions for easier error handling
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Read and decode input JSON
    $rawData = file_get_contents("php://input");
    $data = json_decode($rawData, true);

    // Validate input
    if (!is_array($data)) {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "Invalid JSON input",
        ]);
        exit;
    }

    // Extract fields
    $first_name   = $data['first_name']   ?? null;
    $last_name    = $data['last_name']    ?? null;
    $gender       = $data['gender']       ?? null;
    $age          = $data['age']          ?? null;
    $weight       = $data['weight']       ?? null;
    $height       = $data['height']       ?? null;
    $activity     = $data['activity']     ?? null;
    $goal_weight  = $data['goal_weight']  ?? null;
    $goal_other   = $data['goal_other']   ?? null;
    $email        = $data['email']        ?? null;
    $phone_number = $data['phone_number'] ?? null;
    $password     = $data['password']     ?? null;

    // Check if email is provided
    if (!$email) {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "Email is required and not provided.",
        ]);
        exit;
    }

    // Begin transaction
    $mysqli->begin_transaction();

    // 1) Insert into userinfo
    $stmtUserInfo = $mysqli->prepare("
        INSERT INTO userinfo (Email, FName, LName, Age, Gender)
        VALUES (?, ?, ?, ?, ?)
    ");
    // Types: s = string, s = string, s = string, i = integer, s = string
    $stmtUserInfo->bind_param("sssis", $email, $first_name, $last_name, $age, $gender);
    $stmtUserInfo->execute();

    // 2) Insert into usersecurity
    $stmtUserSec = $mysqli->prepare("
        INSERT INTO usersecurity (Email, PhoneNumber, Password)
        VALUES (?, ?, ?)
    ");
    // Types: s = string, i = integer, s = string
    $stmtUserSec->bind_param("sis", $email, $phone_number, $password);
    $stmtUserSec->execute();

    // 3) Insert into nutrirequirements
    $stmtNutri = $mysqli->prepare("
        INSERT INTO nutrirequirements (Email, Weight, Height, Activity, WeightGoal, DietGoal)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    // Types: s = string, d = double, d = double, s = string, s = string, s = string
    $stmtNutri->bind_param("sddsss", $email, $weight, $height, $activity, $goal_weight, $goal_other);
    $stmtNutri->execute();

    // Commit the transaction
    $mysqli->commit();

    // Store email in session
    $_SESSION['userEmail'] = $email;

    // Return success response
    echo json_encode([
        "status" => "success",
        "message" => "All data inserted successfully.",
        "inserted_data" => [
            "Email"       => $email,
            "FName"       => $first_name,
            "LName"       => $last_name,
            "Age"         => $age,
            "Gender"      => $gender,
            "PhoneNumber" => $phone_number,
            "Password"    => $password,
            "Weight"      => $weight,
            "Height"      => $height,
            "Activity"    => $activity,
            "WeightGoal"  => $goal_weight,
            "DietGoal"    => $goal_other
        ]
    ]);
    
} catch (mysqli_sql_exception $e) {
    // If there's an active transaction, roll it back
    if ($mysqli->errno) {
        $mysqli->rollback();
    }

    // Check for duplicate entry (e.g., "Duplicate entry 'xyz' for key '...'");
    // MySQL's code for duplicate entry is typically 1062, but we can also use string matching:
    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "Email already registered, please change it",
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => "Database error: " . $e->getMessage(),
        ]);
    }
} finally {
    // Close statements if they were created
    if (isset($stmtUserInfo)) $stmtUserInfo->close();
    if (isset($stmtUserSec))  $stmtUserSec->close();
    if (isset($stmtNutri))    $stmtNutri->close();
    
    // Close the DB connection
    $mysqli->close();
}
