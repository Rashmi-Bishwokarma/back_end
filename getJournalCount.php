<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
include './database/connection.php'; // Adjust the path to your database connection file

// SQL to get the count of journal entries by date
$sql = "SELECT DATE(created_at) AS date, COUNT(*) AS count FROM journals GROUP BY DATE(created_at) ORDER BY DATE(created_at)";

$result = $CON->query($sql);

// Initialize an array to hold the counts
$journalCounts = [];

// Check if the query was successful
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $journalCounts[] = $row;
    }
} else {
    // Handle the error properly
    die("Error fetching journal counts: " . $CON->error);
}

// Set header to return JSON content
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allow cross-domain requests if needed

// Output the journal counts
echo json_encode(['journal_counts' => $journalCounts]);

// Close the database connection
$CON->close();
?>
