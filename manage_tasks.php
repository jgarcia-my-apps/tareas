<?php
session_start(); // Iniciar la sesión

$message = ''; // Inicializa la variable de mensaje

// Comprobación de sesión
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'db_connection.php'; // Incluye la conexión a la base de datos

// Obtener usuarios
$sql_users = "SELECT * FROM users";
$result_users = $conn->query($sql_users);

// Consulta para obtener notificaciones
$sql_notifications = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
$stmt_notifications = $conn->prepare($sql_notifications);
$stmt_notifications->bind_param("i", $_SESSION['user_id']);
$stmt_notifications->execute();
$result_notifications = $stmt_notifications->get_result();

// Manejar creación de tarea
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_task'])) {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $due_date = $_POST['due_date'] ?? '';
    $priority = $_POST['priority'] ?? '';
    $user_id = $_POST['user_id'] ?? '';

    if (!empty($title) && !empty($description) && !empty($start_date) && !empty($due_date) && !empty($priority) && !empty($user_id)) {
        $sql_create = "INSERT INTO tasks (task_name, description, start_date, due_date, priority, user_id) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql_create);
        $stmt->bind_param("sssssi", $title, $description, $start_date, $due_date, $priority, $user_id);
        
        if ($stmt->execute()) {
            $message = 'Tarea creada exitosamente.';
        } else {
            $message = 'Error al crear la tarea: ' . $stmt->error; // Agregado para más información
        }
    } else {
        $message = 'Por favor, completa todos los campos.';
    }
}

// Manejar edición de tarea
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_task'])) {
    $task_id = $_POST['task_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $start_date = $_POST['start_date'];
    $due_date = $_POST['due_date'];
    $priority = $_POST['priority'];
    $user_id = $_POST['user_id'];
    $status = $_POST['status']; // Asegúrate de que estás recogiendo el estado

    // Consulta para actualizar la tarea
    $sql_update = "UPDATE tasks SET task_name = ?, description = ?, start_date = ?, due_date = ?, priority = ?, user_id = ?, status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql_update);

    // Cambia 'ssssssi' por 'sssssssi' para incluir el estado
    $stmt->bind_param("sssssssi", $title, $description, $start_date, $due_date, $priority, $user_id, $status, $task_id);

    // Ejecutar la consulta
    if ($stmt->execute()) {
        $message = 'Tarea actualizada exitosamente.';
    } else {
        $message = 'Error al actualizar la tarea: ' . $stmt->error;
    }
}


// Manejar eliminación de notificación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_notification_id'])) {
    $notification_id = $_POST['delete_notification_id'];
    $sql_delete = "DELETE FROM notifications WHERE id = ?";
    $stmt = $conn->prepare($sql_delete);
    $stmt->bind_param("i", $notification_id);

    if ($stmt->execute()) {
        $message = 'Notificación eliminada exitosamente.';
    } else {
        $message = 'Error al eliminar la notificación: ' . $stmt->error;
    }
}

// Filtrar tareas por fecha
$start_filter = $_GET['start_filter'] ?? '';
$end_filter = $_GET['end_filter'] ?? '';

