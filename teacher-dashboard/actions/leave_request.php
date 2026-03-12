<?php
session_start();
require_once '../../includes/db-config.php';

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'teacher') {
    header("Location: ../../teacher-login/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php");
    exit();
}

$teacher_id  = $_SESSION['user_id'];
$routine_id  = intval($_POST['routine_id'] ?? 0);
$day_of_week = trim($_POST['day_of_week'] ?? '');
$reason      = trim($_POST['reason'] ?? '');

if (!$routine_id || !$day_of_week || !$reason) {
    header("Location: ../index.php?msg=leave_error");
    exit();
}

try {
    // Check if a pending/approved leave already exists for this slot
    $check = $pdo->prepare("SELECT id FROM leave_request WHERE teacher_id = ? AND routine_id = ? AND day_of_week = ? AND status != 'Rejected'");
    $check->execute([$teacher_id, $routine_id, $day_of_week]);
    if ($check->fetch()) {
        header("Location: ../index.php?msg=leave_duplicate");
        exit();
    }

    $stmt = $pdo->prepare("INSERT INTO leave_request (teacher_id, routine_id, day_of_week, reason) VALUES (?, ?, ?, ?)");
    $stmt->execute([$teacher_id, $routine_id, $day_of_week, $reason]);
    header("Location: ../index.php?msg=leave_submitted");
} catch (PDOException $e) {
    header("Location: ../index.php?msg=leave_error");
}
exit();
