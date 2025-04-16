<?php
$servername = "localhost";  // Change if using a different host
$username = "root";  // Default XAMPP username
$password = "";  // Default XAMPP password (empty)
$database = "bhatia_sales"; // Ensure this database exists

$conn = mysqli_connect($servername, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}
?>
