<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');



include "./database/connection.php";
include "./helpers/auth.php";


$token = $_POST['token'] ?? ''; // Assuming token is passed as a query parameter
$userId = getUserId($token);

if (!$userId) {
    echo json_encode(["success" => false, "message" => "Invalid token!"]);
    exit();
}

// Optionally, you can fetch a single journal entry by its ID
// $journalId = $_GET['id'] ?? ''; // For fetching a specific journal entry

$sql = "SELECT * FROM journals WHERE user_id = ?";
$stmt = mysqli_prepare($CON, $sql);
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$entries = [];
while ($row = mysqli_fetch_assoc($result)) {
    $entries[] = $row;
}

if (!empty($entries)) {
    echo json_encode(["success" => true, "data" => $entries]);
} else {
    echo json_encode(["success" => false, "message" => "No journal entries found."]);
}

?>
