<?php
/**
 * Subject Cards Component
 * Fetches subjects from the database and displays them as cards.
 */

require_once dirname(__DIR__) . '/includes/db-config.php';

try {
    // Fetch subjects.
    $where_clause = "";
    $params = [];
    $join_clause = "";

    if (isset($selected_teacher) && $selected_teacher !== "") {
        // Show only subjects assigned to this teacher in routine OR approved requests
        $where_clause = " WHERE s.subject_code IN (
                            SELECT subject_code FROM routine WHERE teacher_id = ?
                            UNION
                            SELECT subject_code FROM teacher_room_request WHERE teacher_id = ? AND status = 'Approved'
                          )";
        $params[] = $selected_teacher;
        $params[] = $selected_teacher;
        $join_clause = ""; // No join needed with IN clause
    } elseif (isset($selected_batch) && $selected_batch !== "") {
        // Show only subjects that appear in the routine OR approved requests for this specific batch
        $where_clause = " WHERE s.subject_code IN (
                            SELECT subject_code FROM routine WHERE batch_id = ?
                            UNION
                            SELECT subject_code FROM teacher_room_request WHERE batch_id = ? AND status = 'Approved'
                          )";
        $params[] = $selected_batch;
        $params[] = $selected_batch;
    }

    $sql = "SELECT DISTINCT 
                s.subject_code, 
                s.name as subject_name, 
                s.department
            FROM subject s" . $join_clause . $where_clause . "
            ORDER BY s.name ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $subjects = $stmt->fetchAll();

    // For display in teacher dashboard, show Semester and Room instead of Teacher name
    function getSemesterAndRoom($pdo, $subject_code, $teacher_id) {
        if (!$teacher_id) return 'Not Scheduled';
        
        $stmt = $pdo->prepare("
            SELECT b.batch_name, rm.room_number 
            FROM routine r 
            JOIN batch b ON r.batch_id = b.id 
            JOIN room rm ON r.room_id = rm.id 
            WHERE r.subject_code = ? AND r.teacher_id = ? 
            UNION
            SELECT b.batch_name, rm.room_number 
            FROM teacher_room_request trr 
            JOIN batch b ON trr.batch_id = b.id 
            JOIN room rm ON trr.room_id = rm.id 
            WHERE trr.subject_code = ? AND trr.teacher_id = ? AND trr.status = 'Approved'
            LIMIT 1
        ");
        $stmt->execute([$subject_code, $teacher_id, $subject_code, $teacher_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            return htmlspecialchars($result['batch_name']) . " | Room: " . htmlspecialchars($result['room_number']);
        }
        return 'Not Scheduled';
    }

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
                    <h3 style="margin-bottom: 0; padding-bottom: 0; border: none; font-size: 1.125rem;"><?php echo htmlspecialchars($subject['subject_code']); ?></h3>
                </div>
                <p class="course-title" style="margin-top: 0.2rem; font-size: 0.95rem; color: #374151; font-weight: 500;"><?php echo htmlspecialchars($subject['subject_name']); ?></p>

                <?php 
                // Only show additional info (Semester & Room) if we are on the Teacher Dashboard
                if (isset($selected_teacher) && $selected_teacher !== "") {
                    // We are in teacher view
                    $tid = $selected_teacher;
                ?>
                    <p class="course-teacher" style="color: #4b5563; font-weight: 500; font-size: 0.9rem; margin-top: 0.75rem;">
                        <?php echo getSemesterAndRoom($pdo, $subject['subject_code'], $tid); ?>
                    </p>
                    <div class="course-footer" style="margin-top: 0.5rem; padding-top: 0.5rem;">
                        <p><?php echo htmlspecialchars($subject['department'] ?? 'General'); ?></p>
                    </div>
                <?php } ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error loading subjects: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
