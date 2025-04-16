<?php
include 'admin/db.php'; // Database connection (adjust path as needed)

// Update cart quantities
if (isset($_POST['update_cart'])) {
    $id = (int)$_POST['id'];
    $quantity = (int)$_POST['quantity'];
    if ($quantity > 0 && isset($_SESSION['cart'][$id])) { // Check if item exists
        $_SESSION['cart'][$id]['quantity'] = $quantity;
    } else {
        unset($_SESSION['cart'][$id]); // Remove item if quantity is 0 or less
    }
}

// Calculate cart item count
$cart_count = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;
?>

<style>
    .cart-icon {
        position: fixed;
        top: 120px; /* Adjust based on your header height */
        right: 20px;
        background: linear-gradient(90deg, #4f46e5, #6b7280);
        border-radius: 50%;
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        cursor: pointer;
        z-index: 1000;
    }
    .cart-count {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #ef4444;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
    }
    .cart-popup {
        position: fixed;
        top: 0;
        right: -400px; /* Hidden by default */
        width: 400px;
        height: 100%;
        background: #ffffff;
        box-shadow: -4px 0 15px rgba(0, 0, 0, 0.1);
        padding: 20px;
        transition: right 0.3s ease;
        z-index: 1000;
        overflow-y: auto;
    }
    .cart-popup.open {
        right: 0;
    }
    .close-btn, .buy-btn {
        background: linear-gradient(90deg, #ef4444, #dc2626);
        transition: background 0.3s ease, transform 0.3s ease;
    }
    .close-btn:hover, .buy-btn:hover {
        background: linear-gradient(90deg, #dc2626, #b91c1c);
        transform: scale(1.05);
    }
    .buy-btn {
        background: linear-gradient(90deg, #4f46e5, #6b7280);
    }
    .buy-btn:hover {
        background: linear-gradient(90deg, #4338ca, #4b5563);
    }
    .input-field {
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }
    .input-field:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
    }
</style>

<!-- Cart Icon -->
<div class="cart-icon" onclick="toggleCart()">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
    </svg>
    <?php if ($cart_count > 0): ?>
        <span class="cart-count"><?php echo $cart_count; ?></span>
    <?php endif; ?>
</div>

<!-- Cart Popup -->
<div id="cartPopup" class="cart-popup">
    <h3 class="text-2xl font-semibold text-gray-900 mb-6">Your Cart</h3>
    <?php if (!empty($_SESSION['cart'])): ?>
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
                            <p class="text-gray-600">₹<?php echo number_format($item['price'], 2); ?></p>
                            <form method="post" class="flex items-center mt-2">
                                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="0" class="input-field w-16 px-2 py-1 border border-gray-300 rounded-lg text-gray-800" onchange="this.form.submit()">
                                <input type="hidden" name="update_cart" value="1">
                            </form>
                        </div>
                    </div>
                    <p class="text-gray-600 font-medium">₹<?php echo number_format($subtotal, 2); ?></p>
                </div>
            <?php endforeach; ?>
            <div class="flex justify-between items-center pt-4">
                <p class="text-xl font-semibold text-gray-900">Total:</p>
                <p class="text-xl font-semibold text-gray-900">₹<?php echo number_format($total, 2); ?></p>
            </div>
            <a href="book_online.php?from_cart=1" class="buy-btn w-full text-white py-3 rounded-lg font-semibold shadow-md text-center block mt-4">Buy Now</a>
        </div>
    <?php else: ?>
        <p class="text-gray-500">Your cart is empty.</p>
    <?php endif; ?>
    <button onclick="toggleCart()" class="close-btn w-full text-white py-2 rounded-lg font-semibold shadow-md mt-4">Close</button>
</div>

<script>
    function toggleCart() {
        const cartPopup = document.getElementById('cartPopup');
        cartPopup.classList.toggle('open');
    }
</script>