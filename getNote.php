<?php
header("Access-Control-Allow-Origin: *");

// Allow the following methods from any origin
header("Access-Control-Allow-Methods: POST");

// Allow the following headers from any origin
header("Access-Control-Allow-Headers: Content-Type");
include "./database/connection.php";
include "./helpers/auth.php";

$token = $_POST['token'] ?? '';

$userId = getUserId($token);

if (!$userId) {
    echo json_encode(["success" => false, "message" => "Invalid token!"]);
    exit();
}

// Select all notes for the user_id without filtering by a specific note id
$sql = "SELECT id, content, created_at, updated_at FROM notes WHERE user_id = ?";
$stmt = mysqli_prepare($CON, $sql);
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$notes = []; // Array to hold all notes

// Loop through all results and add each note to the notes array
while ($note = mysqli_fetch_assoc($result)) {
    $notes[] = $note;
}

// Check if at least one note was found
if (count($notes) > 0) {
    echo json_encode(["success" => true, "notes" => $notes]);
} else {
    echo json_encode(["success" => false, "message" => "No notes found for this user."]);
}

?>
