<?php
include 'db.php'; // Include your database connection

$admin_username = 'admin';
$admin_password = password_hash('admin123', PASSWORD_DEFAULT); // Hashing the password

$query = "INSERT INTO admin (username, password) VALUES ('$admin_username', '$admin_password')";

if (mysqli_query($conn, $query)) {
    echo "Admin user created successfully.";
} else {
    echo "Error: " . mysqli_error($conn);
}
?>
