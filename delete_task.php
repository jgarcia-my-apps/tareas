<?php
session_start();
include 'db_connection.php';

// Verificar si el usuario está autenticado y tiene el rol adecuado
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Verificar si se ha proporcionado un ID de tarea
if (isset($_GET['id'])) {
    $task_id = $_GET['id'];

    // Preparar la consulta para eliminar la tarea
    $sql_delete = "DELETE FROM tasks WHERE id = ?";
    $stmt = $conn->prepare($sql_delete);
    $stmt->bind_param("i", $task_id);

    // Ejecutar la consulta y manejar el resultado
    if ($stmt->execute()) {
        $message = 'Tarea eliminada exitosamente.';
    } else {
        $message = 'Error al eliminar la tarea: ' . $stmt->error;
    }

    // Redirigir a la página de administración de tareas con un mensaje
    header("Location: manage_tasks.php?message=" . urlencode($message));
    exit();
} else {
    // Si no se proporciona ID, redirigir con un mensaje de error
    header("Location: manage_tasks.php?message=" . urlencode('ID de tarea no proporcionado.'));
    exit();
}
