<?php
header("Access-Control-Allow-Origin: *");

// Allow the following methods from any origin
header("Access-Control-Allow-Methods: POST");

// Allow the following headers from any origin
header("Access-Control-Allow-Headers: Content-Type");
// Assuming you're using token-based authentication
header('Content-Type: application/json');

include "./database/connection.php"; 
include "./helpers/auth.php";

$userId = getUserId($_POST['token']);// Get user ID based on provided token

if (!$userId) {
    echo json_encode(["success" => false, "message" => "Authentication failed"]);
    exit;
}

// Prepare SQL to fetch active subscription for the user
$sql = "SELECT * FROM subscriptions WHERE user_id = ? AND is_active = TRUE ORDER BY end_date DESC LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);
$subscription = $stmt->fetch(PDO::FETCH_ASSOC);

if ($subscription) {
    // You might also want to include plan details if you have a separate plans table
    echo json_encode(["success" => true, "subscription" => $subscription]);
} else {
    echo json_encode(["success" => false, "message" => "No active subscription found"]);
}
?>
