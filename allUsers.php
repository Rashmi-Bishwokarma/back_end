<?php
header("Access-Control-Allow-Origin: *");

// Allow the following methods from any origin
header("Access-Control-Allow-Methods: POST");

// Allow the following headers from any origin
header("Access-Control-Allow-Headers: Content-Type");
include "./database/connection.php";
// SQL to get all user information
$sql = "SELECT* FROM users";

$result = $CON->query($sql);

$users = [];

// Check if the query was successful
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
} else {
    die("Error fetching users: " . $CON->error);
}


// Output the users list
echo json_encode(['users' => $users]);

// Close the database connection
$CON->close();
?>
