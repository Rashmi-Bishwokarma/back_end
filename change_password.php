<?php
header("Access-Control-Allow-Origin: *");

// Allow the following methods from any origin
header("Access-Control-Allow-Methods: POST");

// Allow the following headers from any origin
header("Access-Control-Allow-Headers: Content-Type");
include "./database/connection.php";

if (isset($_POST['token'], $_POST['old_password'], $_POST['new_password'])) {

    $token = $_POST['token'];
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];

    global $CON;

    // Authenticate User with Token
    $userId = authenticateUserWithToken($token);
    if (!$userId) {
        echo json_encode([
            "success" => false,
            "message" => "Invalid token!"
        ]);
        die();
    }

    // Verify Old Password
    $sql = "SELECT password FROM users WHERE user_id = '$userId'";
    $result = mysqli_query($CON, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $hashed_old_password = $row['password'];
        if (!password_verify($old_password, $hashed_old_password)) {
            echo json_encode([
                "success" => false,
                "message" => "Old password is incorrect!"
            ]);
            die();
        }

        // Update Password
        $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_sql = "UPDATE users SET password = '$hashed_new_password' WHERE user_id = '$userId'";
        if (mysqli_query($CON, $update_sql)) {
            echo json_encode([
                "success" => true,
                "message" => "Password updated successfully!"
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Failed to update password!"
            ]);
        }
    } else {
        echo json_encode([
            "success" => false,
            "message" => "User not found!"
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "Token, old password, and new password are required!"
    ]);
}

function authenticateUserWithToken($token) {
    global $CON;
    $sql = "SELECT user_id FROM personal_access_token WHERE token = '$token'";
    $result = mysqli_query($CON, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['user_id'];
    }

    return false;
}

?>
