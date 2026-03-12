<?php
session_start();
require_once '../../../includes/db-config.php';

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: ../../../Admin-login/index.php");
    exit();
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'approve':
        $id = intval($_GET['id'] ?? 0);
        try {
            $stmt = $pdo->prepare("UPDATE leave_request SET status = 'Approved', admin_remarks = NULL WHERE id = ?");
            $stmt->execute([$id]);
            header("Location: ../../index.php?page=leave_requests&msg=approved");
        } catch (PDOException $e) {
            header("Location: ../../index.php?page=leave_requests&msg=error");
        }
        break;

    case 'reject':
        $id = intval($_POST['id'] ?? 0);
        $remarks = trim($_POST['admin_remarks'] ?? '');
        try {
            $stmt = $pdo->prepare("UPDATE leave_request SET status = 'Rejected', admin_remarks = ? WHERE id = ?");
            $stmt->execute([$remarks ?: null, $id]);
            header("Location: ../../index.php?page=leave_requests&msg=rejected");
        } catch (PDOException $e) {
            header("Location: ../../index.php?page=leave_requests&msg=error");
        }
        break;

    default:
        header("Location: ../../index.php?page=leave_requests");
        break;
}
exit();
