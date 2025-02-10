<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: userlogin.php");
    exit();
}

// Safely retrieve and display user data
$firstname = isset($_SESSION['firstname']) ? htmlspecialchars($_SESSION['firstname']) : 'Guest';
$email = isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : '';

require_once '../connection/connection.php'; // Include your database connection file

try {
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL query to fetch user names
    $sql = "SELECT * FROM users"; // Use '*' to fetch all user columns
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    // Fetch all the results
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetching as an associative array

} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

 // Fetch user profile picture from the database
 $userId = $_SESSION['user_id'];
 $stmt = $conn->prepare("SELECT profile_pic FROM users WHERE id = :id");
 $stmt->bindParam(':id', $userId);
 $stmt->execute();
 $user = $stmt->fetch(PDO::FETCH_ASSOC);
 
 // Check if user profile picture exists
 $profilePic = !empty($user['profile_pic']) ? $user['profile_pic'] : 'default.png'; // Use a default image if none is found
  
 
 

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users</title>
    <link rel="stylesheet" href="./admindashboardcss/adminusers.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> 
    <style>
    .modal {
    display: none; /* Hide modal by default */
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    right:50px;
    width: 140%;
    height: 110%;
    background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
    justify-content: center;
    align-items: center;
    padding-right:40%;
}

.modal-content {
    background: white;
    padding:10px;
    width: 100%;
    max-width: 800px;
    border-radius: 10px;
   
}
.label{
    float: left;
    padding: 1px;
}
    </style>
</head>
<body>

