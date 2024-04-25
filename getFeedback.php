<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

// Allow the following methods from any origin
header("Access-Control-Allow-Methods: POST");

// Allow the following headers from any origin
header("Access-Control-Allow-Headers: Content-Type");

include "./database/connection.php"; // Adjust the path as needed

// Check if the connection was successful
if ($CON->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Failed to connect to the database: " . $CON->connect_error
    ]);
    exit(); // Stop script execution
}

// Prepare the SQL statement to select feedback records
$sql = "SELECT 
f.id, 
f.user_id, 
f.message, 
f.rating, 
f.created_at, 
u.full_name AS username, 
u.email, 
u.profile_image 
FROM 
feedback AS f
JOIN 
users AS u 
ON 
f.user_id = u.user_id 
ORDER BY 
f.created_at DESC;
";

$result = $CON->query($sql);

// Check if the query was successful
if ($result === false) {
    echo json_encode([
        "success" => false,
        "message" => "Error executing query: " . $CON->error
    ]);
    $CON->close();
    exit(); // Stop script execution
}

$feedbacks = [];

// Check if there are any rows returned
if ($result->num_rows > 0) {
    // Fetch all feedback records
    while ($row = $result->fetch_assoc()) {
        $feedbacks[] = $row;
    }

    // Return the feedbacks as JSON
    echo json_encode([
        "success" => true,
        "feedbacks" => $feedbacks
    ]);
} else {
    // No feedback found, return an empty array
    echo json_encode([
        "success" => true,
        "feedbacks" => []
    ]);
}

$CON->close();
?>
