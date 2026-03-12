<?php
// Start session
session_start();

// Include database connection
require_once '../includes/db-config.php';

// Check if user is logged in and has teacher role
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'teacher') {
    header("Location: ../teacher-login/login.php");
    exit();
}

// Get user data from session
$userName = $_SESSION['name'] ?? 'Teacher';
$userEmail = $_SESSION['email'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - UniRoutine</title>
    <link rel="stylesheet" href="../assets/css/teacher-style.css?v=<?php echo filemtime('../assets/css/teacher-style.css'); ?>">
    <script src="https://cdn.jsdelivr.net/npm/lucide@0.263.1/dist/umd/lucide.min.js"></script>
</head>
<body>
    <!-- Header -->
    <header class="header teacher-header">
        <div class="header-content">
            <div class="header-left">
                <div class="header-logo">
                    <img src="../assets/images/ku.png" alt="University Logo">
                </div>
                <div>
                    <h1>Teacher Dashboard</h1>
                    <p>Manage your classes and schedules</p>
                </div>
            </div>
            <div class="header-right">
                <div class="user-info">
                    <p id="userName"><?php echo htmlspecialchars($userName); ?></p>
                    <p class="user-role">Teacher - <?php echo htmlspecialchars($userEmail); ?></p>
                </div>

                <button onclick="logout()" class="btn-secondary">
                    <i data-lucide="log-out"></i>
                    Logout
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <?php
        // Global Alert System
        if (isset($_GET['msg'])) {
            $msgType = 'success';
            $msgText = '';
            
            switch ($_GET['msg']) {
                case 'booked': $msgText = 'Your classroom booking request has been submitted successfully.'; break;
                case 'duplicate': $msgType = 'error'; $msgText = 'Warning: You have already submitted a request for this room and time slot.'; break;
                case 'overlap_conflict': $msgType = 'error'; $msgText = 'Error: This classroom or batch is already scheduled for that time slot.'; break;
                case 'leave_submitted': $msgText = 'Your leave request has been submitted successfully.'; break;
                case 'leave_duplicate': $msgType = 'error'; $msgText = 'You already have a pending or approved leave for that class.'; break;
                case 'leave_error': $msgType = 'error'; $msgText = 'An error occurred while submitting your leave request.'; break;
                case 'error': $msgType = 'error'; $msgText = 'An error occurred while processing your request.'; break;
            }
            
            if ($msgText) {
                echo '<div class="alert alert-' . $msgType . '" id="globalAlert">';
                echo '<i data-lucide="' . ($msgType == 'success' ? 'check-circle' : 'alert-circle') . '"></i>';
                echo '<span>' . $msgText . '</span>';
                echo '</div>';
                echo '<script>setTimeout(() => { document.getElementById("globalAlert")?.remove(); }, 5000);</script>';
            }
        }
        ?>
        
        <!-- My Schedule Card -->
        <div class="card">
            <div class="card-header teacher-card-header">
                <h2>My Schedule</h2>
                <i data-lucide="calendar"></i>
            </div>
            <?php 
                $selected_teacher = $_SESSION['user_id'];
                include '../routine/routine_table.php'; 
            ?>
        </div>

        <!-- Leave Request Card -->
        <div class="card">
            <div class="card-header" style="background: linear-gradient(135deg, #7c3aed, #6d28d9); color: white; display:flex; justify-content:space-between; align-items:center; padding:1.25rem 1.5rem; border-radius:0.75rem 0.75rem 0 0;">
                <h2 style="margin:0; font-size:1.1rem; font-weight:600;">Leave Requests</h2>
                <i data-lucide="calendar-x" style="width:1.25rem; height:1.25rem;"></i>
            </div>
            <div style="overflow-x:auto; padding:1.25rem;">
                <?php
                try {
                    $leave_stmt2 = $pdo->prepare("
                        SELECT r.id as routine_id, r.day_of_week, s.subject_code, s.name as subject_name,
                               b.batch_name, rm.room_number, ts.start_time, ts.end_time,
                               (SELECT lr.status FROM leave_request lr 
                                WHERE lr.routine_id = r.id AND lr.teacher_id = ?
                                ORDER BY FIELD(lr.status, 'Pending', 'Approved', 'Rejected') ASC
                                LIMIT 1) as leave_status
                        FROM routine r
                        JOIN subject s ON r.subject_code = s.subject_code
                        JOIN batch b ON r.batch_id = b.id
                        JOIN room rm ON r.room_id = rm.id
                        JOIN time_slot ts ON r.time_slot_id = ts.id
                        WHERE r.teacher_id = ?
                        ORDER BY ts.start_time ASC, r.day_of_week ASC
                    ");
                    $leave_stmt2->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
                    $my_classes = $leave_stmt2->fetchAll();

                    if (empty($my_classes)) {
                        echo '<p style="text-align:center; color:#9ca3af; padding:2rem;">No scheduled classes found.</p>';
                    } else {
                        echo '<table class="data-table" style="width:100%;">';
                        echo '<thead><tr>
                            <th>Subject</th>
                            <th>Day</th>
                            <th>Time</th>
                            <th>Batch</th>
                            <th>Room</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr></thead><tbody>';
                        foreach ($my_classes as $cls) {
                            $timeRange = date('H:i', strtotime($cls['start_time'])) . ' – ' . date('H:i', strtotime($cls['end_time']));
                            $leaveStatus = $cls['leave_status'];
                            echo '<tr>';
                            echo '<td><strong>' . htmlspecialchars($cls['subject_code']) . '</strong><br><small>' . htmlspecialchars($cls['subject_name']) . '</small></td>';
                            echo '<td>' . htmlspecialchars($cls['day_of_week']) . '</td>';
                            echo '<td>' . htmlspecialchars($timeRange) . '</td>';
                            echo '<td>' . htmlspecialchars($cls['batch_name']) . '</td>';
                            echo '<td>' . htmlspecialchars($cls['room_number']) . '</td>';
                            if ($leaveStatus === 'Approved') {
                                echo '<td><span class="status-badge status-rejected">Approved Leave</span></td>';
                                echo '<td><span style="color:#9ca3af; font-size:0.8rem;">Already approved</span></td>';
                            } elseif ($leaveStatus === 'Pending') {
                                echo '<td><span class="status-badge status-pending">Pending</span></td>';
                                echo '<td><span style="color:#9ca3af; font-size:0.8rem;">Awaiting admin</span></td>';
                            } elseif ($leaveStatus === 'Rejected') {
                                echo '<td><span class="status-badge status-rejected">Rejected</span></td>';
                                echo '<td><button class="btn-purple" onclick="openLeaveModal(' . $cls['routine_id'] . ', \'' . htmlspecialchars($cls['day_of_week'], ENT_QUOTES) . '\', \'' . htmlspecialchars($cls['subject_code'] . ' – ' . $cls['subject_name'], ENT_QUOTES) . '\')">Request Leave</button></td>';
                            } else {
                                echo '<td><span class="status-badge" style="background:#e5e7eb; color:#6b7280;">No Leave</span></td>';
                                echo '<td><button class="btn-purple" onclick="openLeaveModal(' . $cls['routine_id'] . ', \'' . htmlspecialchars($cls['day_of_week'], ENT_QUOTES) . '\', \'' . htmlspecialchars($cls['subject_code'] . ' – ' . $cls['subject_name'], ENT_QUOTES) . '\')">Request Leave</button></td>';
                            }

                            echo '</tr>';
                        }
                        echo '</tbody></table>';
                    }
                } catch (Exception $e) {
                    echo '<p style="color:#ef4444;">Error loading classes: ' . htmlspecialchars($e->getMessage()) . '</p>';
                }
                ?>
            </div>
        </div>

        <!-- Leave Request Modal -->
        <div id="leaveModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:2000; align-items:center; justify-content:center;">
            <div style="background:white; border-radius:0.75rem; padding:2rem; max-width:440px; width:90%; box-shadow:0 25px 50px rgba(0,0,0,0.3);">
                <h3 style="margin-bottom:0.25rem; color:#111827;">Request Leave</h3>
                <p id="leaveModalSubject" style="color:#6b7280; font-size:0.9rem; margin-bottom:1.25rem;"></p>
                <form method="POST" action="actions/leave_request.php">
                    <input type="hidden" name="routine_id" id="leaveRoutineId">
                    <input type="hidden" name="day_of_week" id="leaveDayOfWeek">
                    <div class="form-group" style="margin-bottom:1rem;">
                        <label for="leaveReason" style="display:block; font-weight:500; margin-bottom:0.4rem; color:#374151;">Reason for Leave</label>
                        <textarea id="leaveReason" name="reason" rows="4" required
                            style="width:100%; padding:0.625rem 0.75rem; border:1px solid #d1d5db; border-radius:0.5rem; font-family:inherit; font-size:0.9rem; resize:vertical;"
                            placeholder="Briefly describe the reason for your leave request..."></textarea>
                    </div>
                    <div style="display:flex; gap:0.75rem; justify-content:flex-end;">
                        <button type="button" class="btn-secondary" onclick="closeLeaveModal()">Cancel</button>
                        <button type="submit" style="background:#7c3aed; color:white; border:none; padding:0.625rem 1.25rem; border-radius:0.5rem; font-weight:600; cursor:pointer;">Submit Request</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
        function openLeaveModal(routineId, day, subject) {
            document.getElementById('leaveRoutineId').value = routineId;
            document.getElementById('leaveDayOfWeek').value = day;
            document.getElementById('leaveModalSubject').textContent = subject + ' · ' + day;
            document.getElementById('leaveModal').style.display = 'flex';
        }
        function closeLeaveModal() {
            document.getElementById('leaveModal').style.display = 'none';
        }
        </script>

        <!-- Classroom Booking Card -->
        <div class="card">
            <div class="card-header request-card-header">
                <h2>Classroom Booking Requests</h2>
                <button onclick="openBookingModal()" class="btn-secondary" style="background-color: white; color: #2563eb;">
                    <i data-lucide="plus"></i>
                    New Request
                </button>
            </div>
            <div class="requests-container">
                <?php
                try {
                    $stmt = $pdo->prepare("
                        SELECT trr.*, r.room_number, ts.day_of_week, ts.start_time, ts.end_time 
                        FROM teacher_room_request trr
                        JOIN room r ON trr.room_id = r.id
                        JOIN time_slot ts ON trr.time_slot_id = ts.id
                        WHERE trr.teacher_id = ?
                        ORDER BY trr.created_at DESC
                    ");
                    $stmt->execute([$_SESSION['user_id']]);
                    $requests = $stmt->fetchAll();

                    if (count($requests) > 0) {
                        foreach ($requests as $req) {
                            $statusClass = 'status-pending';
                            if ($req['status'] === 'Approved') $statusClass = 'status-approved';
                            if ($req['status'] === 'Rejected') $statusClass = 'status-rejected';
                            
                            echo '
                            <div class="request-card">
                                <div class="request-header">
                                    <div>
                                        <h3>Room ' . htmlspecialchars($req['room_number']) . '</h3>
                                        <p>' . htmlspecialchars($req['day_of_week']) . ', ' . date('H:i', strtotime($req['start_time'])) . ' - ' . date('H:i', strtotime($req['end_time'])) . '</p>
                                    </div>
                                    <span class="status-badge ' . $statusClass . '">' . htmlspecialchars($req['status']) . '</span>
                                </div>
                                <p class="request-remarks">' . htmlspecialchars($req['reason']) . '</p>
                                ' . ($req['status'] === 'Rejected' && $req['reject_reason'] ? '<div class="reject-reason" style="margin-top: 0.5rem; padding: 0.5rem; background-color: #fef2f2; color: #991b1b; border-radius: 4px; border-left: 3px solid #ef4444; font-size: 0.875rem;"><strong>Rejection Reason:</strong> ' . htmlspecialchars($req['reject_reason']) . '</div>' : '') . '
                                <p class="request-date">Requested on: ' . date('M d, Y', strtotime($req['created_at'])) . '</p>
                            </div>';
                        }
                    } else {
                        echo '<p style="text-align:center; padding: 2rem; color: #9ca3af;">No booking requests found.</p>';
                    }
                } catch (Exception $e) {
                    echo '<p style="color:red;">Error loading requests: ' . $e->getMessage() . '</p>';
                }
                ?>
            </div>
        </div>

    </main>

    <!-- Booking Modal -->
    <div id="bookingModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Book Classroom</h2>
                <button class="close-btn" onclick="closeBookingModal()">
                    <i data-lucide="x"></i>
                </button>
            </div>
            <form id="bookingForm" method="POST" action="actions/book_room.php">
                <div class="form-row">
                    <div>
                        <label for="batch_id">Target Batch</label>
                        <select id="batch_id" name="batch_id" required onchange="validateBooking()">
                            <option value="">Select Batch</option>
                            <?php
                                try {
                                    $stmt = $pdo->query("SELECT id, batch_name FROM batch ORDER BY batch_name ASC");
                                    while($row = $stmt->fetch()) {
                                        echo "<option value='".htmlspecialchars($row['id'])."'>".htmlspecialchars($row['batch_name'])."</option>";
                                    }
                                } catch (PDOException $e) {
                                    echo "<option value=''>Error loading batches</option>";
                                }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label for="classroom">Classroom</label>
                        <select id="classroom" name="room_id" required onchange="validateBooking()">
                            <option value="">Select Classroom</option>
                            <?php
                                try {
                                    $stmt = $pdo->query("SELECT * FROM room ORDER BY room_number ASC");
                                    $roomsData = [];
                                    while($row = $stmt->fetch()) {
                                        $roomsData[$row['id']] = [
                                            'capacity' => $row['capacity'],
                                            'smart_board' => $row['smart_board'],
                                            'white_board' => $row['white_board'],
                                            'ac' => $row['ac'],
                                            'fan' => $row['fan'],
                                            'projector' => $row['projector']
                                        ];
                                        echo "<option value='".htmlspecialchars($row['id'])."'>".htmlspecialchars($row['room_number'])."</option>";
                                    }
                                } catch (PDOException $e) {
                                    echo "<option value=''>Error loading rooms</option>";
                                }

                                // Also fetch existing routines for conflict checking
                                $routine_stmt = $pdo->query("SELECT room_id, time_slot_id, batch_id, day_of_week FROM routine");
                                $existingRoutines = $routine_stmt->fetchAll();
                            ?>
                        </select>
                        
                        <!-- Room Facility Info Display -->
                        <div id="roomFacilityInfo" class="room-facilities">
                            <h4><i data-lucide="info"></i> Classroom Facilities</h4>
                            <div class="facilities-grid" id="facilitiesContainer">
                                <!-- Populated by JS -->
                            </div>
                        </div>

                        <script>
                            const roomsInfo = <?php echo json_encode($roomsData); ?>;
                            const timeslotsData = <?php 
                                $ts_stmt = $pdo->query("SELECT id, start_time, end_time, day_of_week FROM time_slot ORDER BY start_time ASC");
                                echo json_encode($ts_stmt->fetchAll()); 
                            ?>;
                            
                            function updateModalTimeslots(selectedDay) {
                                const timeslotSelect = document.getElementById('time_slot_id');
                                let options = '<option value="">Select Time Slot</option>';
                                
                                if (selectedDay) {
                                    // Filter timeslots - now that they are global, we just show them
                                    // User said "there should be no 'day' option [in time slots]. it should apply for all days"
                                    // So we show ALL time slots for any day selected.
                                    timeslotsData.forEach(ts => {
                                        const start = new Date('2000-01-01T' + ts.start_time);
                                        const end = new Date('2000-01-01T' + ts.end_time);
                                        const rangeStr = start.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) + ' - ' + end.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                                        options += `<option value="${ts.id}">${rangeStr}</option>`;
                                    });
                                }
                                
                                timeslotSelect.innerHTML = options;
                                validateBooking();
                            }

                            function validateBooking() {
                                updateRoomFacilities();
                                checkConflicts();
                            }

                            function updateRoomFacilities() {
                                const roomId = document.getElementById('classroom').value;
                                const container = document.getElementById('roomFacilityInfo');
                                const grid = document.getElementById('facilitiesContainer');
                                
                                if (!roomId || !roomsInfo[roomId]) {
                                    container.classList.remove('active');
                                    return;
                                }
                                
                                const info = roomsInfo[roomId];
                                grid.innerHTML = `
                                    <div class="facility-item available">
                                        <i data-lucide="users"></i>
                                        <span>Capacity: ${info.capacity || '-'}</span>
                                    </div>
                                    <div class="facility-item ${info.smart_board ? 'available' : 'not-available'}">
                                        <i data-lucide="tablet"></i>
                                        <span>Smart Board</span>
                                    </div>
                                    <div class="facility-item ${info.white_board ? 'available' : 'not-available'}">
                                        <i data-lucide="edit-3"></i>
                                        <span>White Board</span>
                                    </div>
                                    <div class="facility-item ${info.ac ? 'available' : 'not-available'}">
                                        <i data-lucide="wind"></i>
                                        <span>AC</span>
                                    </div>
                                    <div class="facility-item ${info.fan ? 'available' : 'not-available'}">
                                        <i data-lucide="fan"></i>
                                        <span>Fan</span>
                                    </div>
                                    <div class="facility-item ${info.projector ? 'available' : 'not-available'}">
                                        <i data-lucide="monitor"></i>
                                        <span>Projector</span>
                                    </div>
                                `;
                                container.classList.add('active');
                                lucide.createIcons();
                            }

                            function checkConflicts() {
                                const roomId = document.getElementById('classroom').value;
                                const timeSelect = document.getElementById('time_slot_id');
                                const batchId = document.getElementById('batch_id').value;
                                const dayOfWeek = document.getElementById('day_of_week').value;
                                const warning = document.getElementById('overlapWarning');
                                const submitBtn = document.querySelector('#bookingForm button[type="submit"]');
                                
                                // Reset all options first
                                Array.from(timeSelect.options).forEach(opt => {
                                    if (opt.value) {
                                        opt.disabled = false;
                                        if (opt.hasAttribute('data-orig')) {
                                            opt.text = opt.getAttribute('data-orig');
                                        }
                                    }
                                });

                                // Disable occupied slots
                                if ((roomId || batchId) && dayOfWeek) {
                                    Array.from(timeSelect.options).forEach(opt => {
                                        if (!opt.value) return;
                                        
                                        const timeId = opt.value;
                                        const isOccupied = existingRoutines.some(r => 
                                            r.time_slot_id == timeId && 
                                            r.day_of_week == dayOfWeek && 
                                            r.room_id == roomId
                                        );
                                        
                                        if (isOccupied) {
                                            if (!opt.hasAttribute('data-orig')) {
                                                opt.setAttribute('data-orig', opt.text);
                                            }
                                            opt.disabled = true;
                                            opt.text = opt.getAttribute('data-orig') + ' [Occupied]';
                                        }
                                    });
                                }

                                const currentTimeId = timeSelect.value;
                                let conflict = false;
                                if (roomId && currentTimeId && dayOfWeek) {
                                    conflict = existingRoutines.some(r => 
                                        r.time_slot_id == currentTimeId && 
                                        r.day_of_week == dayOfWeek &&
                                        r.room_id == roomId
                                    );
                                }
                                
                                if (conflict) {
                                    warning.innerHTML = '<i data-lucide="alert-circle"></i> <span>Note: This time slot overlaps with an existing routine. You can still submit, but admin will see the conflict.</span>';
                                    warning.style.backgroundColor = '#fef3c7';
                                    warning.style.color = '#92400e';
                                    warning.style.borderColor = '#f59e0b';
                                    warning.classList.add('active');
                                } else {
                                    warning.classList.remove('active');
                                }
                                
                                submitBtn.disabled = false;
                                submitBtn.style.opacity = '1';
                                submitBtn.style.cursor = 'pointer';
                                lucide.createIcons();
                            }
                        </script>
                    </div>
                </div>

                <div class="form-row">
                    <div>
                        <label for="subject_selection">Subject</label>
                        <select id="subject_selection" name="subject_code" required>
                            <option value="">Select Subject</option>
                            <?php
                                try {
                                    $stmt = $pdo->query("SELECT subject_code, name FROM subject ORDER BY name ASC");
                                    while($row = $stmt->fetch()) {
                                        echo "<option value='".htmlspecialchars($row['subject_code'])."'>".htmlspecialchars($row['name'])." (".htmlspecialchars($row['subject_code']).")</option>";
                                    }
                                } catch (PDOException $e) {
                                    echo "<option value=''>Error loading subjects</option>";
                                }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label for="day_of_week">Day of Week</label>
                        <select id="day_of_week" name="day_of_week" required onchange="updateModalTimeslots(this.value)">
                            <option value="">Select Day</option>
                            <option value="Sunday">Sunday</option>
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednesday">Wednesday</option>
                            <option value="Thursday">Thursday</option>
                            <option value="Friday">Friday</option>
                            <option value="Saturday">Saturday</option>
                        </select>
                    </div>
                    <div>
                        <label for="time_slot_id">Time Slot</label>
                        <select id="time_slot_id" name="time_slot_id" required onchange="validateBooking()">
                            <option value="">Select Day first</option>
                        </select>
                    </div>
                </div>
                
                <!-- Conflict Warning -->
                <div id="overlapWarning" class="conflict-warning">
                    <i data-lucide="alert-triangle"></i>
                    <span>This classroom or batch is already scheduled for the selected time slot.</span>
                </div>

                <div class="modal-actions">
                    <button type="button" onclick="closeBookingModal()" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">
                        <i data-lucide="send"></i>
                        Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        function logout() {
            showConfirm(
                'Logout Session',
                'Are you sure you want to end your current session?',
                '../teacher-login/logout.php',
                '../assets/images/logout.png'
            );
        }

        // GUI Confirmation
        function showConfirm(title, message, actionUrl, iconPath = null) {
            const modal = document.getElementById('bookingModal');
            modal.classList.add('modal-confirm');
            
            // Set header
            modal.querySelector('.modal-header h2').textContent = 'Confirmation Required';
            
            // Set body
            modal.querySelector('form').style.display = 'none';
            let confirmContainer = modal.querySelector('.confirm-container');
            if (!confirmContainer) {
                confirmContainer = document.createElement('div');
                confirmContainer.className = 'confirm-container';
                confirmContainer.style.padding = '2rem';
                modal.querySelector('.modal-content').appendChild(confirmContainer);
            }
            
            confirmContainer.style.display = 'block';
            
            let iconHtml = '<i data-lucide="help-circle"></i>';
            if (iconPath) {
                iconHtml = `<img src="${iconPath}" alt="Logout" style="width: 2rem; height: 2rem; object-fit: contain;">`;
            }
            
            confirmContainer.innerHTML = `
                <div class="confirm-icon">
                    ${iconHtml}
                </div>
                <h3 class="confirm-title">${title}</h3>
                <p class="confirm-text">${message}</p>
                <div class="confirm-actions">
                    <button type="button" class="btn-confirm-cancel" onclick="closeBookingModal()">Cancel</button>
                    <a href="${actionUrl}" class="btn-confirm-primary">Confirm</a>
                </div>
            `;
            
            modal.style.display = 'flex';
            lucide.createIcons();
        }

        function openBookingModal() {
            const modal = document.getElementById('bookingModal');
            modal.classList.remove('modal-confirm');
            modal.querySelector('.modal-header h2').textContent = 'Book Classroom';
            modal.querySelector('form').style.display = 'block';
            const confirmContainer = modal.querySelector('.confirm-container');
            if (confirmContainer) confirmContainer.style.display = 'none';
            
            modal.style.display = 'flex';
            lucide.createIcons();
        }

        function closeBookingModal() {
            document.getElementById('bookingModal').style.display = 'none';
            document.getElementById('roomFacilityInfo').classList.remove('active');
            document.getElementById('overlapWarning').classList.remove('active');
            
            // Re-enable submit button in case it was disabled
            const submitBtn = document.querySelector('#bookingForm button[type="submit"]');
            submitBtn.disabled = false;
            submitBtn.style.opacity = '1';
            submitBtn.style.cursor = 'pointer';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('bookingModal');
            if (event.target === modal) {
                closeBookingModal();
            }
        }
    </script>
</body>
</html>