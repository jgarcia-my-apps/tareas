

<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="stylesheet" href="path/to/header_admin.css"> <!-- Cambia la ruta según sea necesario -->
</head>
<body>
<header>
    <div class="container">
        <div class="user-info">
            <span><?php echo $_SESSION['user']; ?></span> <!-- Muestra el nombre del usuario conectado -->
            <a href="profile.php" class="settings-icon" title="Configuración">
                <i class="fas fa-cog"></i> <i class="fas fa-user"></i> Mi perfil <!-- Ícono de perfil de usuario -->
            </a>
            <a href="logout.php" class="logout-icon" title="Cerrar Sesión">
                <i class="fas fa-sign-out-alt"></i> <!-- Icono de cerrar sesión -->
            </a>
        </div>
    </div>
</header>
</body>
</html>
