<?php
header("Access-Control-Allow-Origin: *");

// Allow the following methods from any origin
header("Access-Control-Allow-Methods: POST");

// Allow the following headers from any origin
header("Access-Control-Allow-Headers: Content-Type");
include "./database/connection.php";
include "./helpers/auth.php";

header('Content-Type: application/json');

// Improved error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Check if the token is present
if (!isset($_POST['token'])) {
    echo json_encode(["success" => false, "message" => "Token not provided"]);
    exit;
}

$token = $_POST['token'];

// Authenticate User with Token and get user ID
$userId = getUserId($token);
if (!$userId) {
    echo json_encode(["success" => false, "message" => "Invalid token"]);
    exit;
}

// Initialize variables for possible image update
$imageUpdated = false;
$profileImagePath = '';

// Check if a new image was uploaded
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
    $profileImage = $_FILES['profile_image'];
    $imageName = $profileImage['name'];
    $imageTmpName = $profileImage['tmp_name'];
    $imageSize = $profileImage['size'];
    $fileExt = explode('.', $imageName);
    $fileActualExt = strtolower(end($fileExt));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (in_array($fileActualExt, $allowed) && $imageSize <= 1000000) {
        $imageNameNew = uniqid('', true).".".$fileActualExt;
        $fileDestination = './images/'.$imageNameNew;
        if (move_uploaded_file($imageTmpName, $fileDestination)) {
            $imageUpdated = true;
            $profileImagePath = $fileDestination; // This path should be saved in the database
        } else {
            echo json_encode(["success" => false, "message" => "Failed to move uploaded file"]);
            exit;
        }
    } else {
        $errorMsg = $imageSize > 1000000 ? "Your file is too large" : "You cannot upload files of this type";
        echo json_encode(["success" => false, "message" => $errorMsg]);
        exit;
    }
}

// Prepare SQL query
$sql = "UPDATE users SET ";
$params = [];
$types = "";

// Dynamically construct SQL query based on provided fields
if (isset($_POST['full_name'])) {
    $sql .= "full_name = ?, ";
    $params[] = $_POST['full_name'];
    $types .= "s";
}

if (isset($_POST['email'])) {
    $sql .= "email = ?, ";
    $params[] = $_POST['email'];
    $types .= "s";
}

if (isset($_POST['date_of_birth'])) {
    $sql .= "date_of_birth = ?, ";
    $params[] = $_POST['date_of_birth'];
    $types .= "s";
}

if (isset($_POST['address'])) {
    $sql .= "address = ?, ";
    $params[] = $_POST['address'];
    $types .= "s";
}

if (isset($_POST['description'])) {
    $sql .= "description = ?, ";
    $params[] = $_POST['description'];
    $types .= "s";
}

if ($imageUpdated) {
    $sql .= "profile_image = ?, ";
    $params[] = $profileImagePath;
    $types .= "s";
}

// Remove trailing comma and space
$sql = rtrim($sql, ", ");

$sql .= " WHERE user_id = ?";
$params[] = $userId;
$types .= "i";

$stmt = mysqli_prepare($CON, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);

// Execute the statement and check for errors
if (mysqli_stmt_execute($stmt)) {
    echo json_encode(["success" => true, "message" => "User details updated successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to update user details"]);
}

mysqli_stmt_close($stmt);
mysqli_close($CON);

?>
