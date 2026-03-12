<!-- Classroom Management Section -->
<section class="content-section active">
    <div class="section-header">
        <h2>Classroom Management</h2>
        <button class="btn-primary" id="addRoomBtn" onclick="openAddRoomModal()">
            <i data-lucide="plus"></i>
            Add Classroom
        </button>
    </div>

    <div class="card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Room Number</th>
                    <th>Capacity</th>
                    <th>Facilities</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="roomsTableBody">
                <?php
                try {
                    $stmt = $pdo->query("SELECT * FROM room ORDER BY room_number ASC");
                    $rooms = $stmt->fetchAll();
                    
                    if (count($rooms) > 0) {
                        foreach ($rooms as $room) {
                            $facilities = [];
                            if ($room['smart_board']) $facilities[] = 'Smart Board';
                            if ($room['white_board']) $facilities[] = 'White Board';
                            if ($room['ac']) $facilities[] = 'AC';
                            if ($room['fan']) $facilities[] = 'Fan';
                            if ($room['projector']) $facilities[] = 'Projector';
                            
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($room['id']) . '</td>';
                            echo '<td>' . htmlspecialchars($room['room_number']) . '</td>';
                            echo '<td>' . htmlspecialchars($room['capacity'] ?? '-') . '</td>';
                            echo '<td>' . (empty($facilities) ? 'None' : implode(', ', $facilities)) . '</td>';
                            echo '<td class="actions">';
                            echo '<button class="btn-edit" onclick="editRoom(' . 
                                 $room['id'] . ', ' . 
                                 '\'' . htmlspecialchars($room['room_number'], ENT_QUOTES) . '\', ' . 
                                 ($room['capacity'] ?? 0) . ', ' . 
                                 $room['smart_board'] . ', ' . 
                                 $room['white_board'] . ', ' . 
                                 $room['ac'] . ', ' . 
                                 $room['fan'] . ', ' . 
                                 $room['projector'] . 
                                 ')">Edit</button>';
                            echo '<button class="btn-delete" onclick="deleteRoom(' . $room['id'] . ')">Delete</button>';
                            echo '</td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="5" style="text-align:center; padding: 2rem; color: #9ca3af;">No classrooms found. Click "Add Classroom" to create one.</td></tr>';
                    }
                } catch (Exception $e) {
                    echo '<tr><td colspan="5" style="text-align:center; padding: 2rem; color: #ef4444;">Error loading classrooms: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</section>

<script>
function openAddRoomModal() {
    document.getElementById('modalTitle').textContent = 'Add New Classroom';
    document.getElementById('modalBody').innerHTML = `
        <form id="addRoomForm" method="POST" action="pages/actions/room_action.php">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label for="roomNumber">Room Number</label>
                <input type="text" id="roomNumber" name="room_number" required placeholder="e.g. 101, Lab A">
            </div>
            <div class="form-group">
                <label for="capacity">Capacity</label>
                <input type="number" id="capacity" name="capacity" placeholder="e.g. 40">
            </div>
            <div class="form-group" style="display: flex; flex-wrap: wrap; gap: 1rem; margin-top: 1rem;">
                <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: normal; cursor: pointer;">
                    <input type="checkbox" name="smart_board" value="1"> Smart Board
                </label>
                <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: normal; cursor: pointer;">
                    <input type="checkbox" name="white_board" value="1"> White Board
                </label>
                <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: normal; cursor: pointer;">
                    <input type="checkbox" name="ac" value="1"> AC
                </label>
                <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: normal; cursor: pointer;">
                    <input type="checkbox" name="fan" value="1"> Fan
                </label>
                <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: normal; cursor: pointer;">
                    <input type="checkbox" name="projector" value="1"> Projector
                </label>
            </div>
            <div class="form-actions" style="margin-top: 2rem;">
                <button type="button" class="btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn-primary">Add Classroom</button>
            </div>
        </form>
    `;
    document.getElementById('modalContainer').classList.add('active');
    lucide.createIcons();
}

function editRoom(id, number, capacity, smart, white, ac, fan, projector) {
    document.getElementById('modalTitle').textContent = 'Edit Classroom';
    document.getElementById('modalBody').innerHTML = `
        <form id="editRoomForm" method="POST" action="pages/actions/room_action.php">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" value="${id}">
            <div class="form-group">
                <label for="roomNumber">Room Number</label>
                <input type="text" id="roomNumber" name="room_number" value="${number}" required>
            </div>
            <div class="form-group">
                <label for="capacity">Capacity</label>
                <input type="number" id="capacity" name="capacity" value="${capacity}">
            </div>
            <div class="form-group" style="display: flex; flex-wrap: wrap; gap: 1rem; margin-top: 1rem;">
                <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: normal; cursor: pointer;">
                    <input type="checkbox" name="smart_board" value="1" ${smart ? 'checked' : ''}> Smart Board
                </label>
                <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: normal; cursor: pointer;">
                    <input type="checkbox" name="white_board" value="1" ${white ? 'checked' : ''}> White Board
                </label>
                <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: normal; cursor: pointer;">
                    <input type="checkbox" name="ac" value="1" ${ac ? 'checked' : ''}> AC
                </label>
                <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: normal; cursor: pointer;">
                    <input type="checkbox" name="fan" value="1" ${fan ? 'checked' : ''}> Fan
                </label>
                <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: normal; cursor: pointer;">
                    <input type="checkbox" name="projector" value="1" ${projector ? 'checked' : ''}> Projector
                </label>
            </div>
            <div class="form-actions" style="margin-top: 2rem;">
                <button type="button" class="btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn-primary">Save Changes</button>
            </div>
        </form>
    `;
    document.getElementById('modalContainer').classList.add('active');
    lucide.createIcons();
}

function deleteRoom(id) {
    showDeleteConfirm(
        'Delete Classroom?', 
        'Are you sure you want to delete this classroom? This will remove it from the available list for bookings and schedules.',
        'pages/actions/room_action.php?action=delete&id=' + id
    );
}
</script>
