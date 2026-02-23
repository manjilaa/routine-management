<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - UniRoutine</title>
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <script src="https://cdn.jsdelivr.net/npm/lucide@0.263.1/dist/umd/lucide.min.js"></script>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-left">
            <div class="logo-container">
                <img src="../assets/images/ku.png" alt="University Logo" class="header-logo">
            </div>
            <div class="header-title">
                <h1>UniRoutine</h1>
                <p>Admin Dashboard</p>
            </div>
        </div>
        <div class="header-right">
            <div class="user-info">
                <i data-lucide="user-circle"></i>
                <span id="adminName"><?php echo isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'Admin User'; ?></span>
            </div>
            <a href="../login/logout.php" class="btn-logout">
                <i data-lucide="log-out"></i>
                Logout
            </a>
        </div>
    </header>
