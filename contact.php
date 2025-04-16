<?php
include 'header.php';

// Database connection
$servername = "localhost";
$username = "root"; // Change this if using a different username
$password = ""; // Change this if your database has a password
$database = "bhatia_sales";

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize message variables
$success_message = "";
$error_message = "";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if all required fields are set
    if (isset($_POST['customer_name'], $_POST['email'], $_POST['phone'], $_POST['message'])) {
        $customer_name = $conn->real_escape_string($_POST['customer_name']);
        $email = $conn->real_escape_string($_POST['email']);
        $phone = $conn->real_escape_string($_POST['phone']);
        $message = $conn->real_escape_string($_POST['message']);

        // Insert data into feedback table
        $sql = "INSERT INTO feedback (customer_name, email, phone, message) VALUES ('$customer_name', '$email', '$phone', '$message')";

        if ($conn->query($sql) === TRUE) {
            $success_message = "Feedback submitted successfully!";
        } else {
            $error_message = "Error: " . $conn->error;
        }
    } else {
        $error_message = "Please fill out all required fields.";
    }
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Bhatia Sales</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom Styles for Enhanced Design */
        .contact-section {
            background: linear-gradient(135deg, #f9fafb, #ffffff);
        }
        .form-container {
            background: linear-gradient(145deg, #ffffff, #f9fafb);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        .form-container:hover {
            transform: translateY(-5px);
        }
        .input-field {
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .input-field:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
        }
        .submit-btn {
            background: linear-gradient(90deg, #4f46e5, #6b7280);
            transition: background 0.3s ease, transform 0.3s ease;
        }
        .submit-btn:hover {
            background: linear-gradient(90deg, #4338ca, #4b5563);
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased">
    <section class="contact-section min-h-screen flex flex-col md:flex-row items-center justify-center p-6 md:p-12">
        <!-- Left Section (Details) -->
        <div class="md:w-1/2 p-6 md:p-10">
            <h2 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-6 tracking-tight">Get in Touch</h2>
            <p class="text-lg text-gray-600 mb-8 leading-relaxed">We’re here to assist you! Whether you have questions, feedback, or need support, feel free to reach out.</p>
            <div class="space-y-4">
                <p class="text-gray-700 flex items-center">
                    <svg class="h-6 w-6 text-indigo-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <span><strong>Email:</strong> bhatiasaleskalka@gmail.com</span>
                </p>
                <p class="text-gray-700 flex items-center">
                    <svg class="h-6 w-6 text-indigo-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                    <span><strong>Phone:</strong> +91 9896528096</span>
                </p>
                <p class="text-gray-700 flex items-center">
                    <svg class="h-6 w-6 text-indigo-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span><strong>Address:</strong> #1022/3 ZL, Ram Nagar, Near Saini Dhaba, Kalka, Haryana</span>
                </p>
            </div>
        </div>

        <!-- Right Section (Form) -->
        <div class="md:w-1/2 flex items-center justify-center p-6 md:p-10">
            <div class="form-container rounded-xl p-8 w-full max-w-lg">
                <h3 class="text-2xl md:text-3xl font-semibold text-gray-900 mb-6 tracking-tight">Send Us a Message</h3>
                <?php if ($success_message): ?>
                    <div class="mb-6 p-4 bg-green-100 text-green-800 rounded-lg"><?php echo $success_message; ?></div>
                <?php endif; ?>
                <?php if ($error_message): ?>
                    <div class="mb-6 p-4 bg-red-100 text-red-800 rounded-lg"><?php echo $error_message; ?></div>
                <?php endif; ?>
                <form action="contact.php" method="POST">
                    <div class="mb-5">
                        <label class="block text-gray-700 font-medium mb-2" for="customer_name">Name</label>
                        <input type="text" id="customer_name" name="customer_name" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none text-gray-800" required>
                    </div>
                    <div class="mb-5">
                        <label class="block text-gray-700 font-medium mb-2" for="email">Email</label>
                        <input type="email" id="email" name="email" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none text-gray-800" required>
                    </div>
                    <div class="mb-5">
                        <label class="block text-gray-700 font-medium mb-2" for="phone">Mobile Number</label>
                        <input type="tel" id="phone" name="phone" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none text-gray-800" required pattern="[0-9]{10}" title="Enter a valid 10-digit mobile number">
                    </div>
                    <div class="mb-6">
                        <label class="block text-gray-700 font-medium mb-2" for="message">Message</label>
                        <textarea id="message" name="message" rows="5" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none text-gray-800" required></textarea>
                    </div>
                    <button type="submit" class="submit-btn w-full text-white py-3 rounded-lg font-semibold shadow-md">Send Message</button>
                </form>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>
</body>
</html>