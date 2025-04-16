<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: admin.php");
    exit();
}

include 'db.php';

// Handle status update
if (isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = mysqli_real_escape_string($conn, $_POST['status']);
    $update_query = "UPDATE accessory_orders SET status = '$new_status' WHERE id = $order_id";
    if (!mysqli_query($conn, $update_query)) {
        $error_message = "Error updating status: " . mysqli_error($conn);
    }
}

// Fetch all orders
$orders_query = "SELECT id, user_name, contact, items, total, status, created_at 
                 FROM accessory_orders 
                 ORDER BY created_at DESC";
$orders_result = mysqli_query($conn, $orders_query);

if (!$orders_result) {
    die("Error fetching orders: " . mysqli_error($conn));
}

$orders = [];
while ($row = mysqli_fetch_assoc($orders_result)) {
    $row['items'] = json_decode($row['items'], true); // Decode JSON items
    $orders[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Bhatia Sales</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .orders-container {
            background: linear-gradient(135deg, #f9fafb, #ffffff);
            min-height: calc(100vh - 80px);
        }
        .order-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
        }
        .status-pending { border-left: 4px solid #f59e0b; }
        .status-confirmed { border-left: 4px solid #22c55e; }
        .status-cancelled { border-left: 4px solid #ef4444; }
        .update-btn {
            background: linear-gradient(90deg, #4f46e5, #6b7280);
            transition: background 0.3s ease, transform 0.3s ease;
        }
        .update-btn:hover {
            background: linear-gradient(90deg, #4338ca, #4b5563);
            transform: scale(1.05);
        }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased">
    <div class="header text-white p-6 flex justify-between items-center bg-gradient-to-r from-gray-800 to-gray-700 shadow-lg">
        <h2 class="text-3xl font-extrabold tracking-tight">Manage Orders</h2>
        <a href="dashboard.php" class="text-white px-4 py-2 rounded-lg font-semibold shadow-md bg-gradient-to-r from-blue-500 to-gray-500 hover:from-blue-600 hover:to-gray-600">Back to Dashboard</a>
    </div>

    <div class="orders-container max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <?php if (isset($error_message)): ?>
            <div class="mb-8 p-4 bg-red-100 text-red-800 rounded-lg shadow-md"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="space-y-6">
            <?php if (count($orders) > 0): ?>
                <?php foreach ($orders as $order): ?>
                    <div class="order-card p-6 <?php echo $order['status'] === 'Pending' ? 'status-pending' : ($order['status'] === 'Confirmed' ? 'status-confirmed' : 'status-cancelled'); ?>">
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Order #<?php echo $order['id']; ?></h3>
                        <p class="text-gray-600"><strong>User:</strong> <?php echo htmlspecialchars($order['user_name']); ?></p>
                        <p class="text-gray-600"><strong>Contact:</strong> <?php echo htmlspecialchars($order['contact']); ?></p>
                        <p class="text-gray-600"><strong>Ordered On:</strong> <?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?></p>
                        <div class="mt-2">
                            <p class="text-gray-600 font-medium">Items:</p>
                            <ul class="list-disc list-inside text-gray-600">
                                <?php foreach ($order['items'] as $item): ?>
                                    <li><?php echo htmlspecialchars($item['name']); ?> (₹<?php echo number_format($item['price'], 2); ?> x <?php echo $item['quantity']; ?>)</li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <p class="text-gray-600 mt-2"><strong>Total:</strong> ₹<?php echo number_format($order['total'], 2); ?></p>
                        <form method="post" class="mt-4 flex items-center space-x-4">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <select name="status" class="input-field px-4 py-2 border border-gray-300 rounded-lg focus:outline-none text-gray-800">
                                <option value="Pending" <?php echo $order['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="Confirmed" <?php echo $order['status'] === 'Confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="Cancelled" <?php echo $order['status'] === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                            <button type="submit" name="update_status" class="update-btn text-white px-4 py-2 rounded-lg font-semibold shadow-md">Update Status</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-12">
                    <p class="text-gray-500 text-lg font-medium">No orders found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>