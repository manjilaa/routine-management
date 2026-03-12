<!-- Batch Management Section -->
<section class="content-section active">
    <div class="section-header">
        <h2>Batch Management</h2>
        <button class="btn-primary" id="addBatchBtn" onclick="openAddBatchModal()">
            <i data-lucide="plus"></i>
            Add Batch
        </button>
    </div>

    <div class="card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Batch Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="batchesTableBody">
                <?php
                try {
                    $stmt = $pdo->query("SELECT * FROM batch ORDER BY batch_name ASC");
                    $batches = $stmt->fetchAll();
                    
                    if (count($batches) > 0) {
                        foreach ($batches as $batch) {
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($batch['id']) . '</td>';
                            echo '<td>' . htmlspecialchars($batch['batch_name']) . '</td>';
                            echo '<td class="actions">';
                            echo '<button class="btn-edit" onclick="editBatch(' . $batch['id'] . ', \'' . htmlspecialchars($batch['batch_name'], ENT_QUOTES) . '\')">Edit</button>';
                            echo '<button class="btn-delete" onclick="deleteBatch(' . $batch['id'] . ')">Delete</button>';
                            echo '</td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="3" style="text-align:center; padding: 2rem; color: #9ca3af;">No batches found. Click "Add Batch" to create one.</td></tr>';
                    }
                } catch (Exception $e) {
                    echo '<tr><td colspan="3" style="text-align:center; padding: 2rem; color: #ef4444;">Error loading batches: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</section>

<script>
function openAddBatchModal() {
    document.getElementById('modalTitle').textContent = 'Add New Batch';
    document.getElementById('modalBody').innerHTML = `
        <form id="addBatchForm" method="POST" action="pages/actions/batch_action.php">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label for="batchName">Batch Name</label>
                <input type="text" id="batchName" name="batch_name" required placeholder="e.g. BSc CSIT 2024">
            </div>
            <div class="form-actions">
                <button type="button" class="btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn-primary">Add Batch</button>
            </div>
        </form>
    `;
    document.getElementById('modalContainer').classList.add('active');
    lucide.createIcons();
}

function editBatch(id, name) {
    document.getElementById('modalTitle').textContent = 'Edit Batch';
    document.getElementById('modalBody').innerHTML = `
        <form id="editBatchForm" method="POST" action="pages/actions/batch_action.php">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" value="${id}">
            <div class="form-group">
                <label for="batchName">Batch Name</label>
                <input type="text" id="batchName" name="batch_name" value="${name}" required>
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

function deleteBatch(id) {
    showDeleteConfirm(
        'Delete Batch?', 
        'Are you sure you want to delete this batch? All records associated with this batch might be affected.',
        'pages/actions/batch_action.php?action=delete&id=' + id
    );
}
</script>
