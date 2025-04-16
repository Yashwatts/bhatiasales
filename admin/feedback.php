<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: admin.php");
    exit();
}

include 'db.php';

// Handle resolving feedback
if (isset($_GET['resolve_id'])) {
    $resolve_id = mysqli_real_escape_string($conn, $_GET['resolve_id']);
    $update_query = "UPDATE feedback SET status = 'resolved' WHERE id = '$resolve_id'";
    if (mysqli_query($conn, $update_query)) {
        $success_message = "Feedback marked as resolved!";
    } else {
        $error_message = "Error updating feedback: " . mysqli_error($conn);
    }
}

// Fetch all feedback entries (including id for resolve functionality)
$feedback_query = "SELECT id, customer_name, email, phone, message, status FROM feedback ORDER BY id DESC";
$feedback_result = mysqli_query($conn, $feedback_query);

if (!$feedback_result) {
    die("Error fetching feedback: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Management - Bhatia Sales</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom Styles for Professional Design */
        .feedback-container {
            background: linear-gradient(135deg, #f9fafb, #ffffff);
            min-height: calc(100vh - 80px);
        }
        .feedback-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .feedback-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
        }
        .resolve-btn {
            background: linear-gradient(90deg, #4f46e5, #6b7280);
            transition: background 0.3s ease, transform 0.3s ease;
        }
        .resolve-btn:hover {
            background: linear-gradient(90deg, #4338ca, #4b5563);
            transform: scale(1.05);
        }
        .resolved-card {
            background: #e5e7eb;
            opacity: 0.85;
        }
        .header {
            background: linear-gradient(90deg, #1f2937, #374151);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased">
    <!-- Header -->
    <div class="header text-white p-6 flex justify-between items-center">
        <h2 class="text-3xl font-extrabold tracking-tight">Feedback Management</h2>
        <a href="dashboard.php" class="text-indigo-200 hover:text-indigo-100 font-medium transition-colors">Back to Dashboard</a>
    </div>

    <!-- Feedback Section -->
    <div class="feedback-container max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <?php if (isset($success_message)): ?>
            <div class="mb-8 p-4 bg-green-100 text-green-800 rounded-lg shadow-md"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
            <div class="mb-8 p-4 bg-red-100 text-red-800 rounded-lg shadow-md"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php if (mysqli_num_rows($feedback_result) > 0): ?>
                <?php while ($feedback = mysqli_fetch_assoc($feedback_result)): ?>
                    <div class="feedback-card p-6 <?php echo $feedback['status'] === 'resolved' ? 'resolved-card' : ''; ?>">
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-xl font-semibold text-gray-900"><?php echo htmlspecialchars($feedback['customer_name']); ?></h3>
                            <span class="px-3 py-1 rounded-full text-sm font-medium <?php echo $feedback['status'] === 'resolved' ? 'bg-gray-300 text-gray-700' : 'bg-indigo-100 text-indigo-800'; ?>">
                                <?php echo ucfirst($feedback['status'] ?? 'pending'); ?>
                            </span>
                        </div>
                        <div class="space-y-3">
                            <p class="text-gray-700 flex items-center">
                                <svg class="h-5 w-5 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                <?php echo htmlspecialchars($feedback['email']); ?>
                            </p>
                            <p class="text-gray-700 flex items-center">
                                <svg class="h-5 w-5 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                                <?php echo htmlspecialchars($feedback['phone']); ?>
                            </p>
                            <p class="text-gray-600 text-sm leading-relaxed"><?php echo htmlspecialchars($feedback['message']); ?></p>
                        </div>
                        <?php if ($feedback['status'] !== 'resolved'): ?>
                            <a href="feedback.php?resolve_id=<?php echo htmlspecialchars($feedback['id']); ?>" class="resolve-btn inline-block mt-4 text-white px-6 py-2 rounded-lg font-semibold shadow-md">Resolve</a>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-full text-center py-12">
                    <p class="text-gray-500 text-lg font-medium">No feedback available.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>