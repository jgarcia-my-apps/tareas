<?php
session_start();
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $task_id = $data['id'];
    $is_new = $data['is_new'];

    // Actualizar el campo is_new en la base de datos
    $sql_update = "UPDATE tasks SET is_new = ? WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("ii", $is_new, $task_id);
    
    if ($stmt_update->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $stmt_update->error]);
    }
}
?>
