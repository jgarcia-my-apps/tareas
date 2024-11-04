<?php
// Iniciar sesión si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está autenticado y su rol
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Establecer la zona horaria
date_default_timezone_set('America/Bogota'); // Cambia a tu zona horaria si es necesario
$current_time = date('Y-m-d H:i:s'); // Formato de fecha y hora
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
            justify-content: space-between; /* Espacio entre logo y usuario */
            align-items: center; /* Alinear verticalmente */
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

        .current-time {
            font-size: 18px; /* Tamaño del texto para la hora */
            font-weight: bold; /* Hacer el texto más visible */
        }
    </style>
    <title>Gestión de Tareas - Task Family</title>
</head>
<body>
    <header>
        <div class="container">
            <img src="assets/images/logo.png" alt="Logo" class="logo">
            <h1>Bienvenido al sistema de gestión de tareas Task Family </h1>
        </div>
       
        <div class="current-time">
            <h3 id="clock"><?php echo $current_time; ?></h3> <!-- Muestra la hora y fecha actual -->
        </div>
        <li><a href="logout.php">Cerrar Sesión</a></li>
    </header>

    <script>
        function updateClock() {
            const now = new Date();
            const formattedTime = now.toLocaleString('es-CO', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false
            });

            document.getElementById('clock').textContent = formattedTime;
        }

        setInterval(updateClock, 1000); // Actualiza cada segundo
        updateClock(); // Llama a la función inmediatamente para mostrar la hora al cargar
    </script>
    
</body>
</html>
