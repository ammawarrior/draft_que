<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$role = intval($_SESSION['role']); // Ensure role is an integer

include('includes/header.php');
include 'temp_db.php';









// ✅ Add this block to fetch and store code_name
if (!isset($_SESSION['code_name'])) {
    $user_id = $_SESSION['user_id'];
    $query_user = "SELECT code_name FROM users WHERE user_id = '$user_id'";
    $result_user = mysqli_query($conn, $query_user);
    
    if ($result_user && mysqli_num_rows($result_user) > 0) {
        $row_user = mysqli_fetch_assoc($result_user);
        $_SESSION['code_name'] = $row_user['code_name'];
    } else {
        $_SESSION['code_name'] = "the teller"; // fallback
    }
}

// Fetch queue data from database (status = 2 and in queue)
$query = "SELECT submission_id, unique_id, priority 
          FROM submissions 
          WHERE status = 2 
            AND queue = 1 
            AND DATE(submission_date_selected) = CURDATE() 
          ORDER BY priority DESC";

$result = mysqli_query($conn, $query);

$queue = [];
while ($row = mysqli_fetch_assoc($result)) {
    $priority = intval($row['priority']);

    $can_access = false;

    if (in_array($priority, [4, 5]) && in_array($role, [1, 6])) {
        $can_access = true;
    } elseif (in_array($priority, [3, 2, 1]) && in_array($role, [1, 6, 7])) {
        $can_access = true;
    }

    if ($can_access) {
        $queue[] = $row;
    }
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="assets/modules/datatables/datatables.min.css">
    <link rel="stylesheet" href="assets/modules/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="assets/modules/datatables/Select-1.2.4/css/select.bootstrap4.min.css">
    <link rel="icon" type="image/png" href="assets/img/dost.png">

    <style>
/* Background blur when modal is open */
body.modal-blur::before {
    content: "";
    position: fixed;
    z-index: 1040;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    backdrop-filter: blur(6px);
    background: rgba(0, 0, 0, 0.3);
    pointer-events: none;
}

/* Custom Modal Styling */
.custom-modal .modal-dialog {
    max-width: 600px; /* Made it wider */
    margin: 0 auto;
    height: 100vh;
    display: flex;
    align-items: center; /* Vertically center */
    justify-content: center;
}

.custom-modal .modal-content {
    background: #ffffffcc; /* Optional: translucent background for glassy look */
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    padding: 30px;
    animation: fadeIn 0.3s ease-in-out;
    backdrop-filter: blur(10px); /* Extra polish */
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.custom-modal .modal-header {
    border-bottom: none;
    padding-bottom: 0;
}

.custom-modal .modal-title {
    font-size: 2rem;
    font-weight: bold;
    color: #343a40;
}

.custom-modal .modal-body p {
    font-size: 1.3rem;
    color: #495057;
}

.custom-modal .modal-footer {
    border-top: none;
    justify-content: center;
    padding-top: 0;
}

.custom-modal .btn-success {
    padding: 12px 24px;
    font-size: 1.1rem;
    border-radius: 12px;
}

.custom-modal .close {
    font-size: 1.8rem;
    color: #6c757d;
    opacity: 1;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>


</head>
<body class="layout-4">
<div class="page-loader-wrapper">
        <span class="loader"><span class="loader-inner"></span></span>
    </div>
    <div id="app">
        <div class="main-wrapper main-wrapper-1">
            <?php include('includes/topnav.php'); ?>
            <?php include('includes/sidebar.php'); ?>

            <div class="main-content">
                <section class="section">
                <div class="section-header d-flex justify-content-between align-items-center">
    <h1>Reservations Dashboard</h1>
    <div id="current-datetime" style="font-size: 1.1rem; font-weight: 500;"></div>
</div>
                    <div class="section-body">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-md-6">
                                    <!-- Teller Interface -->
                                    <div class="card shadow-sm p-3">
                                        <h4 class="text-center">Teller Interface</h4>
                                        <div class="text-center mt-3">
                                            <h5 id="current-customer">Waiting for next customer...</h5>
                                        </div>

                                        <div class="text-center mt-3">
                                            <button id="call-next" class="btn btn-primary">Next</button>
                                        </div>
                                    </div>

                                    <!-- File Upload -->
                                    <div class="card shadow-sm p-3 mt-3">
                                        <h4 class="text-center">Upload File</h4>
                                        <form action="upload.php" method="POST" enctype="multipart/form-data">
                                            <div class="form-group">
                                                <label for="file">Choose file:</label>
                                                <input type="file" class="form-control" name="file" id="file" required>
                                            </div>
                                            <div class="text-center mt-3">
                                                <button type="submit" class="btn btn-success">Upload</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <!-- Queue List -->
                                <div class="col-md-6">
                                    <div class="card shadow-sm p-3">
                                        <h4 class="text-center">Queue List</h4>
                                        <ul id="queue-list" class="list-group mt-3">
                                            <?php foreach ($queue as $customer): ?>
                                                <?php
                                                    $priority_levels = [
                                                        5 => "High Priority",
                                                        4 => "Medium High Priority",
                                                        3 => "Medium Priority",
                                                        2 => "Medium Low Priority",
                                                        1 => "Low Priority"
                                                    ];
                                                    $priority_label = $priority_levels[$customer['priority']] ?? "Unknown Priority";
                                                ?>
                                                <li class="list-group-item d-flex justify-content-between align-items-center" data-id="<?php echo $customer['submission_id']; ?>">
                                                    <span><?php echo htmlspecialchars($customer['unique_id']); ?></span>
                                                    <span class="badge badge-primary"><?php echo $priority_label; ?></span>
                                                </li>

                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
            <?php include('includes/footer.php'); ?>
        </div>
    </div>

    <audio id="notify-sound" src="assets/sounds/alarm.mp3" preload="auto"></audio>



   <!-- Next Customer Modal -->
<div class="modal custom-modal" id="nextCustomerModal" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content text-center">
      <div class="modal-header border-0 d-block">
        <h2 class="modal-title w-100 font-weight-bold">Now Serving</h2>
      </div>
      <div class="modal-body">
        <h3 id="next-customer-id" class="font-weight-bold mb-4">TTC-00000</h3>
        <p><strong>Lab:</strong> <span id="lab-name">Loading...</span></p>
        <p><strong>Category:</strong> <span id="category">Loading...</span></p>
        <p><strong>Customer Name:</strong> <span id="full-name">Loading...</span></p>
        <input type="hidden" id="next-submission-id">
      </div>
      <div class="modal-footer justify-content-center border-0">
      <button class="btn btn-info px-5 py-2 mr-2" id="notify-btn">Notify</button>
    <button class="btn btn-success px-5 py-2" id="done-btn">Done</button>
</div>

    </div>
  </div>
</div>





    <script src="assets/bundles/lib.vendor.bundle.js"></script>
    <script src="js/CodiePie.js"></script>
    <script src="assets/modules/datatables/datatables.min.js"></script>
    <script src="assets/modules/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js"></script>
    <script src="assets/modules/datatables/Select-1.2.4/js/dataTables.select.min.js"></script>
    <script src="assets/modules/jquery-ui/jquery-ui.min.js"></script>
    <script src="assets/modules/sweetalert/sweetalert.min.js"></script>
    <script src="js/page/modules-datatables.js"></script>
    <script src="js/page/modules-sweetalert.js"></script>
    <script src="js/scripts.js"></script>
    <script src="js/custom.js"></script>

    <script>
document.getElementById('call-next').addEventListener('click', function () {
    const callNextBtn = this;
    callNextBtn.disabled = true;

    fetch('get_next_customer.php', {
        method: 'POST'
    })
    .then(res => res.json())
    .then(data => {
        callNextBtn.disabled = false;

        if (data.success) {
            const customer = data.customer;

            // Map lab_id to lab name
            const labNames = {
                1: 'Metrology Calibration',
                2: 'Chemical Analysis',
                3: 'Microbiological Analysis',
                4: 'Shelf-life Analysis',
                5: 'Get Certificates',
                6: 'General Inquiry'
            };

            // Set values in modal
            document.getElementById('next-submission-id').value = customer.submission_id;
            document.getElementById('next-customer-id').textContent = customer.unique_id;
            document.getElementById('lab-name').textContent = labNames[customer.lab_id] || "Unknown Lab";
            document.getElementById('category').textContent = customer.category || "N/A";
            document.getElementById('full-name').textContent = customer.full_name || "N/A";

            // ➕ Teller is now serving this customer
            fetch('update_currently_serving.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `submission_id=${customer.submission_id}`
            });

            $('#nextCustomerModal').modal('show');
        } else {
            alert(data.message || "No customer in queue.");
        }
    })
    .catch(() => {
        callNextBtn.disabled = false;
        alert("Error fetching next customer.");
    });
});

document.getElementById('done-btn').addEventListener('click', function () {
    const submissionId = document.getElementById('next-submission-id').value;

    fetch('update_queue.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `submission_id=${submissionId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            $('#nextCustomerModal').modal('hide');
            refreshQueueList();

            // ➖ Teller is done serving the customer
            fetch('clear_currently_serving.php', {
                method: 'POST'
            });
        } else {
            alert("Failed to update queue. Try again.");
        }
    });
});