<div class="row">
<div class="left-content col-3"> 
<div class="adminprofile">
                            <center>
                            <img src="../uploads/profile_pics/<?php echo $profilePic; ?>" alt="Profile Picture">
                                <div class="dropdown">
                                    <button class="dropdown-btn">
                                        <?php echo "<h4> $firstname</h4>" ?> 
                                    </button>
                                    <!-- <i class="fas fa-caret-down dropdown-icon"></i> -->
                                    <!-- <div class="dropdown-content">
                                        <button onclick="openModal('changePasswordModal')">Change Password</button>
                                        <button onclick="openModal('termsModal')">Terms and Conditions</button>
                                    </div> -->
                                </div>
                        </center>
                    </div>
                        <br>
                        <div class="adminlinks">
                            <span><img src="../images/dashboard.png" alt="">&nbsp;&nbsp;&nbsp;<a href="adminDashboard.php">Dashboard</a></span> 
                            <span><img src="../images/deceased.png" alt="">&nbsp;&nbsp;&nbsp;<a href="adminDeceased.php">Deceased</a></span>
                            <span><img src="../images/reservation.png" alt="">&nbsp;&nbsp;&nbsp;<a href="adminreservation.php">Reservation</a></span>
                            <span><img src="../images/payment.png" alt="">&nbsp;&nbsp;&nbsp;<a href="adminpayment.php">Transaction</a></span>
                            <span><img src="../images/users.png" alt="">&nbsp;&nbsp;&nbsp;<a href="adminusers.php">User's</a></span>
                            <span><img src="../images/review.png" alt="">&nbsp;&nbsp;&nbsp;<a href="adminreviews.php">Reviews</a></span>
                            <span><img src="../images/logout.png" alt="">&nbsp;&nbsp;&nbsp;<a href="../logout.php">Logout</a></span>
                        </div>
                        <br>
                </div>
                <div class="main">
                <div class="right-content1">
                    <div class="right-header col-10">
                        <br>
                        <h2>USERS DATA</h2>
                        <div class="search-box">
                            <i class="fas fa-search search-icon"></i>
                            <!-- <input type="text" class="search-input" placeholder="Search"> -->
                            <input type="text" id="search" class="search-input" placeholder="Search Customers ..." onkeyup="searchUsers()">
                        </div>
                        <!-- <button onclick="openAddModal()">Add Reservation</button> -->
                    </div>
                </div>
        <div class="right-content2">
            <div class="right-header2 col-9">
                <div class="left-content1">
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>First Name</th>
                                    <th>Surname</th>
                                </tr>
                            </thead>
                                <tbody id="userTableBody">
                                <?php
                                if (count($users) > 0) {
                                    foreach ($users as $row) {
                                        // Display each user as a table row, with their first name and surname.
                                        echo "<tr onclick='fetchUserDetails(" . $row["id"] . ")'>";
                                        echo "<td>" . htmlspecialchars($row["firstname"]) . "</td>";
                                        echo "<td>" . htmlspecialchars($row["surname"]) . "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    // Show message when no users are found.
                                    echo "<tr><td colspan='2'>No users found</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- TODO: transaction modal -->
                <div class="right-content">
                <div id="userDisplay">
                    <div id="userDetails" style="display: none;">
                        <center>
                        <img id="userImage" src="../images/default-profile.png" alt="Profile Picture">
                        <br>
                        <div class="user-row">
                            <label>FirstName:</label>
                            <span id="labelFirstname">John</span>
                        </div>
                        <div class="user-row">
                            <label>Surname:</label>
                            <span id="labelSurname">Doe</span>
                        </div>
                        <div class="user-row">
                            <label>Contact:</label>
                            <span id="labelContact">123-456-7890</span>
                        </div>
                        <div class="user-row">
                            <label>Email:</label>
                            <span id="labelEmail">john.doe@example.com</span>
                        </div>
                        <div class="user-row">
                            <!-- <label>Address:</label> -->
                            <span id="labelAddress"></span>
                        </div>
                        <div class="buttons">
                            <!-- Transaction Button -->
                            <!-- Updated Transaction Button -->
                            <button onclick="fetchTransactionDetails(document.getElementById('userId').value)">Transaction</button>
                             <!-- <button onclick="openUpdateModal()">Transaction</button> -->
                            <button class="btn-danger" onclick="deleteUser()">Delete</button>
                        </div>
                        </center>
                    </div>
                </div>  
            </div>

                
            <!-- Modal for updating user data -->
                <div id="updateModal" class="modal">
                    <div id="modalContent" class="modal-content">
                        <span class="close" onclick="closeModal()">&times;</span>
                        <h2>Transaction Details</h2>
                        <br>
                        <input type="hidden" id="userId" name="userId">
                        <label class="label"><b>Reservation ID:</b></label>
                        <p id="reservationId"></p>
                        <br>
                        <label class="label"><b>Total Amount:</b></label>
                        <p id="totalAmount"></p>
                        <br>
                        <label class="label"><b>Duration (Months):</b></label>
                        <p id="duration"></p>
                        <br>
                        <label class="label"><b>Amount:</b></label>
                        <p id="installmentAmount"></p>
                        <br>
                        <!-- NEW: Remaining Balance -->
                        <label class="label"><b>Remaining Balance:</b></label>
                        <p id="remainingBalance" style="color: red; font-weight: bold;"></p>
                        <br>

                        <center><h3>Payment Breakdown</h3></center>
                        <br>
                        <table border="1">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>Due Date</th>
                                    <th>Amount</th>
                                    <th>Payment Date</th>
                                    <th>Payment Status</th>
                                    <th>Print</th>
                                </tr>
                            </thead>
                            <tbody id="paymentBreakdown">
                            </tbody>
                        </table>

                    </div>
                </div>





            <script>
                // TODO: fetch the user details
                function fetchUserDetails(userId) {
                    const xhr = new XMLHttpRequest();
                    xhr.open('GET', 'fetch_user.php?id=' + userId, true);
                    xhr.onload = function() {
                        if (this.status === 200) {
                            const user = JSON.parse(this.responseText);
                            if (user && user.id) {
                                // Update user details
                                document.getElementById("userId").value = user.id;
                                document.getElementById("labelFirstname").textContent = user.firstname;
                                document.getElementById("labelSurname").textContent = user.surname;
                                document.getElementById("labelContact").textContent = user.contact;
                                document.getElementById("labelEmail").textContent = user.email;
                                document.getElementById("labelAddress").textContent = user.address;

                                // Update profile picture
                                const userImage = document.getElementById("userImage");
                                if (user.profile_pic) {
                                    userImage.src = `../uploads/profile_pics/${user.profile_pic}`;
                                } else {
                                    userImage.src = "../images/default-profile.png"; // Fallback image
                                }

                                document.getElementById("userDetails").style.display = 'block';
                            } else {
                                console.error("No user data found");
                            }
                        } else {
                            console.error("Failed to fetch user details. Status:", this.status);
                        }
                    };
                    xhr.send();
                }



                function deleteUser() {
                const userId = document.getElementById("userId").value;
                if (confirm("Are you sure you want to delete this user?")) {
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', 'delete_user.php', true);
                    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                    xhr.onload = function() {
                        if (this.status === 200) {
                            alert(this.responseText);
                            location.reload(); // Reload the page to remove the deleted user from the UI
                        } else {
                            console.error("Failed to delete user. Status:", this.status);
                        }
                    };
                    xhr.send("id=" + userId); // Sending user ID as a POST parameter
                }
            }

            // Function to search users based on the input
            function searchUsers() {
                const searchQuery = document.getElementById('search').value;

                // AJAX request to search_users.php
                const xhr = new XMLHttpRequest();
                xhr.open('GET', 'search_users.php?query=' + searchQuery, true);
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        const users = JSON.parse(xhr.responseText);
                        const userTableBody = document.getElementById('userTableBody');
                        userTableBody.innerHTML = ''; // Clear existing table data

                        if (users.length > 0) {
                            users.forEach(function(user) {
                                const row = document.createElement('tr');
                                row.onclick = function() {
                                    fetchUserDetails(user.id);
                                };
                                row.innerHTML = `
                                    <td>${user.firstname}</td>
                                    <td>${user.surname}</td>
                                `;
                                userTableBody.appendChild(row);
                            });
                        } else {
                            userTableBody.innerHTML = '<tr><td colspan="2">No users found</td></tr>';
                        }
                    } else {
                        console.error('Error fetching users');
                    }
                };
                xhr.send();
            }

            // On page load, display all users
            window.onload = function() {
                searchUsers(); // This will load all users in descending order initially
            };



            function searchUsers() {
            // Get the search input value
            const searchQuery = document.getElementById('search').value;

            // Create an XMLHttpRequest object
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'search_users.php?query=' + encodeURIComponent(searchQuery), true);

            // Define what happens on successful data submission
            xhr.onload = function() {
                if (xhr.status === 200) {
                    // Parse the JSON data returned from the server
                    const users = JSON.parse(xhr.responseText);

                    // Select the table body element where we will display users
                    const userTableBody = document.getElementById('userTableBody');
                    userTableBody.innerHTML = '';  // Clear the table body

                    if (users.length > 0) {
                        // Loop through each user and insert into the table
                        users.forEach(function(user) {
                            const row = document.createElement('tr');
                            row.setAttribute('onclick', `fetchUserDetails(${user.id})`); // Fetch details on click

                            row.innerHTML = `
                                <td>${user.firstname}</td>
                                <td>${user.surname}</td>
                            `;

                            userTableBody.appendChild(row);  // Add the row to the table body
                        });
                    } else {
                        // If no users found, display message
                        userTableBody.innerHTML = '<tr><td colspan="2">No users found</td></tr>';
                    }
                } else {
                    console.error('Error fetching users:', xhr.status);
                }
            };

            // Send the request to the server
            xhr.send();
        }

        // Automatically load all users on page load
        window.onload = function() {
            searchUsers(); // This will load all users initially
        };

        function openUpdateModal(reservationId) {
        fetch(`fetch_payment_details.php?reservation_id=${reservationId}`)
        .then(response => response.json())
        .then(data => {
            console.log(data); // Debugging

            if (data.success) {
                document.getElementById("reservationId").textContent = data.reservation_id;
                document.getElementById("totalAmount").textContent = data.total_amount;
                document.getElementById("installmentAmount").textContent = 
                    data.installment_amount > 0 ? data.installment_amount : "Not Available";

                // Handle ENUM duration
                let durationText = "";
                if (data.duration === "full") {
                    durationText = "Full Payment";
                } else if (data.duration === "6months") {
                    durationText = "6 Months";
                } else if (data.duration === "9months") {
                    durationText = "9 Months";
                } else {
                    durationText = "Not Available";
                }
                document.getElementById("duration").textContent = durationText;

                // Generate payment breakdown
                let breakdownHTML = "";
                let months = 0;

                if (data.duration === "6months") {
                    months = 6;
                } else if (data.duration === "9months") {
                    months = 9;
                }

                if (months > 0 && data.installment_amount > 0) {
                    for (let i = 1; i <= months; i++) {
                        breakdownHTML += `<tr>
                            <td>Month ${i}</td>
                            <td>${data.installment_amount}</td>
                        </tr>`;
                    }
                } else if (data.duration === "full") {
                    breakdownHTML = "<tr><td colspan='2'>Paid in Full</td></tr>";
                } else {
                    breakdownHTML = "<tr><td colspan='2'>No installment plan available</td></tr>";
                }

                document.getElementById("paymentBreakdown").innerHTML = breakdownHTML;

                document.getElementById("updateModal").style.display = "flex";
            } else {
                alert(data.message);
            }
        })
        .catch(error => console.error("Fetch error:", error));
}

