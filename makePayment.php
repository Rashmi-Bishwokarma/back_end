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
$user_id = getUserId($token);


if (isset($_POST['appointmentId'], $_POST['amount'], $_POST['details'])) {


    global $CON;
    $appointment_id = $_POST['appointmentId'];
    $amount = $_POST['amount'];
    $details = $_POST['details'];


    $sql = "select * from payments where appointment_id = $appointment_id";

    $result = mysqli_query($CON, $sql);

    if (mysqli_num_rows($result) > 0) {
        echo json_encode([
            "success" => false,
            "message" => "Payment already made, thank you!"
        ]);
        die();
    }


    $sql = "insert into payments (user_id,appointment_id,amount,details) values ('$user_id','$appointment_id','$amount','$details')";

    $result = mysqli_query($CON, $sql);

    $sql = "update appointments set status = 'paid' where appointment_id = $appointment_id";

    $result = mysqli_query($CON, $sql);

    if (!$result) {
        echo json_encode([
            "success" => false,
            "message" => "Something went wrong"
        ]);
    } else {
        echo json_encode([
            "success" => true,
            "message" => "Payment made successfully!"
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "appointmentId, amount and details are required!"
    ]);
    die();
}
