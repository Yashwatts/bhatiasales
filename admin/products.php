<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: admin.php");
    exit();
}

include 'db.php';

// Handle product addition
if (isset($_POST['add_product'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    if ($_FILES['image']['name']) {
        $target_dir = "uploads/";
        $imageFileType = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $new_filename = "product_" . time() . "." . $imageFileType;
        $target_file = $target_dir . $new_filename;

        if ($_FILES["image"]["size"] > 2 * 1024 * 1024) {
            $error_message = "File size should be less than 2MB.";
        } elseif (!in_array($imageFileType, ["jpg", "jpeg", "png"])) {
            $error_message = "Only JPG, JPEG, and PNG files are allowed.";
        } elseif (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $insert_query = "INSERT INTO products (name, category, price, description, image) 
                             VALUES ('$name', '$category', '$price', '$description', '$new_filename')";
            if (mysqli_query($conn, $insert_query)) {
                $success_message = "Product added successfully!";
            } else {
                $error_message = "Failed to add product: " . mysqli_error($conn);
            }
        } else {
            $error_message = "Failed to upload image.";
        }
    } else {
        $error_message = "Please upload an image.";
    }
}

// Fetch products
$products = mysqli_query($conn, "SELECT * FROM products");

// Handle product deletion
if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    $delete_query = "DELETE FROM products WHERE id='$id'";
    if (mysqli_query($conn, $delete_query)) {
        $success_message = "Product deleted successfully!";
        header("Location: products.php"); // Redirect to refresh the page
        exit();
    } else {
        $error_message = "Failed to delete product: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Bhatia Sales</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom Styles for Professional Design */
        .products-container {
            background: linear-gradient(135deg, #f9fafb, #ffffff);
            min-height: calc(100vh - 80px);
        }
        .form-container {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
            transition: transform 0.3s ease;
        }
        .form-container:hover {
            transform: translateY(-5px);
        }
        .input-field, .textarea-field, .select-field {
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .input-field:focus, .textarea-field:focus, .select-field:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
        }
        .add-btn {
            background: linear-gradient(90deg, #4f46e5, #6b7280);
            transition: background 0.3s ease, transform 0.3s ease;
        }
        .add-btn:hover {
            background: linear-gradient(90deg, #4338ca, #4b5563);
            transform: scale(1.05);
        }
        .product-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
        }
        .delete-btn {
            background: linear-gradient(90deg, #ef4444, #dc2626);
            transition: background 0.3s ease, transform 0.3s ease;
        }
        .delete-btn:hover {
            background: linear-gradient(90deg, #dc2626, #b91c1c);
            transform: scale(1.05);
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
        <h2 class="text-3xl font-extrabold tracking-tight">Manage Products</h2>
        <a href="dashboard.php" class="text-indigo-200 hover:text-indigo-100 font-medium transition-colors">Back to Dashboard</a>
    </div>

    <!-- Products Section -->
    <div class="products-container max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <?php if (isset($success_message)): ?>
            <div class="mb-8 p-4 bg-green-100 text-green-800 rounded-lg shadow-md"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
            <div class="mb-8 p-4 bg-red-100 text-red-800 rounded-lg shadow-md"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Add Product Form -->
        <div class="form-container p-6 mb-12">
            <h3 class="text-2xl font-semibold text-gray-900 mb-6">Add New Product</h3>
            <form method="POST" enctype="multipart/form-data" class="space-y-5">
                <div>
                    <label for="name" class="block text-gray-700 font-medium mb-2">Product Name</label>
                    <input type="text" id="name" name="name" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none text-gray-800" placeholder="Enter product name" required>
                </div>
                <div>
                    <label for="category" class="block text-gray-700 font-medium mb-2">Category</label>
                    <select id="category" name="category" class="select-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none text-gray-800" required>
                        <option value="bike">Bike</option>
                        <option value="scooter">Scooter</option>
                        <option value="ev">EV</option>
                    </select>
                </div>
                <div>
                    <label for="price" class="block text-gray-700 font-medium mb-2">Price (₹)</label>
                    <input type="number" id="price" name="price" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none text-gray-800" placeholder="Enter price" required>
                </div>
                <div>
                    <label for="description" class="block text-gray-700 font-medium mb-2">Description</label>
                    <textarea id="description" name="description" class="textarea-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none text-gray-800" rows="4" placeholder="Enter product description" required></textarea>
                </div>
                <div>
                    <label for="image" class="block text-gray-700 font-medium mb-2">Product Image</label>
                    <input type="file" id="image" name="image" class="input-field w-full text-gray-700" required>
                </div>
                <button type="submit" name="add_product" class="add-btn w-full text-white py-3 rounded-lg font-semibold shadow-md">Add Product</button>
            </form>
        </div>

        <!-- Product List -->
        <h3 class="text-2xl font-semibold text-gray-900 mb-6">Product List</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if (mysqli_num_rows($products) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($products)): ?>
                    <div class="product-card p-6">
                        <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" class="w-full h-40 object-cover rounded-lg mb-4">
                        <h4 class="text-xl font-semibold text-gray-900 mb-2"><?php echo htmlspecialchars($row['name']); ?></h4>
                        <p class="text-gray-600 mb-1"><strong>Category:</strong> <?php echo ucfirst(htmlspecialchars($row['category'])); ?></p>
                        <p class="text-gray-600 mb-1"><strong>Price:</strong> ₹<?php echo number_format($row['price'], 2); ?></p>
                        <p class="text-gray-600 text-sm mb-4 line-clamp-2"><?php echo htmlspecialchars($row['description']); ?></p>
                        <a href="products.php?delete=<?php echo $row['id']; ?>" class="delete-btn inline-block text-white px-4 py-2 rounded-lg font-semibold shadow-md" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-full text-center py-12">
                    <p class="text-gray-500 text-lg font-medium">No products available.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>