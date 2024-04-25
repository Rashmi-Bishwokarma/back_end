<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include "./database/connection.php"; // Adjust this path

$data = json_decode(file_get_contents("php://input"), true);

// Basic validation - make sure to enhance this with proper validation
if(isset($data["plan_id"], $data["name"], $data["price"], $data["duration"], $data["features"])) {
    $planId = mysqli_real_escape_string($CON, $data["plan_id"]);
    $name = mysqli_real_escape_string($CON, $data["name"]);
    $price = mysqli_real_escape_string($CON, $data["price"]);
    $duration = mysqli_real_escape_string($CON, $data["duration"]);
    $features = mysqli_real_escape_string($CON, $data["features"]);

    $sql = "UPDATE plans SET name='$name', price='$price', duration='$duration', features='$features' WHERE plan_id='$planId'";

    if(mysqli_query($CON, $sql)) {
        echo json_encode(['success' => true, 'message' => 'Plan updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating plan: ' . mysqli_error($CON)]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Incomplete data for updating plan']);
}

mysqli_close($CON);
?>
