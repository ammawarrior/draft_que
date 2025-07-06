<?php

include('temp_db.php');

$labNames = [
    1 => 'Metrology Calibration',
    2 => 'Chemical Analysis',
    3 => 'Microbiological Analysis',
    4 => 'Shelf-life Analysis',
    5 => 'Get Certificates',
    6 => 'General Inquiry'
];

// Get all users with role 1 (Admin), 6 (Sample Receiving/Releasing), or 7 (Inquiry Counter)
$tellerQuery = $conn->query("SELECT user_id, code_name FROM users WHERE role IN (6, 7)");

while ($teller = $tellerQuery->fetch_assoc()) {
    $user_id = $teller['user_id'];
    $code_name = htmlspecialchars($teller['code_name']);

    // Get currently serving info
    $stmt = $conn->prepare("
        SELECT s.unique_id, s.full_name, s.lab_id
        FROM currently_serving cs
        LEFT JOIN submissions s ON cs.submission_id = s.submission_id
        WHERE cs.user_id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $serving = $result->fetch_assoc();

    // Start building the customer info
    if ($serving && $serving['unique_id']) {
        $unique_id = htmlspecialchars($serving['unique_id']);
        $full_name = htmlspecialchars($serving['full_name']);
        $lab = $labNames[$serving['lab_id']] ?? "Unknown Lab";

        // Unique ID glows, name + lab underneath
        $info = "
    <span class='customer-info glow'>{$unique_id}</span><br>
    <span class='full-name'>{$full_name}</span>
";

    } else {
        // Not serving anyone
        $info = "<span class='customer-info'>--- - -----</span>";
    }

    // Output each teller box
    echo "
    <div class='teller-box'>
        <div class='teller-name'>{$code_name}</div>
        <div>{$info}</div>
    </div>";
}
?>
