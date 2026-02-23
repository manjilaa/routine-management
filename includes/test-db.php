<?php
// Test file to verify database connection
require_once 'includes/db-config.php';

try {
    echo "<h1>Database Connection Test</h1>";
    echo "<p style='color: green;'>Successfully connected to the database: <strong>$db_name</strong></p>";
    
    // Check if the tables exist
    $tables = ['batch', 'room', 'routine', 'student', 'student_subject', 'subject', 'teacher_room_request', 'time_slot', 'user'];
    echo "<h2>Table Status:</h2><ul>";
    
    foreach ($tables as $table) {
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        $exists = $stmt->rowCount() > 0;
        
        $status = $exists ? "<span style='color: green;'>Found</span>" : "<span style='color: red;'>Not Found (Check if CRM database and tables are created)</span>";
        echo "<li>Table <strong>$table</strong>: $status</li>";
    }
    echo "</ul>";

} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
