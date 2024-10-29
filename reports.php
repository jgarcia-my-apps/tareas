<?php
session_start(); // Iniciar la sesión

$message = ''; // Inicializa la variable de mensaje

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}


include 'db_connection.php';

// Obtener usuarios
$sql = "SELECT * FROM users";
$result = $conn->query($sql);

// Mensaje de éxito o error
$message = '';
if (isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Administrar Usuarios</title>
    <link rel="stylesheet" href="styles/admin_users.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

   
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="container">
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
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
