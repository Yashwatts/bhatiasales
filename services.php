<?php
include 'header.php'; // Assuming you have a header file
include 'admin/db.php'; // Database connection

// Handle Service Booking Submission
if (isset($_POST['book_service'])) {
    $service_type = mysqli_real_escape_string($conn, $_POST['service_type']);
    $user_name = mysqli_real_escape_string($conn, $_POST['user_name']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);

    // Calculate queue position (count of pending bookings for this service type + 1)
    $queue_query = "SELECT COUNT(*) AS pending_count FROM service_bookings WHERE service_type = '$service_type' AND status = 'Pending'";
    $queue_result = mysqli_query($conn, $queue_query);
    if ($queue_result) {
        $pending_count = mysqli_fetch_assoc($queue_result)['pending_count'];
        $queue_position = $pending_count + 1;

        // Insert booking with queue position
        $stmt = $conn->prepare("INSERT INTO service_bookings (service_type, user_name, contact, queue_position) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $service_type, $user_name, $contact, $queue_position);
        if ($stmt->execute()) {
            $success_message = "Service booked successfully! You are number $queue_position in the queue. The admin will contact you for further timings.";
        } else {
            $error_message = "Error booking service: " . $conn->error;
        }
        $stmt->close();
    } else {
        $error_message = "Error calculating queue position: " . mysqli_error($conn);
    }
}

// Handle Check Status
$status_results = [];
if (isset($_POST['check_status'])) {
    $check_contact = mysqli_real_escape_string($conn, $_POST['check_contact']);
    $status_query = "
        SELECT service_type, user_name, booking_date, status, queue_position 
        FROM service_bookings 
        WHERE contact = '$check_contact' 
        ORDER BY booking_date DESC
    ";
    $status_result = mysqli_query($conn, $status_query);

    if ($status_result && mysqli_num_rows($status_result) > 0) {
        while ($row = mysqli_fetch_assoc($status_result)) {
            $status_results[] = $row;
        }
    } else {
        $status_message = "No service bookings found for this mobile number.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Services - Bhatia Sales</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .services-container {
            background: linear-gradient(135deg, #f3f4f6, #ffffff);
            min-height: calc(100vh - 160px);
        }
        .form-container, .status-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .form-container:hover, .status-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
        }
        .input-field {
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .input-field:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
        }
        .submit-btn, .check-btn {
            background: linear-gradient(90deg, #4f46e5, #6b7280);
            transition: background 0.3s ease, transform 0.3s ease;
        }
        .submit-btn:hover, .check-btn:hover {
            background: linear-gradient(90deg, #4338ca, #4b5563);
            transform: scale(1.05);
        }
        .service-option {
            transition: background 0.3s ease, transform 0.3s ease;
        }
        .service-option:hover {
            background: #f3f4f6;
            transform: scale(1.02);
        }
        .status-pending { border-left: 4px solid #f59e0b; }
        .status-confirmed { border-left: 4px solid #22c55e; }
        .status-completed { border-left: 4px solid #6b7280; }
    </style>
    <script>
        window.onload = function() {
            <?php if (!empty($status_results) || isset($status_message)): ?>
                document.getElementById('status-section').scrollIntoView({ behavior: 'smooth' });
            <?php endif; ?>
        };
    </script>
</head>
<body class="bg-gray-50 font-sans antialiased">
    <div class="services-container max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-8 text-center tracking-tight">Book a Service</h2>

        <?php if (isset($success_message)): ?>
            <div class="mb-8 p-4 bg-green-100 text-green-800 rounded-lg shadow-md"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
            <div class="mb-8 p-4 bg-red-100 text-red-800 rounded-lg shadow-md"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Book Service Form -->
            <div class="form-container p-8">
                <h3 class="text-2xl font-semibold text-gray-900 mb-6 text-center">Choose a Service</h3>
                <form action="services.php" method="post" class="space-y-6">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Select Service</label>
                        <div class="space-y-4">
                            <label class="service-option flex items-center p-4 border border-gray-300 rounded-lg cursor-pointer">
                                <input type="radio" name="service_type" value="Repairing" class="mr-3 text-indigo-600 focus:ring-indigo-500" required>
                                <span class="text-gray-800 font-medium">Repairing</span>
                            </label>
                            <label class="service-option flex items-center p-4 border border-gray-300 rounded-lg cursor-pointer">
                                <input type="radio" name="service_type" value="Washing" class="mr-3 text-indigo-600 focus:ring-indigo-500" required>
                                <span class="text-gray-800 font-medium">Washing</span>
                            </label>
                        </div>
                    </div>
                    <div>
                        <label for="user_name" class="block text-gray-700 font-medium mb-2">Your Name</label>
                        <input type="text" id="user_name" name="user_name" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none text-gray-800" placeholder="Your Name" required>
                    </div>
                    <div>
                        <label for="contact" class="block text-gray-700 font-medium mb-2">Contact Number</label>
                        <input type="text" id="contact" name="contact" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none text-gray-800" placeholder="Contact Number" required pattern="[0-9]{10}" title="Enter a valid 10-digit mobile number">
                    </div>
                    <button type="submit" name="book_service" class="submit-btn w-full text-white py-3 rounded-lg font-semibold shadow-md">Book Service</button>
                </form>
                <p class="mt-4 text-sm text-gray-600 italic text-center">Note: After booking, you’ll be assigned a queue position. The admin will contact you for further timings.</p>
            </div>

            <!-- Check Status Form -->
            <div class="form-container p-8">
                <h3 class="text-2xl font-semibold text-gray-900 mb-6 text-center">Check Service Status</h3>
                <form action="services.php#status-section" method="post" class="space-y-6">
                    <div>
                        <label for="check_contact" class="block text-gray-700 font-medium mb-2">Mobile Number</label>
                        <input type="text" id="check_contact" name="check_contact" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none text-gray-800" placeholder="Enter your mobile number" required pattern="[0-9]{10}" title="Enter a valid 10-digit mobile number" value="<?php echo isset($_POST['check_contact']) ? htmlspecialchars($_POST['check_contact']) : ''; ?>">
                    </div>
                    <button type="submit" name="check_status" class="check-btn w-full text-white py-3 rounded-lg font-semibold shadow-md">Check Status</button>
                </form>

                <!-- Status Results -->
                <div id="status-section" class="mt-6">
                    <?php if (!empty($status_results)): ?>
                        <div class="space-y-4">
                            <?php foreach ($status_results as $status): ?>
                                <div class="status-card p-4 <?php echo $status['status'] === 'Pending' ? 'status-pending' : ($status['status'] === 'Confirmed' ? 'status-confirmed' : 'status-completed'); ?>">
                                    <h4 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($status['service_type']); ?></h4>
                                    <p class="text-gray-600"><strong>User:</strong> <?php echo htmlspecialchars($status['user_name']); ?></p>
                                    <p class="text-gray-600"><strong>Booked On:</strong> <?php echo date('d M Y, H:i', strtotime($status['booking_date'])); ?></p>
                                    <p class="text-gray-600"><strong>Status:</strong> 
                                        <span class="px-2 py-1 rounded-full text-sm font-medium <?php echo $status['status'] === 'Pending' ? 'bg-amber-100 text-amber-800' : ($status['status'] === 'Confirmed' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'); ?>">
                                            <?php echo htmlspecialchars($status['status']); ?>
                                        </span>
                                    </p>
                                    <?php if ($status['status'] === 'Pending'): ?>
                                        <p class="text-gray-600"><strong>Queue Position:</strong> <?php echo $status['queue_position']; ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif (isset($status_message)): ?>
                        <div class="p-4 bg-yellow-100 text-yellow-800 rounded-lg shadow-md"><?php echo $status_message; ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; // Assuming you have a footer file ?>
</body>
</html>