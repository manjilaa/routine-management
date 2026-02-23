<?php
/**
 * Routine Fetcher
 * Connects to the database and fetches all routine entries.
 * 
 * Table: routine
 * Schema: id, subject_code, teacher_id, batch_id, room_id, time_slot_id, created_at, updated_by
 */

// Include database connection
// Assuming this file is inside the 'routine' folder, we go up one level to 'includes'
require_once dirname(__DIR__) . '/includes/db-config.php';

// Set header to JSON for API-like response
header('Content-Type: application/json');

try {
    /**
     * Option 1: Basic Fetch
     * This fetches exactly what is in the routine table as specified in the schema.
     */
    $sql = "SELECT id, subject_code, teacher_id, batch_id, room_id, time_slot_id, created_at, updated_by FROM routine";
    
    // We can also join with related tables to get readable names if needed
    // Example Join Query (Uncomment if related tables have 'name' columns):
    /*
    $sql = "SELECT 
                r.*, 
                s.name as subject_name, 
                u.name as teacher_name, 
                b.batch_name, 
                rm.room_number as room_name, 
                ts.day_of_week as day, ts.start_time, ts.end_time 
            FROM routine r
            LEFT JOIN subject s ON r.subject_code = s.subject_code
            LEFT JOIN user u ON r.teacher_id = u.id
            LEFT JOIN batch b ON r.batch_id = b.id
            LEFT JOIN room rm ON r.room_id = rm.id
            LEFT JOIN time_slot ts ON r.time_slot_id = ts.id";
    */

    $stmt = $pdo->query($sql);
    $routines = $stmt->fetchAll();

    // Return the results as JSON
    echo json_encode([
        "status" => "success",
        "data" => $routines
    ]);

} catch (PDOException $e) {
    // Handle database errors
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Database error: " . $e->getMessage()
    ]);
}
?>
