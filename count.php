<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
// Database connection setup
include './database/connection.php'; // Adjust the path to your database connection file

// Define an associative array to store table counts
$tableCounts = [];

// List of all your table names
$tables = ['comments', 'feedback', 'journals', 'journal_tags', 'likes', 'notes', 'notifications', 'payments', 'personal_access_token', 'plans', 'subscriptions', 'tags', 'tasks', 'users'];

// Loop through the table names, count the entries, and add to the array
foreach ($tables as $table) {
    $query = "SELECT COUNT(*) AS count FROM `$table`";
    $result = $CON->query($query);

    if ($result) {
        $row = $result->fetch_assoc();
        $tableCounts[$table] = $row['count'];
    } else {
        // Handle the error properly - in production, you should not output raw error messages
        $tableCounts[$table] = "Error counting entries: " . $CON->error;
    }
}

// Set header to return JSON content
header('Content-Type: application/json');

// Output the JSON-encoded array
echo json_encode($tableCounts);

// Close the database connection
$CON->close();
?>
