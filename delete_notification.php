<?php
session_start();
include 'db_connection.php';

if (isset($_GET['id'])) {
    $notification_id = $_GET['id'];
    $sql_delete = "DELETE FROM notifications WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $notification_id);

    if ($stmt_delete->execute()) {
        header("Location: manage_tasks.php?message=Notificación eliminada exitosamente.");
    } else {
        header("Location: manage_tasks.php?message=Error al eliminar la notificación.");
    }
    exit();
}
?>
