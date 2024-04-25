<?php
header("Access-Control-Allow-Origin: *");

// Allow the following methods from any origin
header("Access-Control-Allow-Methods: POST");

// Allow the following headers from any origin
header("Access-Control-Allow-Headers: Content-Type");
include "./database/connection.php"; // Adjust this path to your project's correct location

// This script assumes you are sending plan_id, name, price, duration, features, is_active in a POST request

if (isset($_POST['token'], $_POST['plan_id'], $_POST['name'], $_POST['price'], $_POST['duration'], $_POST['features'], $_POST['is_active'])) {
    $token = $_POST['token'];
    $planId = $_POST['plan_id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $duration = $_POST['duration'];
    $features = $_POST['features'];
    $isActive = $_POST['is_active'];

    global $CON;
    // Authenticate the user with the provided token
    $userId = authenticateUserWithToken($token);
    if (!$userId) {
        echo json_encode([
            "success" => false,
            "message" => "Invalid token!"
        ]);
        exit;
    }

    // Validate the input
    if (empty($planId) || empty($name) || empty($price) || empty($duration) || empty($features) || !isset($isActive)) {
        echo json_encode([
            "success" => false,
            "message" => "All fields are required!"
        ]);
        exit;
    }

    // Convert is_active to a boolean before inserting
    $isActive = filter_var($isActive, FILTER_VALIDATE_BOOLEAN);

    // Prepare the SQL statement to insert the plan into the database
    $sql = "INSERT INTO plans (plan_id, name, price, duration, features, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $stmt = mysqli_prepare($CON, $sql);
    mysqli_stmt_bind_param($stmt, 'issdssi', $planId, $name, $price, $duration, $features, $isActive);
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode([
            "success" => true,
            "message" => "Plan added successfully!"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Failed to add plan."
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "Token, plan_id, name, price, duration, features, and is_active are required!"
    ]);
}

function authenticateUserWithToken($token) {
    global $CON;
    $sql = "SELECT user_id FROM personal_access_tokens WHERE token = ?"; // Adjust the table name if necessary
    $stmt = mysqli_prepare($CON, $sql);
    mysqli_stmt_bind_param($stmt, 's', $token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['user_id'];
    }

    return false;
}

?>
