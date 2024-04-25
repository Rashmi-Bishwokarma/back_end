<?php
header("Access-Control-Allow-Origin: *");

// Allow the following methods from any origin
header("Access-Control-Allow-Methods: POST");

// Allow the following headers from any origin
header("Access-Control-Allow-Headers: Content-Type");
include "./database/connection.php"; // Adjust the path as necessary for your project
include "./helpers/auth.php";
// Function to authenticate the user with the provided token


// Main code execution starts here
if (isset($_POST['token'], $_POST['journal_id'])) {
    $token = $_POST['token'];
    $journalId = $_POST['journal_id'];

    global $CON;
    // Authenticate the user with the provided token
    $userId = getUserId($token);
    if (!$userId) {
        echo json_encode(["success" => false, "message" => "Invalid token!"]);
        exit;
    }

    // Check if the journal entry belongs to the user trying to delete it
    if ($checkStmt = $CON->prepare("SELECT user_id FROM journals WHERE journal_id = ?")) {
        $checkStmt->bind_param('i', $journalId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult && $row = $checkResult->fetch_assoc()) {
            if ($row['user_id'] != $userId) {
                echo json_encode(["success" => false, "message" => "Unauthorized to delete this journal entry."]);
                exit;
            }
        } else {
            echo json_encode(["success" => false, "message" => "Failed to fetch journal entries:"]);
            exit;
        }
    } else {
        echo json_encode(["success" => false, "message" => "Failed to prepare the database query."]);
        exit;
    }

    // Delete the journal entry
    if ($deleteStmt = $CON->prepare("DELETE FROM journals WHERE journal_id = ?")) {
        $deleteStmt->bind_param('i', $journalId);
        if ($deleteStmt->execute()) {
            echo json_encode(["success" => true, "message" => "Journal entry deleted successfully!"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to delete journal entry."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Failed to prepare the delete query."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Token and journal ID are required!"]);
}

?>
