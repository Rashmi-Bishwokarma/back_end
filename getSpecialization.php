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

$sql = "SELECT * FROM specialization";

global $CON;

$result = mysqli_query($CON, $sql);

if ($result) {
    $specializations = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $specializations[] = $row;
    }

    echo json_encode([
        "success" => true,
        "message" => "Specializations fetched successfully!",
        "specalizations" => $specializations
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Something went wrong!"
    ]);
}
