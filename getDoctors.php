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

$is_hospital = isHospital($token);
$hospital_Id = getUserId($token);

$sql = '';

if ($is_hospital) {
    $sql = "SELECT doctors.*,specialization.*,users.full_name as hospital_name, users.email as hospital_email, users.address as hospital_address FROM doctors join users on doctors.hospital_id = users.user_id join specialization on doctors.specialization_id = specialization.specialization_id where hospital_id = $hospital_Id";
} else {
    $sql = "SELECT doctors.*,specialization.*,users.full_name as hospital_name, users.email as hospital_email, users.address as hospital_address FROM doctors join users on doctors.hospital_id = users.user_id join specialization on doctors.specialization_id = specialization.specialization_id where isDeleted=0";
}

global $CON;

$result = mysqli_query($CON, $sql);

if ($result) {
    $doctors = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $doctors[] = $row;
    }

    echo json_encode([
        "success" => true,
        "message" => "Doctors fetched successfully!",
        "doctors" => $doctors
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Something went wrong!"
    ]);
}
