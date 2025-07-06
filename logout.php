<?php
session_start();
include 'temp_db.php'; // Database connection

// Function to log user activity
function logActivity($conn, $user_id, $activity) {
    $stmt = $conn->prepare("INSERT INTO user_activity (user_id, activity, timestamp) VALUES (?, ?, NOW())");
    $stmt->bind_param("is", $user_id, $activity);
    $stmt->execute();
    $stmt->close();
}

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Clear currently serving for this user
    $stmt = $conn->prepare("DELETE FROM currently_serving WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    // Log user logout
    logActivity($conn, $user_id, "User logged out");

    // Destroy session
    session_unset();
    session_destroy();
}

// Redirect to login page
header("Location: login.php");
exit();
?>
