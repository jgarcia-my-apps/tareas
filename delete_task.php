<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'No tienes permiso para realizar esta acción.']);
    exit();
}

$task_id = $_POST['id'] ?? '';

if (!empty($task_id)) {
    $sql_delete = "DELETE FROM tasks WHERE id = ?";
    $stmt = $conn->prepare($sql_delete);
    $stmt->bind_param("i", $task_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Tarea eliminada exitosamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar la tarea.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ID de tarea no válido.']);
}
?>
