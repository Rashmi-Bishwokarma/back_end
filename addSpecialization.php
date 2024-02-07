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

$token = $_POST['token'];

if (!isAdmin($token)) {
    echo json_encode([
        "success" => false,
        "message" => "You are not authorized!"
    ]);
    die();
}

global $CON;

if (isset($_POST['title'])) {
    $title = $_POST['title'];


    $sql = "SELECT * FROM specialization WHERE title = '$title'";

    $result = mysqli_query($CON, $sql);

    $count = mysqli_num_rows($result);

    if ($count > 0) {
        echo json_encode([
            "success" => false,
            "message" => "Specialization already exists!"
        ]);
        die();
    }



    $sql = "INSERT INTO specialization (title) VALUES ('$title')";
    $result = mysqli_query($CON, $sql);

    if ($result) {
        echo json_encode([
            "success" => true,
            "message" => "Specialization added successfully!"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Something went wrong!"
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "title is required!"
    ]);
}
