<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - UniRoutine</title>
    <link rel="stylesheet" href="../assets/css/teacher-style.css">
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
                    <p id="userName">Dr. John Smith</p>
                    <p class="user-role">Teacher - Computer Science</p>
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
        
        <!-- My Schedule Card -->
        <div class="card">
            <div class="card-header teacher-card-header">
                <h2>My Schedule</h2>
                <i data-lucide="calendar"></i>
            </div>
            <?php include '../routine/schedule_table.php'; ?>
        </div>

        <!-- My Courses Card -->
        <div class="card">
            <div class="card-header course-card-header">
                <h2>My Courses</h2>
                <i data-lucide="book-open"></i>
            </div>
            <?php include '../subjects/subject_cards.php'; ?>
        </div>

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
                
                <!-- Pending Request -->
                <div class="request-card">
                    <div class="request-header">
                        <div>
                            <h3>Room 303</h3>
                            <p>Monday, 09:30 - 11:30</p>
                        </div>
                        <span class="status-badge status-pending">Pending</span>
                    </div>
                    <p class="request-remarks">Extra class for Database Systems - Quiz preparation session</p>
                    <p class="request-date">Requested on: Jan 15, 2024</p>
                </div>

                <!-- Approved Request -->
                <div class="request-card">
                    <div class="request-header">
                        <div>
                            <h3>Lab 1</h3>
                            <p>Wednesday, 11:30 - 12:30</p>
                        </div>
                        <span class="status-badge status-approved">Approved</span>
                    </div>
                    <p class="request-remarks">Web Development practical session - React.js workshop</p>
                    <p class="request-date">Approved on: Jan 12, 2024</p>
                </div>

                <!-- Rejected Request -->
                <div class="request-card">
                    <div class="request-header">
                        <div>
                            <h3>Room 101</h3>
                            <p>Thursday, 12:30 - 13:30</p>
                        </div>
                        <span class="status-badge status-rejected">Rejected</span>
                    </div>
                    <p class="request-remarks">Additional lecture for Data Structures</p>
                    <p class="request-date">Rejected on: Jan 10, 2024</p>
                    <p style="color: #dc2626; font-size: 0.875rem; margin-top: 0.5rem;">
                        <strong>Reason:</strong> Room already booked for another class
                    </p>
                </div>

                <!-- Pending Request 2 -->
                <div class="request-card">
                    <div class="request-header">
                        <div>
                            <h3>Room 205</h3>
                            <p>Tuesday, 06:30 - 07:30</p>
                        </div>
                        <span class="status-badge status-pending">Pending</span>
                    </div>
                    <p class="request-remarks">Make-up class for CS302 - Missed due to university event</p>
                    <p class="request-date">Requested on: Jan 16, 2024</p>
                </div>

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
            <form id="bookingForm" onsubmit="submitBookingRequest(event)">
                <div>
                    <label for="classroom">Classroom</label>
                    <select id="classroom" required>
                        <option value="">Select Classroom</option>
                        <?php
                            try {
                                $stmt = $pdo->query("SELECT id, room_number FROM room ORDER BY room_number ASC");
                                while($row = $stmt->fetch()) {
                                    echo "<option value='".htmlspecialchars($row['id'])."'>".htmlspecialchars($row['room_number'])."</option>";
                                }
                            } catch (PDOException $e) {
                                echo "<option value=''>Error loading rooms</option>";
                            }
                        ?>
                    </select>
                </div>

                <div class="form-row">
                    <div>
                        <label for="day">Day</label>
                        <select id="day" required>
                            <option value="">Select Day</option>
                            <option value="Sunday">Sunday</option>
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednesday">Wednesday</option>
                            <option value="Thursday">Thursday</option>
                            <option value="Friday">Friday</option>
                        </select>
                    </div>
                    <div>
                        <label for="timeSlot">Time Slot</label>
                        <select id="timeSlot" required>
                            <option value="">Select Time</option>
                            <option value="06:30-07:30">06:30 - 07:30</option>
                            <option value="07:30-08:30">07:30 - 08:30</option>
                            <option value="09:30-11:30">09:30 - 11:30</option>
                            <option value="11:30-12:30">11:30 - 12:30</option>
                            <option value="12:30-13:30">12:30 - 13:30</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="course">Course</label>
                    <select id="course" required>
                        <option value="">Select Course</option>
                        <?php
                            try {
                                $stmt = $pdo->query("SELECT subject_code, name FROM subject ORDER BY name ASC");
                                while($row = $stmt->fetch()) {
                                    echo "<option value='".htmlspecialchars($row['subject_code'])."'>".htmlspecialchars($row['subject_code'])." - ".htmlspecialchars($row['name'])."</option>";
                                }
                            } catch (PDOException $e) {
                                echo "<option value=''>Error loading subjects</option>";
                            }
                        ?>
                    </select>
                </div>

                <div>
                    <label for="purpose">Purpose/Remarks</label>
                    <textarea id="purpose" required rows="4" placeholder="Brief description of the booking purpose (e.g., Extra class, Lab session, Quiz, Workshop, etc.)"></textarea>
                    <p class="help-text">Please provide a clear reason for booking this classroom</p>
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

        // Logout function - redirects to login page
        function logout() {
            window.location.href = '../login/logout.php';
        }

        function openBookingModal() {
            document.getElementById('bookingModal').style.display = 'flex';
            lucide.createIcons();
        }

        function closeBookingModal() {
            document.getElementById('bookingModal').style.display = 'none';
        }

        function submitBookingRequest(event) {
            event.preventDefault();
            alert('Booking request functionality will be implemented');
            closeBookingModal();
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