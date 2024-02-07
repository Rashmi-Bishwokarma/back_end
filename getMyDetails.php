<?php

if (!isset($_POST['token'])) {
    echo json_encode([
        "success" => false,
        "message" => "Token not found!"
    ]);
    die();
}

include "./database/connection.php";
include "./helpers/auth.php";

$user_id = getUserId($_POST['token']);


$sql = "SELECT user_id,email, full_name,role, email, address FROM users WHERE user_id = '$user_id'";

global $CON;

$result = mysqli_query($CON, $sql);

if ($result) {
    $user = mysqli_fetch_assoc($result);

    echo json_encode([
        "success" => true,
        "message" => "User details fetched successfully!",
        "user" => $user
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Something went wrong!"
    ]);
}
