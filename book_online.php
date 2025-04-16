<?php
session_start();
include 'header.php'; // Including the header
include 'admin/db.php'; // Database connection

// Handle Booking Submission
if (isset($_POST['book'])) {
    $product_id = (int)$_POST['product_id'];
    $user_name = mysqli_real_escape_string($conn, $_POST['user_name']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);

    $product_query = "SELECT name FROM products WHERE id = $product_id";
    $product_result = mysqli_query($conn, $product_query);
    if ($product_result && mysqli_num_rows($product_result) > 0) {
        $sql = "INSERT INTO online_bookings (product_id, user_name, contact, status) 
                VALUES ($product_id, '$user_name', '$contact', 'Pending')";
        
        if (mysqli_query($conn, $sql)) {
            $success_message = "Product booked successfully! Awaiting confirmation.";
        } else {
            $error_message = "Error booking product: " . mysqli_error($conn);
        }
    } else {
        $error_message = "Invalid product selected.";
    }
}

// Handle Cart Purchase Submission
if (isset($_POST['book_cart']) && !empty($_SESSION['cart'])) {
    $user_name = mysqli_real_escape_string($conn, $_POST['user_name']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $items = json_encode($_SESSION['cart']);
    $total = array_sum(array_map(function($item) { return $item['price'] * $item['quantity']; }, $_SESSION['cart']));

    $sql = "INSERT INTO accessory_orders (user_name, contact, items, total, status) 
            VALUES ('$user_name', '$contact', '$items', '$total', 'Pending')";
    if (mysqli_query($conn, $sql)) {
        $success_message = "Confirmation sent! Your accessory order has been placed.";
        unset($_SESSION['cart']); // Clear the cart after successful order
    } else {
        $error_message = "Error placing accessory order: " . mysqli_error($conn);
    }
}

// Handle Booking Status Check
$booking_results = [];
if (isset($_POST['check_status'])) {
    $check_contact = mysqli_real_escape_string($conn, $_POST['check_contact']);
    $status_query = "SELECT p.name, p.price, b.booking_date, b.status 
                     FROM online_bookings b 
                     JOIN products p ON b.product_id = p.id 
                     WHERE b.contact = '$check_contact' 
                     ORDER BY b.booking_date DESC";
    $status_result = mysqli_query($conn, $status_query);

    if ($status_result && mysqli_num_rows($status_result) > 0) {
        while ($row = mysqli_fetch_assoc($status_result)) {
            $booking_results[] = $row;
        }
    } else {
        $status_message = "No bookings found for this mobile number.";
    }
}

// Handle Orders Check
$order_results = [];
if (isset($_POST['check_orders'])) {
    $order_contact = mysqli_real_escape_string($conn, $_POST['order_contact']);
    
    // Fetch product bookings
    $product_query = "SELECT p.name, p.price, b.booking_date, b.status, 'Product' as type 
                      FROM online_bookings b 
                      JOIN products p ON b.product_id = p.id 
                      WHERE b.contact = '$order_contact'";
    $product_result = mysqli_query($conn, $product_query);
    
    // Fetch accessory orders
    $accessory_query = "SELECT items, total, created_at as booking_date, status, 'Accessories' as type 
                        FROM accessory_orders 
                        WHERE contact = '$order_contact'";
    $accessory_result = mysqli_query($conn, $accessory_query);

    if ($product_result && mysqli_num_rows($product_result) > 0) {
        while ($row = mysqli_fetch_assoc($product_result)) {
            $order_results[] = $row;
        }
    }
    if ($accessory_result && mysqli_num_rows($accessory_result) > 0) {
        while ($row = mysqli_fetch_assoc($accessory_result)) {
            $row['items'] = json_decode($row['items'], true); // Decode JSON items
            $order_results[] = $row;
        }
    }
    if (empty($order_results)) {
        $order_message = "No orders found for this mobile number.";
    }
}

// Get Product Details if product_id is provided
$selected_product = null;
if (isset($_GET['product_id'])) {
    $product_id = (int)$_GET['product_id'];
    $product_query = "SELECT id, name, price FROM products WHERE id = $product_id";
    $product_result = mysqli_query($conn, $product_query);
    if ($product_result && mysqli_num_rows($product_result) > 0) {
        $selected_product = mysqli_fetch_assoc($product_result);
    }
}

// Check if coming from cart
$from_cart = isset($_GET['from_cart']) && $_GET['from_cart'] == 1;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Online - Bhatia Sales</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .booking-container {
            background: linear-gradient(135deg, #f3f4f6, #ffffff);
            min-height: calc(100vh - 160px);
        }
        .form-container, .booking-card, .cart-container {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .form-container:hover, .booking-card:hover, .cart-container:hover {
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
        .submit-btn, .check-btn, .accessories-btn {
            background: linear-gradient(90deg, #4f46e5, #6b7280);
            transition: background 0.3s ease, transform 0.3s ease;
        }
        .submit-btn:hover, .check-btn:hover, .accessories-btn:hover {
            background: linear-gradient(90deg, #4338ca, #4b5563);
            transform: scale(1.05);
        }
        .status-pending { border-left: 4px solid #f59e0b; }
        .status-confirmed { border-left: 4px solid #22c55e; }
        .status-cancelled { border-left: 4px solid #ef4444; }
    </style>
    <script>
        window.onload = function() {
            <?php if (!empty($booking_results) || isset($status_message)): ?>
                document.getElementById('status-section').scrollIntoView({ behavior: 'smooth' });
            <?php endif; ?>
            <?php if ($from_cart): ?>
                document.getElementById('cart-section').scrollIntoView({ behavior: 'smooth' });
            <?php endif; ?>
            <?php if (!empty($order_results) || isset($order_message)): ?>
                document.getElementById('orders-section').scrollIntoView({ behavior: 'smooth' });
            <?php endif; ?>

            const productSelect = document.getElementById('product_id');
            const priceDisplay = document.getElementById('price-display');
            if (productSelect) {
                productSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const price = selectedOption.getAttribute('data-price');
                    if (price) {
                        priceDisplay.textContent = `Price: ₹${parseFloat(price).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                        priceDisplay.classList.remove('hidden');
                    } else {
                        priceDisplay.textContent = '';
                        priceDisplay.classList.add('hidden');
                    }
                });
            }
        };
    </script>
</head>
<body class="bg-gray-50 font-sans antialiased">
    <?php include 'cart.php'; // Include the cart component ?>
    <div class="booking-container max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-8 text-center tracking-tight">Book Products Online</h2>

        <?php if (isset($success_message)): ?>
            <div class="mb-8 p-4 bg-green-100 text-green-800 rounded-lg shadow-md"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
            <div class="mb-8 p-4 bg-red-100 text-red-800 rounded-lg shadow-md"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Book Product Form -->
            <div class="form-container p-8">
                <h3 class="text-2xl font-semibold text-gray-900 mb-6">Book a Product</h3>
                <form action="book_online.php" method="post" class="space-y-6">
                    <div>
                        <label for="product_id" class="block text-gray-700 font-medium mb-2">Selected Product</label>
                        <?php if ($selected_product): ?>
                            <input type="text" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-800" value="<?= htmlspecialchars($selected_product['name']) ?>" readonly>
                            <input type="hidden" name="product_id" value="<?= $selected_product['id'] ?>">
                            <div class="mt-2">
                                <label class="block text-gray-700 font-medium mb-1">Price</label>
                                <input type="text" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-800" value="₹<?= number_format($selected_product['price'], 2) ?>" readonly>
                            </div>
                        <?php else: ?>
                            <select id="product_id" name="product_id" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none text-gray-800" required>
                                <option value="" data-price="">-- Select a Product --</option>
                                <?php
                                $products_query = "SELECT id, name, price FROM products ORDER BY name";
                                $products_result = mysqli_query($conn, $products_query);
                                while ($product = mysqli_fetch_assoc($products_result)) {
                                    echo "<option value='" . $product['id'] . "' data-price='" . $product['price'] . "'>" . htmlspecialchars($product['name']) . "</option>";
                                }
                                ?>
                            </select>
                            <p id="price-display" class="mt-2 text-gray-700 font-medium hidden"></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label for="user_name" class="block text-gray-700 font-medium mb-2">Your Name</label>
                        <input type="text" id="user_name" name="user_name" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none text-gray-800" placeholder="Your Name" required>
                    </div>
                    <div>
                        <label for="contact" class="block text-gray-700 font-medium mb-2">Contact Number</label>
                        <input type="text" id="contact" name="contact" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none text-gray-800" placeholder="Contact Number" required pattern="[0-9]{10}" title="Enter a valid 10-digit mobile number">
                    </div>
                    <button type="submit" name="book" class="submit-btn w-full text-white py-3 rounded-lg font-semibold shadow-md">Book Now</button>
                </form>
                <p class="mt-4 text-sm text-gray-600 italic">Note: The admin will contact you for a 10% advance booking payment to confirm your booking.</p>
                <a href="accessories.php" class="accessories-btn w-full text-white py-3 rounded-lg font-semibold shadow-md text-center block mt-4">Buy Some Accessories</a>
            </div>

            <!-- Check Booking Status Form -->
            <div class="form-container p-8">
                <h3 class="text-2xl font-semibold text-gray-900 mb-6">Check Booking Status</h3>
                <form action="book_online.php#status-section" method="post" class="space-y-6">
                    <div>
                        <label for="check_contact" class="block text-gray-700 font-medium mb-2">Mobile Number</label>
                        <input type="text" id="check_contact" name="check_contact" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none text-gray-800" placeholder="Enter your mobile number" required pattern="[0-9]{10}" title="Enter a valid 10-digit mobile number" value="<?php echo isset($_POST['check_contact']) ? htmlspecialchars($_POST['check_contact']) : ''; ?>">
                    </div>
                    <button type="submit" name="check_status" class="check-btn w-full text-white py-3 rounded-lg font-semibold shadow-md">Check Status</button>
                </form>

                <!-- Booking Status Results -->
                <div id="status-section" class="mt-6">
                    <?php if (!empty($booking_results)): ?>
                        <div class="space-y-4">
                            <?php foreach ($booking_results as $booking): ?>
                                <div class="booking-card p-4 <?php echo $booking['status'] === 'Pending' ? 'status-pending' : ($booking['status'] === 'Confirmed' ? 'status-confirmed' : 'status-cancelled'); ?>">
                                    <h4 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($booking['name']); ?></h4>
                                    <p class="text-gray-600"><strong>Price:</strong> ₹<?php echo number_format($booking['price'], 2); ?></p>
                                    <p class="text-gray-600"><strong>Booked On:</strong> <?php echo date('d M Y, H:i', strtotime($booking['booking_date'])); ?></p>
                                    <p class="text-gray-600"><strong>Status:</strong> 
                                        <span class="px-2 py-1 rounded-full text-sm font-medium <?php echo $booking['status'] === 'Pending' ? 'bg-amber-100 text-amber-800' : ($booking['status'] === 'Confirmed' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'); ?>">
                                            <?php echo htmlspecialchars($booking['status']); ?>
                                        </span>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif (isset($status_message)): ?>
                        <div class="p-4 bg-yellow-100 text-yellow-800 rounded-lg shadow-md"><?php echo $status_message; ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Cart Items Display -->
        <?php if ($from_cart && !empty($_SESSION['cart'])): ?>
            <div id="cart-section" class="cart-container p-8 mt-12">
                <h3 class="text-2xl font-semibold text-gray-900 mb-6">Your Accessories Cart</h3>
                <div class="space-y-4">
                    <?php 
                    $total = 0;
                    foreach ($_SESSION['cart'] as $item): 
                        $subtotal = $item['price'] * $item['quantity'];
                        $total += $subtotal;
                    ?>
                        <div class="flex justify-between items-center border-b pb-4">
                            <div class="flex items-center gap-4">
                                <img src="admin/uploads/<?php echo htmlspecialchars($item['image']); ?>" alt="Cart Item" class="w-16 h-16 object-cover rounded-lg">
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($item['name']); ?></h4>
                                    <p class="text-gray-600">₹<?php echo number_format($item['price'], 2); ?> x <?php echo $item['quantity']; ?></p>
                                    <p class="text-gray-600"><strong>Color:</strong> <?php echo htmlspecialchars($item['color']); ?></p>
                                    <p class="text-gray-600"><strong>Type:</strong> <?php echo htmlspecialchars($item['type']); ?></p>
                                </div>
                            </div>
                            <p class="text-gray-600 font-medium">₹<?php echo number_format($subtotal, 2); ?></p>
                        </div>
                    <?php endforeach; ?>
                    <div class="flex justify-between items-center pt-4">
                        <p class="text-xl font-semibold text-gray-900">Total:</p>
                        <p class="text-xl font-semibold text-gray-900">₹<?php echo number_format($total, 2); ?></p>
                    </div>
                </div>
                <p class="mt-4 text-sm text-gray-600 italic">Please provide your details below and the admin will contact you for payment and delivery details.</p>
                <form action="book_online.php" method="post" class="space-y-6 mt-6">
                    <div>
                        <label for="cart_user_name" class="block text-gray-700 font-medium mb-2">Your Name</label>
                        <input type="text" id="cart_user_name" name="user_name" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none text-gray-800" placeholder="Your Name" required>
                    </div>
                    <div>
                        <label for="cart_contact" class="block text-gray-700 font-medium mb-2">Contact Number</label>
                        <input type="text" id="cart_contact" name="contact" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none text-gray-800" placeholder="Contact Number" required pattern="[0-9]{10}" title="Enter a valid 10-digit mobile number">
                    </div>
                    <button type="submit" name="book_cart" class="submit-btn w-full text-white py-3 rounded-lg font-semibold shadow-md">Confirm Purchase</button>
                </form>
            </div>
        <?php endif; ?>

        <!-- Your Orders Section -->
        <div class="form-container p-8 mt-12">
            <h3 class="text-2xl font-semibold text-gray-900 mb-6">Your Orders</h3>
            <form action="book_online.php#orders-section" method="post" class="space-y-6">
                <div>
                    <label for="order_contact" class="block text-gray-700 font-medium mb-2">Mobile Number</label>
                    <input type="text" id="order_contact" name="order_contact" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none text-gray-800" placeholder="Enter your mobile number" required pattern="[0-9]{10}" title="Enter a valid 10-digit mobile number" value="<?php echo isset($_POST['order_contact']) ? htmlspecialchars($_POST['order_contact']) : ''; ?>">
                </div>
                <button type="submit" name="check_orders" class="check-btn w-full text-white py-3 rounded-lg font-semibold shadow-md">Check Orders</button>
            </form>

            <!-- Orders Results -->
            <div id="orders-section" class="mt-6">
                <?php if (!empty($order_results)): ?>
                    <div class="space-y-4">
                        <?php foreach ($order_results as $order): ?>
                            <div class="booking-card p-4 <?php echo $order['status'] === 'Pending' ? 'status-pending' : ($order['status'] === 'Confirmed' ? 'status-confirmed' : 'status-cancelled'); ?>">
                                <?php if ($order['type'] === 'Product'): ?>
                                    <h4 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($order['name']); ?> (Product)</h4>
                                    <p class="text-gray-600"><strong>Price:</strong> ₹<?php echo number_format($order['price'], 2); ?></p>
                                <?php else: ?>
                                    <h4 class="text-lg font-semibold text-gray-900">Accessories Order</h4>
                                    <div class="space-y-2">
                                        <?php foreach ($order['items'] as $item): ?>
                                            <p class="text-gray-600"><?php echo htmlspecialchars($item['name']); ?> (₹<?php echo number_format($item['price'], 2); ?> x <?php echo $item['quantity']; ?>)</p>
                                        <?php endforeach; ?>
                                    </div>
                                    <p class="text-gray-600"><strong>Total:</strong> ₹<?php echo number_format($order['total'], 2); ?></p>
                                <?php endif; ?>
                                <p class="text-gray-600"><strong>Ordered On:</strong> <?php echo date('d M Y, H:i', strtotime($order['booking_date'])); ?></p>
                                <p class="text-gray-600"><strong>Status:</strong> 
                                    <span class="px-2 py-1 rounded-full text-sm font-medium <?php echo $order['status'] === 'Pending' ? 'bg-amber-100 text-amber-800' : ($order['status'] === 'Confirmed' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'); ?>">
                                        <?php echo htmlspecialchars($order['status']); ?>
                                    </span>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php elseif (isset($order_message)): ?>
                    <div class="p-4 bg-yellow-100 text-yellow-800 rounded-lg shadow-md"><?php echo $order_message; ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; // Including the footer ?>
</body>
</html>