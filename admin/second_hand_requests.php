<?php
session_start();
include 'db.php'; // Include database connection from admin/db.php
if (!isset($_SESSION['admin'])) {
    header("Location: admin.php");
    exit();
}

// Handle approval, rejection, or sold status
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['request_id'])) {
    $request_id = $_POST['request_id'];
    $status = $_POST['status']; // 'Approved', 'Declined', or 'Sold'

    $update_query = "UPDATE second_hand_vehicles SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    if ($stmt) {
        $stmt->bind_param("si", $status, $request_id);
        if ($stmt->execute()) {
            $success_message = "Request status updated to '$status' successfully!";
        } else {
            $error_message = "Failed to update status!";
        }
        $stmt->close();
    } else {
        $error_message = "Database error: Unable to prepare statement!";
    }
}

// Fetch second-hand vehicle requests
$query = "SELECT id, user_name, vehicle_type, company, model, color, travelled_km, asking_price, location, contact, vehicle_image, registered_on, status FROM second_hand_vehicles ORDER BY registered_on DESC";
$result = $conn->query($query);

// Check if query execution was successful
if (!$result) {
    die("Database Query Failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Second-Hand Vehicle Requests - Bhatia Sales</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom Styles for Professional Design */
        .requests-container {
            background: linear-gradient(135deg, #f9fafb, #ffffff);
            min-height: calc(100vh - 80px);
        }
        .request-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .request-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
        }
        .approve-btn {
            background: linear-gradient(90deg, #22c55e, #16a34a);
            transition: background 0.3s ease, transform 0.3s ease;
        }
        .approve-btn:hover {
            background: linear-gradient(90deg, #16a34a, #15803d);
            transform: scale(1.05);
        }
        .decline-btn {
            background: linear-gradient(90deg, #ef4444, #dc2626);
            transition: background 0.3s ease, transform 0.3s ease;
        }
        .decline-btn:hover {
            background: linear-gradient(90deg, #dc2626, #b91c1c);
            transform: scale(1.05);
        }
        .sold-btn {
            background: linear-gradient(90deg, #3b82f6, #1d4ed8);
            transition: background 0.3s ease, transform 0.3s ease;
        }
        .sold-btn:hover {
            background: linear-gradient(90deg, #1d4ed8, #1e40af);
            transform: scale(1.05);
        }
        .header {
            background: linear-gradient(90deg, #1f2937, #374151);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        .pending-card {
            border-left: 4px solid #f59e0b; /* Amber for pending */
        }
        .approved-card {
            border-left: 4px solid #22c55e; /* Green for approved */
            opacity: 0.9;
        }
        .declined-card {
            border-left: 4px solid #ef4444; /* Red for declined */
            opacity: 0.9;
        }
        .sold-card {
            border-left: 4px solid #3b82f6; /* Blue for sold */
            opacity: 0.9;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased">
    <!-- Header -->
    <div class="header text-white p-6 flex justify-between items-center">
        <h2 class="text-3xl font-extrabold tracking-tight">Second-Hand Vehicle Requests</h2>
        <a href="dashboard.php" class="text-indigo-200 hover:text-indigo-100 font-medium transition-colors">Back to Dashboard</a>
    </div>

    <!-- Requests Section -->
    <div class="requests-container max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <?php if (isset($success_message)): ?>
            <div class="mb-8 p-4 bg-green-100 text-green-800 rounded-lg shadow-md"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
            <div class="mb-8 p-4 bg-red-100 text-red-800 rounded-lg shadow-md"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="request-card p-6 <?php 
                        echo $row['status'] === 'Pending' ? 'pending-card' : 
                            ($row['status'] === 'Approved' ? 'approved-card' : 
                            ($row['status'] === 'Declined' ? 'declined-card' : 'sold-card')); 
                    ?>">
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-xl font-semibold text-gray-900"><?php echo htmlspecialchars($row['company'] . ' ' . $row['model']); ?></h3>
                            <span class="px-3 py-1 rounded-full text-sm font-medium <?php 
                                echo $row['status'] === 'Pending' ? 'bg-amber-100 text-amber-800' : 
                                    ($row['status'] === 'Approved' ? 'bg-green-100 text-green-800' : 
                                    ($row['status'] === 'Declined' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800')); 
                            ?>">
                                <?php echo htmlspecialchars($row['status']); ?>
                            </span>
                        </div>
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <p class="text-gray-600"><strong>Seller:</strong> <?php echo htmlspecialchars($row['user_name']); ?></p>
                                <p class="text-gray-600"><strong>Type:</strong> <?php echo htmlspecialchars($row['vehicle_type']); ?></p>
                                <p class="text-gray-600"><strong>Color:</strong> <?php echo htmlspecialchars($row['color']); ?></p>
                                <p class="text-gray-600"><strong>Travelled:</strong> <?php echo number_format($row['travelled_km']); ?> KM</p>
                            </div>
                            <div>
                                <p class="text-gray-600"><strong>Price:</strong> ₹<?php echo number_format($row['asking_price'], 2); ?></p>
                                <p class="text-gray-600"><strong>Location:</strong> <?php echo htmlspecialchars($row['location']); ?></p>
                                <p class="text-gray-600"><strong>Contact:</strong> <?php echo htmlspecialchars($row['contact']); ?></p>
                                <p class="text-gray-600"><strong>Registered:</strong> <?php echo date('d M Y', strtotime($row['registered_on'])); ?></p>
                            </div>
                        </div>
                        <div class="mb-4">
                            <img src="uploads/<?php echo htmlspecialchars($row['vehicle_image']); ?>" alt="Vehicle Image" class="w-full h-40 object-cover rounded-lg">
                        </div>
                        <?php if ($row['status'] === 'Pending'): ?>
                            <form method="POST" class="flex gap-4">
                                <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="status" value="Approved" class="approve-btn w-full text-white py-2 rounded-lg font-semibold shadow-md">Approve</button>
                                <button type="submit" name="status" value="Declined" class="decline-btn w-full text-white py-2 rounded-lg font-semibold shadow-md">Decline</button>
                            </form>
                        <?php elseif ($row['status'] === 'Approved'): ?>
                            <form method="POST" class="flex gap-4">
                                <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="status" value="Sold" class="sold-btn w-full text-white py-2 rounded-lg font-semibold shadow-md">Mark as Sold</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-full text-center py-12">
                    <p class="text-gray-500 text-lg font-medium">No vehicle requests available.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>