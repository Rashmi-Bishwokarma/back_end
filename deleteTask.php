<?php
header("Access-Control-Allow-Origin: *");

// Allow the following methods from any origin
header("Access-Control-Allow-Methods: POST");

// Allow the following headers from any origin
header("Access-Control-Allow-Headers: Content-Type");
include "./database/connection.php"; // Make sure this path is correct for your project

if (isset($_POST['token'], $_POST['id'])) {
    $token = $_POST['token'];
    $taskId = $_POST['id'];

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
    if (empty($taskId)) {
        echo json_encode([
            "success" => false,
            "message" => "Task ID is required!"
        ]);
        exit;
    }

    // Check if the task belongs to the user trying to delete it
    $checkSql = "SELECT user_id FROM tasks WHERE id = ?";
    $checkStmt = mysqli_prepare($CON, $checkSql);
    mysqli_stmt_bind_param($checkStmt, 'i', $taskId);
    mysqli_stmt_execute($checkStmt);
    $checkResult = mysqli_stmt_get_result($checkStmt);

    if ($checkResult && mysqli_num_rows($checkResult) > 0) {
        $row = mysqli_fetch_assoc($checkResult);
        if ($row['user_id'] != $userId) {
            echo json_encode([
                "success" => false,
                "message" => "Unauthorized to delete this task."
            ]);
            exit;
        }
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Task not found."
        ]);
        exit;
    }

    // Prepare the SQL statement to delete the task from the database
    $sql = "DELETE FROM tasks WHERE id = ?";
    $stmt = mysqli_prepare($CON, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $taskId);
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode([
            "success" => true,
            "message" => "Task deleted successfully!"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Failed to delete task."
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "Token and task ID are required!"
    ]);
}

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
