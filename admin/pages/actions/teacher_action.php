<?php
require_once '../../../includes/db-config.php';

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

switch ($action) {
    case 'add':
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare("INSERT INTO user (name, email, username, password, role) VALUES (:name, :email, :username, :password, 'Teacher')");
            $stmt->execute([
                ':name' => $name, 
                ':email' => $email, 
                ':username' => $email, // Using email as username for now
                ':password' => $password
            ]);
            header('Location: ../../index.php?page=teachers&msg=added');
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                header('Location: ../../index.php?page=teachers&msg=duplicate');
            } else {
                header('Location: ../../index.php?page=teachers&msg=error');
            }
        }
        break;

    case 'edit':
        $id = $_POST['id'];
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        try {
            $stmt = $pdo->prepare("UPDATE user SET name = :name, email = :email, username = :email WHERE id = :id");
            $stmt->execute([':name' => $name, ':email' => $email, ':id' => $id]);
            header('Location: ../../index.php?page=teachers&msg=updated');
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                header('Location: ../../index.php?page=teachers&msg=duplicate');
            } else {
                header('Location: ../../index.php?page=teachers&msg=error');
            }
        }
        break;

    case 'delete':
        $id = $_GET['id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM user WHERE id = :id AND role = 'teacher'");
            $stmt->execute([':id' => $id]);
            header('Location: ../../index.php?page=teachers&msg=deleted');
        } catch (PDOException $e) {
            header('Location: ../../index.php?page=teachers&msg=error');
        }
        break;

    default:
        header('Location: ../../index.php?page=teachers');
        break;
}
exit;
?>
