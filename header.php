<?php
// header.php

// Iniciar sesión si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está autenticado y su rol
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Aquí puedes agregar más lógica si es necesario, como cargar información de usuario

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        header {
            background-color: #f4f4f4; /* Color de fondo para el header */
            padding: 20px; /* Espacio alrededor del contenido */
            display: flex; /* Hacer el header un contenedor flex */
            justify-content: center; /* Centrar el contenido horizontalmente */
        }

        .container {
            display: flex;
            flex-direction: column; /* Alinear logo y título en columna */
            align-items: center; /* Centrar horizontalmente */
            text-align: center; /* Alinear el texto al centro */
        }

        .logo {
            max-width: 100px; /* Ajusta según sea necesario */
            height: auto; /* Mantener proporciones */
        }

        h1 {
            margin-top: 10px; /* Espacio entre el logo y el título */
            font-size: 24px; /* Tamaño del texto */
        }
    </style>
    <title>Gestión de Tareas - Task Family</title>
</head>
<body>
    <header>
        <div class="container">
            <img src="assets/images/logo.png" alt="Logo" class="logo">
            <h1>Bienvenido al sistema de gestión de tareas Task Family v 1.0</h1>
        </div>
    </header>
</body>
</html>