function refreshQueueList() {
    fetch('get_queue.php')
        .then(response => response.text())
        .then(html => {
            document.getElementById('queue-list').innerHTML = html;
        });
}

// Automatically refresh queue list every 5 seconds
setInterval(refreshQueueList, 100);

// Blur effect when modal is shown
$('#nextCustomerModal').on('shown.bs.modal', function () {
    $('body').addClass('modal-blur');
});

$('#nextCustomerModal').on('hidden.bs.modal', function () {
    $('body').removeClass('modal-blur');
});

document.getElementById('notify-btn').addEventListener('click', function () {
    const audio = document.getElementById('notify-sound');
    const fullName = document.getElementById('full-name').textContent.trim();
    const tellerName = "<?php echo $_SESSION['code_name'] ?? 'the teller'; ?>";

    // Play beep sound first
    audio.play();

    // After beep is done, speak message
    audio.onended = function () {
        const message = `${fullName}, please proceed to ${tellerName} please. again, ${fullName}, please proceed to ${tellerName} please.`;

        const utterance = new SpeechSynthesisUtterance(message);
        utterance.lang = 'en-PH';
        utterance.pitch = 1;
        utterance.rate = 1;
        speechSynthesis.speak(utterance);
    };
});

function updateDateTime() {
        const now = new Date();

        // Formatting date and time
        const dateOptions = { year: 'numeric', month: 'long', day: 'numeric' };
        const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };

        // Get the formatted date and time
        const dateStr = now.toLocaleDateString('en-US', dateOptions);  // e.g., "April 10, 2025"
        const timeStr = now.toLocaleTimeString('en-US', timeOptions);  // e.g., "09:32:13 AM"

        // Combine date and time with '|' separator and update the content of #current-datetime
        document.getElementById('current-datetime').textContent = `${dateStr} | ${timeStr}`;
    }

    // Update both date and time every second
    setInterval(updateDateTime, 1000);

    // Initial call to display date and time immediately
    updateDateTime();

</script>




   
</body>
</html>
