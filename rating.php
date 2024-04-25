<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
include './database/connection.php'; // Adjust the path to your database connection file

// SQL to calculate the average rating
$sql = "SELECT AVG(rating) as average_rating FROM feedback WHERE rating IS NOT NULL";
$result = $CON->query($sql);

// Check if the query was successful
if ($result) {
    $row = $result->fetch_assoc();
    $averageRating = $row['average_rating'];
} else {
    // Handle the error properly
    $averageRating = "Error calculating average rating: " . $CON->error;
}

// Set header to return JSON content
header('Content-Type: application/json');

// Output the average rating
echo json_encode(['average_rating' => $averageRating]);

// Close the database connection
$CON->close();
?>
