<!-- Course Management Section -->
<section class="content-section active">
    <div class="section-header">
        <h2>Course Management</h2>
        <button class="btn-primary" id="addCourseBtn" onclick="openAddCourseModal()">
            <i data-lucide="plus"></i>
            Add Course
        </button>
    </div>

    <div class="card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="coursesTableBody">
                <?php
                try {
                    $stmt = $pdo->query("SELECT * FROM subject ORDER BY name ASC");
                    $subjects = $stmt->fetchAll();
                    
                    if (count($subjects) > 0) {
                        foreach ($subjects as $subject) {
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($subject['subject_code']) . '</td>';
                            echo '<td>' . htmlspecialchars($subject['name']) . '</td>';
                            echo '<td>' . htmlspecialchars($subject['department'] ?? '-') . '</td>';
                            echo '<td class="actions">';
                            echo '<button class="btn-edit" onclick="editCourse(\'' . htmlspecialchars($subject['subject_code'], ENT_QUOTES) . '\', \'' . htmlspecialchars($subject['name'], ENT_QUOTES) . '\', \'' . htmlspecialchars($subject['department'] ?? '', ENT_QUOTES) . '\')">Edit</button>';
                            echo '<button class="btn-delete" onclick="deleteCourse(\'' . htmlspecialchars($subject['subject_code'], ENT_QUOTES) . '\')">Delete</button>';
                            echo '</td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="4" style="text-align:center; padding: 2rem; color: #9ca3af;">No courses found. Click "Add Course" to create one.</td></tr>';
                    }
                } catch (Exception $e) {
                    echo '<tr><td colspan="4" style="text-align:center; padding: 2rem; color: #ef4444;">Error loading courses: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</section>

<script>
function openAddCourseModal() {
    document.getElementById('modalTitle').textContent = 'Add New Course';
    document.getElementById('modalBody').innerHTML = `
        <form id="addCourseForm" method="POST" action="pages/actions/course_action.php">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label for="subjectCode">Subject Code</label>
                <input type="text" id="subjectCode" name="subject_code" required placeholder="e.g. CS201">
            </div>
            <div class="form-group">
                <label for="subjectName">Subject Name</label>
                <input type="text" id="subjectName" name="name" required placeholder="e.g. Data Structures">
            </div>
            <div class="form-group">
                <label for="department">Department</label>
                <input type="text" id="department" name="department" placeholder="e.g. Computer Science">
            </div>
            <div class="form-actions">
                <button type="button" class="btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn-primary">Add Course</button>
            </div>
        </form>
    `;
    document.getElementById('modalContainer').classList.add('active');
    lucide.createIcons();
}

function editCourse(code, name, dept) {
    document.getElementById('modalTitle').textContent = 'Edit Course';
    document.getElementById('modalBody').innerHTML = `
        <form id="editCourseForm" method="POST" action="pages/actions/course_action.php">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="old_code" value="${code}">
            <div class="form-group">
                <label for="subjectCode">Subject Code</label>
                <input type="text" id="subjectCode" name="subject_code" value="${code}" required>
            </div>
            <div class="form-group">
                <label for="subjectName">Subject Name</label>
                <input type="text" id="subjectName" name="name" value="${name}" required>
            </div>
            <div class="form-group">
                <label for="department">Department</label>
                <input type="text" id="department" name="department" value="${dept}">
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

function deleteCourse(code) {
    showDeleteConfirm(
        'Delete Course?', 
        'Are you sure you want to delete this course? This will permanently remove the subject and its schedule entries.',
        'pages/actions/course_action.php?action=delete&code=' + encodeURIComponent(code)
    );
}
</script>

