<?php

include "../database/connection.php";

if (
    isset(
        $_POST['email'],
        $_POST['password'],
        $_POST['fullname']
    )

) {
    $email =  $_POST['email'];
    $password = $_POST['password'];
    $fullname = $_POST['fullname'];

    global $CON;

    $sql = "select * from users where email ='$email'";

    $result = mysqli_query($CON, $sql);


    if ($result) {
        $count = mysqli_num_rows($result);
        if ($count > 0) {
            echo json_encode([
                "success" => false,
                "message" => "User already exists!"
            ]);
            die();
        }

        $encrypted_password = password_hash($password, PASSWORD_DEFAULT);


        $sql = "insert into users (email,password,full_name,role) values ('$email','$encrypted_password','$fullname','user')";
        $result = mysqli_query($CON, $sql);

        if ($result) {
            echo json_encode([
                "success" => true,
                "message" => "User registered Successfully!"
            ]);
        } else {

            echo json_encode([
                "success" => false,
                "message" => "User registration failed!"
            ]);
        }
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Something went wrong!"
        ]);
    }


    // echo json_encode(array([
    //     "success" => true,
    //     "message" => "User registered Successfully!"
    // ]));
} else {

    echo json_encode([
        "success" => false,
        "message" => "email, password and fullname is required"
    ]);
}
