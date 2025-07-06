<?php
include 'temp_db.php';
session_start();

// Get the user's role from the session
$role = $_SESSION['role'];

// Prepare the priority levels based on the role
$priority_levels = [];
if ($role == 7) {
    // Role 7: Only priority 3, 2, 1
    $priority_levels = [3, 2, 1];
} elseif (in_array($role, [1, 6])) {
    // Roles 1 and 6: Priority 5, 4, 3, 2, 1
    $priority_levels = [5, 4, 3, 2, 1];
}

if (empty($priority_levels)) {
    echo json_encode(['success' => false, 'message' => 'Role does not have access to any queue priorities.']);
    exit();
}

// Build the query dynamically with the selected priorities
$placeholders = implode(',', array_fill(0, count($priority_levels), '?'));
$query = "SELECT submission_id, unique_id, priority 
          FROM submissions 
          WHERE status = 2 
            AND queue = 1 
            AND DATE(submission_date_selected) = CURDATE() 
            AND priority IN ($placeholders) 
          ORDER BY priority DESC";


// Prepare the query and bind parameters
$stmt = $conn->prepare($query);
$stmt->bind_param(str_repeat('i', count($priority_levels)), ...$priority_levels);
$stmt->execute();
$result = $stmt->get_result();

// Priority labels
$priority_labels = [
    5 => "High Priority",
    4 => "Medium High Priority",
    3 => "Medium Priority",
    2 => "Medium Low Priority",
    1 => "Low Priority"
];

// Generate the queue list
while ($customer = $result->fetch_assoc()) {
    $priority_label = $priority_labels[$customer['priority']] ?? "Unknown Priority";
    echo '<li class="list-group-item d-flex justify-content-between align-items-center" data-id="' . $customer['submission_id'] . '">';
    echo '<span>' . htmlspecialchars($customer['unique_id']) . '</span>';
    echo '<span class="badge badge-primary">' . $priority_label . '</span>';
    echo '</li>';
}
?>
