<!-- Booking Requests Section -->
<section class="content-section active">
    <div class="section-header">
        <h2>Classroom Booking Requests</h2>
    </div>

    <div class="booking-filters">
        <?php $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all'; ?>
        <a href="index.php?page=bookings&filter=all" class="filter-btn <?php echo $filter == 'all' ? 'active' : ''; ?>">All</a>
        <a href="index.php?page=bookings&filter=Pending" class="filter-btn <?php echo $filter == 'Pending' ? 'active' : ''; ?>">Pending</a>
        <a href="index.php?page=bookings&filter=Approved" class="filter-btn <?php echo $filter == 'Approved' ? 'active' : ''; ?>">Approved</a>
        <a href="index.php?page=bookings&filter=Rejected" class="filter-btn <?php echo $filter == 'Rejected' ? 'active' : ''; ?>">Rejected</a>
    </div>

    <div class="card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Teacher</th>
                    <th>Room</th>
                    <th>Batch</th>
                    <th>Subject</th>
                    <th>Reason</th>
                    <th>Requested On</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="bookingsTableBody">
                <?php
                try {
                    $query = "SELECT trr.*, u.name as teacher_name, r.room_number, ts.start_time, ts.end_time, b.batch_name, s.name as subject_name
                              FROM teacher_room_request trr 
                              LEFT JOIN user u ON trr.teacher_id = u.id 
                              LEFT JOIN room r ON trr.room_id = r.id
                              LEFT JOIN time_slot ts ON trr.time_slot_id = ts.id
                              LEFT JOIN batch b ON trr.batch_id = b.id
                              LEFT JOIN subject s ON trr.subject_code = s.subject_code";
                    
                    if ($filter != 'all') {
                        $query .= " WHERE trr.status = :status";
                    }
                    $query .= " ORDER BY trr.id DESC";
                    
                    $stmt = $pdo->prepare($query);
                    if ($filter != 'all') {
                        $stmt->execute([':status' => $filter]);
                    } else {
                        $stmt->execute();
                    }
                    $bookings = $stmt->fetchAll();
                    
                    if (count($bookings) > 0) {
                        foreach ($bookings as $booking) {
                            $status_class = 'status-pending';
                            if ($booking['status'] == 'Approved') $status_class = 'status-approved';
                            if ($booking['status'] == 'Rejected') $status_class = 'status-rejected';
                            
                            $timeStr = $booking['day_of_week'] . ' ' . date('h:i A', strtotime($booking['start_time'])) . '-' . date('h:i A', strtotime($booking['end_time']));
                            
                            $conflictMsg = "";
                            $conflict_stmt = $pdo->prepare("SELECT COUNT(*) as count FROM routine WHERE day_of_week = ? AND time_slot_id = ? AND room_id = ?");
                            $conflict_stmt->execute([$booking['day_of_week'], $booking['time_slot_id'], $booking['room_id']]);
                            $conflict = $conflict_stmt->fetch();
                            $isOverlapping = $conflict['count'] > 0;
                            if ($isOverlapping) {
                                $conflictMsg = "<div style='color: #ef4444; font-size: 0.75rem; font-weight: 600;'><i data-lucide='alert-triangle' style='width: 12px; display: inline; vertical-align: middle;'></i> Overlaps Routine</div>";
                            }

                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($booking['id']) . '</td>';
                            echo '<td>' . htmlspecialchars($booking['teacher_name'] ?? 'N/A') . '</td>';
                            echo '<td>' . htmlspecialchars($booking['room_number'] ?? 'N/A') . '</td>';
                            echo '<td>' . htmlspecialchars($timeStr) . $conflictMsg . '</td>';
                            echo '<td>' . htmlspecialchars($booking['batch_name'] ?? 'All') . '</td>';
                            echo '<td>' . htmlspecialchars($booking['subject_name'] ?? ($booking['subject_code'] ?? '-')) . '</td>';
                            echo '<td>' . htmlspecialchars($booking['reason'] ?? '-') . '</td>';
                            echo '<td>' . date('M d, h:i A', strtotime($booking['created_at'])) . '</td>';
                            echo '<td><span class="status-badge ' . $status_class . '">' . htmlspecialchars($booking['status'] ?? 'Pending') . '</span></td>';
                            echo '<td class="actions">';
                            if (($booking['status'] ?? 'Pending') == 'Pending') {
                                $approveAttr = $isOverlapping ? 'disabled title="Classroom already occupied" style="background-color: #d1d5db; color: #6b7280; cursor: not-allowed; filter: grayscale(1);"' : 'onclick="updateBooking(' . $booking['id'] . ', \'Approved\')"';
                                echo '<button class="btn-approve" style="background-color: #10b981; color: white; border: none; padding: 0.25rem 0.5rem; border-radius: 4px; cursor: pointer; margin-right: 4px;" ' . $approveAttr . '>Approve</button>';
                                echo '<button class="btn-reject" style="background-color: #ef4444; color: white; border: none; padding: 0.25rem 0.5rem; border-radius: 4px; cursor: pointer; margin-right: 4px;" onclick="openRejectModal(' . $booking['id'] . ')">Reject</button>';
                            }
                            echo '<button class="btn-delete" style="background-color: #6b7280; color: white; border: none; padding: 0.25rem 0.5rem; border-radius: 4px; cursor: pointer;" onclick="deleteBooking(' . $booking['id'] . ')">Delete</button>';
                            echo '</td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="8" style="text-align:center; padding: 2rem; color: #9ca3af;">No booking requests found.</td></tr>';
                    }
                } catch (Exception $e) {
                    echo '<tr><td colspan="8" style="text-align:center; padding: 2rem; color: #ef4444;">Error loading bookings: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Reject Reason Modal -->
    <div id="rejectModal" class="modal-container" style="display:none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div class="card" style="width: 400px; padding: 1.5rem;">
            <h3 style="margin-bottom: 1rem;">Reject Booking Request</h3>
            <form id="rejectForm" method="POST" action="pages/actions/booking_action.php?action=update">
                <input type="hidden" name="id" id="rejectBookingId">
                <input type="hidden" name="status" value="Rejected">
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="reject_reason" style="display: block; margin-bottom: 0.5rem;">Reason for Rejection</label>
                    <textarea name="reject_reason" id="reject_reason" required style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 4px; min-height: 100px;"></textarea>
                </div>
                <div class="form-actions" style="display: flex; justify-content: flex-end; gap: 0.5rem;">
                    <button type="button" class="btn-secondary" onclick="closeRejectModal()" style="padding: 0.5rem 1rem;">Cancel</button>
                    <button type="submit" class="btn-primary" style="padding: 0.5rem 1rem; background-color: #ef4444;">Confirm Reject</button>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
function updateBooking(id, status) {
    const isApprove = status === 'Approved';
    showActionConfirm(
        (isApprove ? 'Approve' : 'Reject') + ' Request?', 
        `Are you sure you want to ${status.toLowerCase()} this classroom booking request?`,
        'pages/actions/booking_action.php?action=update&id=' + id + '&status=' + status,
        isApprove ? 'Approve Request' : 'Reject Request',
        isApprove ? 'btn-primary' : 'btn-confirm-delete',
        isApprove ? 'check-circle' : 'x-circle'
    );
}

function openRejectModal(id) {
    document.getElementById('rejectBookingId').value = id;
    document.getElementById('rejectModal').style.display = 'flex';
}

function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
}

function deleteBooking(id) {
    showDeleteConfirm(
        'Delete Request?', 
        'Are you sure you want to delete this booking request record permanently?',
        'pages/actions/booking_action.php?action=delete&id=' + id
    );
}
</script>

