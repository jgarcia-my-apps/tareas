<?php


if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

include 'db_connection.php';

// Obtener usuarios solo si el rol es admin
if ($_SESSION['role'] === 'admin') {
    // Obtener usuarios
    $sql = "SELECT * FROM users";
    $result = $conn->query($sql);
    $message = '';
    if (isset($_GET['message'])) {
        $message = htmlspecialchars($_GET['message']);
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Página de Usuario</title>
    <link rel="stylesheet" href="styles/user_home.css"> <!-- Asegúrate de tener estilos aquí -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="container">
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <h1>Lista de Usuarios</h1>
                <?php if ($message): ?>
                    <div class="alert"><?php echo $message; ?></div>
                <?php endif; ?>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Nombre de Usuario</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                    <?php while ($user = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo $user['username']; ?></td>
                        <td><?php echo $user['role']; ?></td>
                        <td>
                            <a href="edit_user.php?id=<?php echo $user['id']; ?>" title="Editar"><i class="fas fa-edit"></i></a>
                            <form action="delete_user.php" method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                <button type="submit" title="Eliminar" onclick="return confirm('¿Estás seguro de que quieres eliminar este usuario?');">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </table>
                <a class="btn-add" href="add_user.php"><i class="fas fa-plus"></i> Agregar Usuario</a>

            <?php else: ?>
                <h1>Bienvenido, <?php echo $_SESSION['user']; ?>!</h1>
                <p>Aquí puedes gestionar tus tareas.</p>
                <!-- Aquí puedes añadir más contenido específico para los usuarios -->
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Descripción</th>
                        <th>Estado</th>
                    </tr>
                    <!-- Ejemplo de tareas del usuario -->
                    <tr>
                        <td>1</td>
                        <td>Tarea de ejemplo 1</td>
                        <td>En progreso</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Tarea de ejemplo 2</td>
                        <td>Completada</td>
                    </tr>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
