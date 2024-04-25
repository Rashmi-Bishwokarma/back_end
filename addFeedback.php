<?php
header("Access-Control-Allow-Origin: *");

// Allow the following methods from any origin
header("Access-Control-Allow-Methods: POST");

// Allow the following headers from any origin
header("Access-Control-Allow-Headers: Content-Type");
include "./database/connection.php";

// Check if the necessary data is present, including the token
if (
    isset(
    $_POST['token'],
    $_POST['message'])
     ) {

    $token = $_POST['token'];
    $message = $_POST['message'];
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : null; // Rating is optional

    global $CON;

    // Step 1: Authenticate the user by validating the token
    $userId = authenticateUserWithToken($token);
    if (!$userId) {
        echo json_encode([
            "success" => false,
            "message" => "Invalid or expired token!"
        ]);
        die();
    }

    // Step 2: Prepare the SQL statement to prevent SQL injection
    $stmt = $CON->prepare("INSERT INTO feedback (user_id, message, rating, created_at) VALUES (?, ?, ?, NOW())");
    
    // Bind parameters to the SQL query
    $stmt->bind_param("isi", $userId, $message, $rating);

    // Execute the query
    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "Feedback submitted successfully!"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Failed to submit feedback."
        ]);
    }

    // Close the prepared statement
    $stmt->close();
} else {
    echo json_encode([
        "success" => false,
        "message" => "Token and message are required."
    ]);
}

function authenticateUserWithToken($token) {
    global $CON;
    $sql = "SELECT user_id FROM personal_access_token WHERE token = ?";
    $stmt = $CON->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return $row['user_id'];
    } else {
        return false;
    }
}

?>
