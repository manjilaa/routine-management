<?php
require_once '../../../includes/db-config.php';

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

switch ($action) {
    case 'update':
        $id = isset($_GET['id']) ? $_GET['id'] : $_POST['id'];
        $status = isset($_GET['status']) ? $_GET['status'] : $_POST['status'];
        $reject_reason = isset($_POST['reject_reason']) ? $_POST['reject_reason'] : null;
        
        $allowed = ['Approved', 'Rejected', 'Pending'];
        if (in_array($status, $allowed)) {
            try {
                if ($status === 'Rejected') {
                    $stmt = $pdo->prepare("UPDATE teacher_room_request SET status = :status, reject_reason = :reject_reason WHERE id = :id");
                    $stmt->execute([':status' => $status, ':reject_reason' => $reject_reason, ':id' => $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE teacher_room_request SET status = :status WHERE id = :id");
                    $stmt->execute([':status' => $status, ':id' => $id]);
                }
                header('Location: ../../index.php?page=bookings&msg=updated');
            } catch (PDOException $e) {
                header('Location: ../../index.php?page=bookings&msg=error');
            }
        } else {
            header('Location: ../../index.php?page=bookings');
        }
        break;

    case 'delete':
        $id = $_GET['id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM teacher_room_request WHERE id = :id");
            $stmt->execute([':id' => $id]);
            header('Location: ../../index.php?page=bookings&msg=deleted');
        } catch (PDOException $e) {
            header('Location: ../../index.php?page=bookings&msg=error');
        }
        break;

    default:
        header('Location: ../../index.php?page=bookings');
        break;
}
exit;
?>

