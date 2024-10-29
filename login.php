<?php
session_start();
include 'db_connection.php'; // Asegúrate de que esta línea sea correcta

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Verificar contraseña
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['user_id'] = $user['id']; // Establecer el ID en la sesión

        // Redirigir según el rol real de la base de datos
        if ($user['role'] === 'admin') {
            header('Location: admin_panel.php'); // Página para administradores
        } else {
            header('Location: user_home.php'); // Página para usuarios
        }
        exit();
    } else {
        $error = "Usuario o contraseña incorrectos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Iniciar Sesión</title>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <div class="card">
            <h1>Iniciar Sesión</h1>
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <form action="login.php" method="POST">
                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" name="username" placeholder="Nombre de usuario" required>
                </div>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="Contraseña" required>
                </div>
                <button type="submit">Iniciar sesión</button>
            </form>
            <div class="logo-container">
                <img src="assets/images/logo.png" alt="Logo" class="logo">
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
