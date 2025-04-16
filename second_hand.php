<?php
include 'header.php'; // Including the header
include 'admin/db.php'; // Ensure database connection

// Handle Vehicle Registration
if (isset($_POST['register'])) {
    $user_name = mysqli_real_escape_string($conn, $_POST['user_name']);
    $vehicle_type = mysqli_real_escape_string($conn, $_POST['vehicle_type']);
    $company = mysqli_real_escape_string($conn, $_POST['company']);
    $model = mysqli_real_escape_string($conn, $_POST['model']);
    $color = mysqli_real_escape_string($conn, $_POST['color']);
    $travelled_km = (int)$_POST['travelled_km'];
    $asking_price = (float)$_POST['asking_price'];
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);

    // Image Upload
    $target_dir = "admin/uploads/";
    $image_name = basename($_FILES["vehicle_image"]["name"]);
    $imageFileType = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
    $filename = time() . "_" . $image_name; // Store only the filename
    $target_file = $target_dir . $filename; // Full path for moving the file

    if ($_FILES["vehicle_image"]["size"] > 2 * 1024 * 1024) {
        $error_message = "File size should be less than 2MB.";
    } elseif (!in_array($imageFileType, ["jpg", "jpeg", "png"])) {
        $error_message = "Only JPG, JPEG, and PNG files are allowed.";
    } elseif (move_uploaded_file($_FILES["vehicle_image"]["tmp_name"], $target_file)) {
        // Insert into Database with only the filename
        $sql = "INSERT INTO second_hand_vehicles (user_name, vehicle_type, company, model, color, travelled_km, asking_price, location, contact, vehicle_image, status)
                VALUES ('$user_name', '$vehicle_type', '$company', '$model', '$color', '$travelled_km', '$asking_price', '$location', '$contact', '$filename', 'Pending')";
        
        if (mysqli_query($conn, $sql)) {
            $success_message = "Vehicle registered successfully! It is pending approval.";
        } else {
            $error_message = "Error: " . mysqli_error($conn);
        }
    } else {
        $error_message = "Failed to upload image.";
    }
}

