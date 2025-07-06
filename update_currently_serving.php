<?php
session_start();
include('temp_db.php');

if (!isset($_SESSION['user_id']) || !isset($_POST['submission_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$user_id = $_SESSION['user_id'];
$submission_id = $_POST['submission_id'];

// Check if already exists
$stmt = $conn->prepare("SELECT * FROM currently_serving WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Update
    $update = $conn->prepare("UPDATE currently_serving SET submission_id = ?, started_at = CURRENT_TIMESTAMP WHERE user_id = ?");
    $update->bind_param("ii", $submission_id, $user_id);
    $update->execute();
} else {
    // Insert
    $insert = $conn->prepare("INSERT INTO currently_serving (user_id, submission_id) VALUES (?, ?)");
    $insert->bind_param("ii", $user_id, $submission_id);
    $insert->execute();
}

echo json_encode(['success' => true]);
