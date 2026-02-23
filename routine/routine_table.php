<?php
/**
 * Schedule Table Partial
 * Fetches routine data and displays it in a structured table.
 */

require_once dirname(__DIR__) . '/includes/db-config.php';

try {
    // 1. Fetch all unique time slots to create rows
    $ts_stmt = $pdo->query("SELECT * FROM time_slot ORDER BY start_time ASC");
    $time_slots = $ts_stmt->fetchAll();

    // 2. Fetch all unique days (or use default set)
    $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

    // 3. Fetch routines with joined data for display (with optional batch filter)
    $where_clause = "";
    $params = [];
    if (isset($selected_batch) && $selected_batch !== "") {
        $where_clause = " WHERE r.batch_id = ?";
        $params = [$selected_batch];
    }

    $routine_sql = "SELECT 
                        r.*, 
                        s.name as subject_name, 
                        u.name as teacher_name, 
                        b.batch_name, 
                        rm.room_number as room_name, 
                        ts.day_of_week as day, ts.start_time, ts.end_time 
                    FROM routine r
                    JOIN subject s ON r.subject_code = s.subject_code
                    JOIN user u ON r.teacher_id = u.id
                    JOIN batch b ON r.batch_id = b.id
                    JOIN room rm ON r.room_id = rm.id
                    JOIN time_slot ts ON r.time_slot_id = ts.id" . $where_clause;
    
    $r_stmt = $pdo->prepare($routine_sql);
    $r_stmt->execute($params);
    $all_routines = $r_stmt->fetchAll();

    // Organize routines by [day][time_slot_id] for easy lookup
    $schedule_data = [];
    foreach ($all_routines as $row) {
        $schedule_data[$row['day']][$row['time_slot_id']] = $row;
    }

?>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Time</th>
                <?php foreach ($days as $day): ?>
                    <th><?php echo $day; ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($time_slots)): ?>
                <tr>
                    <td colspan="<?php echo count($days) + 1; ?>" style="text-align: center; padding: 2rem;">
                        No time slots defined in the database.
                    </td>
                </tr>
            <?php else: ?>
                <?php 
                // Group time slots by time range (assuming same range might exist for different days)
                // but usually time_slots table has specific IDs. We'll iterate by unique time ranges.
                $unique_time_ranges = [];
                foreach($time_slots as $ts) {
                    $range = date("H:i", strtotime($ts['start_time'])) . " - " . date("H:i", strtotime($ts['end_time']));
                    if (!in_array($range, $unique_time_ranges)) {
                        $unique_time_ranges[] = $range;
                    }
                }

                foreach ($unique_time_ranges as $range): 
                ?>
                    <tr>
                        <td class="time-cell"><?php echo $range; ?></td>
                        <?php foreach ($days as $day): ?>
                            <td>
                                <?php 
                                // Find if any routine exists for this day and this time range
                                $found_routine = null;
                                foreach ($all_routines as $r) {
                                    $curr_range = date("H:i", strtotime($r['start_time'])) . " - " . date("H:i", strtotime($r['end_time']));
                                    if ($r['day'] === $day && $curr_range === $range) {
                                        $found_routine = $r;
                                        break;
                                    }
                                }

                                if ($found_routine): 
                                ?>
                                    <div class="schedule-cell student-schedule">
                                        <div class="course-code"><?php echo htmlspecialchars($found_routine['subject_code']); ?></div>
                                        <div class="course-name"><?php echo htmlspecialchars($found_routine['subject_name']); ?></div>
                                        <div class="teacher-name"><?php echo htmlspecialchars($found_routine['teacher_name']); ?></div>
                                        <div class="room-name"><?php echo htmlspecialchars($found_routine['room_name']); ?></div>
                                    </div>
                                <?php else: ?>
                                    <div class="empty-cell">-</div>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
} catch (PDOException $e) {
    echo "<div style='color: red; padding: 1rem; border: 1px solid red; border-radius: 0.5rem;'>
            Error loading schedule: " . htmlspecialchars($e->getMessage()) . "
          </div>";
}
?>
