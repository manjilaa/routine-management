<?php
/**
 * Login Page for UniRoutine
 * Handles user authentication and session management.
 */

// Start session
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: ../admin/admin-dashboard.html");
    } elseif ($_SESSION['role'] === 'teacher') {
        header("Location: ../dashboard/teacher-dashboard.html");
    } else {
        header("Location: ../dashboard/student-dashboard.html");
    }
    exit();
}

// Include database connection
require_once '../includes/db-config.php';

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
            $stmt = $pdo->prepare("SELECT * FROM user WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role']; // e.g., 'admin', 'teacher', 'student'
                $_SESSION['name'] = $user['name'] ?? '';

                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header("Location: ../admin/admin-dashboard.html");
                } elseif ($user['role'] === 'teacher') {
                    header("Location: ../dashboard/teacher-dashboard.html");
                } else {
                    header("Location: ../dashboard/student-dashboard.html");
                }
                exit();
            } else {
                // If password_verify fails, check plain text (only for initial setup/testing)
                if ($user && $password === $user['password']) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    header("Location: ../dashboard/student-dashboard.html");
                    exit();
                }
                $error = "Invalid email or password.";
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
    <title>UniRoutine - Login</title>
    <link rel="stylesheet" href="../assets/css/login-styles.css">
    <script src="https://cdn.jsdelivr.net/npm/lucide@0.263.1/dist/umd/lucide.min.js"></script>
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-header">
            <div class="logo-circle">
                <img src="../assets/images/ku.png" alt="University Logo">
            </div>
            <h1>UniRoutine</h1>
            <p>University Routine Management System</p>
        </div>

        <div class="login-card">
            <h2 style="text-align: center; margin-bottom: 1.5rem; color: #111827;">Teacher's Portal</h2>

            <form id="authForm" method="POST" action="login.php">
                <div>
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                </div>

                <div>
                    <label for="password">Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" required>
                        <span class="password-toggle" id="passwordToggle">Show</span>
                    </div>
                </div>

                <!-- Error Message Display -->
                <?php if (!empty($error)): ?>
                <div id="errorMessage" style="display: block; color: #dc2626; font-size: 0.875rem; margin-bottom: 1rem; text-align: center;">
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>

                <button type="submit" class="btn-primary">
                    <i data-lucide="log-in"></i>
                    Login
                </button>
            </form>

            <div class="student-access">
                <a href="../index.php" style="text-decoration: none; width: 100%;">
                    <button class="btn-student-view" type="button" style="width: 100%; background-color: #f3f4f6; color: #4b5563;">
                        <i data-lucide="arrow-left"></i>
                        Back to Schedule
                    </button>
                </a>
            </div>
        </div>
    </div>

    <!-- Include the login script for UI features like password toggle -->
    <script src="../assets/js/login-script.js"></script>
    <script>
        // Initialize icons
        lucide.createIcons();

        // Disable the JS-based auth handling from login-script.js 
        // to allow PHP to process the form submission
        document.addEventListener('DOMContentLoaded', function() {
            const authForm = document.getElementById('authForm');
            if (authForm) {
                // We remove the listener added by login-script.js by cloning the node 
                // and replacing it, or simply knowing that PHP will process it 
                // because we changed the action and method.
                // However, the JS has event.preventDefault(), so we MUST override it.
                authForm.onsubmit = null; 
                authForm.removeEventListener('submit', handleAuth);
            }
        });
    </script>
</body>
</html>
