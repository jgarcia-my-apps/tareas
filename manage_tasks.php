manage tasks con notificaciones
<?php
session_start(); // Iniciar la sesión

$message = ''; // Inicializa la variable de mensaje

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'db_connection.php';

// Obtener usuarios

$sql_users = "SELECT * FROM users";
$result_users = $conn->query($sql_users);

if (!$result_users) {
    $message = 'Error al obtener usuarios: ' . $conn->error; // Manejo de error
} else {
    // Verificar si hay usuarios
    if ($result_users->num_rows === 0) {
        $message = 'No hay usuarios disponibles.';
    }
}


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
            $message = 'Error al crear la tarea.';
        }
    } else {
        $message = 'Por favor, completa todos los campos.';
    }
}


//logica para eliminar la notificacion

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

// Manejar edición de tarea
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_task'])) {
    $task_id = $_POST['task_id'] ?? '';
    $title = $_POST['title'] ?? ''; 
    $description = $_POST['description'] ?? ''; 
    $start_date = $_POST['start_date'] ?? ''; 
    $due_date = $_POST['due_date'] ?? ''; 
    $priority = $_POST['priority'] ?? ''; 
    $user_id = $_POST['user_id'] ?? ''; 
    $status = $_POST['status'] ?? ''; // Asegúrate de que se incluya el estado

    if (!empty($task_id) && !empty($title) && !empty($description) && !empty($start_date) && !empty($due_date) && !empty($priority) && !empty($user_id) && !empty($status)) {
        $sql_edit = "UPDATE tasks SET user_id=?, task_name=?, description=?, start_date=?, due_date=?, priority=?, status=? WHERE id=?";
        $stmt = $conn->prepare($sql_edit);
        $stmt->bind_param("issssssi", $user_id, $title, $description, $start_date, $due_date, $priority, $status, $task_id);
        
        if ($stmt->execute()) {
            $message = 'Tarea actualizada exitosamente.';
            header("Location: manage_tasks.php?message=" . urlencode($message));
            exit();
        } else {
            $message = 'Error al actualizar la tarea: ' . $stmt->error; // Manejar el error
        }
    } else {
        $message = 'Por favor, completa todos los campos.';
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
        .modal {
            display: none; 
            position: fixed; 
            z-index: 1; 
            left: 0;
            top: 0;
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            background-color: rgba(0, 0, 0, 0.4); 
            padding-top: 60px; 
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto; 
            padding: 20px;
            border: 1px solid #888;
            width: 80%; 
            max-width: 600px; 
            border-radius: 8px; /* Esquinas redondeadas */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Sombra suave */
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        .form-group {
            margin-bottom: 15px; /* Espacio entre campos */
        }
        label {
            display: block; /* Etiquetas en bloque */
            margin-bottom: 5px; /* Espacio debajo de la etiqueta */
            font-weight: bold; /* Texto en negrita */
        }
        input[type="text"],
        input[type="date"],
        textarea,
        select {
            width: 100%; /* Ancho completo */
            padding: 10px; /* Espaciado interno */
            border: 1px solid #ccc; /* Borde gris */
            border-radius: 4px; /* Esquinas redondeadas */
        }
        textarea {
            resize: vertical; /* Permitir redimensionar verticalmente */
        }
        .btn-submit {
            background-color: #28a745; /* Color verde */
            color: white; /* Texto blanco */
            padding: 10px 15px; /* Espaciado interno */
            border: none; /* Sin borde */
            border-radius: 4px; /* Esquinas redondeadas */
            cursor: pointer; /* Cambia el cursor al pasar por encima */
        }
        .btn-submit:hover {
            background-color: #218838; /* Color más oscuro al pasar el mouse */
        }
        .btn-cancel {
    background-color: #dc3545; /* Color rojo */
    color: white; /* Texto blanco */
    padding: 10px 15px; /* Espaciado interno */
    border: none; /* Sin borde */
    border-radius: 4px; /* Esquinas redondeadas */
    cursor: pointer; /* Cambia el cursor al pasar por encima */
    margin-left: 10px; /* Espacio a la izquierda */
}

.btn-cancel:hover {
    background-color: #c82333; /* Color más oscuro al pasar el mouse */
}
.btn-filter {
    background-color: #28a745; /* Color verde */
    color: white; /* Texto blanco */
    padding: 10px 15px; /* Espaciado interno */
    border: none; /* Sin borde */
    border-radius: 4px; /* Esquinas redondeadas */
    cursor: pointer; /* Cambia el cursor al pasar por encima */
    transition: background-color 0.3s; /* Transición suave para el color de fondo */
}

.btn-filter:hover {
    background-color: #218838; /* Color más oscuro al pasar el mouse */
}

    </style>
</head>
<body>

    <?php include 'header_admin.php'; ?>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="container">
            <h1>ADMINISTRAR TAREAS</h1>

            <?php if (!empty($message)): ?>
                <div class="alert"><?php echo $message; ?></div>
            <?php endif; ?>

            <h2>Notificaciones</h2>
            <button id="createNotificationModalBtn" class="btn-add"><i class="fas fa-plus"></i> Crear Notificación</button><table>
                    <tr>
                        <th>ID</th>
                        <th>Mensaje</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                    <?php if ($result_notifications->num_rows > 0): ?>
                        <?php while ($notification = $result_notifications->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $notification['id']; ?></td>
                            <td><?php echo htmlspecialchars($notification['message']); ?></td>
                            <td><?php echo htmlspecialchars($notification['created_at']); ?></td>
                            <td>
                                <form method="POST" action="manage_tasks.php" style="display:inline;">
                                    <input type="hidden" name="delete_notification_id" value="<?php echo $notification['id']; ?>">
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

<!-- Modal para editar tarea -->
<div id="editTaskModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Editar Tarea</h2>
        <form method="POST" action="manage_tasks.php">
            <input type="hidden" name="task_id" value="">
            <div class="form-group">
                <label>Título:</label>
                <input type="text" name="title" required>
            </div>
            <div class="form-group">
                <label>Descripción:</label>
                <textarea name="description" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label>Fecha de Inicio:</label>
                <input type="date" name="start_date" required>
            </div>
            <div class="form-group">
                <label>Fecha Límite:</label>
                <input type="date" name="due_date" required>
            </div>
            <div class="form-group">
                <label>Prioridad:</label>
                <select name="priority" required>
                    <option value="alta">Alta</option>
                    <option value="media">Media</option>
                    <option value="baja">Baja</option>
                </select>
            </div>
            <div class="form-group">
                <label>Usuario Asignado:</label>
                <select name="user_id" required>
                    <?php while ($user = $result_users->fetch_assoc()): ?>
                        <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['username']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Estado:</label>
                <select name="status" required>
                    <option value="pendiente">Pendiente</option>
                    <option value="en progreso">En Progreso</option>
                    <option value="completada">Completada</option>
                </select>
            </div>
            <button type="submit" name="edit_task" class="btn-submit">Actualizar Tarea</button>
            <button type="button" class="btn-cancel" onclick="closeModal()">Cancelar</button>

        </form>
    </div>
</div>


            <button id="createTaskModalBtn" class="btn-add"><i class="fas fa-plus"></i> Crear Tarea</button>
            <button id="filterModalBtn" class="btn-add"><i class="fas fa-filter"></i> Filtrar Tareas</button>

            <!-- Modal para filtrar tareas -->
            <div id="filterModal" class="modal">
                <div class="modal-content">
                    <span class="close-filter">&times;</span>
                    <h2>Filtrar Tareas por Fecha</h2>
                    <form method="GET" action="manage_tasks.php">
                        <label>Desde:</label>
                        <input type="date" name="start_filter" required>
                        <label>Hasta:</label>
                        <input type="date" name="end_filter" required>
                        
                        <button type="submit" class="btn-filter">Filtrar</button>
                        <button type="button" class="btn-cancel" onclick="document.getElementById('filterModal').style.display='none'">Cancelar</button>
                    </form>
                </div>
            </div>

            <!-- Modal para crear tarea -->
            <div id="createTaskModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Crear Nueva Tarea</h2>
        <form method="POST" action="manage_tasks.php">
            <div class="form-group">
                <label>Título:</label>
                <input type="text" name="title" required>
            </div>
            <div class="form-group">
                <label>Descripción:</label>
                <textarea name="description" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label>Fecha de Inicio:</label>
                <input type="date" name="start_date" required>
            </div>
            <div class="form-group">
                <label>Fecha Límite:</label>
                <input type="date" name="due_date" required>
            </div>
            <div class="form-group">
                <label>Prioridad:</label>
                <select name="priority" required>
                    <option value="alta">Alta</option>
                    <option value="media">Media</option>
                    <option value="baja">Baja</option>
                </select>
            </div>
            <div class="form-group">
                <label>Usuario Asignado:</label>
                <select name="user_id" required>
                    <?php if ($result_users && $result_users->num_rows > 0): ?>
                        <?php while ($user = $result_users->fetch_assoc()): ?>
                            <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['username']); ?></option>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <option value="">No hay usuarios disponibles</option>
                    <?php endif; ?>
                </select>
            </div>
            <button type="submit" name="create_task" class="btn-submit">Crear Tarea</button>
            <button type="button" class="btn-cancel" onclick="document.getElementById('createTaskModal').style.display='none'">Cancelar</button>
        </form>
    </div>
</div>





            <h2>Tareas Asignadas</h2>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Descripción</th>
                    <th>Usuario Asignado</th>
                    <th>Fecha Inicio</th>
                    <th>Fecha Límite</th>
                    <th>Prioridad</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
                <?php while ($task = $result_tasks->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $task['id']; ?></td>
                        <td><?php echo htmlspecialchars($task['task_name']); ?></td>
                        <td><?php echo htmlspecialchars($task['description']); ?></td>
                        <td>
                            <?php
                            $user_sql = "SELECT username FROM users WHERE id = " . $task['user_id'];
                            $user_result = $conn->query($user_sql);
                            $user = $user_result->fetch_assoc();
                            echo $user ? htmlspecialchars($user['username']) : 'No asignado';
                            ?>
                        </td>
                        <td><?php echo $task['start_date']; ?></td>
                        <td><?php echo $task['due_date']; ?></td>
                        <td><?php echo htmlspecialchars($task['priority']); ?></td>
                        <td><?php echo htmlspecialchars($task['status']); ?></td>
                        <td>
                            <button class="edit-btn" 
                                   data-id="<?php echo $task['id']; ?>" 
                                   data-title="<?php echo htmlspecialchars($task['task_name']); ?>" 
                                   data-description="<?php echo htmlspecialchars($task['description']); ?>" 
                                   data-start-date="<?php echo $task['start_date']; ?>" 
                                   data-due-date="<?php echo $task['due_date']; ?>" 
                                   data-priority="<?php echo $task['priority']; ?>" 
                                   data-user-id="<?php echo $task['user_id']; ?>" 
                                   data-status="<?php echo htmlspecialchars($task['status']); ?>" 
                                   title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="delete-btn" data-id="<?php echo $task['id']; ?>" title="Eliminar">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        // Abrir el modal para crear tarea
        document.getElementById('createTaskModalBtn').onclick = function() {
            document.getElementById('createTaskModal').style.display = 'block';
        }

        // Abrir el modal para filtrar tareas
        document.getElementById('filterModalBtn').onclick = function() {
            document.getElementById('filterModal').style.display = 'block';
        }

        // Cerrar el modal de crear tarea
        document.getElementsByClassName('close')[0].onclick = function() {
            document.getElementById('createTaskModal').style.display = 'none';
        }

        // Cerrar el modal de filtrar tareas
        document.getElementsByClassName('close-filter')[0].onclick = function() {
            document.getElementById('filterModal').style.display = 'none';
        }

        // Cerrar los modales al hacer clic fuera de ellos
        window.onclick = function(event) {
            if (event.target == document.getElementById('createTaskModal')) {
                document.getElementById('createTaskModal').style.display = 'none';
            }
            if (event.target == document.getElementById('filterModal')) {
                document.getElementById('filterModal').style.display = 'none';
            }
        }

        
        // Manejo de la edición de tareas
const editBtns = document.querySelectorAll('.edit-btn');
editBtns.forEach(btn => {
    btn.onclick = function() {
        const taskId = this.dataset.id;
        const title = this.dataset.title;
        const description = this.dataset.description;
        const startDate = this.dataset.startDate;
        const dueDate = this.dataset.dueDate;
        const priority = this.dataset.priority;
        const userId = this.dataset.userId;
        const status = this.dataset.status;

        // Cambia aquí para abrir el modal de edición
        document.getElementById('editTaskModal').style.display = 'block'; // Cambia 'createTaskModal' a 'editTaskModal'
        document.querySelector('input[name="task_id"]').value = taskId; // Asigna el ID a un campo oculto
        document.querySelector('input[name="title"]').value = title;
        document.querySelector('textarea[name="description"]').value = description;
        document.querySelector('input[name="start_date"]').value = startDate;
        document.querySelector('input[name="due_date"]').value = dueDate;
        document.querySelector('select[name="priority"]').value = priority;
        document.querySelector('select[name="user_id"]').value = userId;
        document.querySelector('select[name="status"]').value = status; // Asignar estado
        document.querySelector('button[type="submit"]').name = 'edit_task'; // Cambia el nombre del botón
    };
});
  // Función para cerrar el modal
  function closeModal() {
        document.getElementById('editTaskModal').style.display = 'none';
    }

        // Manejo de la eliminación de tareas
        const deleteBtns = document.querySelectorAll('.delete-btn');
        deleteBtns.forEach(btn => {
            btn.onclick = function() {
                const taskId = this.dataset.id;
                if (confirm("¿Estás seguro de que deseas eliminar esta tarea?")) {
                    window.location.href = 'delete_task.php?id=' + taskId; // Llama al archivo para eliminar la tarea
                }
            };
        });
    </script>

</body>
</html>
