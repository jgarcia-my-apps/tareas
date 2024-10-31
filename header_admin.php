<?php
// Asegúrate de que la sesión esté iniciada
date_default_timezone_set('America/Bogota'); // Establecer la zona horaria adecuada
$current_time = date('Y-m-d H:i:s'); // Formato de fecha y hora
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="stylesheet" href="path/to/header_admin.css"> <!-- Cambia la ruta según sea necesario -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script> <!-- Asegúrate de incluir Font Awesome -->
</head>
<body>
<header>
    <div class="container">
        <div class="user-info">
            <span><?php echo $_SESSION['user']; ?></span> <!-- Muestra el nombre del usuario conectado -->
            <a href="profile_user.php" class="settings-icon" title="Configuración">
                <i class="fas fa-cog"></i> <i class="fas fa-user"></i> Mi perfil <!-- Ícono de perfil de usuario -->
            </a>
            <!-- <a href="logout.php" class="logout-icon" title="Cerrar Sesión">
                <i class="fas fa-sign-out-alt"></i> <!-- Icono de cerrar sesión -->
            </a> 
        </div>
        <div class="current-time">
            <h3 id="clock"><?php echo $current_time; ?></h3> <!-- Muestra la hora y fecha actual -->
        </div>
    </div>
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
