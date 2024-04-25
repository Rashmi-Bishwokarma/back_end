<?php
header("Access-Control-Allow-Origin: *");

// Allow the following methods from any origin
header("Access-Control-Allow-Methods: POST");

// Allow the following headers from any origin
header("Access-Control-Allow-Headers: Content-Type");
include "./database/connection.php"; // Adjust this path as necessary

header('Content-Type: application/json');

if (isset($_POST['token'])) {
    $token = $_POST['token'];

    // Authenticate the user with the provided token
    $userId = authenticateUserWithToken($token);
    if (!$userId) {
        echo json_encode([
            "success" => false,
            "message" => "Invalid token!"
        ]);
        exit;
    }

    // Query to get all tasks for the authenticated user
    $sql = "SELECT id, title, description, start_date, start_time, end_time, priority, created_at FROM tasks WHERE user_id = ?";

    $stmt = mysqli_prepare($CON, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $tasks = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $tasks[] = $row;
    }

    if (count($tasks) > 0) {
        echo json_encode([
            "success" => true,
            "tasks" => $tasks
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "No tasks found."
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "Token is required!"
    ]);
}

// Reuse the same authentication function from your previous script
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

?>
