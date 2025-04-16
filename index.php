<?php 
include 'admin/db.php'; // Database connection
include 'header.php';

// Fetch Bikes
$bikes_query = "SELECT * FROM products WHERE category = 'bike' LIMIT 6";
$bikes_result = mysqli_query($conn, $bikes_query);
$total_bikes_query = "SELECT COUNT(*) as total FROM products WHERE category = 'bike'";
$total_bikes_result = mysqli_fetch_assoc(mysqli_query($conn, $total_bikes_query))['total'];

// Fetch Scooters
$scooters_query = "SELECT * FROM products WHERE category = 'scooter' LIMIT 6";
$scooters_result = mysqli_query($conn, $scooters_query);
$total_scooters_query = "SELECT COUNT(*) as total FROM products WHERE category = 'scooter'";
$total_scooters_result = mysqli_fetch_assoc(mysqli_query($conn, $total_scooters_query))['total'];

// Fetch EVs
$ev_query = "SELECT * FROM products WHERE category = 'ev' LIMIT 6";
$ev_result = mysqli_query($conn, $ev_query);
$total_ev_query = "SELECT COUNT(*) as total FROM products WHERE category = 'ev'";
$total_ev_result = mysqli_fetch_assoc(mysqli_query($conn, $total_ev_query))['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bhatia Sales - Motorcycle Shop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Category switching and button highlighting
        function showCategory(category) {
            // Show/hide sections
            document.getElementById('bikes').classList.add('hidden');
            document.getElementById('scooters').classList.add('hidden');
            document.getElementById('ev').classList.add('hidden');
            document.getElementById(category).classList.remove('hidden');

            // Update button styles
            const buttons = {
                'bikes': document.getElementById('bikes-btn'),
                'scooters': document.getElementById('scooters-btn'),
                'ev': document.getElementById('ev-btn')
            };

            for (let key in buttons) {
                if (key === category) {
                    buttons[key].classList.remove('bg-gray-200', 'text-gray-800');
                    buttons[key].classList.add('bg-gray-900', 'text-white');
                } else {
                    buttons[key].classList.remove('bg-gray-900', 'text-white');
                    buttons[key].classList.add('bg-gray-200', 'text-gray-800');
                }
            }
        }

        // Slideshow and Quick View functionality
        document.addEventListener('DOMContentLoaded', function () {
            const slides = document.querySelectorAll('.slide');
            let currentSlide = 0;

            function showSlide(index) {
                slides.forEach((slide, i) => {
                    slide.classList.toggle('opacity-0', i !== index);
                    slide.classList.toggle('opacity-100', i === index);
                });
            }

            function nextSlide() {
                currentSlide = (currentSlide + 1) % slides.length;
                showSlide(currentSlide);
            }

            showSlide(currentSlide);
            setInterval(nextSlide, 4000);

            // Quick View Modal Functionality
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
                    setTimeout(() => {
                        modal.classList.remove('opacity-0');
                    }, 10);
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
                if (e.target === modal) {
                    closeBtn.click();
                }
            });

            // Set initial active category to 'bikes'
            showCategory('bikes');
        });
    </script>
