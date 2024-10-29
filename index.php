<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user'])) {
    header("Location: login.php"); // Redirigir al login si no está autenticado
    exit();
}

// Incluir el header
include 'header.php';

// Contenido basado en el rol del usuario
if ($_SESSION['role'] === 'admin') {
    echo "<h1>Bienvenido Administrador</h1>";
    // Aquí va el contenido para administradores
    echo "<p>Desde aquí puedes gestionar usuarios y tareas.</p>";
} else {
    echo "<h1>Bienvenido Usuario</h1>";
    // Aquí va el contenido para usuarios
    echo "<p>Desde aquí puedes ver y gestionar tus tareas.</p>";
}

// Incluir el footer
include 'footer.php';
?>
