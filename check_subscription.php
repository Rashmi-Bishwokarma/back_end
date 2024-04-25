<?php
header("Access-Control-Allow-Origin: *");

// Allow the following methods from any origin
header("Access-Control-Allow-Methods: POST");

// Allow the following headers from any origin
header("Access-Control-Allow-Headers: Content-Type");
include "./database/connection.php";

// Function to authenticate the user by token
function authenticateUserWithToken($token) {
    global $CON;
    $sql = "SELECT user_id FROM personal_access_token WHERE token = ?";
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

// Function to check if the user has an active subscription
function checkUserSubscription($userId) {
    global $CON;
    $sql = "SELECT * FROM subscriptions WHERE user_id = ? AND end_date >= CURDATE() AND status = 'active' LIMIT 1";
    $stmt = mysqli_prepare($CON, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return $result && mysqli_num_rows($result) > 0;
}

// Get token from request
$token = $_POST['token'] ?? '';

// Authenticate user
$userId = authenticateUserWithToken($token);

if (!$userId) {
    echo json_encode(["success" => false, "message" => "Authentication failed!"]);
    exit();
}

// Check subscription status
$hasSubscription = checkUserSubscription($userId);

echo json_encode(["success" => true, "hasActiveSubscription" => $hasSubscription]);
?>
