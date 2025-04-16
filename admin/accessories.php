<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: admin.php");
    exit();
}

include 'db.php';

// Handle accessory addition
if (isset($_POST['add_accessory'])) {
    $name = $_POST['name'];
    $price = (float)$_POST['price'];
    $color = $_POST['color'];
    $type = $_POST['type'];

    $target_dir = "uploads/";
    $image_name = basename($_FILES["image"]["name"]);
    $imageFileType = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
    $filename = time() . "_" . $image_name;
    $target_file = $target_dir . $filename;

    if ($_FILES["image"]["size"] > 2 * 1024 * 1024) {
        $error_message = "File size should be less than 2MB.";
    } elseif (!in_array($imageFileType, ["jpg", "jpeg", "png"])) {
        $error_message = "Only JPG, JPEG, and PNG files are allowed.";
    } elseif (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        $stmt = $conn->prepare("INSERT INTO accessories (name, price, color, type, image) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sdsss", $name, $price, $color, $type, $filename);
        if ($stmt->execute()) {
            $success_message = "Accessory added successfully!";
        } else {
            $error_message = "Error adding accessory: " . $conn->error;
        }
        $stmt->close();
    } else {
        $error_message = "Failed to upload image.";
    }
}

// Handle accessory edit
if (isset($_POST['edit_accessory'])) {
    $id = (int)$_POST['id'];
    $name = $_POST['name'];
    $price = (float)$_POST['price'];
    $color = $_POST['color'];
    $type = $_POST['type'];

    if (!empty($_FILES["image"]["name"])) {
        $target_dir = "uploads/";
        $image_name = basename($_FILES["image"]["name"]);
        $imageFileType = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
        $filename = time() . "_" . $image_name;
        $target_file = $target_dir . $filename;

        if ($_FILES["image"]["size"] > 2 * 1024 * 1024) {
            $error_message = "File size should be less than 2MB.";
        } elseif (!in_array($imageFileType, ["jpg", "jpeg", "png"])) {
            $error_message = "Only JPG, JPEG, and PNG files are allowed.";
        } elseif (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $stmt = $conn->prepare("UPDATE accessories SET name = ?, price = ?, color = ?, type = ?, image = ? WHERE id = ?");
            $stmt->bind_param("sdsssi", $name, $price, $color, $type, $filename, $id);
        } else {
            $error_message = "Failed to upload new image.";
        }
    } else {
        $stmt = $conn->prepare("UPDATE accessories SET name = ?, price = ?, color = ?, type = ? WHERE id = ?");
        $stmt->bind_param("sdssi", $name, $price, $color, $type, $id);
    }

    if ($stmt && $stmt->execute()) {
        $success_message = "Accessory updated successfully!";
    } else {
        $error_message = "Error updating accessory: " . $conn->error;
    }
    $stmt->close();
}

// Handle accessory deletion
if (isset($_POST['delete_accessory'])) {
    $id = (int)$_POST['id'];
    $stmt = $conn->prepare("DELETE FROM accessories WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $success_message = "Accessory deleted successfully!";
    } else {
        $error_message = "Error deleting accessory: " . $conn->error;
    }
    $stmt->close();
}

// Fetch all accessories with search filter
$search_query = "";
if (isset($_POST['search'])) {
    $search_term = mysqli_real_escape_string($conn, $_POST['search_term']);
    $search_query = " WHERE name LIKE '%$search_term%' OR color LIKE '%$search_term%' OR type LIKE '%$search_term%'";
}

