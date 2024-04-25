<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
include "./database/connection.php"; 
include "./helpers/auth.php";

// Ensure this path is correct
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
if (isset($_POST['token'], $_POST['plan_id'])) {
    $token = $_POST['token'];
    $planId = $_POST['plan_id'];

    global $CON;
    // Authenticate the user with the provided token
    $userId = getUserId($token);
    if (!$userId) {
        echo json_encode([
            "success" => false,
            "message" => "Invalid token!"
        ]);
        exit;
    }

    // Validate the input
    if (empty($planId)) {
        echo json_encode([
            "success" => false,
            "message" => "Plan ID is required!"
        ]);
        exit;
    }

    // Prepare the SQL statement to delete the plan from the database
    $sql = "DELETE FROM plans WHERE plan_id = ? AND user_id = ?";
    $stmt = mysqli_prepare($CON, $sql);
    mysqli_stmt_bind_param($stmt, 'ii', $planId, $userId);
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode([
            "success" => true,
            "message" => "Plan deleted successfully!"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Failed to delete plan."
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "Token and Plan ID are required!"
    ]);
}


?>