</head>
<body class="bg-gray-100 min-h-screen font-sans">
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
                <div class="w-full md:w-1/2 h-[300px] mdockingh-auto">
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

    <!-- Slideshow -->
    <section class="w-full relative overflow-hidden h-[400px] md:h-[500px] lg:h-[600px]">
        <div class="slide absolute inset-0 w-full h-full transition-opacity duration-1000 opacity-100">
            <img class="w-full h-full object-cover" src="images/img1.jpg" alt="Bike 1">
            <div class="absolute inset-0 bg-gradient-to-r from-black/50 to-transparent flex items-center"></div>
        </div>
        <div class="slide absolute inset-0 w-full h-full transition-opacity duration-1000 opacity-0">
            <img class="w-full h-full object-cover" src="images/img2.jpg" alt="Bike 2">
            <div class="absolute inset-0 bg-gradient-to-r from-black/50 to-transparent flex items-center"></div>
        </div>
        <div class="slide absolute inset-0 w-full h-full transition-opacity duration-1000 opacity-0">
            <img class="w-full h-full object-cover" src="images/img3.jpg" alt="Bike 3">
            <div class="absolute inset-0 bg-gradient-to-r from-black/50 to-transparent flex items-center"></div>
        </div>
    </section>

    <section class="text-center py-12 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-8">Explore Our Collection</h2>
            <div class="flex flex-wrap justify-center gap-4 mt-6">
                <button id="bikes-btn" class="bg-gray-900 text-white px-8 py-3 rounded-lg font-medium shadow-md transition-all hover:bg-gray-800 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2" onclick="showCategory('bikes')">Bikes</button>
                <button id="scooters-btn" class="bg-gray-200 text-gray-800 px-8 py-3 rounded-lg font-medium shadow-md transition-all hover:bg-gray-300 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2" onclick="showCategory('scooters')">Scooters</button>
                <button id="ev-btn" class="bg-gray-200 text-gray-800 px-8 py-3 rounded-lg font-medium shadow-md transition-all hover:bg-gray-300 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2" onclick="showCategory('ev')">EVs</button>
            </div>
        </div>
    </section>

    <!-- Bikes Section -->
    <div id="bikes" class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php 
            if (mysqli_num_rows($bikes_result) > 0) {
                while ($bike = mysqli_fetch_assoc($bikes_result)) { 
            ?>
                <div class="product-card bg-white overflow-hidden transition-all duration-300 hover:shadow-xl" 
                     data-category="<?= $bike['category'] ?>" 
                     data-id="<?= $bike['id'] ?>"
                     data-name="<?= htmlspecialchars($bike['name']) ?>"
                     data-price="₹<?= number_format($bike['price'], 2) ?>"
                     data-description="<?= htmlspecialchars($bike['description']) ?>"
                     data-image="admin/uploads/<?= $bike['image'] ?>">
                    <div class="relative">
                        <div class="absolute top-4 left-4 z-10">
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-medium bg-white text-gray-800 shadow-md">
                                <?= ucfirst($bike['category']) ?>
                            </span>
                        </div>
                        <div class="relative overflow-hidden group">
                            <img src="admin/uploads/<?= $bike['image'] ?>" alt="<?= $bike['name'] ?>" class="w-full h-64 object-cover transition-transform duration-500 group-hover:scale-105">
                            <div class="absolute inset-0 bg-black bg-opacity-30 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                                <button class="quick-view-btn px-6 py-2 bg-white text-gray-900 rounded-lg text-sm font-medium transform translate-y-4 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-300">
                                    Quick View
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-3">
                            <h3 class="text-xl font-bold text-gray-900 leading-tight"><?= $bike['name'] ?></h3>
                            <p class="text-xl font-bold text-gray-900">₹<?= number_format($bike['price'], 2) ?></p>
                        </div>
                        <div class="h-20 overflow-hidden mb-6 relative">
                            <p class="text-gray-600 text-sm"><?= $bike['description'] ?></p>
                            <div class="absolute bottom-0 left-0 right-0 h-8 bg-gradient-to-t from-white to-transparent"></div>
                        </div>
                        <a href="book_online.php?product_id=<?= $bike['id'] ?>" class="block w-full text-center bg-gray-900 text-white font-medium px-6 py-3 rounded-lg transition-colors hover:bg-gray-800">
                            Book Online
                        </a>
                    </div>
                </div>
            <?php 
                }
            } else {
                echo "<p class='text-center text-gray-500 col-span-3 py-12'>No bikes available.</p>";
            }
            ?>
        </div>
    </div>

    <!-- Scooters Section -->
    <div id="scooters" class="hidden container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php 
            if (mysqli_num_rows($scooters_result) > 0) {
                while ($scooter = mysqli_fetch_assoc($scooters_result)) { 
            ?>
                <div class="product-card bg-white overflow-hidden transition-all duration-300 hover:shadow-xl" 
                     data-category="<?= $scooter['category'] ?>" 
                     data-id="<?= $scooter['id'] ?>"
                     data-name="<?= htmlspecialchars($scooter['name']) ?>"
                     data-price="₹<?= number_format($scooter['price'], 2) ?>"
                     data-description="<?= htmlspecialchars($scooter['description']) ?>"
                     data-image="admin/uploads/<?= $scooter['image'] ?>">
                    <div class="relative">
                        <div class="absolute top-4 left-4 z-10">
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-medium bg-white text-gray-800 shadow-md">
                                <?= ucfirst($scooter['category']) ?>
                            </span>
                        </div>
                        <div class="relative overflow-hidden group">
                            <img src="admin/uploads/<?= $scooter['image'] ?>" alt="<?= $scooter['name'] ?>" class="w-full h-64 object-cover transition-transform duration-500 group-hover:scale-105">
                            <div class="absolute inset-0 bg-black bg-opacity-30 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                                <button class="quick-view-btn px-6 py-2 bg-white text-gray-900 rounded-lg text-sm font-medium transform translate-y-4 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-300">
                                    Quick View
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-3">
                            <h3 class="text-xl font-bold text-gray-900 leading-tight"><?= $scooter['name'] ?></h3>
                            <p class="text-xl font-bold text-gray-900">₹<?= number_format($scooter['price'], 2) ?></p>
                        </div>
                        <div class="h-20 overflow-hidden mb-6 relative">
                            <p class="text-gray-600 text-sm"><?= $scooter['description'] ?></p>
                            <div class="absolute bottom-0 left-0 right-0 h-8 bg-gradient-to-t from-white to-transparent"></div>
                        </div>
                        <a href="book_online.php?product_id=<?= $scooter['id'] ?>" class="block w-full text-center bg-gray-900 text-white font-medium px-6 py-3 rounded-lg transition-colors hover:bg-gray-800">
                            Book Online
                        </a>
                    </div>
                </div>
            <?php 
                }
            } else {
                echo "<p class='text-center text-gray-500 col-span-3 py-12'>No scooters available.</p>";
            }
            ?>
        </div>
    </div>

    <!-- EV Section -->
    <div id="ev" class="hidden container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php 
            if (mysqli_num_rows($ev_result) > 0) {
                while ($ev = mysqli_fetch_assoc($ev_result)) { 
            ?>
                <div class="product-card bg-white overflow-hidden transition-all duration-300 hover:shadow-xl" 
                     data-category="<?= $ev['category'] ?>" 
                     data-id="<?= $ev['id'] ?>"
                     data-name="<?= htmlspecialchars($ev['name']) ?>"
                     data-price="₹<?= number_format($ev['price'], 2) ?>"
                     data-description="<?= htmlspecialchars($ev['description']) ?>"
                     data-image="admin/uploads/<?= $ev['image'] ?>">
                    <div class="relative">
                        <div class="absolute top-4 left-4 z-10">
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-medium bg-white text-gray-800 shadow-md">
                                <?= ucfirst($ev['category']) ?>
                            </span>
                        </div>
                        <div class="relative overflow-hidden group">
                            <img src="admin/uploads/<?= $ev['image'] ?>" alt="<?= $ev['name'] ?>" class="w-full h-64 object-cover transition-transform duration-500 group-hover:scale-105">
                            <div class="absolute inset-0 bg-black bg-opacity-30 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                                <button class="quick-view-btn px-6 py-2 bg-white text-gray-900 rounded-lg text-sm font-medium transform translate-y-4 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-300">
                                    Quick View
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-3">
                            <h3 class="text-xl font-bold text-gray-900 leading-tight"><?= $ev['name'] ?></h3>
                            <p class="text-xl font-bold text-gray-900">₹<?= number_format($ev['price'], 2) ?></p>
                        </div>
                        <div class="h-20 overflow-hidden mb-6 relative">
                            <p class="text-gray-600 text-sm"><?= $ev['description'] ?></p>
                            <div class="absolute bottom-0 left-0 right-0 h-8 bg-gradient-to-t from-white to-transparent"></div>
                        </div>
                        <a href="book_online.php?product_id=<?= $ev['id'] ?>" class="block w-full text-center bg-gray-900 text-white font-medium px-6 py-3 rounded-lg transition-colors hover:bg-gray-800">
                            Book Online
                        </a>
                    </div>
                </div>
            <?php 
                }
            } else {
                echo "<p class='text-center text-gray-500 col-span-3 py-12'>No electric vehicles available.</p>";
            }
            ?>
        </div>
    </div>

    <!-- See All Vehicles Button -->
    <?php if ($total_ev_result > 6 || $total_bikes_result > 6 || $total_scooters_result > 6) { ?>
        <div class="container mx-auto px-4 py-8">
            <div class="w-full flex justify-center mt-10">
                <a href="product.php" class="inline-block text-gray-900 text-lg font-medium hover:text-gray-800 transition-colors duration-300 border-b-2 border-gray-900 hover:border-gray-800 pb-1">See All Vehicles</a>
            </div>
        </div>
    <?php } ?>

    <?php include 'footer.php'; ?>
</body>
</html>