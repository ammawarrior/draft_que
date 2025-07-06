<?php


include('includes/header.php');
include('temp_db.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Now Serving</title>
    <link rel="stylesheet" href="assets/modules/bootstrap/css/bootstrap.min.css">
    <link rel="icon" type="image/png" href="assets/img/dost.png">
    <style>
        

       html, body {
        background: linear-gradient(-45deg, #0d4e86, #1A2980, #26D0CE);
        animation: gradientAnimation 20s ease infinite;
    margin: 0;
    padding: 0;
    height: 100%;
    font-family: 'Aptos', 'Segoe UI', sans-serif;
}


.main-container {
    display: flex;
    height: 100vh;
    gap: 20px;
    padding: 30px;
    box-sizing: border-box;
}

.left-panel {
    flex: 1;
    background-color: #f0f8ff; /* Alice Blue */
    border: 2px solid #d0e1f9;
    animation: gradientAnimation 20s ease infinite;
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
    padding: 30px 25px;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
}

.left-panel h2 {
    font-weight: 700;
    font-size: 2.8rem;
    text-align: center;
    margin-bottom: 25px;
    color: #0d4e86;
    letter-spacing: 0.5px;
}

.teller-box {
    background: linear-gradient(-45deg, #0d4e86, #1A2980, #26D0CE);
    animation: gradientAnimation 20s ease infinite;
    border: 1px solid #ccc;
    border-radius: 16px;
    padding: 20px;
    
    width: 100%;
    text-align: center;
    font-family: 'Segoe UI', sans-serif;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    display: flex;
    flex-direction: column;
    justify-content: center;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}



.teller-name {
    font-size: 1.73rem;
    font-weight: 600;
    color: white; /* text color */
    padding-bottom: 8px;
    border-bottom: 1px solid #aaa;
    margin-bottom: 10px;
}

.customer-info {
    font-size: 1.8rem;
    font-weight: bold;
    color: white; /* text color */
    margin-bottom: 5px;
}

.right-panel {
    flex: 3;
    background-color: rgb(255, 255, 255);
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    display: flex;
    justify-content: center;
    align-items: center;
    overflow: hidden;
}

video {
    width: 100%;
    height: 100%;
    object-fit: contain;
    background-color: white;
    border-radius: 16px;
}


.ad-placeholder {
    font-size: 1.8rem;
    font-weight: 600;
    color: #607d8b;
}



@media (max-width: 768px) {
    .main-container {
        flex-direction: column;
        height: auto;
    }
}
.full-name {
    font-size: 1.1rem;
    font-weight: 500;
    color: white; /* text color */
}
@keyframes pulseGlow {
    0%, 100% {
        box-shadow: 0 0 10px rgba(25, 118, 210, 0.3);
    }
    50% {
        box-shadow: 0 0 20px rgba(25, 118, 210, 0.6);
    }
}
#now-serving-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
    align-items: center;
}



    </style>
</head>
<body>
<audio id="notify-sound" src="assets/sounds/beep.mp3" preload="auto"></audio>
<div class="main-container">
    <div class="left-panel">
        <h2>Now Serving</h2>
        <div id="now-serving-list">
            <!-- Dynamically loaded teller boxes here -->
        </div>
    </div>
    <div class="right-panel">
    <?php
// Fetch the most recently uploaded video
$videoQuery = "SELECT file_path FROM video_ads ORDER BY uploaded_at DESC LIMIT 1";
$videoResult = mysqli_query($conn, $videoQuery);
$videoPath = null;

if ($videoResult && mysqli_num_rows($videoResult) > 0) {
    $row = mysqli_fetch_assoc($videoResult);
    $videoPath = $row['file_path'];
}
?>

<?php if ($videoPath): ?>
    <video autoplay muted loop style="width: 100%; height: 100%; object-fit: cover; border-radius: 16px;">

        <source src="<?php echo $videoPath; ?>" type="video/mp4">
        Your browser does not support the video tag.
    </video>
<?php else: ?>
    <div class="ad-placeholder">No video uploaded yet.</div>
<?php endif; ?>

    </div>
</div>


<script>
function fetchNowServing() {
    fetch('fetch_currently_serving.php')
        .then(response => response.text())
        .then(html => {
            document.getElementById('now-serving-list').innerHTML = html;
        });
}



// Load every 2 seconds
setInterval(fetchNowServing, 2000);
fetchNowServing();
</script>

</body>
</html>
