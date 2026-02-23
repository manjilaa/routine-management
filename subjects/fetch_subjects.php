<?php
/**
 * Subject Fetcher
 * Fetches all subjects from the database.
 * 
 * Table: subject
 * Schema: subject_code, name, department, created_at, updated_by
 */

require_once dirname(__DIR__) . '/includes/db-config.php';

header('Content-Type: application/json');

try {
    $sql = "SELECT subject_code, name as subject_name, department FROM subject ORDER BY name ASC";
    $stmt = $pdo->query($sql);
    $subjects = $stmt->fetchAll();

    echo json_encode([
        "status" => "success",
        "data" => $subjects
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Database error: " . $e->getMessage()
    ]);
}
?>
