<?php
session_start();

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: ../Admin-login/index.php");
    exit();
}

$page_title = 'Dashboard';
$current_page = 'index.php';
require_once '../includes/db-config.php';

// Determine which page to load
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$allowed_pages = ['dashboard', 'batches', 'courses', 'teachers', 'bookings', 'leave_requests', 'schedules', 'timeslots', 'rooms'];

// Set page title based on current page
$page_titles = [
    'dashboard' => 'Dashboard',
    'batches' => 'Batch Management',
    'courses' => 'Course Management',
    'teachers' => 'Teacher Management',
    'bookings'       => 'Booking Requests',
    'leave_requests' => 'Leave Requests',
    'schedules' => 'Schedule Management',
    'timeslots' => 'Time Slot Management',
    'rooms' => 'Classroom Management'
];
$page_title = isset($page_titles[$page]) ? $page_titles[$page] : 'Dashboard';
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

    <!-- Content Area -->
    <main class="content">
        <?php
        // Global Alert System
        if (isset($_GET['msg'])) {
            $msgType = 'success';
            $msgText = '';
            
            switch ($_GET['msg']) {
                case 'added': $msgText = 'Record has been successfully added.'; break;
                case 'updated': $msgText = 'Changes have been saved successfully.'; break;
                case 'deleted': $msgText = 'Record has been permanently deleted.'; break;
                case 'duplicate': $msgType = 'error'; $msgText = 'Error: A record with this unique identifier already exists.'; break;
                case 'error': $msgType = 'error'; $msgText = 'An unexpected error occurred. Please try again.'; break;
            }
            
            if ($msgText) {
                echo '<div class="alert alert-' . $msgType . '" id="globalAlert">';
                echo '<i data-lucide="' . ($msgType == 'success' ? 'check-circle' : 'alert-circle') . '"></i>';
                echo '<span>' . $msgText . '</span>';
                echo '</div>';
                echo '<script>setTimeout(() => { document.getElementById("globalAlert")?.remove(); }, 5000);</script>';
            }
        }
        
        if (in_array($page, $allowed_pages)) {
            $page_file = 'pages/' . $page . '.php';
            if (file_exists($page_file)) {
                include $page_file;
            } else {
                echo '<div class="content-section active"><div class="section-header"><h2>Page Not Found</h2></div><p>The requested page could not be found.</p></div>';
            }
        } else {
            echo '<div class="content-section active"><div class="section-header"><h2>Page Not Found</h2></div><p>The requested page does not exist.</p></div>';
        }
        ?>
    </main>
    </div> <!-- Close main-container from sidebar.php -->

    <!-- Modal Container (for forms) -->
    <div id="modalContainer" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3 id="modalTitle">Modal Title</h3>
                <button class="modal-close" id="modalClose">
                    <i data-lucide="x"></i>
                </button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Modal content will be dynamically inserted -->
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        function closeModal() {
            const modal = document.querySelector('.modal');
            modal.classList.remove('modal-confirm'); // Reset to default state
            document.getElementById('modalContainer').classList.remove('active');
        }

        function logout() {
            showActionConfirm(
                'Logout Session',
                'Are you sure you want to end your current session?',
                '../Admin-login/logout.php',
                'Confirm Logout',
                'btn-confirm-delete',
                null,
                '../assets/images/logout.png'
            );
        }

        // GUI Generic Action Confirmation
        function showActionConfirm(title, message, actionUrl, buttonText = 'Confirm', buttonClass = 'btn-primary', icon = 'check-circle', iconImage = null) {
            document.getElementById('modalTitle').textContent = 'Confirm Action';
            const modal = document.querySelector('.modal');
            modal.classList.add('modal-confirm');
            
            let iconHtml = `<i data-lucide="${icon}"></i>`;
            if (iconImage) {
                iconHtml = `<img src="${iconImage}" alt="Confirm" style="width: 2rem; height: 2rem; object-fit: contain;">`;
            }

            document.getElementById('modalBody').innerHTML = `
                <div class="confirm-icon" style="color: var(--primary-color);">
                    ${iconHtml}
                </div>
                <h3 class="confirm-title">${title}</h3>
                <p class="confirm-text">${message}</p>
                <div class="confirm-actions">
                    <button type="button" class="btn-confirm-cancel" onclick="closeModal()">Cancel</button>
                    <a href="${actionUrl}" class="${buttonClass}" style="text-decoration: none; padding: 0.75rem 1.5rem; border-radius: 0.5rem; font-weight: 600; color: white;">${buttonText}</a>
                </div>
            `;
            document.getElementById('modalContainer').classList.add('active');
            lucide.createIcons();
        }

        // GUI Delete Confirmation
        function showDeleteConfirm(title, message, deleteUrl) {
            document.getElementById('modalTitle').textContent = 'Confirm Deletion';
            const modal = document.querySelector('.modal');
            modal.classList.add('modal-confirm');
            
            document.getElementById('modalBody').innerHTML = `
                <div class="confirm-icon" style="color: #ef4444;">
                    <i data-lucide="trash-2"></i>
                </div>
                <h3 class="confirm-title">${title}</h3>
                <p class="confirm-text">${message}</p>
                <div class="confirm-actions">
                    <button type="button" class="btn-confirm-cancel" onclick="closeModal()">Cancel</button>
                    <a href="${deleteUrl}" class="btn-confirm-delete" style="text-decoration: none;">Delete Now</a>
                </div>
            `;
            document.getElementById('modalContainer').classList.add('active');
            lucide.createIcons();
        }

        document.getElementById('modalClose').addEventListener('click', closeModal);
        document.getElementById('modalContainer').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
    </script>
</body>
</html>