$query = "SELECT * FROM accessories" . $search_query . " ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Error fetching accessories: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accessories Management - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .accessories-container {
            background: linear-gradient(135deg, #f9fafb, #ffffff);
            min-height: calc(100vh - 80px);
        }
        .header {
            background: linear-gradient(90deg, #1f2937, #374151);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        .table-container, .form-container {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }
        .input-field, .select-field {
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .input-field:focus, .select-field:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
        }
        .submit-btn {
            background: linear-gradient(90deg, #10b981, #059669);
        }
        .submit-btn:hover {
            background: linear-gradient(90deg, #059669, #10b981);
            transform: scale(1.05);
        }
        .edit-btn {
            background: linear-gradient(90deg, #3b82f6, #1d4ed8);
        }
        .edit-btn:hover {
            background: linear-gradient(90deg, #1d4ed8, #1e40af);
            transform: scale(1.05);
        }
        .delete-btn {
            background: linear-gradient(90deg, #ef4444, #dc2626);
        }
        .delete-btn:hover {
            background: linear-gradient(90deg, #dc2626, #b91c1c);
            transform: scale(1.05);
        }
        .back-btn {
            background: linear-gradient(90deg, #6b7280, #4b5563);
        }
        .back-btn:hover {
            background: linear-gradient(90deg, #4b5563, #6b7280);
            transform: scale(1.05);
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background: #ffffff;
            padding: 2rem;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        }
        .btn {
            transition: background 0.3s ease, transform 0.3s ease;
        }
    </style>
    <script>
        function openEditModal(id, name, price, color, type) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_price').value = price;
            document.getElementById('edit_color').value = color;
            document.getElementById('edit_type').value = type;
            document.getElementById('editModal').style.display = 'flex';
        }
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
    </script>
</head>
<body class="bg-gray-50 font-sans antialiased">
    <!-- Header Section -->
    <div class="header text-white p-6 flex justify-between items-center">
        <h2 class="text-3xl font-extrabold tracking-tight">Accessories Management</h2>
        <a href="dashboard.php" class="back-btn text-white px-4 py-2 rounded-lg font-semibold shadow-md btn">Back to Dashboard</a>
    </div>

    <!-- Accessories Container -->
    <div class="accessories-container max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <?php if (isset($success_message)): ?>
            <div class="mb-6 p-4 bg-green-100 text-green-800 rounded-lg shadow-md"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
            <div class="mb-6 p-4 bg-red-100 text-red-800 rounded-lg shadow-md"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Add Accessory Form -->
        <div class="form-container p-6 mb-8">
            <h3 class="text-2xl font-semibold text-gray-900 mb-6">Add New Accessory</h3>
            <form action="accessories.php" method="post" enctype="multipart/form-data" class="space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-gray-700 font-medium mb-2">Name</label>
                        <input type="text" id="name" name="name" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none text-gray-800" placeholder="Accessory Name" required>
                    </div>
                    <div>
                        <label for="price" class="block text-gray-700 font-medium mb-2">Price (₹)</label>
                        <input type="number" id="price" name="price" step="0.01" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none text-gray-800" placeholder="Price" required>
                    </div>
                    <div>
                        <label for="color" class="block text-gray-700 font-medium mb-2">Color</label>
                        <input type="text" id="color" name="color" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none text-gray-800" placeholder="Color" required>
                    </div>
                    <div>
                        <label for="type" class="block text-gray-700 font-medium mb-2">Type</label>
                        <select id="type" name="type" class="select-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none text-gray-800" required>
                            <option value="Helmet">Helmet</option>
                            <option value="Spare Parts">Spare Parts</option>
                            <option value="Accessories">Accessories</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="image" class="block text-gray-700 font-medium mb-2">Image</label>
                        <input type="file" id="image" name="image" class="input-field w-full text-gray-700" accept="image/*" required>
                    </div>
                </div>
                <button type="submit" name="add_accessory" class="submit-btn w-full text-white py-3 rounded-lg font-semibold shadow-md btn">Add Accessory</button>
            </form>
        </div>

        <!-- Search Form -->
        <div class="form-container p-6 mb-8">
            <h3 class="text-2xl font-semibold text-gray-900 mb-6">Search Accessories</h3>
            <form action="accessories.php" method="post" class="flex gap-4">
                <input type="text" name="search_term" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none text-gray-800" placeholder="Search by name, color, or type" value="<?php echo isset($_POST['search_term']) ? htmlspecialchars($_POST['search_term']) : ''; ?>">
                <button type="submit" name="search" class="submit-btn text-white px-6 py-3 rounded-lg font-semibold shadow-md btn">Search</button>
            </form>
        </div>

        <!-- Accessories Table -->
        <div class="table-container p-6 overflow-x-auto">
            <h3 class="text-2xl font-semibold text-gray-900 mb-6">All Accessories</h3>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-200 text-gray-700">
                            <th class="p-4 font-semibold">Image</th>
                            <th class="p-4 font-semibold">Name</th>
                            <th class="p-4 font-semibold">Price</th>
                            <th class="p-4 font-semibold">Color</th>
                            <th class="p-4 font-semibold">Type</th>
                            <th class="p-4 font-semibold">Created At</th>
                            <th class="p-4 font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="p-4">
                                    <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="Accessory Image" class="w-16 h-16 object-cover rounded-lg">
                                </td>
                                <td class="p-4"><?php echo htmlspecialchars($row['name']); ?></td>
                                <td class="p-4">₹<?php echo number_format($row['price'], 2); ?></td>
                                <td class="p-4"><?php echo htmlspecialchars($row['color']); ?></td>
                                <td class="p-4"><?php echo htmlspecialchars($row['type']); ?></td>
                                <td class="p-4"><?php echo date('d M Y, H:i', strtotime($row['created_at'])); ?></td>
                                <td class="p-4 flex space-x-2">
                                    <button onclick="openEditModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['name']); ?>', '<?php echo $row['price']; ?>', '<?php echo htmlspecialchars($row['color']); ?>', '<?php echo htmlspecialchars($row['type']); ?>')" class="edit-btn text-white px-3 py-1 rounded-lg font-medium btn">Edit</button>
                                    <form method="post" onsubmit="return confirm('Are you sure you want to delete this accessory?');">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="delete_accessory" class="delete-btn text-white px-3 py-1 rounded-lg font-medium btn">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-center text-gray-500 py-6">No accessories found.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h3 class="text-2xl font-semibold text-gray-900 mb-6">Edit Accessory</h3>
            <form action="accessories.php" method="post" enctype="multipart/form-data" class="space-y-6">
                <input type="hidden" id="edit_id" name="id">
                <div>
                    <label for="edit_name" class="block text-gray-700 font-medium mb-2">Name</label>
                    <input type="text" id="edit_name" name="name" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none text-gray-800" required>
                </div>
                <div>
                    <label for="edit_price" class="block text-gray-700 font-medium mb-2">Price (₹)</label>
                    <input type="number" id="edit_price" name="price" step="0.01" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none text-gray-800" required>
                </div>
                <div>
                    <label for="edit_color" class="block text-gray-700 font-medium mb-2">Color</label>
                    <input type="text" id="edit_color" name="color" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none text-gray-800" required>
                </div>
                <div>
                    <label for="edit_type" class="block text-gray-700 font-medium mb-2">Type</label>
                    <select id="edit_type" name="type" class="select-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none text-gray-800" required>
                        <option value="Helmet">Helmet</option>
                        <option value="Spare Parts">Spare Parts</option>
                        <option value="Accessories">Accessories</option>
                    </select>
                </div>
                <div>
                    <label for="edit_image" class="block text-gray-700 font-medium mb-2">Image (optional)</label>
                    <input type="file" id="edit_image" name="image" class="input-field w-full text-gray-700" accept="image/*">
                </div>
                <div class="flex gap-4">
                    <button type="submit" name="edit_accessory" class="submit-btn w-full text-white py-3 rounded-lg font-semibold shadow-md btn">Save Changes</button>
                    <button type="button" onclick="closeEditModal()" class="back-btn w-full text-white py-3 rounded-lg font-semibold shadow-md btn">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>