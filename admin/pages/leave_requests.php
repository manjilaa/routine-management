<?php
// Leave Requests Admin Page
?>
<!-- Leave Requests Section -->
<section class="content-section active">
    <div class="section-header">
        <h2>Leave Requests</h2>
    </div>

    <div class="card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Teacher</th>
                    <th>Subject</th>
                    <th>Day</th>
                    <th>Time</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                try {
                    $stmt = $pdo->query("
                        SELECT lr.*, 
                               u.name AS teacher_name,
                               s.name AS subject_name,
                               s.subject_code,
                               ts.start_time, ts.end_time,
                               r.day_of_week AS routine_day
                        FROM leave_request lr
                        JOIN user u ON lr.teacher_id = u.id
                        JOIN routine r ON lr.routine_id = r.id
                        JOIN subject s ON r.subject_code = s.subject_code
                        JOIN time_slot ts ON r.time_slot_id = ts.id
                        ORDER BY 
                            FIELD(lr.status,'Pending','Approved','Rejected'),
                            lr.created_at DESC
                    ");
                    $requests = $stmt->fetchAll();

                    if (empty($requests)) {
                        echo '<tr><td colspan="8" style="text-align:center; padding:2rem; color:#9ca3af;">No leave requests found.</td></tr>';
                    } else {
                        $i = 1;
                        foreach ($requests as $req) {
                            $statusClass = match($req['status']) {
                                'Approved' => 'status-approved',
                                'Rejected' => 'status-rejected',
                                default    => 'status-pending',
                            };
                            $timeRange = date('H:i', strtotime($req['start_time'])) . ' - ' . date('H:i', strtotime($req['end_time']));
                            echo '<tr>';
                            echo '<td>' . $i++ . '</td>';
                            echo '<td>' . htmlspecialchars($req['teacher_name']) . '</td>';
                            echo '<td>' . htmlspecialchars($req['subject_code']) . ' – ' . htmlspecialchars($req['subject_name']) . '</td>';
                            echo '<td>' . htmlspecialchars($req['day_of_week']) . '</td>';
                            echo '<td>' . htmlspecialchars($timeRange) . '</td>';
                            echo '<td>' . htmlspecialchars($req['reason']) . '</td>';
                            echo '<td><span class="status-badge ' . $statusClass . '">' . htmlspecialchars($req['status']) . '</span>';
                            if ($req['status'] === 'Rejected' && $req['admin_remarks']) {
                                echo '<br><small style="color:#6b7280;">' . htmlspecialchars($req['admin_remarks']) . '</small>';
                            }
                            echo '</td>';

                            echo '<td class="actions">';
                            if ($req['status'] === 'Pending') {
                                echo '<a href="pages/actions/leave_request_action.php?action=approve&id=' . $req['id'] . '" class="btn-edit" onclick="return confirm(\'Approve this leave request?\')">Approve</a>';
                                echo '<button class="btn-delete" onclick="openRejectModal(' . $req['id'] . ')">Reject</button>';
                            } else {
                                echo '<span style="color:#9ca3af; font-size:0.85rem;">Done</span>';
                            }
                            echo '</td>';
                            echo '</tr>';
                        }
                    }
                } catch (Exception $e) {
                    echo '<tr><td colspan="8" style="color:#ef4444; text-align:center; padding:2rem;">Error loading requests: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</section>

<!-- Reject Modal -->
<div id="rejectModalOverlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:white; border-radius:0.75rem; padding:2rem; max-width:420px; width:90%; box-shadow:0 25px 50px rgba(0,0,0,0.3);">
        <h3 style="margin-bottom:1rem; color:#111827;">Reject Leave Request</h3>
        <form method="POST" action="pages/actions/leave_request_action.php">
            <input type="hidden" name="action" value="reject">
            <input type="hidden" name="id" id="rejectId">
            <div class="form-group">
                <label for="admin_remarks">Reason for Rejection (optional)</label>
                <textarea id="admin_remarks" name="admin_remarks" rows="3" style="width:100%; padding:0.5rem; border:1px solid #d1d5db; border-radius:0.5rem; font-family:inherit;"></textarea>
            </div>
            <div style="display:flex; gap:0.75rem; justify-content:flex-end; margin-top:1.25rem;">
                <button type="button" class="btn-secondary" onclick="closeRejectModal()">Cancel</button>
                <button type="submit" class="btn-delete" style="border:none; padding:0.5rem 1.25rem; cursor:pointer;">Confirm Reject</button>
            </div>
        </form>
    </div>
</div>

<script>
function openRejectModal(id) {
    document.getElementById('rejectId').value = id;
    const overlay = document.getElementById('rejectModalOverlay');
    overlay.style.display = 'flex';
}
function closeRejectModal() {
    document.getElementById('rejectModalOverlay').style.display = 'none';
}
</script>
