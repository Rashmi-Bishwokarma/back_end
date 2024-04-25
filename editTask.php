<?php
header("Access-Control-Allow-Origin: *");

// Allow the following methods from any origin
header("Access-Control-Allow-Methods: POST");

// Allow the following headers from any origin
header("Access-Control-Allow-Headers: Content-Type");
include "./database/connection.php"; // Make sure this path is correct for your project

// Function to authenticate user with token
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

if (isset($_POST['token'], $_POST['id'], $_POST['title'], $_POST['description'], $_POST['start_date'], $_POST['start_time'], $_POST['end_time'], $_POST['priority'])) {
    $token = $_POST['token'];
    $taskId = $_POST['id']; // Task ID for identifying which task to edit
    $title = $_POST['title'];
    $description = $_POST['description'];
    $startDate = $_POST['start_date'];
    $startTime = $_POST['start_time'];
    $endTime = $_POST['end_time'];
    $priority = $_POST['priority'];

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
    if (empty($taskId) || empty($title) || empty($description) || empty($startDate) || empty($startTime) || empty($endTime) || empty($priority)) {
        echo json_encode([
            "success" => false,
            "message" => "All fields are required!"
        ]);
        exit;
    }

    // Prepare the SQL statement to update the task in the database
    $sql = "UPDATE tasks SET title = ?, description = ?, start_date = ?, start_time = ?, end_time = ?, priority = ? WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($CON, $sql);
    mysqli_stmt_bind_param($stmt, 'ssssssii', $title, $description, $startDate, $startTime, $endTime, $priority, $taskId, $userId);
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode([
            "success" => true,
            "message" => "Task updated successfully!"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Failed to update task."
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "Token, task ID, title, description, start date, start time, end time, and priority are required!"
    ]);
}

?>
