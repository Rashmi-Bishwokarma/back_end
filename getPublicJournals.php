<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
include "./database/connection.php";

require_once "./helpers/cors.php";
header('Content-Type: application/json');

// Prepare the SQL statement to select journals with 'public' privacy.
$sql = "SELECT journal_id, title, entry, location, mood, featured_image, created_at FROM journals WHERE privacy = 'public' ORDER BY created_at DESC";
$result = mysqli_query($CON, $sql);

$publicJournals = [];

// Check if there are any results.
if ($result && mysqli_num_rows($result) > 0) {
    // Fetch all public journal entries.
    while($row = mysqli_fetch_assoc($result)) {
        $publicJournals[] = $row;
    }
    
    // Send back a JSON response with success status and journal data.
    echo json_encode(["success" => true, "data" => $publicJournals]);
} else {
    // If there are no public entries, send a JSON response with a success status and an empty data array.
    echo json_encode(["success" => true, "data" => $publicJournals, "message" => "No public journal entries found."]);
}

?>
