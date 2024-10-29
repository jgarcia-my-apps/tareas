<?php
session_start();

// Verifica si el usuario está conectado
if (!isset($_SESSION['user'])) {
    header("Location: login.php"); // Redirigir si no está conectado
    exit();
}

// Verifica que el ID del usuario esté en la sesión
if (!isset($_SESSION['user_id'])) {
    echo "Error: ID de usuario no encontrado.";
    exit();
}

include 'db_connection.php'; // Asegúrate de incluir tu archivo de conexión

$userId = $_SESSION['user_id']; // ID del usuario desde la sesión

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $photo = $_FILES['photo'];

    // Lógica para manejar la foto de perfil
    $photoPath = 'uploads/default-avatar.png'; // Ruta por defecto
    if ($photo['error'] == UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/'; // Carpeta donde se guardará la foto
        // Asegúrate de que la carpeta exista
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $photoPath = $uploadDir . basename($photo['name']);
        move_uploaded_file($photo['tmp_name'], $photoPath);
    }

    // Lógica para actualizar la contraseña
    $hashedPassword = $password ? password_hash($password, PASSWORD_DEFAULT) : null;

    // Construir la consulta de actualización
    $sql = "UPDATE users SET username = ?, email = ?, ";
    $params = [$username, $email];

    if ($hashedPassword) {
        $sql .= "password = ?, ";
        $params[] = $hashedPassword;
    }
    
    $sql .= "photo = ? WHERE id = ?";
    $params[] = $photoPath;
    $params[] = $userId;

    try {
        // Ejecutar la consulta
        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        // Actualizar la sesión con los nuevos datos
        $_SESSION['user'] = $username;

        // Redirigir a la página de perfil o de inicio
        header("Location: user_home.php?success=1");
        exit();
    } catch (PDOException $e) {
        echo "Error al actualizar el perfil: " . $e->getMessage();
    }
}
?>
