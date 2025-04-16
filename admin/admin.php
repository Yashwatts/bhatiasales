<?php
session_start();
include 'db.php'; // Database connection

// Check if admin is already logged in
if (isset($_SESSION['admin'])) {
    header("Location: dashboard.php");
    exit();
}

// Initialize login attempts counter
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}

// Generate CSRF token if not set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle login form submission
if (isset($_POST['login'])) {
    if ($_SESSION['login_attempts'] >= 3) {
        $error = "Too many login attempts. Please wait 5 minutes.";
        // Reset attempts after 5 minutes (basic implementation)
        if (!isset($_SESSION['lockout_time']) || (time() - $_SESSION['lockout_time']) > 300) {
            $_SESSION['login_attempts'] = 0;
            unset($_SESSION['lockout_time']);
        } else {
            goto render_page;
        }
    }

    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid request. Please try again.";
        goto render_page;
    }

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Use prepared statement for security
    $query = "SELECT * FROM admin WHERE username = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $admin = mysqli_fetch_assoc($result);
        
        if (password_verify($password, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin'] = $username;
            $_SESSION['login_attempts'] = 0; // Reset attempts on success
            unset($_SESSION['lockout_time']);
            header("Location: dashboard.php");
            exit();
        } else {
            $_SESSION['login_attempts']++;
            if ($_SESSION['login_attempts'] >= 3) {
                $_SESSION['lockout_time'] = time();
                $error = "Too many login attempts. Locked for 5 minutes.";
            } else {
                $error = "Incorrect password!";
            }
        }
    } else {
        $_SESSION['login_attempts']++;
        if ($_SESSION['login_attempts'] >= 3) {
            $_SESSION['lockout_time'] = time();
            $error = "Too many login attempts. Locked for 5 minutes.";
        } else {
            $error = "Admin not found!";
        }
    }
    mysqli_stmt_close($stmt);
}

render_page:
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Bhatia Sales</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #1e293b, #111827);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
        .login-container {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 2rem;
            width: 100%;
            max-width: 400px;
            transition: transform 0.3s ease;
        }
        .login-container:hover {
            transform: translateY(-5px);
        }
        .input-field {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            background: #f9fafb;
            font-size: 0.875rem;
            color: #1f2937;
            transition: all 0.3s ease;
        }
        .input-field:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
            outline: none;
        }
        .login-btn {
            width: 100%;
            padding: 0.75rem;
            background: linear-gradient(90deg, #3b82f6, #1e3a8a);
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .login-btn:hover {
            background: linear-gradient(90deg, #2563eb, #1e40af);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        .login-btn:disabled {
            background: #6b7280;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        .error {
            background: #fee2e2;
            color: #dc2626;
            padding: 0.5rem;
            border-radius: 6px;
            font-size: 0.875rem;
            margin-bottom: 1rem;
            text-align: center;
        }
        .label {
            font-size: 0.875rem;
            color: #4b5563;
            margin-bottom: 0.5rem;
            display: block;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">Admin Login</h2>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <div>
                <label for="username" class="label">Username</label>
                <input type="text" id="username" name="username" class="input-field" placeholder="Enter your username" required>
            </div>
            <div>
                <label for="password" class="label">Password</label>
                <input type="password" id="password" name="password" class="input-field" placeholder="Enter your password" required>
            </div>
            <button type="submit" name="login" class="login-btn" <?php echo ($_SESSION['login_attempts'] >= 3 && (time() - $_SESSION['lockout_time']) < 300) ? 'disabled' : ''; ?>>Login</button>
        </form>
    </div>
</body>
</html>