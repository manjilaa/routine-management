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

    // 3. Fetch routines with joined data for display (with optional filters)
    $where_conditions = [];
    $request_where_conditions = ["trr.status = 'Approved'"];
    $params = [];

    if (isset($selected_batch) && $selected_batch !== "") {
        $where_conditions[] = "r.batch_id = ?";
        $request_where_conditions[] = "trr.batch_id = ?";
        $params[] = $selected_batch;
    }

    if (isset($selected_teacher) && $selected_teacher !== "") {
        $where_conditions[] = "r.teacher_id = ?";
        $request_where_conditions[] = "trr.teacher_id = ?";
        $params[] = $selected_teacher;
    }

    $where_clause = !empty($where_conditions) ? " WHERE " . implode(" AND ", $where_conditions) : "";
    $request_where_clause = !empty($request_where_conditions) ? " WHERE " . implode(" AND ", $request_where_conditions) : "";

    // Fetch Regular Routine
    $routine_sql = "SELECT 
                        r.*, 
                        s.name as subject_name, 
                        u.name as teacher_name, 
                        b.batch_name, 
                        rm.room_number as room_name, 
                        r.day_of_week as day, ts.start_time, ts.end_time 
                    FROM routine r
                    JOIN subject s ON r.subject_code = s.subject_code
                    JOIN user u ON r.teacher_id = u.id
                    JOIN batch b ON r.batch_id = b.id
                    JOIN room rm ON r.room_id = rm.id
                    JOIN time_slot ts ON r.time_slot_id = ts.id" . $where_clause;
    
    $r_stmt = $pdo->prepare($routine_sql);
    $r_stmt->execute($params);
    $all_routines = $r_stmt->fetchAll();

    // Fetch Approved Requests (Overrides)
    $request_sql = "SELECT 
                        trr.id,
                        trr.batch_id,
                        trr.time_slot_id,
                        trr.subject_code,
                        s.name as subject_name,
                        b.batch_name,
                        rm.room_number as room_name,
                        trr.day_of_week as day, ts.start_time, ts.end_time,
                        'override-class' as class_type
                    FROM teacher_room_request trr
                    JOIN subject s ON trr.subject_code = s.subject_code
                    JOIN batch b ON trr.batch_id = b.id
                    JOIN room rm ON trr.room_id = rm.id
                    JOIN time_slot ts ON trr.time_slot_id = ts.id" . $request_where_clause;
    
    $req_stmt = $pdo->prepare($request_sql);
    $req_stmt->execute($params);
    $approved_requests = $req_stmt->fetchAll();

    // Organize schedule [day][range]
    $schedule_data = [];
    
    // First, map regular routines
    foreach ($all_routines as $row) {
        $startTime = date("h:i A", strtotime($row['start_time']));
        $endTime = date("h:i A", strtotime($row['end_time']));
        $rangeKey = $startTime . " - " . $endTime;
        $schedule_data[$row['day']][$rangeKey] = $row;
    }

    // Then, map approved requests which REPLACE regular routines for that slot
    foreach ($approved_requests as $row) {
        $startTime = date("h:i A", strtotime($row['start_time']));
        $endTime = date("h:i A", strtotime($row['end_time']));
        $rangeKey = $startTime . " - " . $endTime;
        $schedule_data[$row['day']][$rangeKey] = $row;
    }

    // Build a leave lookup: [routine_id][day_of_week] => true
    $leave_lookup = [];
    try {
        $leave_sql = "SELECT lr.routine_id, lr.day_of_week FROM leave_request lr WHERE lr.status = 'Approved'";
        $leave_stmt = $pdo->query($leave_sql);
        foreach ($leave_stmt->fetchAll() as $lr) {
            $leave_lookup[$lr['routine_id']][$lr['day_of_week']] = true;
        }
    } catch (PDOException $e) {
        // leave_request table may not exist yet; silently skip
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
                    $range = date("h:i A", strtotime($ts['start_time'])) . " - " . date("h:i A", strtotime($ts['end_time']));
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
                                $found_routine = $schedule_data[$day][$range] ?? null;
                                // Check if this slot has an approved leave
                                $is_leave = $found_routine && isset($leave_lookup[$found_routine['id']][$day]);

                                if ($found_routine): 
                                ?>
                                    <?php if ($is_leave): ?>
                                        <div class="schedule-cell" style="background:#fef2f2; border:2px solid #ef4444; border-radius:0.5rem; padding:0.5rem; text-align:center;">
                                            <div style="color:#ef4444; font-weight:700; font-size:0.95rem; letter-spacing:0.05em;">LEAVE</div>
                                            <div class="course-code" style="color:#b91c1c; font-size:0.75rem;"><?php echo htmlspecialchars($found_routine['subject_code']); ?></div>
                                            <div class="course-name" style="color:#b91c1c; font-size:0.7rem;"><?php echo htmlspecialchars($found_routine['subject_name']); ?></div>
                                        </div>
                                    <?php else: ?>
                                        <div class="schedule-cell student-schedule">
                                            <div class="course-code">
                                                <?php echo htmlspecialchars($found_routine['subject_code']); ?>
                                            </div>
                                            <div class="course-name"><?php echo htmlspecialchars($found_routine['subject_name']); ?></div>
                                            <div class="teacher-name"><?php echo htmlspecialchars($found_routine['batch_name'] ?? '-'); ?></div>
                                            <div class="room-name"><?php echo htmlspecialchars($found_routine['room_name']); ?></div>
                                        </div>
                                    <?php endif; ?>
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
