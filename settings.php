<?php
session_start();

// Asegúrate de que el usuario esté autenticado
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Aquí puedes manejar la lógica para actualizar el perfil
// Por simplicidad, el código para guardar cambios no está incluido

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Configuración de Perfil</title>
    <link rel="stylesheet" href="styles/styles.css">
</head>
<body>
    <?php include 'header_admin.php'; ?>

    <div class="container">
        <div class="main-content">
            <h1>Configuración de Perfil</h1>
            <form action="update_profile.php" method="POST" enctype="multipart/form-data">
                <div class="input-group">
                    <label for="username">Nombre de usuario:</label>
                    <input type="text" name="username" id="username" value="<?php echo $_SESSION['user']; ?>" required>
                </div>
                <div class="input-group">
                    <label for="profile_picture">Foto de perfil:</label>
                    <input type="file" name="profile_picture" id="profile_picture">
                </div>
                <div class="input-group">
                    <label for="role">Rol:</label>
                    <select name="role" required>
                        <option value="user" <?php if ($_SESSION['role'] == 'user') echo 'selected'; ?>>Usuario</option>
                        <option value="admin" <?php if ($_SESSION['role'] == 'admin') echo 'selected'; ?>>Administrador</option>
                    </select>
                </div>
                <div class="input-group">
                    <label for="password">Nueva Contraseña:</label>
                    <input type="password" name="password" id="password" placeholder="Dejar vacío si no deseas cambiarla">
                </div>
                <button type="submit">Actualizar Perfil</button>
            </form>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
