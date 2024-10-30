<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tareas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4; /* Color de fondo general */
        }

        header {
            background-color: #007bff; /* Color de fondo del header */
            color: white; /* Color del texto */
            padding: 20px 0; /* Espaciado vertical */
        }

        .container {
            display: flex;
            justify-content: space-between; /* Espaciado entre elementos */
            align-items: center; /* Alineación vertical */
            max-width: 1200px; /* Ancho máximo */
            margin: 0 auto; /* Centrado */
            padding: 0 20px; /* Espaciado lateral */
        }

        .title {
            font-size: 24px; /* Tamaño del título */
            font-weight: bold; /* Negrita */
        }

        .user-info {
            display: flex;
            align-items: center; /* Alineación vertical */
        }

        .username {
            margin-right: 20px; /* Espacio entre el nombre y los íconos */
            font-size: 18px; /* Tamaño del nombre */
        }

        .settings-icon,
        .logout-icon {
            color: white; /* Color de los íconos */
            text-decoration: none; /* Sin subrayado */
            display: flex; /* Flex para alinear icono y texto */
            align-items: center; /* Alineación vertical */
            margin-left: 15px; /* Espacio entre los enlaces */
        }

        .settings-icon:hover,
        .logout-icon:hover {
            opacity: 0.8; /* Efecto al pasar el mouse */
        }

        .settings-icon i,
        .logout-icon i {
            margin-right: 5px; /* Espacio entre el ícono y el texto */
        }
    </style>
</head>
<body>

<header>
    <div class="container">
        <h1 class="title">Tareas</h1>
        <div class="user-info">
            <span class="username"><?php echo htmlspecialchars($_SESSION['user']); ?></span>
            <a href="profile_user.php" class="settings-icon" title="Configuración">
                <i class="fas fa-cog"></i>
                <span>Mi perfil</span>
            </a>
            <a href="logout.php" class="logout-icon" title="Cerrar Sesión">
                <i class="fas fa-sign-out-alt"></i>
                <span>Cerrar sesión</span>
            </a>
        </div>
    </div>
</header>

<!-- Resto de tu contenido aquí -->

</body>
</html>
