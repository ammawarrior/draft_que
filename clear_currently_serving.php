<?php
session_start();
include('temp_db.php');

if (!isset($_SESSION['user_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];

$clear = $conn->prepare("UPDATE currently_serving SET submission_id = NULL WHERE user_id = ?");
$clear->bind_param("i", $user_id);
$clear->execute();

echo json_encode(['success' => true]);
