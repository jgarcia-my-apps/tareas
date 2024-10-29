<?php
session_start();
include 'db_connection.php'; // Archivo de conexión

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (username, password, role, created_at) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $hashedPassword, $role);

    if ($stmt->execute()) {
        echo "Registro exitoso. Ahora puedes <a href='login.php'>iniciar sesión</a>.";
    } else {
        echo "Error en el registro: " . $stmt->error;
    }

    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuario</title>
    <link rel="stylesheet" href="styles/register_styles.css"> <!-- Asegúrate de que esta ruta sea correcta -->
</head>
<body>
    <div class="form-container">
        <h2>Registro de Usuario</h2>
        <form action="" method="POST">
            <label for="username">Nombre de Usuario:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>

            <label for="role">Rol:</label>
            <select id="role" name="role" required>
                <option value="user">Usuario</option>
                <option value="admin">Administrador</option>
            </select>

            <button type="submit">Registrar</button>
        </form>
    </div>
</body>
</html>
