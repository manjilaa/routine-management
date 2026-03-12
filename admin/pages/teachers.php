<!-- Teacher Management Section -->
<section class="content-section active">
    <div class="section-header">
        <h2>Teacher Management</h2>
        <button class="btn-primary" id="addTeacherBtn" onclick="openAddTeacherModal()">
            <i data-lucide="plus"></i>
            Add Teacher
        </button>
    </div>

    <div class="card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="teachersTableBody">
                <?php
                try {
                    $stmt = $pdo->query("SELECT * FROM user WHERE LOWER(role) = 'teacher' ORDER BY name ASC");
                    $teachers = $stmt->fetchAll();
                    
                    if (count($teachers) > 0) {
                        $counter = 1;
                        foreach ($teachers as $teacher) {
                            echo '<tr>';
                            echo '<td>' . $counter++ . '</td>';
                            echo '<td>' . htmlspecialchars($teacher['name']) . '</td>';
                            echo '<td>' . htmlspecialchars($teacher['email']) . '</td>';
                            echo '<td><span class="status-badge status-approved">' . htmlspecialchars($teacher['role']) . '</span></td>';
                            echo '<td class="actions">';
                            echo '<button class="btn-edit" onclick="editTeacher(' . $teacher['id'] . ', \'' . htmlspecialchars($teacher['name'], ENT_QUOTES) . '\', \'' . htmlspecialchars($teacher['email'], ENT_QUOTES) . '\')">Edit</button>';
                            echo '<button class="btn-delete" onclick="deleteTeacher(' . $teacher['id'] . ')">Delete</button>';
                            echo '</td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="5" style="text-align:center; padding: 2rem; color: #9ca3af;">No teachers found. Click "Add Teacher" to create one.</td></tr>';
                    }
                } catch (Exception $e) {
                    echo '<tr><td colspan="5" style="text-align:center; padding: 2rem; color: #ef4444;">Error loading teachers: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</section>

<script>
function openAddTeacherModal() {
    document.getElementById('modalTitle').textContent = 'Add New Teacher';
    document.getElementById('modalBody').innerHTML = `
        <form id="addTeacherForm" method="POST" action="pages/actions/teacher_action.php">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label for="teacherName">Full Name</label>
                <input type="text" id="teacherName" name="name" required placeholder="e.g. Dr. John Doe">
            </div>
            <div class="form-group">
                <label for="teacherEmail">Email Address</label>
                <input type="email" id="teacherEmail" name="email" required placeholder="e.g. johndoe@ku.edu.np">
            </div>
            <div class="form-group">
                <label for="teacherPassword">Password</label>
                <input type="password" id="teacherPassword" name="password" required placeholder="Enter password">
            </div>
            <div class="form-actions">
                <button type="button" class="btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn-primary">Add Teacher</button>
            </div>
        </form>
    `;
    document.getElementById('modalContainer').classList.add('active');
    lucide.createIcons();
}

function editTeacher(id, name, email) {
    document.getElementById('modalTitle').textContent = 'Edit Teacher';
    document.getElementById('modalBody').innerHTML = `
        <form id="editTeacherForm" method="POST" action="pages/actions/teacher_action.php">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" value="${id}">
            <div class="form-group">
                <label for="teacherName">Full Name</label>
                <input type="text" id="teacherName" name="name" value="${name}" required>
            </div>
            <div class="form-group">
                <label for="teacherEmail">Email Address</label>
                <input type="email" id="teacherEmail" name="email" value="${email}" required>
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

function deleteTeacher(id) {
    showDeleteConfirm(
        'Delete Teacher?', 
        'Are you sure you want to delete this teacher? They will no longer be able to log in or manage requests.',
        'pages/actions/teacher_action.php?action=delete&id=' + id
    );
}
</script>
