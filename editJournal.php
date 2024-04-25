<?php
header("Access-Control-Allow-Origin: *");

// Allow the following methods from any origin
header("Access-Control-Allow-Methods: POST");

// Allow the following headers from any origin
header("Access-Control-Allow-Headers: Content-Type");
include "./database/connection.php";
include "./helpers/auth.php";

// Improved error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (isset($_POST['token'], $_POST['journal_id'])) {
    $token = $_POST['token'];
    $journalId = $_POST['journal_id'];

    global $CON;

    // Authenticate user with token
    $userId = getUserId($token); // This function validates the token and returns the user ID
    if (!$userId) {
        echo json_encode(["success" => false, "message" => "Invalid token!"]);
        exit();
    }

    // Fetch existing journal data
    $existingDataQuery = "SELECT title, entry, location, mood, privacy FROM journals WHERE journal_id = ? AND user_id = ?";
    $stmt = mysqli_prepare($CON, $existingDataQuery);
    mysqli_stmt_bind_param($stmt, 'ii', $journalId, $userId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $existingTitle, $existingEntry, $existingLocation, $existingMood, $existingPrivacy);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    // Check POST data, use existing data if POST data is empty
    $title = !empty($_POST['title']) ? $_POST['title'] : $existingTitle;
    $entry = !empty($_POST['entry']) ? $_POST['entry'] : $existingEntry;
    $location = !empty($_POST['location']) ? $_POST['location'] : $existingLocation; // Use isset to allow empty string updates
    $mood = !empty($_POST['mood']) ? $_POST['mood'] : $existingMood; // Use isset to allow empty string updates
    // Ensure the privacy setting is either 'public' or 'private', default to existing privacy if not provided or invalid
    $privacy = (!empty($_POST['privacy']) && in_array($_POST['privacy'], ['public', 'private'])) ? $_POST['privacy'] : $existingPrivacy;

    $imageUpdated = false;
    $featuredImagePath = '';

    // Image upload logic goes here (omitted for brevity, but it's the same as in your original script)

    // Update SQL statement to include dynamic image path and privacy setting
    $sql = "UPDATE journals SET title = ?, entry = ?, location = ?, mood = ?, privacy = ?";
    $types = 'sssss'; // Types of the parameters, add 's' for privacy
    $params = [$title, $entry, $location, $mood, $privacy]; // Parameters, add privacy

    if ($imageUpdated) {
        $sql .= ", featured_image = ?";
        $types .= 's'; // Add string type for the image path
        $params[] = $featuredImagePath;
    }

    $sql .= " WHERE journal_id = ? AND user_id = ?";
    $types .= 'ii'; // Add integer types for journal ID and user ID
    $params[] = $journalId;
    $params[] = $userId;

    $stmt = mysqli_prepare($CON, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(["success" => true, "message" => "Journal entry updated successfully!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update journal entry"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Required fields are missing"]);
}

?>
