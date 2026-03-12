<!-- Time Slot Management Section -->
<section class="content-section active">
    <div class="section-header">
        <h2>Time Slot Management</h2>
        <button class="btn-primary" id="addTimeslotBtn" onclick="openAddTimeslotModal()">
            <i data-lucide="plus"></i>
            Add Time Slot
        </button>
    </div>

    <div class="card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="timeslotsTableBody">
                <?php
                try {
                    $stmt = $pdo->query("SELECT * FROM time_slot ORDER BY start_time ASC");
                    $timeslots = $stmt->fetchAll();
                    
                    if (count($timeslots) > 0) {
                        foreach ($timeslots as $ts) {
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($ts['id']) . '</td>';
                            echo '<td>' . date('h:i A', strtotime($ts['start_time'])) . '</td>';
                            echo '<td>' . date('h:i A', strtotime($ts['end_time'])) . '</td>';
                            echo '<td class="actions">';
                            echo '<button class="btn-edit" onclick="editTimeslot(' . $ts['id'] . ', \'' . $ts['start_time'] . '\', \'' . $ts['end_time'] . '\')">Edit</button>';
                            echo '<button class="btn-delete" onclick="deleteTimeslot(' . $ts['id'] . ')">Delete</button>';
                            echo '</td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="5" style="text-align:center; padding: 2rem; color: #9ca3af;">No time slots found. Click "Add Time Slot" to create one.</td></tr>';
                    }
                } catch (Exception $e) {
                    echo '<tr><td colspan="5" style="text-align:center; padding: 2rem; color: #ef4444;">Error loading time slots: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</section>

<script>
function openAddTimeslotModal() {
    document.getElementById('modalTitle').textContent = 'Add New Time Slot';
    document.getElementById('modalBody').innerHTML = `
        <form id="addTimeslotForm" method="POST" action="pages/actions/timeslot_action.php">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label for="startTime">Start Time</label>
                <input type="time" id="startTime" name="start_time" required>
            </div>
            <div class="form-group">
                <label for="endTime">End Time</label>
                <input type="time" id="endTime" name="end_time" required>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn-primary">Add Time Slot</button>
            </div>
        </form>
    `;
    document.getElementById('modalContainer').classList.add('active');
    lucide.createIcons();
}

function editTimeslot(id, start, end) {
    document.getElementById('modalTitle').textContent = 'Edit Time Slot';
    document.getElementById('modalBody').innerHTML = `
        <form id="editTimeslotForm" method="POST" action="pages/actions/timeslot_action.php">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" value="${id}">
            <div class="form-group">
                <label for="startTime">Start Time</label>
                <input type="time" id="startTime" name="start_time" value="${start}" required>
            </div>
            <div class="form-group">
                <label for="endTime">End Time</label>
                <input type="time" id="endTime" name="end_time" value="${end}" required>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn-primary">Save Changes</button>
            </div>
        </form>
    `;
    document.getElementById('modalContainer').classList.add('active');
    lucide.createIcons();
}

function deleteTimeslot(id) {
    showDeleteConfirm(
        'Delete Time Slot?', 
        'Are you sure you want to delete this time slot? This could affect existing schedule entries.',
        'pages/actions/timeslot_action.php?action=delete&id=' + id
    );
}
</script>
