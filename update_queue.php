<?php
include 'temp_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submission_id = intval($_POST['submission_id']);
    $query = "UPDATE submissions SET queue = 2 WHERE submission_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $submission_id);
    $stmt->execute();

    echo json_encode(['success' => true]);
}
?>