function closeModal() {
    document.getElementById("updateModal").style.display = "none";
}

function fetchTransactionDetails(userId) {
    fetch(`fetch_transaction.php?user_id=${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.length > 0) {
                let transaction = data[0]; // Get the first transaction entry

                let totalAmount = parseFloat(transaction.total_amount);
                let installmentAmount = parseFloat(transaction.installment_amount || 0);
                let remainingBalance = parseFloat(transaction.remaining_balance ?? (totalAmount - installmentAmount));

                console.log("Transaction Data:", transaction);
                console.log("Remaining Balance from DB:", remainingBalance);

                let displayedAmount;
                let remainingBalanceText;

                if (transaction.installment_plan === "fullpayment") {
                    displayedAmount = totalAmount;
                    remainingBalanceText = "No remaining balance";
                    transaction.duration = "0"; 
                    transaction.installment_amount = "0";
                } else {
                    displayedAmount = installmentAmount;
                    remainingBalanceText = remainingBalance > 0 
                        ? "₱ " + remainingBalance.toLocaleString() 
                        : "No remaining balance";
                }

                // ✅ Display Data in Modal
                document.getElementById("reservationId").textContent = transaction.reservation_id;
                document.getElementById("totalAmount").textContent = "₱ " + totalAmount.toLocaleString();
                document.getElementById("duration").textContent = transaction.duration;
                document.getElementById("installmentAmount").textContent = displayedAmount !== 0 
                    ? "₱ " + displayedAmount.toLocaleString() 
                    : "0";
                document.getElementById("remainingBalance").textContent = remainingBalanceText;

                // ✅ Generate payment breakdown
                generatePaymentBreakdown(transaction, data);

                // ✅ Show the modal
                document.getElementById("updateModal").style.display = "flex";
            } else {
                alert("No transaction details found.");
            }
        })
        .catch(error => console.error("Fetch error:", error));
}


function generatePaymentBreakdown(transaction, payments) {
    const breakdownTable = document.getElementById("paymentBreakdown");
    const remainingBalanceElement = document.getElementById("remainingBalance");
    breakdownTable.innerHTML = "";

    let durationMonths = parseInt(transaction.duration) || 0;
    let installmentAmount = parseFloat(transaction.installment_amount) || 0;
    let totalAmount = parseFloat(transaction.total_amount) || 0;
    let remainingBalance = parseFloat(transaction.remaining_balance) || totalAmount;

    console.log("Transaction Data:", transaction);
    console.log("Payments Data:", payments);
    console.log("Installment Plan:", transaction.installment_plan);

    // ✅ If Full Payment
    if (transaction.installment_plan === "fullpayment") {
        let paymentDate = payments[0]?.payment_date || "N/A";
        let paymentTime = payments[0]?.created_at || "N/A";
        let paidAmount = parseFloat(payments[0]?.amount_paid) || 0; // ✅ Ensure we use amount_paid

        let row = `<tr style="background-color: green; color: white;">
                      <td colspan="2">Full Payment</td>
                      <td>₱${paidAmount.toLocaleString()}</td>
                      <td>${paymentDate}</td>
                      <td>✅ Paid</td>
                      <td>
                          <button onclick="printReceipt(
                              '${transaction.reservation_id}', 
                              'Full Payment', 
                              '${paymentDate}', 
                              '${paymentTime}', 
                              ${paidAmount}, 
                              'No remaining balance'
                          )"> 🖨 Print</button>
                      </td>
                   </tr>`;
        breakdownTable.innerHTML += row;
        remainingBalanceElement.innerHTML = "No remaining balance";
        return;
    }

    let startDate = new Date(transaction.payment_date);

    for (let i = 0; i < durationMonths; i++) {
        let dueDate = new Date(startDate);
        dueDate.setMonth(startDate.getMonth() + i);
        let dueMonth = dueDate.toLocaleString("en-US", { month: "long", year: "numeric" });
        let formattedDueDate = dueDate.toLocaleDateString("en-US", { month: "long", day: "2-digit", year: "numeric" });

        console.log(`Month ${i + 1}: Due Date = ${formattedDueDate}, Month Name = ${dueMonth}`);

        let paidEntry = payments.find(p => {
            let paidDate = new Date(p.payment_date);
            return paidDate.getMonth() === dueDate.getMonth() && paidDate.getFullYear() === dueDate.getFullYear();
        });

        let isPaid = paidEntry !== undefined;
        let paymentDate = isPaid ? paidEntry.payment_date : "Not Paid";
        let paymentTime = isPaid ? paidEntry.created_at : "N/A";
        let paidAmount = isPaid ? parseFloat(paidEntry.amount_paid) || 0 : 0; // ✅ Use amount_paid when available

        if (isPaid) {
            console.log(`Paid Entry Found for ${dueMonth}: Amount = ${paidAmount}`);
            remainingBalance -= paidAmount;
            console.log(`Updated Remaining Balance: ${remainingBalance}`);
        }

        let remainingBalanceText = remainingBalance > 0 ? `₱${remainingBalance.toLocaleString()}` : "No remaining balance";

let row = `<tr style="background-color: ${isPaid ? 'dodgerblue' : 'orange'}; color: white;">
      <td>${dueMonth}</td>
      <td>${formattedDueDate}</td>
      <td>₱${installmentAmount.toLocaleString()}</td>
      <td>${paymentDate}</td>
      <td>${isPaid ? "✅ Paid" : "❌ Not Paid"}</td>
      <td>
          ${isPaid ? `<button onclick="printReceipt(
              '${transaction.reservation_id}', 
              '${dueMonth}', 
              '${paymentDate}', 
              '${paymentTime}', 
              ${paidAmount}, 
              '${remainingBalanceText}', 
              '${totalAmount}', 
              '${installmentAmount}', 
              '${transaction.installment_plan}'
          )"> 🖨 Print</button>` : ""}
      </td>
   </tr>`;

breakdownTable.innerHTML += row;

    }

    remainingBalanceElement.innerHTML = remainingBalance > 0 
        ? `₱${remainingBalance.toLocaleString()}`
        : "No remaining balance";

    console.log("Final Remaining Balance:", remainingBalance);
}
function printReceipt(reservationId, paymentType, paymentDate, paymentTime, amountPaid, remainingBalance, totalAmount, installmentAmount, installmentPlan) {
    console.log("🔹 Print Receipt Triggered!");
    console.log("Reservation ID:", reservationId);
    console.log("Payment Type (Before Fix):", paymentType);
    console.log("Installment Plan:", installmentPlan);
    console.log("Payment Date:", paymentDate);
    console.log("Payment Time:", paymentTime);
    console.log("Amount Paid (Before Fix):", amountPaid);
    console.log("Installment Amount:", installmentAmount);
    console.log("Remaining Balance:", remainingBalance);
    console.log("Total Amount:", totalAmount);

    // Ensure all numeric values are properly converted
    totalAmount = parseFloat(totalAmount) || 0;
    installmentAmount = parseFloat(installmentAmount) || 0;
    amountPaid = parseFloat(amountPaid) || 0;
    remainingBalance = parseFloat(remainingBalance) || 0;

    let paymentDetails = "";

    if (installmentPlan === "fullpayment") {
        // ✅ Full Payment Case - Ensure correct values
        paymentType = "Full Payment"; // Fix payment type
        amountPaid = totalAmount; // Set amount paid to full amount
        remainingBalance = 0; // No remaining balance

        paymentDetails = `<p><strong>Payment Type:</strong> ${paymentType}</p>
                          <p><strong>Total Amount:</strong> ₱${totalAmount.toLocaleString()}</p>
                          <p><strong>Amount Paid:</strong> ₱${amountPaid.toLocaleString()}</p>`;
    } else if (installmentPlan === "installment") {
        // ✅ Keep Installment Logic Unchanged
        amountPaid = installmentAmount; // Keep installment logic same
        remainingBalance = totalAmount - amountPaid;

        paymentDetails = `<p><strong>Payment Type:</strong> Installment (${installmentPlan})</p>
                          <p><strong>Total Amount:</strong> ₱${totalAmount.toLocaleString()}</p>
                          <p><strong>Installment Amount:</strong> ₱${installmentAmount.toLocaleString()}</p>
                          <p><strong>Amount Paid:</strong> ₱${amountPaid.toLocaleString()}</p>`;
    } else {
        // ✅ Handle missing or unknown payment plans
        paymentType = "Unknown";
        paymentDetails = `<p><strong>Payment Type:</strong> ${paymentType}</p>`;
    }

    let receiptContent = `
        <html>
        <head>
            <title>Payment Receipt</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 20px; }
                h2 { color: #333; }
                p { margin: 5px 0; }
                hr { margin: 15px 0; }
            </style>
        </head>
        <body>
            <h2>Payment Receipt</h2>
            <p><strong>Reservation ID:</strong> ${reservationId}</p>
            ${paymentDetails}
            <p><strong>Payment Date:</strong> ${paymentDate}</p>
            <p><strong>Payment Time:</strong> ${paymentTime}</p>
            <p><strong>Remaining Balance:</strong> ₱${remainingBalance.toLocaleString()}</p>
            <hr>
            <p>Thank you for your payment!</p>
        </body>
        </html>
    `;

    let printWindow = window.open("", "_blank");
    printWindow.document.write(receiptContent);
    printWindow.document.close();
    printWindow.print();
}







            </script>

</body>
</html>


