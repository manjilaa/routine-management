<!-- Schedule Management Section -->
<section class="content-section active">
    <div class="section-header">
        <h2>Schedule Management</h2>
        <button class="btn-primary" id="addScheduleBtn" onclick="openAddScheduleModal()">
            <i data-lucide="plus"></i>
            Add Schedule
        </button>
    </div>

    <?php
    // Fetch batches and time slots for filters/forms
    try {
        $batch_stmt = $pdo->query("SELECT * FROM batch ORDER BY batch_name ASC");
        $all_batches = $batch_stmt->fetchAll();
        
        $timeslot_stmt = $pdo->query("SELECT * FROM time_slot ORDER BY FIELD(day_of_week, 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'), start_time ASC");
        $all_timeslots = $timeslot_stmt->fetchAll();
        
        $subject_stmt = $pdo->query("SELECT * FROM subject ORDER BY name ASC");
        $all_subjects = $subject_stmt->fetchAll();
        
        $room_stmt = $pdo->query("SELECT * FROM room ORDER BY room_number ASC");
        $all_rooms = $room_stmt->fetchAll();
        
        $teacher_stmt = $pdo->query("SELECT * FROM user WHERE LOWER(role) = 'teacher' ORDER BY name ASC");
        $all_teachers = $teacher_stmt->fetchAll();
    } catch (Exception $e) {
        $all_batches = $all_timeslots = $all_subjects = $all_rooms = $all_teachers = [];
    }
    
    $sel_batch = isset($_GET['batch_filter']) ? $_GET['batch_filter'] : '';
    $sel_day = isset($_GET['day_filter']) ? $_GET['day_filter'] : '';
    ?>

    <div class="schedule-filters">
        <form method="GET" action="index.php" style="display: flex; gap: 1rem; align-items: center;">
            <input type="hidden" name="page" value="schedules">
            <select name="batch_filter" class="filter-select" onchange="this.form.submit()">
                <option value="">All Batches</option>
                <?php foreach ($all_batches as $b): ?>
                    <option value="<?php echo $b['id']; ?>" <?php echo $sel_batch == $b['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($b['batch_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="day_filter" class="filter-select" onchange="this.form.submit()">
                <option value="">All Days</option>
                <?php
                $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                foreach ($days as $day):
                ?>
                    <option value="<?php echo $day; ?>" <?php echo $sel_day == $day ? 'selected' : ''; ?>>
                        <?php echo $day; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <div class="card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Batch</th>
                    <th>Day</th>
                    <th>Time</th>
                    <th>Subject</th>
                    <th>Teacher</th>
                    <th>Room</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="schedulesTableBody">
                <?php
                try {
                    $query = "SELECT r.*, b.batch_name, ts.start_time, ts.end_time, ts.day_of_week, 
                              s.name as subject_name, s.subject_code, u.name as teacher_name, rm.room_number
                              FROM routine r
                              LEFT JOIN batch b ON r.batch_id = b.id
                              LEFT JOIN time_slot ts ON r.time_slot_id = ts.id
                              LEFT JOIN subject s ON r.subject_code = s.subject_code
                              LEFT JOIN user u ON r.teacher_id = u.id
                              LEFT JOIN room rm ON r.room_id = rm.id
                              WHERE 1=1";
                    
                    $params = [];
                    if (!empty($sel_batch)) {
                        $query .= " AND r.batch_id = :batch_id";
                        $params[':batch_id'] = $sel_batch;
                    }
                    if (!empty($sel_day)) {
                        $query .= " AND r.day_of_week = :day";
                        $params[':day'] = $sel_day;
                    }
                    
                    $query .= " ORDER BY b.batch_name, FIELD(r.day_of_week, 'Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'), ts.start_time";
                    
                    $stmt = $pdo->prepare($query);
                    $stmt->execute($params);
                    $routines = $stmt->fetchAll();
                    
                    if (count($routines) > 0) {
                        foreach ($routines as $routine) {
                            $time_display = '';
                            if ($routine['start_time'] && $routine['end_time']) {
                                $time_display = date('h:i A', strtotime($routine['start_time'])) . ' - ' . date('h:i A', strtotime($routine['end_time']));
                            }
                            
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($routine['id']) . '</td>';
                            echo '<td>' . htmlspecialchars($routine['batch_name'] ?? 'N/A') . '</td>';
                            echo '<td>' . htmlspecialchars($routine['day_of_week'] ?? '-') . '</td>';
                            echo '<td>' . htmlspecialchars($time_display ?: '-') . '</td>';
                            echo '<td>' . htmlspecialchars(($routine['subject_code'] ? $routine['subject_code'] . ' - ' : '') . ($routine['subject_name'] ?? 'N/A')) . '</td>';
                            echo '<td>' . htmlspecialchars($routine['teacher_name'] ?? 'N/A') . '</td>';
                            echo '<td>' . htmlspecialchars($routine['room_number'] ?? 'N/A') . '</td>';
                            echo '<td class="actions">';
                            echo '<button class="btn-delete" onclick="deleteSchedule(' . $routine['id'] . ')">Delete</button>';
                            echo '</td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="8" style="text-align:center; padding: 2rem; color: #9ca3af;">No schedules found. Click "Add Schedule" to create one.</td></tr>';
                    }
                } catch (Exception $e) {
                    echo '<tr><td colspan="8" style="text-align:center; padding: 2rem; color: #ef4444;">Error loading schedules: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</section>

<script>
// Store data for the modal selects
const batchesData = <?php echo json_encode($all_batches); ?>;
const timeslotsData = <?php echo json_encode($all_timeslots); ?>;
const subjectsData = <?php echo json_encode($all_subjects); ?>;
const roomsData = <?php echo json_encode($all_rooms); ?>;
const teachersData = <?php echo json_encode($all_teachers); ?>;

function updateModalTimeslots() {
    const timeslotSelect = document.getElementById('scheduleTimeslot');
    let options = '<option value="">Select Time Slot</option>';
    
    timeslotsData.forEach(ts => {
        const start = new Date('2000-01-01T' + ts.start_time);
        const end = new Date('2000-01-01T' + ts.end_time);
        const rangeStr = start.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) + ' - ' + end.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        options += `<option value="${ts.id}">${rangeStr}</option>`;
    });
    
    timeslotSelect.innerHTML = options;
}

function openAddScheduleModal() {
    let batchOptions = '<option value="">Select Batch</option>';
    batchesData.forEach(b => {
        batchOptions += `<option value="${b.id}">${b.batch_name}</option>`;
    });
    
    const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    let dayOptions = '<option value="">Select Day</option>';
    days.forEach(d => {
        dayOptions += `<option value="${d}">${d}</option>`;
    });
    
    let subjectOptions = '<option value="">Select Subject</option>';
    subjectsData.forEach(s => {
        subjectOptions += `<option value="${s.subject_code}">${s.subject_code} - ${s.name}</option>`;
    });
    
    let roomOptions = '<option value="">Select Room</option>';
    roomsData.forEach(r => {
        roomOptions += `<option value="${r.id}">${r.room_number}</option>`;
    });
    
    let teacherOptions = '<option value="">Select Teacher</option>';
    teachersData.forEach(t => {
        teacherOptions += `<option value="${t.id}">${t.name}</option>`;
    });

    document.getElementById('modalTitle').textContent = 'Add New Schedule';
    document.getElementById('modalBody').innerHTML = `
        <form id="addScheduleForm" method="POST" action="pages/actions/schedule_action.php">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label for="scheduleBatch">Batch</label>
                <select id="scheduleBatch" name="batch_id" required>${batchOptions}</select>
            </div>
            <div class="form-group">
                <label for="scheduleDay">Day of Week</label>
                <select id="scheduleDay" name="day_of_week" required>
                    ${dayOptions}
                </select>
            </div>
            <div class="form-group">
                <label for="scheduleTimeslot">Time Slot</label>
                <select id="scheduleTimeslot" name="time_slot_id" required>
                    <option value="">Select Time Slot</option>
                </select>
            </div>
            <div class="form-group">
                <label for="scheduleSubject">Subject</label>
                <select id="scheduleSubject" name="subject_code" required>${subjectOptions}</select>
            </div>
            <div class="form-group">
                <label for="scheduleTeacher">Teacher</label>
                <select id="scheduleTeacher" name="teacher_id" required>${teacherOptions}</select>
            </div>
            <div class="form-group">
                <label for="scheduleRoom">Room</label>
                <select id="scheduleRoom" name="room_id" required>${roomOptions}</select>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn-primary">Add Schedule</button>
            </div>
        </form>
    `;
    document.getElementById('modalContainer').classList.add('active');
    updateModalTimeslots();
    lucide.createIcons();
}

function deleteSchedule(id) {
    showDeleteConfirm(
        'Delete Schedule Entry?', 
        'Are you sure you want to remove this entry from the routine?',
        'pages/actions/schedule_action.php?action=delete&id=' + id
    );
}
</script>
