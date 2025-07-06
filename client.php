<?php
require 'temp_db.php'; // Adjust the path if needed

// Function to generate a 5-digit random transaction code
function generateTransactionCode($prefix) {
    return $prefix . '-' . str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
}

// Initialize variables
$full_name = $transaction_code = "";
$lab_id = 0;
$category = "";
$quantity = "";

$address = "";
$contact_number = "";
$email_address = "";
$request_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $_POST["full_name"];
    $lab_id = $_POST["lab_id"];
    
    // Define transaction code prefix
    $prefixes = [
        1 => "WIC", // Items for Calibration
        4 => "WSL", // Shelf-Life
        5 => "WGC", // Get Certificates
        6 => "WGI"  // General Inquiry
    ];
    
    $transaction_code = generateTransactionCode($prefixes[$lab_id]);
    
    // Determine priority based on lab_id
    $priority = 4; // Default priority
    if ($lab_id == 5) {
        $priority = 3; // Get Certificates
    } elseif ($lab_id == 6) {
        $priority = 2; // General Inquiry
    }

    // Assign Category and Quantity based on lab_id
    if (in_array($lab_id, [1, 4, 5, 6])) {
        $category = "Walk-in";
        $quantity = "-"; // Ensure it's stored as a string
        $address = "-";
        $contact_number = "-";
        $email_address = "-";
        $request_type = "Walk-in";
    }

    // Insert into database
    $sql = "INSERT INTO submissions (submission_id, lab_id, unique_id, submission_date, submission_date_selected, full_name, status, priority, category, quantity, address, contact_number, email_address, request_type) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        $submission_id = str_pad(mt_rand(1, 9) . mt_rand(100000000000000000, 999999999999999999), 19, '0', STR_PAD_LEFT);
        $submission_date = date("Y-m-d H:i:s");
        $submission_date_selected = $submission_date; // Automatically set when form is created
        $status = 2;
        
        // Use transaction_code as unique_id
        $unique_id = $transaction_code;
        
        $stmt->bind_param("sissssiissssss", $submission_id, $lab_id, $unique_id, $submission_date, $submission_date_selected, $full_name, $status, $priority, $category, $quantity, $address, $contact_number, $email_address, $request_type);

        $stmt->execute();
        $stmt->close();
    }
}
?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RSTL Queue Form</title>
    <link rel="icon" type="image/png" href="assets/img/dost.png">

    <style>
        body {
            background: linear-gradient(-45deg, #0d4e86, #1A2980, #26D0CE);
            background-size: 400% 400%;
            animation: gradientAnimation 20s ease infinite;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 20px;
        }

        @keyframes gradientAnimation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .container {
            text-align: center;
            background: rgba(255, 255, 255, 0.2);
            padding: 40px;
            border-radius: 20px;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            width: 450px;
            transition: transform 0.5s ease, opacity 0.5s ease, border-radius 0.5s ease;
            opacity: 1;
            transform: scale(1);
            color: white; /* Added font color */
        }

        .hidden { display: none; }

        .btn, .input-field {
            width: calc(100% - 50px);
            padding: 15px;
            margin: 15px 0;
            border: none;
            border-radius: 8px;
            font-size: 19px;
        }

        .btn {
            background: linear-gradient(-45deg, #0d4e86, #1A2980, #26D0CE);
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.5s ease, box-shadow 0.5s ease;
        }

        .btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }

        .input-field {
            border: 1px solid #ccc;
        }

        /* Modal Styling */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(8px);
            display: flex;
            justify-content: center;
            align-items: center;
            visibility: hidden;
            opacity: 0;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .modal {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            text-align: center;
            width: 500px;
        }

        .modal-overlay.show {
            visibility: visible;
            opacity: 1;
        }



        .logo {
    width: 100px;
    margin-bottom: 10px;
    filter: drop-shadow(0px 4px 6px rgba(0, 0, 0, 0.3)); /* Soft glow effect */
    transition: transform 0.3s ease, filter 0.3s ease;
}

.logo:hover {
    transform: scale(1.1);
    filter: drop-shadow(0px 6px 12px rgba(255, 255, 255, 0.4)); /* Enhanced glow */
}



    </style>
</head>
<body>
    <div id="step1" class="container">
    <img src="assets/img/dost.png" alt="Logo" class="logo">

        <h2>Step 1: What would you like to do?</h2>
        <button class="btn" onclick="showStep1A()">Submit Samples</button>
        <button class="btn" onclick="selectLab(5)">Get Certificates</button>
        <button class="btn" onclick="selectLab(6)">General Inquiry</button>
    </div>

    <div id="step1A" class="container hidden">
    <img src="assets/img/dost.png" alt="Logo" class="logo">

        <h2>Step 1A: Select Sample Type</h2>
        <button class="btn" onclick="selectLab(1)">Items for Calibration<br>(Weighing Scale, Thermometer, Proving Buckets, etc)</button>
        <button class="btn" onclick="selectLab(4)">Shelf-Life</button>
        <button class="btn" onclick="goBack()">Back</button>
    </div>

    <div id="step2" class="container hidden">
    <img src="assets/img/dost.png" alt="Logo" class="logo">

        <h2>Step 2: Enter Name</h2>
        <form method="POST">
            <input type="hidden" name="lab_id" id="lab_id">
            <input type="text" name="full_name" class="input-field" placeholder="Enter Full Name" required>
            <button type="submit" class="btn">Get Queue</button>
        </form>
        <button class="btn" onclick="goBack()">Back</button>
    </div>

    <?php if (!empty($transaction_code)) : ?>
        <div class="modal-overlay show" id="modalOverlay">
            <div class="modal" id="modal">
                <h3>Your Queue Number is successfully generated.</h3>
                <p><strong>Transaction Code:</strong> <?= $transaction_code ?></p>
                <p><strong>Name:</strong> <?= htmlspecialchars($full_name) ?></p>
                <p>If you are submitting samples, please fill out the Customer Information Sheet found at the back of this tablet.</p>
                <button class="btn" onclick="resetForm()">OK</button>
            </div>
        </div>
    <?php endif; ?>

    <script>
    function showStep1A() {
        document.getElementById('step1').classList.add('hidden');
        document.getElementById('step1A').classList.remove('hidden');
    }

    function selectLab(id) {
        document.getElementById('lab_id').value = id;
        document.getElementById('step1').classList.add('hidden');
        document.getElementById('step1A').classList.add('hidden');
        document.getElementById('step2').classList.remove('hidden');
    }

    function goBack() {
    // Clear the input field when going back to Step 2
    document.querySelector('input[name="full_name"]').value = '';
    
    // Hide the current steps and show the previous ones
    document.getElementById('step2').classList.add('hidden');
    document.getElementById('step1A').classList.add('hidden');
    document.getElementById('step1').classList.remove('hidden');
}


    function resetForm() {
    // Clear form fields
    document.querySelector('input[name="full_name"]').value = '';
    
    // Hide the modal
    document.getElementById('modalOverlay').classList.remove('show');
    
    // Wait for the modal to close, then reset the form and go back to the first step
    setTimeout(function() {
        document.getElementById('step2').classList.add('hidden');
        document.getElementById('step1').classList.remove('hidden');
        document.getElementById('step1A').classList.add('hidden');
    }, 300);
}

</script>

</body>
</html>
