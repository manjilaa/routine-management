<?php
$current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
?>
<!-- Main Container -->
<div class="main-container">
    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <nav class="sidebar-nav">
            <a href="index.php?page=dashboard" class="nav-item <?php echo $current_page == 'dashboard' ? 'active' : ''; ?>">
                <i data-lucide="layout-dashboard"></i>
                <span>Dashboard</span>
            </a>
            <a href="index.php?page=batches" class="nav-item <?php echo $current_page == 'batches' ? 'active' : ''; ?>">
                <i data-lucide="layers"></i>
                <span>Batch Management</span>
            </a>
            <a href="index.php?page=courses" class="nav-item <?php echo $current_page == 'courses' ? 'active' : ''; ?>">
                <i data-lucide="book-open"></i>
                <span>Course Management</span>
            </a>
            <a href="index.php?page=teachers" class="nav-item <?php echo $current_page == 'teachers' ? 'active' : ''; ?>">
                <i data-lucide="users"></i>
                <span>Teacher Management</span>
            </a>
            <a href="index.php?page=bookings" class="nav-item <?php echo $current_page == 'bookings' ? 'active' : ''; ?>">
                <i data-lucide="calendar-check"></i>
                <span>Booking Requests</span>
            </a>
            <a href="index.php?page=leave_requests" class="nav-item <?php echo $current_page == 'leave_requests' ? 'active' : ''; ?>">
                <i data-lucide="calendar-x"></i>
                <span>Leave Requests</span>
            </a>
            <a href="index.php?page=schedules" class="nav-item <?php echo $current_page == 'schedules' ? 'active' : ''; ?>">
                <i data-lucide="calendar"></i>
                <span>Schedule Management</span>
            </a>
            <a href="index.php?page=timeslots" class="nav-item <?php echo $current_page == 'timeslots' ? 'active' : ''; ?>">
                <i data-lucide="clock"></i>
                <span>Time Slot Management</span>
            </a>
            <a href="index.php?page=rooms" class="nav-item <?php echo $current_page == 'rooms' ? 'active' : ''; ?>">
                <i data-lucide="door-open"></i>
                <span>Classroom Management</span>
            </a>
        </nav>
    </aside>
