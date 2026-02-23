<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Main Container -->
<div class="main-container">
    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <nav class="sidebar-nav">
            <a href="index.php" class="nav-item <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
                <i data-lucide="layout-dashboard"></i>
                <span>Dashboard</span>
            </a>
            <a href="batches.php" class="nav-item <?php echo $current_page == 'batches.php' ? 'active' : ''; ?>">
                <i data-lucide="layers"></i>
                <span>Batch Management</span>
            </a>
            <a href="courses.php" class="nav-item <?php echo $current_page == 'courses.php' ? 'active' : ''; ?>">
                <i data-lucide="book-open"></i>
                <span>Course Management</span>
            </a>
            <a href="teachers.php" class="nav-item <?php echo $current_page == 'teachers.php' ? 'active' : ''; ?>">
                <i data-lucide="users"></i>
                <span>Teacher Management</span>
            </a>
            <a href="bookings.php" class="nav-item <?php echo $current_page == 'bookings.php' ? 'active' : ''; ?>">
                <i data-lucide="calendar-check"></i>
                <span>Booking Requests</span>
            </a>
            <a href="schedules.php" class="nav-item <?php echo $current_page == 'schedules.php' ? 'active' : ''; ?>">
                <i data-lucide="calendar"></i>
                <span>Schedule Management</span>
            </a>
        </nav>
    </aside>
