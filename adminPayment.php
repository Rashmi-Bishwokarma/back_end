<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
include './database/connection.php'; // Adjust the path to your database connection file

// SQL to fetch subscription data with user and plan details
$sql = "
SELECT 
    subscriptions.subscription_id,
    subscriptions.user_id,
    subscriptions.start_date,
    subscriptions.end_date,
    subscriptions.status,
    users.full_name,
    users.email,
    plans.name AS plan_name,
    plans.price,
    plans.duration,
    plans.features
FROM 
    subscriptions
INNER JOIN 
    users ON subscriptions.user_id = users.user_id
INNER JOIN 
    plans ON subscriptions.plan_id = plans.plan_id
WHERE 
    subscriptions.status = 'active'
";

$result = $CON->query($sql);

// Check if the query was successful
if ($result) {
    // Fetch all subscription records into an array
    $subscriptionList = $result->fetch_all(MYSQLI_ASSOC);
} else {
    // Handle the error properly
    $subscriptionList = "Error fetching subscription data: " . $CON->error;
}

// Set header to return JSON content
header('Content-Type: application/json');

// Output the subscription list
echo json_encode(['subscription_list' => $subscriptionList]);

// Close the database connection
$CON->close();
?>