// Handle Status Check
$status_results = [];
if (isset($_POST['check_status'])) {
    $check_contact = mysqli_real_escape_string($conn, $_POST['check_contact']);
    $status_query = "SELECT company, model, color, vehicle_image, status FROM second_hand_vehicles WHERE contact = '$check_contact' ORDER BY registered_on DESC";
    $status_result = mysqli_query($conn, $status_query);

    if ($status_result && mysqli_num_rows($status_result) > 0) {
        while ($row = mysqli_fetch_assoc($status_result)) {
            $status_results[] = $row;
        }
    } else {
        $status_message = "No vehicles found for this mobile number.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Second-Hand Vehicles - Bhatia Sales</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom Styles for Enhanced Design */
        .second-hand-container {
            background: linear-gradient(135deg, #f3f4f6, #ffffff);
            min-height: calc(100vh - 160px); /* Adjust for header and footer */
        }
        .form-container, .vehicle-card, .status-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .form-container:hover, .vehicle-card:hover, .status-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
        }
        .input-field, .select-field, .textarea-field {
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .input-field:focus, .select-field:focus, .textarea-field:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
        }
        .submit-btn, .check-btn {
            background: linear-gradient(90deg, #4f46e5, #6b7280);
            transition: background 0.3s ease, transform 0.3s ease;
        }
        .submit-btn:hover, .check-btn:hover {
            background: linear-gradient(90deg, #4338ca, #4b5563);
            transform: scale(1.05);
        }
        .toggle-btn {
            background: linear-gradient(90deg, #4f46e5, #6b7280);
            transition: background 0.3s ease, transform 0.3s ease;
        }
        .toggle-btn:hover {
            background: linear-gradient(90deg, #4338ca, #4b5563);
            transform: scale(1.05);
        }
        .toggle-btn.active {
            background: linear-gradient(90deg, #4338ca, #4b5563);
        }
        .filter-container {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
        .status-pending {
            border-left: 4px solid #f59e0b; /* Amber for Pending */
        }
        .status-approved {
            border-left: 4px solid #22c55e; /* Green for Approved */
        }
        .status-declined {
            border-left: 4px solid #ef4444; /* Red for Declined */
        }
        .status-sold {
            border-left: 4px solid #3b82f6; /* Blue for Sold */
        }
    </style>
    <script>
        function toggleSection(section) {
            const buySection = document.querySelector('.buy-section');
            const sellSection = document.querySelector('.form-section');
            const buyBtn = document.getElementById('buyBtn');
            const sellBtn = document.getElementById('sellBtn');

            if (section === 'buy') {
                buySection.style.display = 'block';
                sellSection.style.display = 'none';
                buyBtn.classList.add('active');
                sellBtn.classList.remove('active');
            } else if (section === 'sell') {
                buySection.style.display = 'none';
                sellSection.style.display = 'block';
                buyBtn.classList.remove('active');
                sellBtn.classList.add('active');
            }
        }

        function filterVehicles() {
            let type = document.getElementById('filterType').value.toLowerCase();
            let location = document.getElementById('filterLocation').value.toLowerCase();
            let price = document.getElementById('filterPrice').value;

            let cards = document.querySelectorAll('.vehicle-card');
            cards.forEach(card => {
                let cardType = card.getAttribute('data-type').toLowerCase();
                let cardLocation = card.getAttribute('data-location').toLowerCase();
                let cardPrice = parseFloat(card.getAttribute('data-price'));

                if ((type === '' || cardType.includes(type)) &&
                    (location === '' || cardLocation.includes(location)) &&
                    (price === '' || cardPrice <= parseFloat(price))) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // Automatically show Sell section if status results are present
        window.onload = function() {
            <?php if (!empty($status_results) || isset($status_message)): ?>
                toggleSection('sell');
            <?php endif; ?>
        };
    </script>
</head>
<body class="bg-gray-50 font-sans antialiased">
    <!-- Main Content -->
    <div class="second-hand-container max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-8 text-center tracking-tight">Buy or Sell Second-Hand Vehicles</h2>

        <?php if (isset($success_message)): ?>
            <div class="mb-8 p-4 bg-green-100 text-green-800 rounded-lg shadow-md"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
            <div class="mb-8 p-4 bg-red-100 text-red-800 rounded-lg shadow-md"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Button Group -->
        <div class="flex justify-center gap-6 mb-12">
            <button id="buyBtn" class="toggle-btn text-white px-6 py-3 rounded-lg font-semibold shadow-md" onclick="toggleSection('buy')">Buy</button>
            <button id="sellBtn" class="toggle-btn text-white px-6 py-3 rounded-lg font-semibold shadow-md" onclick="toggleSection('sell')">Sell</button>
        </div>

        <!-- Sell Section -->
        <div class="form-section hidden">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Register Vehicle Form -->
                <div class="form-container p-8">
                    <h3 class="text-2xl font-semibold text-gray-900 mb-6">Register Your Vehicle for Sale</h3>
                    <form action="second_hand.php" method="post" enctype="multipart/form-data" class="space-y-6">
                        <div>
                            <label for="user_name" class="block text-gray-700 font-medium mb-2">Your Name</label>
                            <input type="text" id="user_name" name="user_name" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none text-gray-800" placeholder="Your Name" required>
                        </div>
                        <div>
                            <label for="vehicle_type" class="block text-gray-700 font-medium mb-2">Vehicle Type</label>
                            <select id="vehicle_type" name="vehicle_type" class="select-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none text-gray-800" required>
                                <option value="Bike">Bike</option>
                                <option value="Scooty">Scooty</option>
                                <option value="EV">EV</option>
                            </select>
                        </div>
                        <div>
                            <label for="company" class="block text-gray-700 font-medium mb-2">Company</label>
                            <input type="text" id="company" name="company" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none text-gray-800" placeholder="Company" required>
                        </div>
                        <div>
                            <label for="model" class="block text-gray-700 font-medium mb-2">Model</label>
                            <input type="text" id="model" name="model" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none text-gray-800" placeholder="Model" required>
                        </div>
                        <div>
                            <label for="color" class="block text-gray-700 font-medium mb-2">Color</label>
                            <input type="text" id="color" name="color" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none text-gray-800" placeholder="Color" required>
                        </div>
                        <div>
                            <label for="travelled_km" class="block text-gray-700 font-medium mb-2">Kilometers Travelled</label>
                            <input type="number" id="travelled_km" name="travelled_km" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none text-gray-800" placeholder="Kilometers Travelled" required>
                        </div>
                        <div>
                            <label for="asking_price" class="block text-gray-700 font-medium mb-2">Asking Price (₹)</label>
                            <input type="number" id="asking_price" name="asking_price" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none text-gray-800" placeholder="Asking Price" required>
                        </div>
                        <div>
                            <label for="location" class="block text-gray-700 font-medium mb-2">Location</label>
                            <input type="text" id="location" name="location" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none text-gray-800" placeholder="Location" required>
                        </div>
                        <div>
                            <label for="contact" class="block text-gray-700 font-medium mb-2">Contact Number</label>
                            <input type="text" id="contact" name="contact" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none text-gray-800" placeholder="Contact Number" required pattern="[0-9]{10}" title="Enter a valid 10-digit mobile number">
                        </div>
                        <div>
                            <label for="vehicle_image" class="block text-gray-700 font-medium mb-2">Vehicle Image</label>
                            <input type="file" id="vehicle_image" name="vehicle_image" class="input-field w-full text-gray-700" accept="image/*" required>
                        </div>
                        <button type="submit" name="register" class="submit-btn w-full text-white py-3 rounded-lg font-semibold shadow-md">Register Vehicle</button>
                    </form>
                </div>

                <!-- Check Status Form -->
                <div class="form-container p-8">
                    <h3 class="text-2xl font-semibold text-gray-900 mb-6">Check Your Vehicle Status</h3>
                    <form action="second_hand.php#status-section" method="post" class="space-y-6">
                        <div>
                            <label for="check_contact" class="block text-gray-700 font-medium mb-2">Mobile Number</label>
                            <input type="text" id="check_contact" name="check_contact" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none text-gray-800" placeholder="Enter your mobile number" required pattern="[0-9]{10}" title="Enter a valid 10-digit mobile number" value="<?php echo isset($_POST['check_contact']) ? htmlspecialchars($_POST['check_contact']) : ''; ?>">
                        </div>
                        <button type="submit" name="check_status" class="check-btn w-full text-white py-3 rounded-lg font-semibold shadow-md">Check Status</button>
                    </form>

                    <!-- Status Results -->
                    <div id="status-section" class="mt-6">
                        <?php if (!empty($status_results)): ?>
                            <div class="space-y-4">
                                <?php foreach ($status_results as $status_row): ?>
                                    <div class="status-card p-4 <?php 
                                        echo $status_row['status'] === 'Pending' ? 'status-pending' : 
                                            ($status_row['status'] === 'Approved' ? 'status-approved' : 
                                            ($status_row['status'] === 'Declined' ? 'status-declined' : 'status-sold')); 
                                    ?>">
                                        <div class="flex items-center gap-4">
                                            <img src="admin/uploads/<?php echo htmlspecialchars($status_row['vehicle_image']); ?>" alt="Vehicle Image" class="w-20 h-20 object-cover rounded-lg">
                                            <div>
                                                <h4 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($status_row['company'] . ' ' . $status_row['model'] . ' (' . $status_row['color'] . ')'); ?></h4>
                                                <p class="text-gray-600"><strong>Status:</strong> 
                                                    <span class="px-2 py-1 rounded-full text-sm font-medium <?php 
                                                        echo $status_row['status'] === 'Pending' ? 'bg-amber-100 text-amber-800' : 
                                                            ($status_row['status'] === 'Approved' ? 'bg-green-100 text-green-800' : 
                                                            ($status_row['status'] === 'Declined' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800')); 
                                                    ?>">
                                                        <?php echo htmlspecialchars($status_row['status']); ?>
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php elseif (isset($status_message)): ?>
                            <div class="p-4 bg-yellow-100 text-yellow-800 rounded-lg shadow-md"><?php echo $status_message; ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Buy Section -->
        <div class="buy-section hidden">
            <h3 class="text-2xl font-semibold text-gray-900 mb-6 text-center">Available Vehicles for Purchase</h3>
            <!-- Search Filter -->
            <div class="filter-container p-6 mb-8">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="filterType" class="block text-gray-700 font-medium mb-2">Vehicle Type</label>
                        <select id="filterType" class="select-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none text-gray-800" onchange="filterVehicles()">
                            <option value="">All Types</option>
                            <option value="Bike">Bike</option>
                            <option value="Scooty">Scooty</option>
                            <option value="EV">EV</option>
                        </select>
                    </div>
                    <div>
                        <label for="filterLocation" class="block text-gray-700 font-medium mb-2">Location</label>
                        <input type="text" id="filterLocation" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none text-gray-800" placeholder="Location" onkeyup="filterVehicles()">
                    </div>
                    <div>
                        <label for="filterPrice" class="block text-gray-700 font-medium mb-2">Max Price (₹)</label>
                        <input type="number" id="filterPrice" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none text-gray-800" placeholder="Max Price" onkeyup="filterVehicles()">
                    </div>
                </div>
            </div>

            <!-- Vehicle Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php
                $query = "SELECT * FROM second_hand_vehicles WHERE status = 'Approved' ORDER BY registered_on DESC";
                $result = mysqli_query($conn, $query);

                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<div class='vehicle-card p-6' data-type='" . htmlspecialchars($row['vehicle_type']) . "' data-location='" . htmlspecialchars($row['location']) . "' data-price='" . htmlspecialchars($row['asking_price']) . "'>";
                        echo "<img src='admin/uploads/" . htmlspecialchars($row['vehicle_image']) . "' alt='Vehicle Image' class='w-full h-40 object-cover rounded-lg mb-4'>";
                        echo "<h4 class='text-xl font-semibold text-gray-900 mb-2'>" . htmlspecialchars($row['company']) . " " . htmlspecialchars($row['model']) . " (" . htmlspecialchars($row['color']) . ")</h4>";
                        echo "<p class='text-gray-600 mb-1'><strong>Price:</strong> ₹" . number_format($row['asking_price'], 2) . "</p>";
                        echo "<p class='text-gray }},600 mb-1'><strong>Location:</strong> " . htmlspecialchars($row['location']) . "</p>";
                        echo "<p class='text-gray-600 mb-1'><strong>Contact:</strong> " . htmlspecialchars($row['contact']) . "</p>";
                        echo "<p class='text-gray-600 mb-1'><strong>Travelled:</strong> " . number_format($row['travelled_km']) . " km</p>";
                        echo "</div>";
                    }
                } else {
                    echo "<div class='col-span-full text-center py-12'><p class='text-gray-500 text-lg font-medium'>No approved vehicles available.</p></div>";
                }
                ?>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; // Including the footer ?>
</body>
</html>