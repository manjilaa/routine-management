<?php
require_once '../../../includes/db-config.php';

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

switch ($action) {
    case 'add':
        $batch_name = trim($_POST['batch_name']);
        try {
            $stmt = $pdo->prepare("INSERT INTO batch (batch_name) VALUES (:batch_name)");
            $stmt->execute([':batch_name' => $batch_name]);
            header('Location: ../../index.php?page=batches&msg=added');
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                header('Location: ../../index.php?page=batches&msg=duplicate');
            } else {
                header('Location: ../../index.php?page=batches&msg=error');
            }
        }
        break;

    case 'edit':
        $id = $_POST['id'];
        $batch_name = trim($_POST['batch_name']);
        try {
            $stmt = $pdo->prepare("UPDATE batch SET batch_name = :batch_name WHERE id = :id");
            $stmt->execute([':batch_name' => $batch_name, ':id' => $id]);
            header('Location: ../../index.php?page=batches&msg=updated');
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                header('Location: ../../index.php?page=batches&msg=duplicate');
            } else {
                header('Location: ../../index.php?page=batches&msg=error');
            }
        }
        break;

    case 'delete':
        $id = $_GET['id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM batch WHERE id = :id");
            $stmt->execute([':id' => $id]);
            header('Location: ../../index.php?page=batches&msg=deleted');
        } catch (PDOException $e) {
            header('Location: ../../index.php?page=batches&msg=error');
        }
        break;

    default:
        header('Location: ../../index.php?page=batches');
        break;
}
exit;
?>
