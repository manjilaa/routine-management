<!-- Dashboard Overview -->
<section class="content-section active">
    <div class="section-header">
        <h2>Dashboard Overview</h2>
    </div>

    <!-- Stats Cards Row -->
    <div class="dashboard-stats">
        <?php
// Fetch stats from database
try {
    $batch_count = $pdo->query("SELECT COUNT(*) FROM batch")->fetchColumn();
    $subject_count = $pdo->query("SELECT COUNT(*) FROM subject")->fetchColumn();
    $room_count = $pdo->query("SELECT COUNT(*) FROM room")->fetchColumn();
    $teacher_count = $pdo->query("SELECT COUNT(*) FROM user WHERE role = 'teacher'")->fetchColumn();
}
catch (Exception $e) {
    $batch_count = $subject_count = $room_count = $teacher_count = 0;
}
?>

        <div class="stat-card stat-card-indigo">
            <div class="stat-icon" style="background: none;">
                <img src="../assets/images/batches.png" alt="Batches" style="width: 2rem; height: 2rem; object-fit: contain;">
            </div>
            <div class="stat-info">
                <p class="stat-label">Total Batches</p>
                <h3 class="stat-value"><?php echo $batch_count; ?></h3>
            </div>
        </div>

        <div class="stat-card stat-card-blue">
            <div class="stat-icon" style="background: none;">
                <img src="../assets/images/courses.png" alt="Courses" style="width: 2rem; height: 2rem; object-fit: contain;">
            </div>
            <div class="stat-info">
                <p class="stat-label">Total Courses</p>
                <h3 class="stat-value"><?php echo $subject_count; ?></h3>
            </div>
        </div>

        <div class="stat-card stat-card-green">
            <div class="stat-icon" style="background: none;">
                <img src="../assets/images/rooms.png" alt="Rooms" style="width: 2rem; height: 2rem; object-fit: contain;">
            </div>
            <div class="stat-info">
                <p class="stat-label">Total Rooms</p>
                <h3 class="stat-value"><?php echo $room_count; ?></h3>
            </div>
        </div>

        <div class="stat-card stat-card-amber">
            <div class="stat-icon" style="background: none;">
                <img src="../assets/images/teachers.png" alt="Teachers" style="width: 2rem; height: 2rem; object-fit: contain;">
            </div>
            <div class="stat-info">
                <p class="stat-label">Total Teachers</p>
                <h3 class="stat-value"><?php echo $teacher_count; ?></h3>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="section-header" style="margin-top: 2rem;">
        <h2>Quick Actions</h2>
    </div>
    <div class="quick-actions">
        <a href="index.php?page=batches" class="quick-action-card">
            <img src="../assets/images/batches.png" alt="Manage Batches" style="width: 2rem; height: 2rem; object-fit: contain;">
            <span>Manage Batches</span>
        </a>
        <a href="index.php?page=courses" class="quick-action-card">
            <img src="../assets/images/courses.png" alt="Manage Courses" style="width: 2rem; height: 2rem; object-fit: contain;">
            <span>Manage Courses</span>
        </a>
        <a href="index.php?page=teachers" class="quick-action-card">
            <img src="../assets/images/teachers.png" alt="Manage Teachers" style="width: 2rem; height: 2rem; object-fit: contain;">
            <span>Manage Teachers</span>
        </a>
        <a href="index.php?page=bookings" class="quick-action-card">
            <img src="../assets/images/bookings.png" alt="Booking Requests" style="width: 2rem; height: 2rem; object-fit: contain;">
            <span>Booking Requests</span>
        </a>
        <a href="index.php?page=schedules" class="quick-action-card">
            <img src="../assets/images/schedules.png" alt="Manage Schedules" style="width: 2rem; height: 2rem; object-fit: contain;">
            <span>Manage Schedules</span>
        </a>
    </div>
</section>
