<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: admin.php");
    exit();
}

include 'db.php';

$admin_username = $_SESSION['admin'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['profile_image'])) {
    $target_dir = "uploads/";
    $imageFileType = strtolower(pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION));
    $new_filename = "admin_" . time() . "." . $imageFileType;
    $target_file = $target_dir . $new_filename;

    // Check file size (limit to 2MB)
    if ($_FILES["profile_image"]["size"] > 2 * 1024 * 1024) {
        echo "<script>alert('File size should be less than 2MB.'); window.location='dashboard.php';</script>";
        exit();
    }

    // Allow only JPG, JPEG, PNG formats
    if (!in_array($imageFileType, ["jpg", "jpeg", "png"])) {
        echo "<script>alert('Only JPG, JPEG, and PNG files are allowed.'); window.location='dashboard.php';</script>";
        exit();
    }

    if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
        // Update profile image in database
        $query = "UPDATE admin SET profile_image = '$new_filename' WHERE username = '$admin_username'";
        mysqli_query($conn, $query);
        header("Location: dashboard.php");
    } else {
        echo "<script>alert('Failed to upload image.'); window.location='dashboard.php';</script>";
    }
}
?>