$sql_tasks = "SELECT * FROM tasks";
if ($start_filter && $end_filter) {
    $sql_tasks .= " WHERE start_date >= ? AND due_date <= ?";
    $stmt = $conn->prepare($sql_tasks);
    $stmt->bind_param("ss", $start_filter, $end_filter);
} else {
    $stmt = $conn->prepare($sql_tasks);
}
$stmt->execute();
$result_tasks = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Administrar Tareas</title>
    <link rel="stylesheet" href="styles/admin_users.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Estilos del Modal */
        .modal { display: none; position: fixed; z-index: 1; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0, 0, 0, 0.4); padding-top: 60px; }
        .modal-content { background-color: #fefefe; margin: 5% auto; padding: 20px; border: 1px solid #888; width: 80%; max-width: 600px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); }
        .close { color: #aaa; float: right; font-size: 28px; font-weight: bold; }
        .close:hover, .close:focus { color: black; text-decoration: none; cursor: pointer; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="date"], textarea, select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
        textarea { resize: vertical; }
        .btn-submit { background-color: #28a745; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-submit:hover { background-color: #218838; }
        .btn-cancel { background-color: #dc3545; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; margin-left: 10px; }
        .btn-cancel:hover { background-color: #c82333; }
        .btn-filter { background-color: #28a745; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; transition: background-color 0.3s; }
        .btn-filter:hover { background-color: #218838; }
    </style>
</head>
<body>

    <?php include 'header_admin.php'; ?>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="container">
            <h1>ADMINISTRAR TAREAS</h1>

            <?php if (!empty($message)): ?>
                <div class="alert"><?php echo htmlspecialchars($message); ?></div> <!-- Seguridad adicional -->
            <?php endif; ?>

            <h2>Notificaciones</h2>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Mensaje</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
                <?php if ($result_notifications->num_rows > 0): ?>
                    <?php while ($notification = $result_notifications->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($notification['id']); ?></td>
                        <td><?php echo htmlspecialchars($notification['message']); ?></td>
                        <td><?php echo htmlspecialchars($notification['created_at']); ?></td>
                        <td>
                            <form method="POST" action="manage_tasks.php" style="display:inline;">
                                <input type="hidden" name="delete_notification_id" value="<?php echo htmlspecialchars($notification['id']); ?>">
                                <button type="submit" onclick="return confirm('¿Estás seguro de que deseas eliminar esta notificación?');" class="btn-cancel">
                                    Eliminar
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">No hay notificaciones.</td>
                    </tr>
                <?php endif; ?>
            </table>

            <h2>Tareas</h2>
            <button id="filterModalBtn" class="btn-add"><i class="fas fa-filter"></i> Filtrar Tareas</button>

            <!-- Modal para filtrar tareas -->
            <div id="filterModal" class="modal" style="display: none;">
                <div class="modal-content">
                    <span class="close-filter">&times;</span>
                    <h2>Filtrar Tareas</h2>
                    <form method="GET" action="manage_tasks.php">
                        <div class="form-group">
                            <label for="start_filter">Fecha de Inicio:</label>
                            <input type="date" name="start_filter" id="start_filter" required>
                        </div>
                        <div class="form-group">
                            <label for="end_filter">Fecha de Vencimiento:</label>
                            <input type="date" name="end_filter" id="end_filter" required>
                        </div>
                        <button type="submit" class="btn-filter">Filtrar</button>
                    </form>
                </div>
            </div>

            <table>
                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Descripción</th>
                    <th>Usuario Asignado</th>
                    <th>Fecha de Inicio</th>
                    <th>Fecha de Vencimiento</th>
                    <th>Prioridad</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
                <?php if ($result_tasks->num_rows > 0): ?>
                    <?php while ($task = $result_tasks->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($task['id']); ?></td>
                        <td><?php echo htmlspecialchars($task['task_name']); ?></td>
                        <td><?php echo htmlspecialchars($task['description']); ?></td>
                        <td>
                            <?php
                            $user_sql = "SELECT username FROM users WHERE id = " . (int)$task['user_id'];
                            $user_result = $conn->query($user_sql);
                            $user = $user_result->fetch_assoc();
                            echo $user ? htmlspecialchars($user['username']) : 'No asignado';
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($task['start_date']); ?></td>
                        <td><?php echo htmlspecialchars($task['due_date']); ?></td>
                        <td><?php echo htmlspecialchars($task['priority']); ?></td>
                        <td><?php echo htmlspecialchars($task['status']); ?></td>
                        <td>
                            <button class="btn-edit" onclick="openEditModal(<?php echo htmlspecialchars($task['id']); ?>, '<?php echo htmlspecialchars($task['task_name']); ?>', '<?php echo htmlspecialchars($task['description']); ?>', '<?php echo htmlspecialchars($task['start_date']); ?>', '<?php echo htmlspecialchars($task['due_date']); ?>', '<?php echo htmlspecialchars($task['priority']); ?>', <?php echo htmlspecialchars($task['user_id']); ?>);">
                                Editar
                            </button>
                            <form method="POST" action="delete_task.php" style="display:inline;">
                                <input type="hidden" name="task_id" value="<?php echo htmlspecialchars($task['id']); ?>">
                                <button type="submit" onclick="return confirm('¿Estás seguro de que deseas eliminar esta tarea?');" class="btn-cancel">
                                    Eliminar
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No hay tareas.</td>
                    </tr>
                <?php endif; ?>
            </table>

           <!-- Modal para editar tarea -->
<div id="editTaskModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close-edit">&times;</span>
        <h2>Editar Tarea</h2>
        <form id="editTaskForm" method="POST" action="manage_tasks.php">
            <input type="hidden" name="task_id" id="edit_task_id" value="">
            <div class="form-group">
                <label for="edit_title">Título:</label>
                <input type="text" name="title" id="edit_title" required>
            </div>
            <div class="form-group">
                <label for="edit_description">Descripción:</label>
                <textarea name="description" id="edit_description" required></textarea>
            </div>
            <div class="form-group">
                <label for="edit_start_date">Fecha de Inicio:</label>
                <input type="date" name="start_date" id="edit_start_date" required>
            </div>
            <div class="form-group">
                <label for="edit_due_date">Fecha de Vencimiento:</label>
                <input type="date" name="due_date" id="edit_due_date" required>
            </div>
            <div class="form-group">
                <label for="edit_priority">Prioridad:</label>
                <select name="priority" id="edit_priority" required>
                    <option value="baja">Baja</option>
                    <option value="media">Media</option>
                    <option value="alta">Alta</option>
                </select>
            </div>
            <div class="form-group">
                <label for="edit_user_id">Asignar a Usuario:</label>
                <select name="user_id" id="edit_user_id" required>
                    <?php while ($user = $result_users->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($user['id']); ?>"><?php echo htmlspecialchars($user['username']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
        <label>Estado:</label>
            <select name="status" id="edit_status" required>
                <option value="nueva" selected>Nueva</option>
                <option value="pendiente">Pendiente</option>
                <option value="asignada">Asignada</option>
                <option value="completada">Completada</option>
            </select>
        </div>

            <button type="submit" name="edit_task" class="btn-submit">Actualizar</button>
            <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancelar</button>
        </form>
    </div>
</div>



    <script>
        function closeEditModal() {
    document.getElementById('editTaskModal').style.display = 'none';
}

        function openEditModal(id, title, description, startDate, dueDate, priority, status, userId) {
            document.getElementById('edit_task_id').value = id;
            document.getElementById('edit_title').value = title;
            document.getElementById('edit_description').value = description;
            document.getElementById('edit_start_date').value = startDate;
            document.getElementById('edit_due_date').value = dueDate;
            document.getElementById('edit_priority').value = priority;
            document.getElementById('edit_status').value = status;
            document.getElementById('edit_user_id').value = userId;

            document.getElementById('editTaskModal').style.display = 'block';
        }

        document.querySelector('.close-edit').onclick = function() {
            document.getElementById('editTaskModal').style.display = 'none';
        }

        document.querySelector('.close-filter').onclick = function() {
            document.getElementById('filterModal').style.display = 'none';
        }

        document.getElementById('filterModalBtn').onclick = function() {
            document.getElementById('filterModal').style.display = 'block';
        }

        window.onclick = function(event) {
            if (event.target === document.getElementById('editTaskModal') || event.target === document.getElementById('filterModal')) {
                document.getElementById('editTaskModal').style.display = 'none';
                document.getElementById('filterModal').style.display = 'none';
            }
        }
    </script>

<?php include 'footer.php'; ?>
</body>

</html>
