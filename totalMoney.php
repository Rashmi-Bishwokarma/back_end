<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

include './database/connection.php';

$sql = "SELECT SUM(amount) AS total_revenue FROM payments WHERE status = 'completed'";

$result = $CON->query($sql);

if ($result) {
    $row = $result->fetch_assoc();
    $totalRevenue = $row['total_revenue'];
    if(is_null($totalRevenue)) {
        $totalRevenue = "0.00"; // If there are no successful payments
    }
} else {
    die("Error calculating total revenue: " . $CON->error);
}

// Set header to return JSON content
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allows cross-origin requests

// Output the total revenue
echo json_encode(['total_revenue' => $totalRevenue]);

// Close the database connection
$CON->close();
?>
