<?php
/**
 * Admin Login Page for UniRoutine
 * Handles administrator authentication.
 */

// Start session
session_start();

// Include database connection
require_once '../includes/db-config.php';

// Redirect if already logged in as admin
if (isset($_SESSION['user_id']) && strtolower($_SESSION['role']) === 'admin') {
    header("Location: ../admin/index.php");
    exit();
}

$error = "";

// Handle login logic
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        try {
            // Prepare statement to prevent SQL injection
            $stmt = $pdo->prepare("SELECT * FROM user WHERE email = ? AND LOWER(role) = 'admin'");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                $authenticated = false;
                
                // Try password_verify first (for hashed passwords)
                if (password_verify($password, $user['password'])) {
                    $authenticated = true;
                } 
                // Fallback for plain text (only for initial setup/testing)
                elseif ($password === $user['password']) {
                    $authenticated = true;
                }

                if ($authenticated) {
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role']; // 'admin'
                    $_SESSION['name'] = $user['name'] ?? 'Admin';

                    header("Location: ../admin/index.php");
                    exit();
                } else {
                    $error = "Invalid admin credentials.";
                }
            } else {
                $error = "Access denied or invalid credentials.";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - UniRoutine</title>
    <link rel="stylesheet" href="../assets/css/login-styles.css">
    <script src="https://cdn.jsdelivr.net/npm/lucide@0.263.1/dist/umd/lucide.min.js"></script>
    <style>
        .login-page {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);
        }
        .logo-circle {
            background-color: #4f46e5;
        }
        .btn-primary {
            background-color: #4f46e5;
        }
        .btn-primary:hover {
            background-color: #4338ca;
        }
    </style>
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-header">
            <div class="logo-circle">
                <img src="../assets/images/ku.png" alt="University Logo">
            </div>
            <h1 style="color: #e0e7ff;">UniRoutine</h1>
            <p style="color: #a5b4fc;">Administrator Portal</p>
        </div>

        <div class="login-card">
            <h2 style="text-align: center; margin-bottom: 1.5rem; color: #111827;">Admin Login</h2>

            <form id="authForm" method="POST" action="index.php">
                <div>
                    <label for="email">Admin Email</label>
                    <input type="email" id="email" name="email" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                </div>

                <div>
                    <label for="password">Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" required>
                        <span class="password-toggle" id="passwordToggle">
                            <i data-lucide="eye"></i>
                        </span>
                    </div>
                </div>

                <!-- Error Message Display -->
                <?php if (!empty($error)): ?>
                <div id="errorMessage" style="display: block; color: #dc2626; font-size: 0.875rem; margin-bottom: 1rem; text-align: center;">
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>

                <button type="submit" class="btn-primary">
                    <i data-lucide="shield-check"></i>
                    Login
                </button>
            </form>

            <div class="student-access">
                <a href="../index.php" style="text-decoration: none; width: 100%;">
                    <button class="btn-student-view" type="button" style="width: 100%; background-color: #f3f4f6; color: #4b5563;">
                        <i data-lucide="arrow-left"></i>
                        Continue to Routine
                    </button>
                </a>
            </div>
        </div>
    </div>

    <script>
        // Initialize icons
        lucide.createIcons();

        // Password toggle functionality
        const passwordToggle = document.getElementById('passwordToggle');
        if (passwordToggle) {
            passwordToggle.addEventListener('click', function() {
                const passwordInput = document.getElementById('password');
                const icon = this.querySelector('i');
                
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.setAttribute('data-lucide', 'eye-off');
                } else {
                    passwordInput.type = 'password';
                    icon.setAttribute('data-lucide', 'eye');
                }
                lucide.createIcons();
            });
        }
    </script>
</body>
</html>
