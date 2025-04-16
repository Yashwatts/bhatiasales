<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: admin.php");
    exit();
}

include 'db.php';

// Handle status updates with queue adjustment
if (isset($_POST['update_status'])) {
    $booking_id = (int)$_POST['booking_id'];
    $new_status = $_POST['new_status'];

    // Start transaction to ensure atomic updates
    $conn->begin_transaction();
    try {
        // Update the booking status
        $stmt = $conn->prepare("UPDATE service_bookings SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $booking_id);
        if (!$stmt->execute()) {
            throw new Exception("Error updating status: " . $conn->error);
        }
        $stmt->close();

        // If status is "Completed" or "Declined," adjust queue positions
        if ($new_status === 'Completed' || $new_status === 'Declined') {
            // Get the service type of the updated booking
            $type_query = "SELECT service_type FROM service_bookings WHERE id = $booking_id";
            $type_result = mysqli_query($conn, $type_query);
            $service_type = mysqli_fetch_assoc($type_result)['service_type'];

            // Reorder queue positions for pending bookings of the same service type
            $queue_update_query = "
                UPDATE service_bookings 
                SET queue_position = queue_position - 1 
                WHERE service_type = '$service_type' 
                AND status = 'Pending' 
                AND queue_position > (
                    SELECT queue_position FROM service_bookings WHERE id = $booking_id
                )
            ";
            if (!mysqli_query($conn, $queue_update_query)) {
                throw new Exception("Error updating queue: " . mysqli_error($conn));
            }
        }

        $conn->commit();
        $success_message = "Service status updated to '$new_status' successfully!";
    } catch (Exception $e) {
        $conn->rollback();
        $error_message = $e->getMessage();
    }
}

// Fetch all service bookings
$bookings_query = "
    SELECT id, service_type, user_name, contact, booking_date, status, queue_position 
    FROM service_bookings 
    ORDER BY booking_date DESC
";
$bookings_result = mysqli_query($conn, $bookings_query);

if (!$bookings_result) {
    die("Error fetching bookings: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Requests - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .bookings-container {
            background: linear-gradient(135deg, #f9fafb, #ffffff);
            min-height: calc(100vh - 80px);
        }
        .header {
            background: linear-gradient(90deg, #1f2937, #374151);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        .table-container {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
        }
        .status-pending { background-color: #fef3c7; color: #d97706; }
        .status-confirmed { background-color: #d1fae5; color: #059669; }
        .status-completed { background-color: #e5e7eb; color: #6b7280; }
        .status-declined { background-color: #fee2e2; color: #dc2626; }
        .action-btn {
            transition: background 0.3s ease, transform 0.3s ease;
        }
        .action-btn:hover {
            transform: scale(1.05);
        }
        .confirm-btn { background: linear-gradient(90deg, #10b981, #059669); }
        .confirm-btn:hover { background: linear-gradient(90deg, #059669, #10b981); }
        .complete-btn { background: linear-gradient(90deg, #6b7280, #4b5563); }
        .complete-btn:hover { background: linear-gradient(90deg, #4b5563, #6b7280); }
        .decline-btn { background: linear-gradient(90deg, #ef4444, #dc2626); }
        .decline-btn:hover { background: linear-gradient(90deg, #dc2626, #ef4444); }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased">
    <div class="header text-white p-6 flex justify-between items-center">
        <h2 class="text-3xl font-extrabold tracking-tight">Service Requests</h2>
        <a href="dashboard.php" class="text-white px-4 py-2 rounded-lg font-semibold shadow-md bg-gray-700 hover:bg-gray-600 transition-colors">Back to Dashboard</a>
    </div>

    <div class="bookings-container max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <?php if (isset($success_message)): ?>
            <div class="mb-6 p-4 bg-green-100 text-green-800 rounded-lg shadow-md"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
            <div class="mb-6 p-4 bg-red-100 text-red-800 rounded-lg shadow-md"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="table-container p-6">
            <h3 class="text-2xl font-semibold text-gray-900 mb-6">Service Requests</h3>
            <?php if (mysqli_num_rows($bookings_result) > 0): ?>
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-200 text-gray-700">
                            <th class="p-4 font-semibold">Service Type</th>
                            <th class="p-4 font-semibold">User Name</th>
                            <th class="p-4 font-semibold">Contact</th>
                            <th class="p-4 font-semibold">Booking Date</th>
                            <th class="p-4 font-semibold">Status</th>
                            <th class="p-4 font-semibold">Queue Position</th>
                            <th class="p-4 font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($booking = mysqli_fetch_assoc($bookings_result)): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="p-4"><?php echo htmlspecialchars($booking['service_type']); ?></td>
                                <td class="p-4"><?php echo htmlspecialchars($booking['user_name']); ?></td>
                                <td class="p-4"><?php echo htmlspecialchars($booking['contact']); ?></td>
                                <td class="p-4"><?php echo date('d M Y, H:i', strtotime($booking['booking_date'])); ?></td>
                                <td class="p-4">
                                    <span class="px-2 py-1 rounded-full text-sm font-medium <?php
                                        echo $booking['status'] === 'Pending' ? 'status-pending' :
                                            ($booking['status'] === 'Confirmed' ? 'status-confirmed' :
                                            ($booking['status'] === 'Completed' ? 'status-completed' : 'status-declined'));
                                    ?>">
                                        <?php echo htmlspecialchars($booking['status']); ?>
                                    </span>
                                </td>
                                <td class="p-4"><?php echo $booking['status'] === 'Pending' ? $booking['queue_position'] : '-'; ?></td>
                                <td class="p-4 flex space-x-2">
                                    <?php if ($booking['status'] === 'Pending'): ?>
                                        <form method="post" class="inline">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                            <input type="hidden" name="new_status" value="Confirmed">
                                            <button type="submit" name="update_status" class="confirm-btn action-btn text-white px-3 py-1 rounded-lg font-medium">Confirm</button>
                                        </form>
                                        <form method="post" class="inline">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                            <input type="hidden" name="new_status" value="Completed">
                                            <button type="submit" name="update_status" class="complete-btn action-btn text-white px-3 py-1 rounded-lg font-medium">Complete</button>
                                        </form>
                                        <form method="post" class="inline">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                            <input type="hidden" name="new_status" value="Declined">
                                            <button type="submit" name="update_status" class="decline-btn action-btn text-white px-3 py-1 rounded-lg font-medium">Decline</button>
                                        </form>
                                    <?php elseif ($booking['status'] === 'Confirmed'): ?>
                                        <form method="post" class="inline">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                            <input type="hidden" name="new_status" value="Completed">
                                            <button type="submit" name="update_status" class="complete-btn action-btn text-white px-3 py-1 rounded-lg font-medium">Complete</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-center text-gray-500 py-6">No service bookings found.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>