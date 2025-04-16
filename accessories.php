<?php
session_start(); // Must be at the very top
include 'header.php'; // Include your public header
include 'admin/db.php'; // Database connection

// Add to cart
if (isset($_POST['add_to_cart'])) {
    $accessory_id = (int)$_POST['accessory_id'];
    $query = "SELECT * FROM accessories WHERE id = $accessory_id";
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $accessory = mysqli_fetch_assoc($result);
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        if (isset($_SESSION['cart'][$accessory_id])) {
            $_SESSION['cart'][$accessory_id]['quantity']++;
        } else {
            $_SESSION['cart'][$accessory_id] = [
                'id' => $accessory['id'],
                'name' => $accessory['name'],
                'price' => $accessory['price'],
                'color' => $accessory['color'],
                'type' => $accessory['type'],
                'image' => $accessory['image'],
                'quantity' => 1
            ];
        }
    }
}

// Fetch all accessories
$query = "SELECT * FROM accessories ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Error fetching accessories: " . mysqli_error($conn));
}

$accessories = [];
while ($row = mysqli_fetch_assoc($result)) {
    $accessories[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accessories - Bhatia Sales</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .accessories-container {
            background: linear-gradient(135deg, #f3f4f6, #ffffff);
            min-height: calc(100vh - 160px);
        }
        .accessory-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .accessory-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
        }
        .filter-container {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
        .input-field, .select-field {
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .input-field:focus, .select-field:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
        }
        .cart-btn {
            background: linear-gradient(90deg, #4f46e5, #6b7280);
            transition: background 0.3s ease, transform 0.3s ease;
        }
        .cart-btn:hover {
            background: linear-gradient(90deg, #4338ca, #4b5563);
            transform: scale(1.05);
        }
    </style>
    <script>
        function filterAccessories() {
            let type = document.getElementById('filterType').value.toLowerCase();
            let color = document.getElementById('filterColor').value.toLowerCase();
            let price = document.getElementById('filterPrice').value;

            let cards = document.querySelectorAll('.accessory-card');
            cards.forEach(card => {
                let cardType = card.getAttribute('data-type').toLowerCase();
                let cardColor = card.getAttribute('data-color').toLowerCase();
                let cardPrice = parseFloat(card.getAttribute('data-price'));

                if ((type === '' || cardType.includes(type)) &&
                    (color === '' || cardColor.includes(color)) &&
                    (price === '' || cardPrice <= parseFloat(price))) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }
    </script>
</head>
<body class="bg-gray-50 font-sans antialiased">
    <?php include 'cart.php'; // Include the cart component after header ?>
    <div class="accessories-container max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-8 text-center tracking-tight">Shop Accessories</h2>

        <!-- Filter Section -->
        <div class="filter-container p-6 mb-8">
            <h3 class="text-xl font-semibold text-gray-900 mb-4">Filter Accessories</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="filterType" class="block text-gray-700 font-medium mb-2">Type</label>
                    <select id="filterType" class="select-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none text-gray-800" onchange="filterAccessories()">
                        <option value="">All Types</option>
                        <option value="Helmet">Helmet</option>
                        <option value="Spare Parts">Spare Parts</option>
                        <option value="Accessories">Accessories</option>
                    </select>
                </div>
                <div>
                    <label for="filterColor" class="block text-gray-700 font-medium mb-2">Color</label>
                    <input type="text" id="filterColor" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none text-gray-800" placeholder="Color" onkeyup="filterAccessories()">
                </div>
                <div>
                    <label for="filterPrice" class="block text-gray-700 font-medium mb-2">Max Price (₹)</label>
                    <input type="number" id="filterPrice" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none text-gray-800" placeholder="Max Price" onkeyup="filterAccessories()">
                </div>
            </div>
        </div>

        <!-- Accessories Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if (count($accessories) > 0): ?>
                <?php foreach ($accessories as $accessory): ?>
                    <div class="accessory-card p-6" data-type="<?php echo htmlspecialchars($accessory['type']); ?>" data-color="<?php echo htmlspecialchars($accessory['color']); ?>" data-price="<?php echo htmlspecialchars($accessory['price']); ?>">
                        <img src="admin/uploads/<?php echo htmlspecialchars($accessory['image']); ?>" alt="Accessory Image" class="w-full h-48 object-cover rounded-lg mb-4">
                        <h3 class="text-xl font-semibold text-gray-900 mb-2"><?php echo htmlspecialchars($accessory['name']); ?></h3>
                        <p class="text-gray-600 mb-1"><strong>Price:</strong> ₹<?php echo number_format($accessory['price'], 2); ?></p>
                        <p class="text-gray-600 mb-1"><strong>Color:</strong> <?php echo htmlspecialchars($accessory['color']); ?></p>
                        <p class="text-gray-600 mb-4"><strong>Type:</strong> <?php echo htmlspecialchars($accessory['type']); ?></p>
                        <form method="post">
                            <input type="hidden" name="accessory_id" value="<?php echo $accessory['id']; ?>">
                            <button type="submit" name="add_to_cart" class="cart-btn w-full text-white py-2 rounded-lg font-semibold shadow-md">Add to Cart</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full text-center py-12">
                    <p class="text-gray-500 text-lg font-medium">No accessories available at the moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'footer.php'; // Include your public footer ?>
</body>
</html>