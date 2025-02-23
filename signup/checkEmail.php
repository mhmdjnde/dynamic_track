<?php
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

// Get JSON input and decode
$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

// Validate input
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Invalid JSON input"
    ]);
    exit;
}

// Check if email is provided
$email = $data['email'] ?? null;
if (!$email) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Email missing"
    ]);
    exit;
}

// Prepare the statement
$stmt = $mysqli->prepare("SELECT Email FROM userinfo WHERE Email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();

// Fetch the result
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    // Email already exists
    echo json_encode([
        "status" => "error",
        "message" => "Email already registered"
    ]);
} else {
    // Email is available
    echo json_encode([
        "status" => "success",
        "message" => "Email is available"
    ]);
}

// Close statement and connection
$stmt->close();
$mysqli->close();
