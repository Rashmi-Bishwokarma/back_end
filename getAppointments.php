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
$user_Id = getUserId($token);


$sql = '';

if ($is_hospital) {

    $sql = "select appointments.*,doctors.*,payments.user_id,payments.amount,payments.details from appointments left join payments on appointments.appointment_id=payments.appointment_id join doctors on appointments.doctor_id = doctors.id where doctors.hospital_id = $user_Id";
} else {
    $sql = "select appointments.*,doctors.*,payments.user_id,payments.amount,payments.details from appointments left join payments on appointments.appointment_id=payments.appointment_id join doctors on appointments.doctor_id=doctors.id where appointments.user_id = $user_Id";
}

global $CON;

$result = mysqli_query($CON, $sql);

if ($result) {
    $appointments = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $appointments[] = $row;
    }

    echo json_encode([
        "success" => true,
        "message" => "Appointments fetched successfully!",
        "appointments" => $appointments
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Something went wrong!"
    ]);
}
