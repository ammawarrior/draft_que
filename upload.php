<?php
include('temp_db.php');

$target_dir = "uploads/videos/";
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
}

if (isset($_FILES["file"])) {
    $file = $_FILES["file"];

    // Check file type
    $fileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    if ($fileType != "mp4") {
        echo "Only MP4 files are allowed.";
        exit;
    }

    // Unique filename
    $fileName = uniqid("video_", true) . ".mp4";
    $target_file = $target_dir . $fileName;

    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        // Save to database
        $stmt = $conn->prepare("INSERT INTO video_ads (file_path) VALUES (?)");
        $stmt->bind_param("s", $target_file);
        $stmt->execute();
        $stmt->close();

        header("Location: queue_dashboard.php?upload=success");
        exit;
    } else {
        echo "Error uploading the file.";
    }
} else {
    echo "No file uploaded.";
}
?>
