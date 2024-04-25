<?php
include "./database/connection.php"; // Ensure this points to your actual database connection script

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

// Allow the following methods from any origin
header("Access-Control-Allow-Methods: POST");

// Allow the following headers from any origin
header("Access-Control-Allow-Headers: Content-Type");
// Function to fetch public journals along with user details and like counts
function fetchPublicJournals($con) {
    $journals = [];
    
    $sql = "SELECT j.journal_id, j.title, j.entry, j.location, j.mood, j.featured_image, j.created_at, j.privacy, 
    u.user_id, u.full_name, u.profile_image, 
    (SELECT COUNT(*) FROM likes l WHERE l.journal_id = j.journal_id) as like_count,
    EXISTS(SELECT 1 FROM likes l WHERE l.journal_id = j.journal_id AND l.user_id = ?) as is_liked
FROM journals j
JOIN users u ON j.user_id = u.user_id
WHERE j.privacy = 'public'
ORDER BY j.created_at DESC";
    $result = mysqli_query($con, $sql);

    if ($stmt = mysqli_prepare($con, $sql)) {
        // Bind the user ID to the prepared statement
        mysqli_stmt_bind_param($stmt, 'i', $userId);

        // Execute the query
        mysqli_stmt_execute($stmt);

        // Store the result so we can check the row count
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $journals[] = [
                    'journal_id' => $row['journal_id'],
                    'title' => $row['title'],
                    'entry' => $row['entry'],
                    'location' => $row['location'],
                    'mood' => $row['mood'],
                    'featured_image' => $row['featured_image'],
                    'created_at' => $row['created_at'],
                    'full_name' => $row['full_name'],
                    'profile_image' => $row['profile_image'],
                    'like_count' => $row['like_count'],
                    'is_liked' => $row['is_liked'],
                ];
            }
        }

        // Close the statement
        mysqli_stmt_close($stmt);
    }

    return $journals;
}

// Main logic to execute function and return JSON response
try {
    $publicJournals = fetchPublicJournals($GLOBALS['CON']); // Replace $GLOBALS['CON'] with your actual connection variable if different
    echo json_encode(["success" => true, "data" => $publicJournals]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "An error occurred while fetching the journals."]);
}
?>
