<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    // Manejo de la foto
    $profile_picture = $_FILES['profile_picture'];
    $profile_picture_path = '';

    if ($profile_picture['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        $file_name = basename($profile_picture['name']);
        $target_file = $upload_dir . uniqid() . '_' . $file_name;

        if (move_uploaded_file($profile_picture['tmp_name'], $target_file)) {
            $profile_picture_path = $target_file;
        }
    } else {
        $sql = "SELECT profile_picture FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $profile_picture_path = $user['profile_picture'];
    }

    $sql = "UPDATE users SET username = ?, email = ?, profile_picture = ?, role = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $username, $email, $profile_picture_path, $role, $id);
    $stmt->execute();

    header("Location: admin_panel.php");
    exit();
}

$id = $_GET['id'];
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "<p>Usuario no encontrado.</p>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario</title>
    <link rel="stylesheet" href="styles/edit_user.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?php include 'header_admin.php'; ?>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="form-container">
            <h1>Editar Usuario</h1>
            <form id="editUserForm" action="edit_user.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">

                <div class="form-group">
                    <label for="username">Nombre de Usuario:</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Correo Electrónico:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="profile_picture">Seleccionar Foto de Perfil:</label>
                    <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
                </div>

                <div class="form-group">
                    <label for="password">Nueva Contraseña (dejar en blanco si no desea cambiar):</label>
                    <input type="password" id="password" name="password">
                </div>

                <div class="form-group">
                    <label for="role">Rol:</label>
                    <select id="role" name="role" required>
                        <option value="user" <?php if ($user['role'] === 'user') echo 'selected'; ?>>Usuario</option>
                        <option value="admin" <?php if ($user['role'] === 'admin') echo 'selected'; ?>>Administrador</option>
                    </select>
                </div>

                <button type="submit">Actualizar Usuario</button>
                <button type="button" id="cancelButton">Cancelar</button>
            </form>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        document.getElementById('cancelButton').addEventListener('click', function() {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Los cambios no se guardarán.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, cancelar',
                cancelButtonText: 'No, volver'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'admin_panel.php'; // Redirigir a admin_users.php
                }
            });
        });

        // Si se necesita, se puede agregar lógica para manejar el envío del formulario y alertar al usuario
        document.getElementById('editUserForm').addEventListener('submit', function(event) {
            event.preventDefault(); // Prevenir el envío por defecto

            Swal.fire({
                title: '¿Confirmar cambios?',
                text: "Asegúrate de que toda la información es correcta.",
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, actualizar',
                cancelButtonText: 'No, volver'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit(); // Enviar el formulario si el usuario confirma
                }
            });
        });
    </script>
</body>
</html>
