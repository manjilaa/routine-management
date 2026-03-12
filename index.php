<?php
require_once 'includes/db-config.php';
$selected_batch = isset($_GET['batch_id']) ? $_GET['batch_id'] : null;

// Fetch all batches
$batch_stmt = $pdo->query("SELECT id, batch_name FROM batch ORDER BY batch_name ASC");
$batches = $batch_stmt->fetchAll();

// If no batch is selected, default to the first one available
if (!$selected_batch && !empty($batches)) {
    $selected_batch = $batches[0]['id'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniRoutine - Student Dashboard</title>
    <link rel="stylesheet" href="assets/css/student-style.css">
    <script src="https://cdn.jsdelivr.net/npm/lucide@0.263.1/dist/umd/lucide.min.js"></script>
</head>

<body>
    <header class="header student-header">
        <div class="header-content">
            <div class="header-left">
                <div class="header-logo">
                    <img src="assets/images/ku.png" alt="University Logo">
                </div>
                <i data-lucide="calendar"></i>
                <div>
                    <h1>UniRoutine</h1>
                    <p>Student Dashboard</p>
                </div>
            </div>
            <div class="header-right">
                <div class="user-info">
                    <p>Student View</p>
                    <p class="user-role">Class Schedules</p>
                </div>
                <a href="teacher-login/login.php" class="btn-teacher-login"
                    style="background-color: #4f46e5; color: white; padding: 0.5rem 1rem; border-radius: 0.5rem; text-decoration: none; display: flex; align-items: center; gap: 0.5rem; font-weight: 500;">
                    <i data-lucide="log-in"></i>
                    Teacher Login
                </a>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="batch-selector">
            <label>Select Batch:</label>
            <select id="batchSelect" onchange="window.location.href='index.php?batch_id=' + this.value">
                <option value="">-- All Batches --</option>
                <?php foreach ($batches as $batch): ?>
                    <option value="<?php echo $batch['id']; ?>" <?php echo ($selected_batch == $batch['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($batch['batch_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="card">
            <div class="card-header student-card-header">
                <h2>Weekly Class Schedule </h2>
            </div>
            <?php include 'routine/routine_table.php'; ?>
        </div>

        <div class="card">
            <div class="card-header course-card-header">
                <i data-lucide="book-open"></i>
                <h2>Enrolled Courses</h2>
            </div>
            <?php include 'subjects/subject_cards.php'; ?>
        </div>
    </main>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>