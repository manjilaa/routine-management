<?php
/**
 * Database Configuration for Routine Management System (RMS)
 * 
 * Database Name: rms
 * Host: localhost (XAMPP default)
 * Tables: batch, room, routine, student, student_subject, subject, teacher_room_request, time_slot, user
 */

// Database credentials
$host = 'localhost';
$db_name = 'rms';
$db_user = 'root'; // Default XAMPP username
$db_pass = '';     // Default XAMPP password (empty)

try {
    // Initialize PDO connection
    $dsn = "mysql:host=$host;dbname=$db_name;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
    
    // Connection successful (optional: remove this comment in production)
    // echo "Connected successfully"; 

} catch (PDOException $e) {
    // Handle connection errors
    error_log("Database Connection Error: " . $e->getMessage());
    die("Error: Could not connect to the database. " . $e->getMessage());
}
?>
