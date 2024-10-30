<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_id = $_POST['id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $status = $_POST['status'];

    // Usar el ID del administrador
    $admin_id = 10; // Cambia esto por el ID correcto del administrador

    // Actualizar la tarea en la base de datos
    $sql = "UPDATE tasks SET task_name = ?, description = ?, status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $title, $description, $status, $task_id);

    if ($stmt->execute()) {
        // Verificar si el estado es "Completada"
        if ($status === 'Completada') {
            $message = "La tarea '$title' ha sido marcada como completada.";
            $notify_sql = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
            $notify_stmt = $conn->prepare($notify_sql);
            $notify_stmt->bind_param("is", $admin_id, $message);
            $notify_stmt->execute();
            $notify_stmt->close();
        }

        // Redirigir con mensaje de éxito
        header("Location: user_home.php?message=Tarea actualizada con éxito");
    } else {
        // Redirigir con mensaje de error
        header("Location: user_home.php?message=Error al actualizar la tarea");
    }

    $stmt->close();
}
?>
