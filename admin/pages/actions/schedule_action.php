<?php
require_once '../../../includes/db-config.php';

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

switch ($action) {
    case 'add':
        $batch_id = $_POST['batch_id'];
        $day_of_week = $_POST['day_of_week'];
        $time_slot_id = $_POST['time_slot_id'];
        $subject_code = $_POST['subject_code'];
        $teacher_id = $_POST['teacher_id'];
        $room_id = $_POST['room_id'];
        
        try {
            $stmt = $pdo->prepare("INSERT INTO routine (batch_id, day_of_week, time_slot_id, subject_code, teacher_id, room_id) VALUES (:batch_id, :day_of_week, :time_slot_id, :subject_code, :teacher_id, :room_id)");
            $stmt->execute([
                ':batch_id' => $batch_id,
                ':day_of_week' => $day_of_week,
                ':time_slot_id' => $time_slot_id,
                ':subject_code' => $subject_code,
                ':teacher_id' => $teacher_id,
                ':room_id' => $room_id
            ]);
            header('Location: ../../index.php?page=schedules&msg=added');
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                header('Location: ../../index.php?page=schedules&msg=duplicate');
            } else {
                header('Location: ../../index.php?page=schedules&msg=error');
            }
        }
        break;

    case 'delete':
        $id = $_GET['id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM routine WHERE id = :id");
            $stmt->execute([':id' => $id]);
            header('Location: ../../index.php?page=schedules&msg=deleted');
        } catch (PDOException $e) {
            header('Location: ../../index.php?page=schedules&msg=error');
        }
        break;

    default:
        header('Location: ../../index.php?page=schedules');
        break;
}
exit;
?>

