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

if (!isHospital($token)) {
    echo json_encode([
        "success" => false,
        "message" => "You are not authorized!"
    ]);
    die();
}

global $CON;

if (isset($_POST['name'], $_POST['consultation_charge'], $_POST['specialization_id'], $_FILES['avatar'], $_POST['experience'],)) {

    $name = $_POST['name'];
    $consultation_charge = $_POST['consultation_charge'];
    $specialization_id = $_POST['specialization_id'];
    $avatar = $_FILES['avatar'];
    $experience = $_POST['experience'];
    $avatar_name = $avatar['name'];
    $avatar_tmp_name = $avatar['tmp_name'];
    $avatar_size = $avatar['size'];

    $hospital_Id = getUserId($token);

    $ext = pathinfo($avatar_name, PATHINFO_EXTENSION);

    if ($ext != "jpg" && $ext != "jpeg" && $ext != "png" && $ext != "webp") {
        echo json_encode([
            "success" => false,
            "message" => "Only image files are allowed!"
        ]);
        die();
    }

    if ($avatar_size > 1000000) {
        echo json_encode([
            "success" => false,
            "message" => "Image size should be less than 1MB!"
        ]);
        die();
    }

    $avatar_name = uniqid() . "." . $ext;

    if (!move_uploaded_file($avatar_tmp_name, "./images/" . $avatar_name)) {
        echo json_encode([
            "success" => false,
            "message" => "Image upload failed!"
        ]);
        die();
    }





    $sql = "INSERT INTO doctors (name, consultation_charge, hospital_id, specialization_id, avatar, experience) VALUES ('$name', '$consultation_charge', '$hospital_Id', '$specialization_id', 'images/$avatar_name', '$experience')";

    $result = mysqli_query($CON, $sql);

    if (!$result) {
        echo json_encode([
            "success" => false,
            "message" => "Doctor not added!"
        ]);
        die();
    } else {
        echo json_encode([
            "success" => true,
            "message" => "Doctor added successfully!"
        ]);
        die();
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "name, consultation_charge, hospital_id, specialization_id, avatar, experience are required!"
    ]);
    die();
}
