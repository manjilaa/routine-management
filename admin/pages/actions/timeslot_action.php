<?php
require_once '../../../includes/db-config.php';

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

switch ($action) {
    case 'add':
        $day_of_week = $_POST['day_of_week'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];
        
        try {
            $stmt = $pdo->prepare("INSERT INTO time_slot (start_time, end_time) VALUES (:start_time, :end_time)");
            $stmt->execute([
                ':start_time' => $start_time,
                ':end_time' => $end_time
            ]);
            header('Location: ../../index.php?page=timeslots&msg=added');
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                header('Location: ../../index.php?page=timeslots&msg=duplicate');
            } else {
                header('Location: ../../index.php?page=timeslots&msg=error');
            }
        }
        break;

    case 'edit':
        $id = $_POST['id'];
        $day_of_week = $_POST['day_of_week'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];
        
        try {
            $stmt = $pdo->prepare("UPDATE time_slot SET start_time = :start_time, end_time = :end_time WHERE id = :id");
            $stmt->execute([
                ':start_time' => $start_time,
                ':end_time' => $end_time,
                ':id' => $id
            ]);
            header('Location: ../../index.php?page=timeslots&msg=updated');
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                header('Location: ../../index.php?page=timeslots&msg=duplicate');
            } else {
                header('Location: ../../index.php?page=timeslots&msg=error');
            }
        }
        break;

    case 'delete':
        $id = $_GET['id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM time_slot WHERE id = :id");
            $stmt->execute([':id' => $id]);
            header('Location: ../../index.php?page=timeslots&msg=deleted');
        } catch (PDOException $e) {
            header('Location: ../../index.php?page=timeslots&msg=error');
        }
        break;

    default:
        header('Location: ../../index.php?page=timeslots');
        break;
}
exit;
?>
