<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Asegúrate de tener el ID del administrador
$admin_id = $_SESSION['user_id']; // Esto debería ser el ID del admin en la sesión

$sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Notificaciones</title>
    <link rel="stylesheet" href="styles/user_home.css">
</head>
<body>

    <h1>Notificaciones</h1>
    <table>
        <tr>
            <th>ID</th>
            <th>Mensaje</th>
            <th>Fecha</th>
        </tr>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($notification = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $notification['id']; ?></td>
                <td><?php echo htmlspecialchars($notification['message']); ?></td>
                <td><?php echo htmlspecialchars($notification['created_at']); ?></td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="3">No hay notificaciones.</td>
            </tr>
        <?php endif; ?>
    </table>

    <?php $stmt->close(); ?>
</body>
</html>
