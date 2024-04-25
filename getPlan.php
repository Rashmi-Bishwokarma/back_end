<?php
header("Access-Control-Allow-Origin: *");

// Allow the following methods from any origin
header("Access-Control-Allow-Methods: POST");

// Allow the following headers from any origin
header("Access-Control-Allow-Headers: Content-Type");
include "./database/connection.php";// Adjust this path

header('Content-Type: application/json');

$sql = "SELECT * FROM plans WHERE is_active = 1";
$result = mysqli_query($CON, $sql);

$plans = [];

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $plans[] = $row;
    }
    echo json_encode(['success' => true, 'plans' => $plans]);
} else {
    echo json_encode(['success' => false, 'message' => 'No plans found']);
}
?>
