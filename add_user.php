<?php
session_start();

// Verificar si el usuario está autenticado y tiene el rol de admin
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'db_connection.php'; // Incluye tu archivo de conexión a la base de datos

// Inicializar el mensaje
$message = "";

// Si hay un mensaje en la sesión, lo guardamos
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // Limpiar el mensaje después de mostrarlo
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);

    // Validaciones
    if (empty($username)) {
        $errors[] = "El nombre de usuario es requerido.";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Un correo electrónico válido es requerido.";
    }
    if (empty($password) || strlen($password) < 6) {
        $errors[] = "La contraseña debe tener al menos 6 caracteres.";
    }
    if (empty($role)) {
        $errors[] = "El rol es requerido.";
    }

    if (empty($errors)) {
        // Preparar la contraseña (hash)
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insertar el nuevo usuario en la base de datos
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $email, $hashedPassword, $role);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Usuario registrado con éxito.";
        } else {
            $_SESSION['message'] = "Error: No se pudo registrar el usuario: " . $stmt->error;
        }

        $stmt->close();
        $conn->close();

        // Redirigir para mostrar el mensaje
        header("Location: add_user.php");
        exit();
    } else {
        $_SESSION['message'] = implode("<br>", $errors);
        header("Location: add_user.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuario</title>
    <link rel="stylesheet" href="styles/add_user.css">
</head>
<body>
    <?php include 'header_admin.php'; ?>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <center><h2>Registro de Usuario</h2></center>
        <div class="form-container">
            <form action="" method="POST">
                <div class="form-group">
                    <label for="username">Nombre de Usuario:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="email">Correo Electrónico:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="role">Rol:</label>
                    <select id="role" name="role" required>
                        <option value="user">Usuario</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
                <button type="submit">Registrar</button>
            </form>

            <?php if (!empty($message)): ?>
                <div class="alert" style="margin-top: 20px; color: <?= strpos($message, 'Error') !== false ? 'red' : 'green' ?>">
                    <?= $message; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
