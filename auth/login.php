<?php

include "../database/connection.php";

if (isset($_POST['identifier'], $_POST['password'])) {

    $identifier = $_POST['identifier'];
    $password = $_POST['password'];

    global $CON;

    // Check if the provided value is an email or a full name
    $columnToCheck = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'full_name';

    $sql = "SELECT * FROM users WHERE $columnToCheck = '$identifier'";

    $result = mysqli_query($CON, $sql);

    if ($result) {
        $count = mysqli_num_rows($result);
        if ($count == 0) {
            echo json_encode([
                "success" => false,
                "message" => "User does not exist!"
            ]);
            die();
        }

        $row = mysqli_fetch_assoc($result);
        $hashed_password = $row['password'];
        $is_correct = password_verify($password, $hashed_password);

        if (!$is_correct) {
            echo json_encode([
                "success" => false,
                "message" => "Password is incorrect!"
            ]);
            die();
        }

        $token = bin2hex(random_bytes(32));
        $userId = $row['user_id'];
        $role = $row['role'];

        $sql = "INSERT INTO personal_access_token (user_id, token) VALUES ('$userId', '$token')";
        $result = mysqli_query($CON, $sql);

        if ($result) {
            echo json_encode([
                "success" => true,
                "message" => "User logged in successfully!",
                "token" => $token,
                "role" => $role
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "User login failed!"
            ]);
        }
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Something went wrong!"
        ]);
        die();
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "Email/Full Name and password are required!"
    ]);
}
