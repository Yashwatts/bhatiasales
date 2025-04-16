<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: admin.php");
    exit();
}

include 'db.php';

$admin_username = $_SESSION['admin'];
$error = $success = '';

// Fetch current admin details
$query = "SELECT username, profile_image FROM admin WHERE username = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $admin_username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$admin = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Handle profile image update
if (isset($_POST['update_image'])) {
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $filename = $_FILES['profile_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $new_filename = uniqid() . '.' . $ext;
            $upload_path = 'uploads/' . $new_filename;
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                if ($admin['profile_image'] && file_exists('uploads/' . $admin['profile_image']) && $admin['profile_image'] !== 'default.jpg') {
                    unlink('uploads/' . $admin['profile_image']);
                }
                $update_query = "UPDATE admin SET profile_image = ? WHERE username = ?";
                $stmt = mysqli_prepare($conn, $update_query);
                mysqli_stmt_bind_param($stmt, "ss", $new_filename, $admin_username);
                if (mysqli_stmt_execute($stmt)) {
                    $success = "Profile image updated successfully!";
                    $admin['profile_image'] = $new_filename;
                } else {
                    $error = "Error updating image: " . mysqli_error($conn);
                }
                mysqli_stmt_close($stmt);
            } else {
                $error = "Failed to upload image.";
            }
        } else {
            $error = "Only JPG, JPEG, and PNG files are allowed.";
        }
    } else {
        $error = "Please select an image.";
    }
}

// Handle new admin creation
if (isset($_POST['add_admin'])) {
    $new_username = trim($_POST['new_username']);
    $new_password = trim($_POST['new_password']);
    if (strlen($new_username) < 3 || strlen($new_password) < 6) {
        $error = "Username must be 3+ characters and password 6+ characters.";
    } else {
        $check_query = "SELECT username FROM admin WHERE username = ?";
        $stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt, "s", $new_username);
        mysqli_stmt_execute($stmt);
        $check_result = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($check_result) > 0) {
            $error = "Username already exists.";
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $insert_query = "INSERT INTO admin (username, password, profile_image) VALUES (?, ?, 'default.jpg')";
            $stmt = mysqli_prepare($conn, $insert_query);
            mysqli_stmt_bind_param($stmt, "ss", $new_username, $hashed_password);
            if (mysqli_stmt_execute($stmt)) {
                $success = "New admin '$new_username' added successfully!";
            } else {
                $error = "Error adding admin: " . mysqli_error($conn);
            }
        }
        mysqli_stmt_close($stmt);
    }
}

// Handle password change
if (isset($_POST['change_password'])) {
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    $verify_query = "SELECT password FROM admin WHERE username = ?";
    $stmt = mysqli_prepare($conn, $verify_query); // Fixed: Use $verify_query
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $admin_username);
        mysqli_stmt_execute($stmt);
        $verify_result = mysqli_stmt_get_result($stmt);
        $current_admin = mysqli_fetch_assoc($verify_result);
        mysqli_stmt_close($stmt);

        if ($current_admin && password_verify($current_password, $current_admin['password'])) {
            if ($new_password === $confirm_password) {
                if (strlen($new_password) < 6) {
                    $error = "New password must be at least 6 characters.";
                } else {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_query = "UPDATE admin SET password = ? WHERE username = ?";
                    $stmt = mysqli_prepare($conn, $update_query);
                    if ($stmt) {
                        mysqli_stmt_bind_param($stmt, "ss", $hashed_password, $admin_username);
                        if (mysqli_stmt_execute($stmt)) {
                            $success = "Password changed successfully!";
                        } else {
                            $error = "Error changing password: " . mysqli_error($conn);
                        }
                        mysqli_stmt_close($stmt);
                    } else {
                        $error = "Failed to prepare password update statement: " . mysqli_error($conn);
                    }
                }
            } else {
                $error = "New passwords do not match.";
            }
        } else {
            $error = "Current password is incorrect.";
        }
    } else {
        $error = "Failed to prepare verification statement: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile - Bhatia Sales</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f9fafb, #e5e7eb);
            min-height: 100vh;
            margin: 0;
        }
        .header {
            background: linear-gradient(90deg, #1f2937, #374151);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .profile-container {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            max-width: 700px;
            margin: 2rem auto;
            color: #1f2937;
        }
        .section {
            background: #f9fafb;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .section:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
        }
        .input-field {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            background: #ffffff;
            color: #1f2937;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }
        .input-field:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
            outline: none;
        }
        .btn {
            background: linear-gradient(90deg, #10b981, #047857);
            padding: 0.75rem;
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }
        .btn:hover {
            background: linear-gradient(90deg, #059669, #065f46);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        .back-btn {
            background: linear-gradient(90deg, #6b7280, #4b5563);
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .back-btn:hover {
            background: linear-gradient(90deg, #4b5563, #374151);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        .alert {
            border-radius: 8px;
            padding: 0.75rem;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            text-align: center;
        }
        .profile-pic {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #10b981;
            margin-bottom: 1.5rem;
            transition: transform 0.3s ease;
        }
        .profile-pic:hover {
            transform: scale(1.05);
        }
        h3 {
            color: #047857;
            font-size: 1.25rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="header text-white p-6 flex justify-between items-center">
        <h2 class="text-3xl font-extrabold tracking-tight">Admin Profile</h2>
        <a href="dashboard.php" class="back-btn text-white">Back to Dashboard</a>
    </div>

    <div class="profile-container">
        <?php if ($success): ?>
            <div class="alert bg-green-100 text-green-800"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert bg-red-100 text-red-800"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Update Profile Image -->
        <div class="section">
            <h3>Profile Image</h3>
            <div class="flex justify-center">
                <img src="uploads/<?php echo htmlspecialchars($admin['profile_image']); ?>" alt="Profile" class="profile-pic">
            </div>
            <form method="POST" enctype="multipart/form-data" class="space-y-4">
                <input type="file" name="profile_image" class="input-field" accept=".jpg,.jpeg,.png" required>
                <button type="submit" name="update_image" class="btn">Update Image</button>
            </form>
        </div>

        <!-- Add New Admin -->
        <div class="section">
            <h3>Add New Admin</h3>
            <form method="POST" class="space-y-4">
                <input type="text" name="new_username" class="input-field" placeholder="New Username" required>
                <input type="password" name="new_password" class="input-field" placeholder="New Password" required>
                <button type="submit" name="add_admin" class="btn">Add Admin</button>
            </form>
        </div>

        <!-- Change Password -->
        <div class="section">
            <h3>Change Password</h3>
            <form method="POST" class="space-y-4">
                <input type="password" name="current_password" class="input-field" placeholder="Current Password" required>
                <input type="password" name="new_password" class="input-field" placeholder="New Password" required>
                <input type="password" name="confirm_password" class="input-field" placeholder="Confirm New Password" required>
                <button type="submit" name="change_password" class="btn">Change Password</button>
            </form>
        </div>
    </div>
</body>
</html>