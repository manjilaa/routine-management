<?php
require_once '../../../includes/db-config.php';

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

switch ($action) {
    case 'add':
        $subject_code = trim($_POST['subject_code']);
        $name = trim($_POST['name']);
        $department = trim($_POST['department']);
        try {
            $stmt = $pdo->prepare("INSERT INTO subject (subject_code, name, department) VALUES (:subject_code, :name, :department)");
            $stmt->execute([':subject_code' => $subject_code, ':name' => $name, ':department' => $department]);
            header('Location: ../../index.php?page=courses&msg=added');
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                header('Location: ../../index.php?page=courses&msg=duplicate');
            } else {
                header('Location: ../../index.php?page=courses&msg=error');
            }
        }
        break;

    case 'edit':
        $old_code = $_POST['old_code'];
        $subject_code = trim($_POST['subject_code']);
        $name = trim($_POST['name']);
        $department = trim($_POST['department']);
        try {
            $stmt = $pdo->prepare("UPDATE subject SET subject_code = :subject_code, name = :name, department = :department WHERE subject_code = :old_code");
            $stmt->execute([':subject_code' => $subject_code, ':name' => $name, ':department' => $department, ':old_code' => $old_code]);
            header('Location: ../../index.php?page=courses&msg=updated');
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                header('Location: ../../index.php?page=courses&msg=duplicate');
            } else {
                header('Location: ../../index.php?page=courses&msg=error');
            }
        }
        break;

    case 'delete':
        $code = $_GET['code'];
        try {
            $stmt = $pdo->prepare("DELETE FROM subject WHERE subject_code = :code");
            $stmt->execute([':code' => $code]);
            header('Location: ../../index.php?page=courses&msg=deleted');
        } catch (PDOException $e) {
            header('Location: ../../index.php?page=courses&msg=error');
        }
        break;

    default:
        header('Location: ../../index.php?page=courses');
        break;
}
exit;
?>

