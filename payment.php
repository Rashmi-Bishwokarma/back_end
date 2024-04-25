<?php

include "./database/connection.php";
include "./helpers/auth.php";

header('Content-Type: application/json');

// Authenticate the user token and get user ID
$token = $_POST['token'] ?? '';
$user_id = getUserId($token);

if (!$user_id) {
    echo json_encode(["success" => false, "message" => "Invalid token!"]);
    exit();
}

// Get the posted data
$plan_id = $_POST['plan_id'] ?? '';
$amount = $_POST['amount'] ?? '';

// Validate the data
if (!$plan_id || !$amount) {
    echo json_encode(["success" => false, "message" => "Plan ID and amount are required!"]);
    exit();
}

// Assuming payment was successful and you received a confirmation from your payment gateway
// Begin database transaction
mysqli_begin_transaction($CON);

try {
    // Insert payment entry
    $insert_payment = "INSERT INTO payments (user_id, plan_id, amount, status) VALUES (?, ?, ?, 'completed')";
    $stmt = mysqli_prepare($CON, $insert_payment);
    mysqli_stmt_bind_param($stmt, 'iis', $user_id, $plan_id, $amount);
    mysqli_stmt_execute($stmt);
    $payment_id = mysqli_stmt_insert_id($stmt);

    // If payment was inserted successfully
    if ($payment_id) {
        // Fetch the plan's duration
        $query_plan = "SELECT duration FROM plans WHERE plan_id = ?";
        $stmt = mysqli_prepare($CON, $query_plan);
        mysqli_stmt_bind_param($stmt, 'i', $plan_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $plan = mysqli_fetch_assoc($result);

        $duration = $plan['duration'];
        $current_date = date('Y-m-d');
        $end_date = date('Y-m-d', strtotime("+$duration days", strtotime($current_date)));

        // Insert subscription entry
        $insert_subscription = "INSERT INTO subscriptions (user_id, plan_id, start_date, end_date, status) VALUES (?, ?, ?, ?, 'active')";
        $stmt = mysqli_prepare($CON, $insert_subscription);
        mysqli_stmt_bind_param($stmt, 'iiss', $user_id, $plan_id, $current_date, $end_date);
        mysqli_stmt_execute($stmt);
        $subscription_id = mysqli_stmt_insert_id($stmt);

        // If subscription was inserted successfully
        if ($subscription_id) {
            // Commit the transaction
            mysqli_commit($CON);
            echo json_encode(["success" => true, "message" => "Payment and subscription processed successfully!"]);
        } else {
            throw new Exception("Failed to create subscription!");
        }
    } else {
        throw new Exception("Failed to record payment!");
    }
} catch (Exception $e) {
    // Rollback the transaction in case of error
    mysqli_rollback($CON);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}

?>
