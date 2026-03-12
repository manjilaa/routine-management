<?php
session_start();
require_once '../../includes/db-config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $teacher_id = $_SESSION['user_id'];
    $batch_id = $_POST['batch_id'];
    $day_of_week = $_POST['day_of_week'];
    $subject_code = $_POST['subject_code'];
    $room_id = $_POST['room_id'];
    $time_slot_id = $_POST['time_slot_id'];
    $reason = trim($_POST['reason']);
    
    try {
        /* 
        // Server-side conflict check (including day) - Disabled to allow overlapping requests
        $check = $pdo->prepare("SELECT COUNT(*) FROM routine WHERE day_of_week = ? AND time_slot_id = ? AND (room_id = ? OR batch_id = ?)");
        $check->execute([$day_of_week, $time_slot_id, $room_id, $batch_id]);
        if ($check->fetchColumn() > 0) {
            // header("Location: ../index.php?msg=overlap_conflict");
            // exit();
        }
        */

        $stmt = $pdo->prepare("INSERT INTO teacher_room_request (teacher_id, batch_id, day_of_week, subject_code, room_id, time_slot_id, reason, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')");
        $stmt->execute([$teacher_id, $batch_id, $day_of_week, $subject_code, $room_id, $time_slot_id, $reason]);
        
        header("Location: ../index.php?msg=booked");
        exit();
    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
            header("Location: ../index.php?msg=duplicate");
        } else {
            header("Location: ../index.php?msg=error");
        }
        exit();
    }
} else {
    header("Location: ../index.php");
    exit();
}
