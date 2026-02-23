<?php
/**
 * Subject Cards Component
 * Fetches subjects from the database and displays them as cards.
 */

require_once dirname(__DIR__) . '/includes/db-config.php';

try {
    // Fetch subjects. We filter by batch if $selected_batch is set.
    $where_clause = "";
    $params = [];
    if (isset($selected_batch) && $selected_batch !== "") {
        // Show only subjects that appear in the routine for this specific batch
        $where_clause = " WHERE s.subject_code IN (SELECT DISTINCT subject_code FROM routine WHERE batch_id = ?)";
        $params = [$selected_batch];
    }

    $sql = "SELECT DISTINCT 
                s.subject_code, 
                s.name as subject_name, 
                s.department,
                (SELECT u.name FROM routine r JOIN user u ON r.teacher_id = u.id WHERE r.subject_code = s.subject_code LIMIT 1) as teacher_name
            FROM subject s" . $where_clause . "
            ORDER BY s.name ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $subjects = $stmt->fetchAll();

?>

<div class="courses-grid">
    <?php if (empty($subjects)): ?>
        <p style="grid-column: 1/-1; text-align: center; padding: 2rem; color: #6b7280;">
            No subjects found in the database.
        </p>
    <?php else: ?>
        <?php foreach ($subjects as $subject): ?>
            <div class="course-card">
                <div class="course-header">
                    <h3><?php echo htmlspecialchars($subject['subject_code']); ?></h3>
                    <!-- Note: Credits not in provided schema, omitting for now -->
                </div>
                <p class="course-title"><?php echo htmlspecialchars($subject['subject_name']); ?></p>
                <p class="course-teacher"><?php echo htmlspecialchars($subject['teacher_name'] ?? 'Not Assigned'); ?></p>
                <div class="course-footer">
                    <p><?php echo htmlspecialchars($subject['department'] ?? 'General'); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error loading subjects: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
