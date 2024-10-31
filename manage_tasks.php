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
                <div class="alert"><?php echo htmlspecialchars($message); ?></div>
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
                                <button type="submit" onclick="return confirm('¿Estás seguro de que deseas eliminar esta notificación?');"><i class="fas fa-trash-alt"></i></button>
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
            <button id="createTaskBtn" class="btn-submit">Crear Tarea</button>
            <button id="filterTasksBtn" class="btn-filter" onclick="openFilterModal()">Filtrar Tareas</button>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Tarea</th>
                    <th>Descripción</th>
                    <th>Fecha de Inicio</th>
                    <th>Fecha de Vencimiento</th>
                    <th>Prioridad</th>
                    <th>Usuario Asignado</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
                <?php if ($result_tasks->num_rows > 0): ?>
                    <?php while ($task = $result_tasks->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($task['id']); ?></td>
                        <td><?php echo htmlspecialchars($task['task_name']); ?></td>
                        <td><?php echo htmlspecialchars($task['description']); ?></td>
                        <td><?php echo htmlspecialchars($task['start_date']); ?></td>
                        <td><?php echo htmlspecialchars($task['due_date']); ?></td>
                        <td><?php echo htmlspecialchars($task['priority']); ?></td>
                        <td>
                            <?php
                            $user_sql = "SELECT username FROM users WHERE id = " . (int)$task['user_id'];
                            $user_result = $conn->query($user_sql);
                            $user = $user_result->fetch_assoc();
                            echo $user ? htmlspecialchars($user['username']) : 'No asignado';
                            ?>
                        </td>

                        <td><?php echo htmlspecialchars($task['status']); ?></td>
                        <td>
                            <button class="editTaskBtn" data-id="<?php echo htmlspecialchars($task['id']); ?>" data-title="<?php echo htmlspecialchars($task['task_name']); ?>" data-description="<?php echo htmlspecialchars($task['description']); ?>" data-start="<?php echo htmlspecialchars($task['start_date']); ?>" data-due="<?php echo htmlspecialchars($task['due_date']); ?>" data-priority="<?php echo htmlspecialchars($task['priority']); ?>" data-user="<?php echo htmlspecialchars($task['user_id']); ?>" data-status="<?php echo htmlspecialchars($task['status']); ?>"><i class="fas fa-edit"></i></button>
                            <button class="deleteTaskBtn" data-id="<?php echo htmlspecialchars($task['id']); ?>"><i class="fas fa-trash-alt"></i></button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9">No hay tareas disponibles.</td>
                    </tr>
                <?php endif; ?>
            </table>

            <!-- Modal para crear tarea -->
            <div id="createTaskModal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h2>Crear Tarea</h2>
                    <form method="POST" action="manage_tasks.php">
                        <div class="form-group">
                            <label for="title">Título:</label>
                            <input type="text" name="title" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Descripción:</label>
                            <textarea name="description" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="start_date">Fecha de Inicio:</label>
                            <input type="date" name="start_date" required>
                        </div>
                        <div class="form-group">
                            <label for="due_date">Fecha de Vencimiento:</label>
                            <input type="date" name="due_date" required>
                        </div>
                        <div class="form-group">
                            <label for="priority">Prioridad:</label>
                            <select name="priority" required>
                                <option value="">Seleccione una prioridad</option>
                                <option value="Baja">Baja</option>
                                <option value="Media">Media</option>
                                <option value="Alta">Alta</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="user_id">Usuario Asignado:</label>
                            <select name="user_id" required>
                                <option value="">Seleccione un usuario</option>
                                <?php while ($user = $result_users->fetch_assoc()): ?>
                                    <option value="<?php echo htmlspecialchars($user['id']); ?>"><?php echo htmlspecialchars($user['username']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>


                        <button type="submit" name="create_task" class="btn-submit">Crear</button>
                        <button type="button" class="btn-cancel close">Cancelar</button>
                    </form>
                </div>
            </div>

            <!-- Modal para editar tarea -->
            <div id="editTaskModal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h2>Editar Tarea</h2>
                    <form method="POST" action="manage_tasks.php">
                        <input type="hidden" name="task_id" id="edit_task_id">
                        <div class="form-group">
                            <label for="title">Título:</label>
                            <input type="text" name="title" id="edit_title" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Descripción:</label>
                            <textarea name="description" id="edit_description" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="start_date">Fecha de Inicio:</label>
                            <input type="date" name="start_date" id="edit_start_date" required>
                        </div>
                        <div class="form-group">
                            <label for="due_date">Fecha de Vencimiento:</label>
                            <input type="date" name="due_date" id="edit_due_date" required>
                        </div>
                        <div class="form-group">
                            <label for="priority">Prioridad:</label>
                            <select name="priority" id="edit_priority" required>
                                <option value="">Seleccione una prioridad</option>
                                <option value="Baja">Baja</option>
                                <option value="Media">Media</option>
                                <option value="Alta">Alta</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="user_id">Usuario Asignado:</label>
                            <select name="user_id" id="edit_user_id" required>
                                <option value="">Seleccione un usuario</option>
                                <?php
                                $result_users->data_seek(0); // Reinicia el puntero del resultado
                                while ($user = $result_users->fetch_assoc()): ?>
                                    <option value="<?php echo htmlspecialchars($user['id']); ?>"><?php echo htmlspecialchars($user['username']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="status">Estado:</label>
                            <select name="status" id="edit_status" required>
                                <option value="">Seleccione un estado</option>
                                <option value="Pendiente">Pendiente</option>
                                <option value="En Progreso">En Progreso</option>
                                <option value="Completada">Completada</option>
                            </select>
                        </div>
                        <button type="submit" name="edit_task" class="btn-submit">Guardar Cambios</button>
                        <button type="button" class="btn-cancel close">Cancelar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<!-- Modal para Filtrar Tareas -->
<div id="filterModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeFilterModal()">&times;</span>
        <h2>Filtrar Tareas</h2>
        <form method="GET" action="manage_tasks.php">
            <div class="form-group">
                <label for="start_filter">Fecha de Inicio:</label>
                <input type="date" name="start_filter">
            </div>
            <div class="form-group">
                <label for="end_filter">Fecha de Vencimiento:</label>
                <input type="date" name="end_filter">
            </div>
                        <div class="form-group">
                <label for="user_id">Usuario Asignado:</label>
                <select name="user_id" id="edit_user_id" required>
                    <option value="">Seleccione un usuario</option>
                    <?php
                    $result_users->data_seek(0); // Reinicia el puntero del resultado
                    while ($user = $result_users->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($user['id']); ?>"><?php echo htmlspecialchars($user['username']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <button type="submit" class="btn-filter">Buscar</button>
            <button type="button" class="btn-cancel" onclick="closeFilterModal()">Cancelar</button>
        </form>
    </div>
</div>

    <script>
        // Obtener elementos del DOM
        const createTaskModal = document.getElementById('createTaskModal');
        const editTaskModal = document.getElementById('editTaskModal');
        const createTaskBtn = document.getElementById('createTaskBtn');
        const closeButtons = document.querySelectorAll('.close');
        const editTaskButtons = document.querySelectorAll('.editTaskBtn');

        // Abrir modal para crear tarea
        createTaskBtn.onclick = function() {
            createTaskModal.style.display = "block";
        };

        // Abrir modal para editar tarea
        editTaskButtons.forEach(button => {
            button.onclick = function() {
                const taskId = this.getAttribute('data-id');
                const title = this.getAttribute('data-title');
                const description = this.getAttribute('data-description');
                const start = this.getAttribute('data-start');
                const due = this.getAttribute('data-due');
                const priority = this.getAttribute('data-priority');
                const userId = this.getAttribute('data-user');
                const status = this.getAttribute('data-status'); // Trae el estado

                document.getElementById('edit_task_id').value = taskId;
                document.getElementById('edit_title').value = title;
                document.getElementById('edit_description').value = description;
                document.getElementById('edit_start_date').value = start;
                document.getElementById('edit_due_date').value = due;
                document.getElementById('edit_priority').value = priority;
                document.getElementById('edit_user_id').value = userId;
                document.getElementById('edit_status').value = status; // Asigna el estado

                editTaskModal.style.display = "block";
            };
        });

        // Cerrar modales
        closeButtons.forEach(button => {
            button.onclick = function() {
                createTaskModal.style.display = "none";
                editTaskModal.style.display = "none";
            };
        });

        // Cerrar modales al hacer clic fuera de ellos
        window.onclick = function(event) {
            if (event.target === createTaskModal) {
                createTaskModal.style.display = "none";
            }
            if (event.target === editTaskModal) {
                editTaskModal.style.display = "none";
            }
        };
        function openFilterModal() {
    document.getElementById("filterModal").style.display = "block";
}

function closeFilterModal() {
    document.getElementById("filterModal").style.display = "none";
}

// Cerrar el modal si el usuario hace clic fuera de él
window.onclick = function(event) {
    const modal = document.getElementById("filterModal");
    if (event.target == modal) {
        closeFilterModal();
    }
}

    </script>
</body>
</html>
