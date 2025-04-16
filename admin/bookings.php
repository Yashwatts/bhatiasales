<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: admin.php");
    exit();
}

include 'db.php';

// Handle status updates with prepared statements
if (isset($_POST['update_status'])) {
    $booking_id = (int)$_POST['booking_id'];
    $new_status = $_POST['new_status'];

    $stmt = $conn->prepare("UPDATE online_bookings SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $booking_id);
    if ($stmt->execute()) {
        $success_message = "Booking status updated successfully!";
    } else {
        $error_message = "Error updating status: " . $conn->error;
    }
    $stmt->close();
}

// Handle status filter
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : 'All';
$where_clause = $status_filter === 'All' ? '' : "WHERE ob.status = '$status_filter'";

// Fetch all bookings with product details
$bookings_query = "
    SELECT ob.id, p.name AS product_name, p.price, ob.user_name, ob.contact, ob.booking_date, ob.status
    FROM online_bookings ob
    JOIN products p ON ob.product_id = p.id
    $where_clause
    ORDER BY ob.booking_date DESC
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
    <title>Bookings - Admin Dashboard</title>
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
        .status-pending {
            background-color: #fef3c7;
            color: #d97706;
        }
        .status-confirmed {
            background-color: #d1fae5;
            color: #059669;
        }
        .status-cancelled {
            background-color: #fee2e2;
            color: #dc2626;
        }
        .action-btn {
            transition: background 0.3s ease, transform 0.3s ease;
        }
        .action-btn:hover {
            transform: scale(1.05);
        }
        .confirm-btn {
            background: linear-gradient(90deg, #10b981, #059669);
        }
        .confirm-btn:hover {
            background: linear-gradient(90deg, #059669, #10b981);
        }
        .cancel-btn {
            background: linear-gradient(90deg, #ef4444, #dc2626);
        }
        .cancel-btn:hover {
            background: linear-gradient(90deg, #dc2626, #ef4444);
        }
        .filter-btn {
            background: linear-gradient(90deg, #4f46e5, #6b7280);
            transition: all 0.3s ease;
        }
        .filter-btn:hover {
            background: linear-gradient(90deg, #4338ca, #4b5563);
        }
        .filter-btn.active {
            background: linear-gradient(90deg, #4338ca, #4b5563);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        th, td {
            padding: 1.25rem; /* Increased padding for better spacing */
        }
        thead tr {
            background: linear-gradient(90deg, #e5e7eb, #d1d5db);
        }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased">
    <!-- Header Section -->
    <div class="header text-white p-6 flex justify-between items-center">
        <h2 class="text-3xl font-extrabold tracking-tight">Bookings Management</h2>
        <a href="dashboard.php" class="text-white px-4 py-2 rounded-lg font-semibold shadow-md bg-gray-700 hover:bg-gray-600 transition-colors">Back to Dashboard</a>
    </div>

    <!-- Bookings Table -->
    <div class="bookings-container max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <?php if (isset($success_message)): ?>
            <div class="mb-6 p-4 bg-green-100 text-green-800 rounded-lg shadow-md"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
            <div class="mb-6 p-4 bg-red-100 text-red-800 rounded-lg shadow-md"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="table-container p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-semibold text-gray-900">All Bookings</h3>
                <div class="flex space-x-3">
                    <a href="bookings.php?status=All" class="filter-btn text-white px-5 py-2 rounded-lg font-medium <?php echo $status_filter === 'All' ? 'active' : ''; ?>">All</a>
                    <a href="bookings.php?status=Pending" class="filter-btn text-white px-5 py-2 rounded-lg font-medium <?php echo $status_filter === 'Pending' ? 'active' : ''; ?>">Pending</a>
                    <a href="bookings.php?status=Confirmed" class="filter-btn text-white px-5 py-2 rounded-lg font-medium <?php echo $status_filter === 'Confirmed' ? 'active' : ''; ?>">Confirmed</a>
                    <a href="bookings.php?status=Cancelled" class="filter-btn text-white px-5 py-2 rounded-lg font-medium <?php echo $status_filter === 'Cancelled' ? 'active' : ''; ?>">Cancelled</a>
                </div>
            </div>

            <?php if (mysqli_num_rows($bookings_result) > 0): ?>
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-gray-700">
                            <th class="font-semibold">Product Name</th>
                            <th class="font-semibold">Price</th>
                            <th class="font-semibold">User Name</th>
                            <th class="font-semibold">Contact</th>
                            <th class="font-semibold">Booking Date</th>
                            <th class="font-semibold">Status</th>
                            <th class="font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($booking = mysqli_fetch_assoc($bookings_result)): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td><?php echo htmlspecialchars($booking['product_name']); ?></td>
                                <td>₹<?php echo number_format($booking['price'], 2); ?></td>
                                <td><?php echo htmlspecialchars($booking['user_name']); ?></td>
                                <td><?php echo htmlspecialchars($booking['contact']); ?></td>
                                <td><?php echo date('d M Y, H:i', strtotime($booking['booking_date'])); ?></td>
                                <td>
                                    <span class="px-3 py-1 rounded-full text-sm font-medium <?php
                                        echo $booking['status'] === 'Pending' ? 'status-pending' :
                                            ($booking['status'] === 'Confirmed' ? 'status-confirmed' : 'status-cancelled');
                                    ?>">
                                        <?php echo htmlspecialchars($booking['status']); ?>
                                    </span>
                                </td>
                                <td class="flex space-x-2">
                                    <?php if ($booking['status'] === 'Pending'): ?>
                                        <form method="post" class="inline">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                            <input type="hidden" name="new_status" value="Confirmed">
                                            <button type="submit" name="update_status" class="confirm-btn action-btn text-white px-4 py-2 rounded-lg font-medium">Confirm</button>
                                        </form>
                                        <form method="post" class="inline">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                            <input type="hidden" name="new_status" value="Cancelled">
                                            <button type="submit" name="update_status" class="cancel-btn action-btn text-white px-4 py-2 rounded-lg font-medium">Cancel</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-center text-gray-500 py-6">No bookings found.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>