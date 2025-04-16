<?php
include 'admin/db.php'; // Ensure this file connects to 'bhatia_sales' database
include 'header.php';

$sql = "SELECT * FROM products";
$result = mysqli_query($conn, $sql);
$products = [];

while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Products - Bhatia Sales</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Full Screen Quick View Modal -->
    <div id="quickViewModal" class="fixed inset-0 bg-black z-50 hidden transition-opacity duration-300 opacity-0">
        <div class="absolute top-6 right-6">
            <button id="closeModal" class="bg-white rounded-full p-2 shadow-lg hover:bg-gray-100 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-900" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="flex items-center justify-center h-full p-4">
            <div class="bg-white rounded-xl overflow-hidden max-w-6xl w-full max-h-[90vh] flex flex-col md:flex-row">
                <div class="w-full md:w-1/2 h-[300px] md:h-auto">
                    <img id="modalImage" src="/placeholder.svg" alt="" class="w-full h-full object-cover">
                </div>
                <div class="w-full md:w-1/2 p-8 overflow-y-auto">
                    <h2 id="modalTitle" class="text-3xl font-bold text-gray-900 mb-4"></h2>
                    <div class="flex items-center mb-6">
                        <span id="modalCategory" class="px-3 py-1 rounded-full text-sm font-medium bg-gray-200 text-gray-800 mr-4"></span>
                        <span id="modalPrice" class="text-2xl font-bold text-gray-900"></span>
                    </div>
                    <p id="modalDescription" class="text-gray-700 mb-8"></p>
                    <a id="modalBookLink" href="#" class="inline-block bg-gray-900 text-white font-medium px-8 py-3 rounded-lg hover:bg-gray-800 transition-colors">
                        Book Online
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Page Header -->
        <div class="text-center mb-16">
            <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">Our Products</h2>
            <p class="max-w-2xl mx-auto text-lg text-gray-600">Explore our premium collection of bikes, scooters, and electric vehicles designed for performance and style.</p>
        </div>

        <!-- Filter Buttons -->
        <div class="flex flex-wrap justify-center gap-4 mb-12">
            <button id="bike-btn" onclick="filterProducts('bike')" class="filter-btn px-8 py-3 rounded-lg font-medium bg-gray-900 text-white shadow-md transition-all hover:bg-gray-800 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">Bikes</button>
            <button id="scooter-btn" onclick="filterProducts('scooter')" class="filter-btn px-8 py-3 rounded-lg font-medium bg-gray-200 text-gray-800 shadow-md transition-all hover:bg-gray-300 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">Scooters</button>
            <button id="ev-btn" onclick="filterProducts('ev')" class="filter-btn px-8 py-3 rounded-lg font-medium bg-gray-200 text-gray-800 shadow-md transition-all hover:bg-gray-300 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">EVs</button>
        </div>

        <!-- Product List -->
        <div id="product-container" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($products as $product): ?>
                <div class="product-card bg-white overflow-hidden transition-all duration-300 hover:shadow-xl" 
                     data-category="<?= $product['category'] ?>" 
                     data-id="<?= $product['id'] ?>"
                     data-name="<?= htmlspecialchars($product['name']) ?>"
                     data-price="₹<?= number_format($product['price'], 2) ?>"
                     data-description="<?= htmlspecialchars($product['description']) ?>"
                     data-image="admin/uploads/<?= $product['image'] ?>"
                     style="<?= $product['category'] === 'bike' ? '' : 'display: none;' ?>">
                    
                    <div class="relative">
                        <div class="absolute top-4 left-4 z-10">
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-medium bg-white text-gray-800 shadow-md">
                                <?= ucfirst($product['category']) ?>
                            </span>
                        </div>
                        <div class="relative overflow-hidden group">
                            <img src="admin/uploads/<?= $product['image'] ?>" alt="<?= $product['name'] ?>" class="w-full h-64 object-cover transition-transform duration-500 group-hover:scale-105">
                            <div class="absolute inset-0 bg-black bg-opacity-30 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                                <button class="quick-view-btn px-6 py-2 bg-white text-gray-900 rounded-lg text-sm font-medium transform translate-y-4 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-300">
                                    Quick View
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-3">
                            <h3 class="text-xl font-bold text-gray-900 leading-tight"><?= $product['name'] ?></h3>
                            <p class="text-xl font-bold text-gray-900">₹<?= number_format($product['price'], 2) ?></p>
                        </div>
                        <div class="h-20 overflow-hidden mb-6 relative">
                            <p class="text-gray-600 text-sm"><?= $product['description'] ?></p>
                            <div class="absolute bottom-0 left-0 right-0 h-8 bg-gradient-to-t from-white to-transparent"></div>
                        </div>
                        <a href="book_online.php?product_id=<?= $product['id'] ?>" class="block w-full text-center bg-gray-900 text-white font-medium px-6 py-3 rounded-lg transition-colors hover:bg-gray-800">
                            Book Online
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        function filterProducts(category) {
            document.querySelectorAll('.filter-btn').forEach(btn => {
                if (btn.id === `${category}-btn`) {
                    btn.classList.remove('bg-gray-200', 'text-gray-800');
                    btn.classList.add('bg-gray-900', 'text-white');
                } else {
                    btn.classList.remove('bg-gray-900', 'text-white');
                    btn.classList.add('bg-gray-200', 'text-gray-800');
                }
            });
            
            let products = document.querySelectorAll('.product-card');
            let delay = 0;
            products.forEach(product => {
                if (product.getAttribute('data-category') === category) {
                    setTimeout(() => {
                        product.style.display = "block";
                        product.style.opacity = 0;
                        product.style.transform = "translateY(20px)";
                        setTimeout(() => {
                            product.style.transition = "opacity 0.4s ease, transform 0.4s ease";
                            product.style.opacity = 1;
                            product.style.transform = "translateY(0)";
                        }, 10);
                    }, delay);
                    delay += 80;
                } else {
                    product.style.display = "none";
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('quickViewModal');
            const closeBtn = document.getElementById('closeModal');
            const quickViewBtns = document.querySelectorAll('.quick-view-btn');
            
            quickViewBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const card = this.closest('.product-card');
                    document.getElementById('modalImage').src = card.getAttribute('data-image');
                    document.getElementById('modalImage').alt = card.getAttribute('data-name');
                    document.getElementById('modalTitle').textContent = card.getAttribute('data-name');
                    document.getElementById('modalCategory').textContent = card.getAttribute('data-category').charAt(0).toUpperCase() + card.getAttribute('data-category').slice(1);
                    document.getElementById('modalPrice').textContent = card.getAttribute('data-price');
                    document.getElementById('modalDescription').textContent = card.getAttribute('data-description');
                    document.getElementById('modalBookLink').href = 'book_online.php?product_id=' + card.getAttribute('data-id');
                    modal.classList.remove('hidden');
                    setTimeout(() => modal.classList.remove('opacity-0'), 10);
                    document.body.style.overflow = 'hidden';
                });
            });
            
            closeBtn.addEventListener('click', function() {
                modal.classList.add('opacity-0');
                setTimeout(() => {
                    modal.classList.add('hidden');
                    document.body.style.overflow = 'auto';
                }, 300);
            });
            
            modal.addEventListener('click', function(e) {
                if (e.target === modal) closeBtn.click();
            });
            
            filterProducts('bike');
        });
    </script>
    <?php include 'footer.php'; ?>
</body>
</html>