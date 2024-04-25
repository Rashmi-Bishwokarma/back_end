<?php
header("Access-Control-Allow-Origin: *");

// Allow the following methods from any origin
header("Access-Control-Allow-Methods: POST");

// Allow the following headers from any origin
header("Access-Control-Allow-Headers: Content-Type");
include "./database/connection.php";

function authenticateUserWithToken($token) {
    global $CON;
    $sql = "SELECT user_id FROM personal_access_token WHERE token = ?";
    $stmt = mysqli_prepare($CON, $sql);
    mysqli_stmt_bind_param($stmt, 's', $token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['user_id'];
    }
    return false;
}

function parseHashtags($text) {
    preg_match_all('/#(\w+)/', $text, $matches);
    return array_unique($matches[1]);
}

function insertTag($tag) {
    global $CON;
    $sql = "SELECT tag_id FROM tags WHERE name = ?";
    $stmt = mysqli_prepare($CON, $sql);
    mysqli_stmt_bind_param($stmt, 's', $tag);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        return $row['tag_id'];
    } else {
        $insertSql = "INSERT INTO tags (name) VALUES (?)";
        $insertStmt = mysqli_prepare($CON, $insertSql);
        mysqli_stmt_bind_param($insertStmt, 's', $tag);
        mysqli_stmt_execute($insertStmt);
        return mysqli_insert_id($CON);
    }
}

function linkTagWithJournal($journalId, $tagId) {
    global $CON;
    $sql = "INSERT INTO journal_tags (journal_id, tag_id) VALUES (?, ?)";
    $stmt = mysqli_prepare($CON, $sql);
    mysqli_stmt_bind_param($stmt, 'ii', $journalId, $tagId);
    mysqli_stmt_execute($stmt);
}

$token = $_POST['token'] ?? '';
$title = $_POST['title'] ?? '';
$entry = $_POST['entry'] ?? '';
$location = $_POST['location'] ?? null;
$mood = $_POST['mood'] ?? null;
$privacy = isset($_POST['privacy']) && in_array($_POST['privacy'], ['public', 'private']) ? $_POST['privacy'] : 'private'; // Default to 'private' if not provided or invalid
$userId = authenticateUserWithToken($token);
$featuredImagePath = null;

if (!$userId) {
    echo json_encode(["success" => false, "message" => "Invalid token!"]);
    exit();
}

if (empty($entry)) {
    $entry = "Nothing in this journal"; // Default text for empty entry
}
if (empty($title)) {
    // Query to find the highest number used in "Untitled" titles
    $untitledQuery = "SELECT title FROM journals WHERE title REGEXP '^Untitled [0-9]+$' ORDER BY LENGTH(title) DESC, title DESC LIMIT 1";
    $untitledResult = mysqli_query($CON, $untitledQuery);

    $untitledNumber = 1; // Default if no untitled entries are found
    if ($untitledRow = mysqli_fetch_assoc($untitledResult)) {
        // Extract the number from the last "Untitled" title and increment it
        $lastNumber = (int) filter_var($untitledRow['title'], FILTER_SANITIZE_NUMBER_INT);
        $untitledNumber = $lastNumber + 1;
    }
    
    $title = "Untitled $untitledNumber"; // Assign the new title
}

if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] !== UPLOAD_ERR_NO_FILE) {
    $featuredImage = $_FILES['featured_image'];
    $imageError = $featuredImage['error'];
    $imageName = $featuredImage['name'];
    $imageTmpName = $featuredImage['tmp_name'];
    $imageSize = $featuredImage['size'];

    $fileExt = explode('.', $imageName);
    $fileActualExt = strtolower(end($fileExt));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($fileActualExt, $allowed) || $imageError !== 0 || $imageSize > 1000000) {
        echo json_encode(["success" => false, "message" => "Invalid image!"]);
        exit();
    }

    $imageNameNew = uniqid('', true) . "." . $fileActualExt;
    $fileDestination = './images/' . $imageNameNew;

    if (!move_uploaded_file($imageTmpName, $fileDestination)) {
        echo json_encode(["success" => false, "message" => "Failed to move uploaded file."]);
        exit();
    }

    $featuredImagePath = $fileDestination;
}
$sql = "INSERT INTO journals (user_id, title, entry, location, mood, featured_image, privacy, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
$stmt = mysqli_prepare($CON, $sql);
mysqli_stmt_bind_param($stmt, 'sssssss', $userId, $title, $entry, $location, $mood, $featuredImagePath, $privacy);

if (mysqli_stmt_execute($stmt)) {
    $journalId = mysqli_insert_id($CON);

    // Handle hashtag functionality.
    $hashtags = parseHashtags($entry);
    foreach ($hashtags as $hashtag) {
        $tagId = insertTag($hashtag);
        linkTagWithJournal($journalId, $tagId);
    }
    echo json_encode(["success" => true, "message" => "Journal entry added successfully!", "journalId" => $journalId]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to add journal entry!"]);
}

?>