<?php
header("Access-Control-Allow-Origin: *");

// Allow the following methods from any origin
header("Access-Control-Allow-Methods: POST");

// Allow the following headers from any origin
header("Access-Control-Allow-Headers: Content-Type");
include "./database/connection.php";
include "./helpers/auth.php";



if (!isset($_POST['token'], $_POST['journal_id'])) {
    echo json_encode(["success" => false, "message" => "Token and journal ID required"]);
    exit;
}

$userId = getUserId($_POST['token']);
$journalId = $_POST['journal_id'];

// Check if the user has already liked the journal entry
$likeCheckSql = "SELECT * FROM likes WHERE user_id = ? AND journal_id = ?";
$likeCheckStmt = mysqli_prepare($CON, $likeCheckSql);
mysqli_stmt_bind_param($likeCheckStmt, 'ii', $userId, $journalId);
mysqli_stmt_execute($likeCheckStmt);
$likeCheckResult = mysqli_stmt_get_result($likeCheckStmt);

if (mysqli_fetch_assoc($likeCheckResult)) {
    // User has already liked, so unlike the journal entry
    $unlikeSql = "DELETE FROM likes WHERE user_id = ? AND journal_id = ?";
    $unlikeStmt = mysqli_prepare($CON, $unlikeSql);
    mysqli_stmt_bind_param($unlikeStmt, 'ii', $userId, $journalId);
    $success = mysqli_stmt_execute($unlikeStmt);
    $action = 'unliked';
} else {
    // Like the journal entry
    $likeSql = "INSERT INTO likes (user_id, journal_id) VALUES (?, ?)";
    $likeStmt = mysqli_prepare($CON, $likeSql);
    mysqli_stmt_bind_param($likeStmt, 'ii', $userId, $journalId);
    $success = mysqli_stmt_execute($likeStmt);
    $action = 'liked';
}

if ($success) {
    echo json_encode(["success" => true, "message" => "Journal entry successfully $action."]);
} else {
    echo json_encode(["success" => false, "message" => "Could not update like status."]);
}
?>
