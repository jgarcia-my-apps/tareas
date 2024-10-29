<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'db_connection.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
    
    if ($id === false) {
        header("Location: admin_users.php?message=ID no válido.");
        exit();
    }

    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        header("Location: admin_users.php?message=Error al preparar la consulta.");
        exit();
    }

    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: admin_users.php?message=Usuario eliminado con éxito.");
    } else {
        header("Location: admin_users.php?message=Error al eliminar el usuario: " . $stmt->error);
    }

    $stmt->close();
} else {
    header("Location: admin_users.php?message=ID de usuario no proporcionado.");
}
