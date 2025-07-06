"use strict";

$(".swal-button").click(function () {
  const buttonId = $(this).data("id");

  $.ajax({
    url: "fetch_transaction.php",
    type: "POST",
    data: { id: buttonId },
    dataType: "json",
    success: function (response) {
      if (response.status !== "error") {
        // Remove any existing SweetAlert content to prevent overlapping
        $(".swal-content").remove(); 
        
        // Convert status to integer for correct comparison
        const statusInt = parseInt(response.status, 10);
        const statusText = statusInt === 2 ? "Confirmed" : statusInt === 3 ? "Rejected" : "Pending";

        // Convert lab_id to its corresponding description
        const labDescriptions = {
          1: "Metrology Calibration",
          2: "Chemical Analysis",
          3: "Microbiological Analysis",
          4: "Shelf-life Analysis"
        };

        const labType = labDescriptions[response.lab_id] || "Unknown"; // Default to 'Unknown' if not found

        swal({
          title: "Transaction Details",
          content: {
            element: "div",
            attributes: {
              id: "swal-content",  // Unique ID to prevent content stacking
              innerHTML: `
                <table style="width: 100%; border-collapse: collapse;">
                  <tr><td style="font-weight: bold;">Transaction ID</td><td>${response.unique_id || "N/A"}</td></tr>
                  <tr><td style="font-weight: bold;">Sample Type</td><td>${labType}</td></tr>
                  <tr><td style="font-weight: bold;">Category</td><td>${response.category}</td></tr>
                  <tr><td style="font-weight: bold;">Quantity</td><td>${response.quantity}</td></tr>
                  <tr><td style="font-weight: bold;">Request Type</td><td>${response.request_type}</td></tr>
                  <tr><td style="font-weight: bold;">Fullname</td><td>${response.full_name}</td></tr>
                  <tr><td style="font-weight: bold;">Contact Number</td><td>${response.contact_number}</td></tr>
                  <tr><td style="font-weight: bold;">Address</td><td>${response.address}</td></tr>
                  <tr><td style="font-weight: bold;">Email Address</td><td>${response.email_address}</td></tr>
                  <tr><td style="font-weight: bold;">Date Submitted</td><td>${response.submission_date}</td></tr>
                  <tr><td style="font-weight: bold;">Date Appointed</td><td>${response.submission_date_selected}</td></tr>
                </table>
              `,
            },
          },
          buttons: {
            cancel: "Close",
            reject: {
              text: "Reject",
              value: "reject",
              visible: true,
              className: "",
            },
            confirm: {
              text: "Confirm",
              value: "confirm",
              visible: true,
              className: "",
            },
          },
          closeOnClickOutside: false,
        }).then((value) => {
          if (value === "confirm") {
            updateTransactionStatus(buttonId, 2); // Confirm (status = 2)
          } else if (value === "reject") {
            updateTransactionStatus(buttonId, 3); // Reject (status = 3)
          }
        });

        // Ensure button styles are set correctly
        setTimeout(() => {
          $(".swal-button--reject").css({
            "background-color": "#d9534f",
            "color": "white"
          });

          $(".swal-button--confirm").css({
            "background-color": "#5cb85c",
            "color": "white"
          });
        }, 100);
      } else {
        swal("Error", "Transaction details not found.", "error");
      }
    },
    error: function () {
      swal("Error", "Failed to fetch data. Please try again later.", "error");
    },
  });
});

function updateTransactionStatus(id, status) {
  console.log("Sending request to update transaction...");
  console.log("Transaction ID:", id);
  console.log("New Status:", status);

  $.ajax({
    url: "update_transaction.php",
    type: "POST",
    data: { id: id, status: status },
    dataType: "text",
    success: function (response) {
      response = response.trim(); // Trim any whitespace
      console.log("Server Response:", response); // Debugging log

      if (response === "success") {
        swal("Success", "Transaction status updated successfully!", "success").then(() => {
          location.reload(); // Refresh the page to reflect changes
        });
      } else {
        swal("Error", response.replace("error: ", ""), "error");
      }
    },
    error: function (xhr, status, error) {
      console.error("AJAX Error:", error);
      console.log("XHR Response:", xhr.responseText); // Debugging log
      swal("Error", "Server error. Please try again later.", "error");
    },
  });
}
