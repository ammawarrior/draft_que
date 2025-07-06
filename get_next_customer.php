<?php
include 'temp_db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the user's role from the session
    session_start();
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

    $conn->begin_transaction();

    try {
        // Lock the row to prevent race conditions, and filter based on allowed priority levels
        $placeholders = implode(',', array_fill(0, count($priority_levels), '?'));
        $query = "SELECT submission_id, unique_id, priority, lab_id, category, full_name 
        FROM submissions 
        WHERE status = 2 
          AND queue = 1 
          AND DATE(submission_date_selected) = CURDATE()
          AND priority IN ($placeholders) 
        ORDER BY priority DESC, submission_date_selected ASC 
        LIMIT 1 
        FOR UPDATE";


        // Prepare the statement and bind the parameters for priority levels
        $stmt = $conn->prepare($query);
        $stmt->bind_param(str_repeat('i', count($priority_levels)), ...$priority_levels);
        $stmt->execute();
        $result = $stmt->get_result();
        $customer = $result->fetch_assoc();

        if ($customer) {
            // Update the customer to mark as 'called'
            $update = $conn->prepare("UPDATE submissions SET queue = 2 WHERE submission_id = ?");
            $update->bind_param("i", $customer['submission_id']);
            $update->execute();

            $conn->commit();

            echo json_encode([
                'success' => true,
                'customer' => [
                    'submission_id' => $customer['submission_id'],
                    'unique_id'     => $customer['unique_id'],
                    'lab_id'        => $customer['lab_id'],
                    'category'      => $customer['category'],
                    'full_name'     => $customer['full_name']
                ]
            ]);
        } else {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'No customer found.']);
        }
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Error occurred.']);
    }
}
?>
