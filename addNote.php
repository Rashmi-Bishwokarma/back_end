<?php
header("Access-Control-Allow-Origin: *");

// Allow the following methods from any origin
header("Access-Control-Allow-Methods: POST");

// Allow the following headers from any origin
header("Access-Control-Allow-Headers: Content-Type");
include "./database/connection.php";

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

$token = $_POST['token'] ?? '';
$content = $_POST['content'] ?? '';

$userId = authenticateUserWithToken($token);

if (!$userId) {
    echo json_encode(["success" => false, "message" => "Invalid token!"]);
    exit();
}

// Here, the content is mandatory, so we check if it's empty
if (empty($content)) {
    echo json_encode(["success" => false, "message" => "Note content cannot be empty!"]);
    exit();
}

// Insert the note into the database
$sql = "INSERT INTO notes (user_id, content, created_at) VALUES (?, ?, NOW())";
$stmt = mysqli_prepare($CON, $sql);
mysqli_stmt_bind_param($stmt, 'is', $userId, $content);

if (mysqli_stmt_execute($stmt)) {
    $noteId = mysqli_insert_id($CON);
    echo json_encode(["success" => true, "message" => "Note added successfully!", "noteId" => $noteId]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to add note!"]);
}

?>
