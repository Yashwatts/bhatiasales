<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: admin.php");
    exit();
}

include 'db.php';

// Fetch admin details
$admin_username = $_SESSION['admin'];
$query = "SELECT username, profile_image FROM admin WHERE username = '$admin_username'";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Error fetching admin details: " . mysqli_error($conn));
}

$admin = mysqli_fetch_assoc($result);

// Fetch counts (unchanged from your code)
$product_query = "SELECT COUNT(*) AS count FROM products";
$product_result = mysqli_query($conn, $product_query);
$product_count = $product_result ? mysqli_fetch_assoc($product_result)['count'] : 0;

$feedback_query = "SELECT COUNT(*) AS count FROM feedback WHERE status = 'pending'";
$feedback_result = mysqli_query($conn, $feedback_query);
$feedback_count = $feedback_result ? mysqli_fetch_assoc($feedback_result)['count'] : 0;

$second_hand_query = "SELECT COUNT(*) AS count FROM second_hand_vehicles WHERE status = 'pending'";
$second_hand_result = mysqli_query($conn, $second_hand_query);
$second_hand_count = $second_hand_result ? mysqli_fetch_assoc($second_hand_result)['count'] : 0;

$bookings_query = "SELECT COUNT(*) AS count FROM online_bookings WHERE status = 'Pending'";
$bookings_result = mysqli_query($conn, $bookings_query);
$bookings_count = $bookings_result ? mysqli_fetch_assoc($bookings_result)['count'] : 0;

$service_requests_query = "SELECT COUNT(*) AS count FROM service_bookings WHERE status = 'Pending'";
$service_requests_result = mysqli_query($conn, $service_requests_query);
$service_requests_count = $service_requests_result ? mysqli_fetch_assoc($service_requests_result)['count'] : 0;

$accessories_query = "SELECT COUNT(*) AS count FROM accessories";
$accessories_result = mysqli_query($conn, $accessories_query);
$accessories_count = $accessories_result ? mysqli_fetch_assoc($accessories_result)['count'] : 0;

$orders_query = "SELECT COUNT(*) AS count FROM accessory_orders WHERE status = 'Pending'";
$orders_result = mysqli_query($conn, $orders_query);
$orders_count = $orders_result ? mysqli_fetch_assoc($orders_result)['count'] : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Bhatia Sales</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .dashboard-container {
            background: linear-gradient(135deg, #f9fafb, #ffffff);
            min-height: calc(100vh - 80px);
        }
        .card {
            background: linear-gradient(135deg, #78AEC6, #5F8FA6);
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-decoration: none;
            color: white;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
            background: linear-gradient(135deg, #5F8FA6, #78AEC6);
        }
        .header {
            background: linear-gradient(90deg, #1f2937, #374151);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        .profile-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #ffffff;
            transition: transform 0.3s ease;
        }
        .profile-icon:hover {
            transform: scale(1.1);
        }
        .logout-btn {
            background: linear-gradient(90deg, #4f46e5, #6b7280);
            transition: background 0.3s ease, transform 0.3s ease;
        }
        .logout-btn:hover {
            background: linear-gradient(90deg, #4338ca, #4b5563);
            transform: scale(1.05);
        }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased">
    <!-- Header Section -->
    <div class="header text-white p-6 flex justify-between items-center">
        <h2 class="text-3xl font-extrabold tracking-tight">Admin Dashboard</h2>
        <div class="admin-info flex items-center space-x-4">
            <span class="text-lg font-medium"><?php echo htmlspecialchars($admin['username']); ?></span>
            <a href="profile.php">
                <img src="uploads/<?php echo htmlspecialchars($admin['profile_image']); ?>" alt="Profile" class="profile-icon">
            </a>
            <a href="logout.php" class="logout-btn text-white px-4 py-2 rounded-lg font-semibold shadow-md">Logout</a>
        </div>
    </div>

    <!-- Dashboard Cards (unchanged) -->
    <div class="dashboard-container max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <a href="products.php" class="card p-6 text-center"><h3 class="text-2xl font-semibold mb-2">Products</h3><p class="text-lg">Total: <?php echo $product_count; ?></p></a>
            <a href="feedback.php" class="card p-6 text-center"><h3 class="text-2xl font-semibold mb-2">Review & Feedback</h3><p class="text-lg">Pending: <?php echo $feedback_count; ?></p></a>
            <a href="second_hand_requests.php" class="card p-6 text-center"><h3 class="text-2xl font-semibold mb-2">Second Hand Requests</h3><p class="text-lg">Pending: <?php echo $second_hand_count; ?></p></a>
            <a href="bookings.php" class="card p-6 text-center"><h3 class="text-2xl font-semibold mb-2">Bookings</h3><p class="text-lg">Pending: <?php echo $bookings_count; ?></p></a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <a href="service_requests.php" class="card p-6 text-center mx-auto w-full"><h3 class="text-2xl font-semibold mb-2">Service Requests</h3><p class="text-lg">Pending: <?php echo $service_requests_count; ?></p></a>
            <a href="accessories.php" class="card p-6 text-center mx-auto w-full"><h3 class="text-2xl font-semibold mb-2">Accessories</h3><p class="text-lg">Total: <?php echo $accessories_count; ?></p></a>
            <a href="manage_orders.php" class="card p-6 text-center mx-auto w-full"><h3 class="text-2xl font-semibold mb-2">Manage Orders</h3><p class="text-lg">Pending: <?php echo $orders_count; ?></p></a>
        </div>
    </div>
</body>
</html>