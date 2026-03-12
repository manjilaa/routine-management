<?php
require_once '../../../includes/db-config.php';

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

switch ($action) {
    case 'add':
        $room_number = trim($_POST['room_number']);
        $capacity = !empty($_POST['capacity']) ? $_POST['capacity'] : NULL;
        $smart_board = isset($_POST['smart_board']) ? 1 : 0;
        $white_board = isset($_POST['white_board']) ? 1 : 0;
        $ac = isset($_POST['ac']) ? 1 : 0;
        $fan = isset($_POST['fan']) ? 1 : 0;
        $projector = isset($_POST['projector']) ? 1 : 0;
        
        try {
            $stmt = $pdo->prepare("INSERT INTO room (room_number, capacity, smart_board, white_board, ac, fan, projector) VALUES (:room_number, :capacity, :smart_board, :white_board, :ac, :fan, :projector)");
            $stmt->execute([
                ':room_number' => $room_number,
                ':capacity' => $capacity,
                ':smart_board' => $smart_board,
                ':white_board' => $white_board,
                ':ac' => $ac,
                ':fan' => $fan,
                ':projector' => $projector
            ]);
            header('Location: ../../index.php?page=rooms&msg=added');
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                header('Location: ../../index.php?page=rooms&msg=duplicate');
            } else {
                header('Location: ../../index.php?page=rooms&msg=error');
            }
        }
        break;

    case 'edit':
        $id = $_POST['id'];
        $room_number = trim($_POST['room_number']);
        $capacity = !empty($_POST['capacity']) ? $_POST['capacity'] : NULL;
        $smart_board = isset($_POST['smart_board']) ? 1 : 0;
        $white_board = isset($_POST['white_board']) ? 1 : 0;
        $ac = isset($_POST['ac']) ? 1 : 0;
        $fan = isset($_POST['fan']) ? 1 : 0;
        $projector = isset($_POST['projector']) ? 1 : 0;
        
        try {
            $stmt = $pdo->prepare("UPDATE room SET room_number = :room_number, capacity = :capacity, smart_board = :smart_board, white_board = :white_board, ac = :ac, fan = :fan, projector = :projector WHERE id = :id");
            $stmt->execute([
                ':room_number' => $room_number,
                ':capacity' => $capacity,
                ':smart_board' => $smart_board,
                ':white_board' => $white_board,
                ':ac' => $ac,
                ':fan' => $fan,
                ':projector' => $projector,
                ':id' => $id
            ]);
            header('Location: ../../index.php?page=rooms&msg=updated');
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                header('Location: ../../index.php?page=rooms&msg=duplicate');
            } else {
                header('Location: ../../index.php?page=rooms&msg=error');
            }
        }
        break;

    case 'delete':
        $id = $_GET['id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM room WHERE id = :id");
            $stmt->execute([':id' => $id]);
            header('Location: ../../index.php?page=rooms&msg=deleted');
        } catch (PDOException $e) {
            header('Location: ../../index.php?page=rooms&msg=error');
        }
        break;

    default:
        header('Location: ../../index.php?page=rooms');
        break;
}
exit;
?>
