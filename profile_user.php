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

include 'db_connection.php'; 

$userId = $_SESSION['user_id']; // ID del usuario desde la sesión

// Lógica para obtener los datos del usuario
$sql = "SELECT username, email FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();

// Verifica si se obtuvo el usuario
if (!$userData) {
    echo "Error: Usuario no encontrado.";
    exit();
}

$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Lógica para actualizar la contraseña
    $hashedPassword = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null;

    // Construir la consulta de actualización
    $sql = "UPDATE users SET username = ?, email = ? ";
    $params = [$username, $email];
    $isPasswordUpdated = false;

    if ($hashedPassword) {
        $sql .= ", password = ?";
        $params[] = $hashedPassword;
        $isPasswordUpdated = true; // Marcamos que la contraseña fue actualizada
    }

    $sql .= " WHERE id = ?";
    $params[] = $userId;

    // Ejecutar la consulta
    $stmt = $conn->prepare($sql);
    $types = str_repeat('s', count($params) - 1) . 'i'; // 's' para string, 'i' para integer
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        $successMessage = 'Perfil actualizado exitosamente!';
        if ($isPasswordUpdated) {
            $successMessage .= ' La contraseña ha sido actualizada.';
        }
    } else {
        $errorMessage = 'Error al actualizar el perfil: ' . $stmt->error;
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Perfil de Usuario</title>
    <link rel="stylesheet" href="styles/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11">
    <style>
        body {
            background-color: #f4f4f4;
            font-family: 'Ubuntu', sans-serif;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
        }
        h1 {
            text-align: center;
            color: #007bff;
        }
        .input-group {
            margin-bottom: 15px;
        }
        .input-group label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
            color: #555;
        }
        .input-group input[type="text"],
        .input-group input[type="email"],
        .input-group input[type="password"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 100%;
            box-sizing: border-box;
        }
        .button-group {
            display: flex;
            justify-content: space-between;
        }
        .button-group button {
            padding: 10px 15px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .button-group button:hover {
            background-color: #218838;
        }
        .cancel-button {
            padding: 10px 15px;
            background-color: #dc3545;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            display: inline-block;
            transition: background-color 0.3s;
        }
        .cancel-button:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    
    <?php include 'sidebar_users.php'; ?>
    
    <div class="container">
        <div class="main-content">
            <h1>Editar Perfil</h1>
            <form action="profile.php" method="POST" class="profile-form">
                <div class="input-group">
                    <label for="username">Nombre de Usuario:</label>
                    <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($userData['username']); ?>" required>
                </div>
                <div class="input-group">
                    <label for="email">Correo Electrónico:</label>
                    <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($userData['email']); ?>" required>
                </div>
                <div class="input-group">
                    <label for="password">Nueva Contraseña:</label>
                    <input type="password" name="password" id="password">
                </div>
                
                <div class="button-group">
                    <button type="submit">Actualizar Información</button>
                    <a href="user_home.php" class="cancel-button">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    <?php if ($successMessage || $errorMessage): ?>
        <script>
            window.onload = function() {
                let message = "<?php echo $successMessage ?: $errorMessage; ?>";
                let title = "<?php echo $successMessage ? 'Actualización Exitosa!' : 'Error!'; ?>";
                let icon = "<?php echo $successMessage ? 'success' : 'error'; ?>";
                
                Swal.fire({
                    title: title,
                    text: message,
                    icon: icon,
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    window.location.href = 'admin_panel.php'; // Cambia a la página que desees
                });
            }
        </script>
    <?php endif; ?>

    <?php include 'footer.php'; ?>
</body>
</html>
