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

// Manejar edición de tarea
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
        // Cambia el tipo de datos según el tipo esperado en la base de datos
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
        .modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 20px;
    border: 1px solid #888;
    border-radius: 8px; /* Esquinas redondeadas */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Sombra suave */
    width: 90%; /* Ancho más responsive */
    max-width: 600px; /* Máximo ancho */
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
                        <button type="submit">Filtrar</button>
                    </form>
                </div>
            </div>

           
          <!-- Modal para crear tarea -->
<div id="createTaskModal" class="modal">
    <div class="modal-content">
        <span class="close-create">&times;</span>
        <h2>Crear Nueva Tarea</h2>
        <form method="POST" action="manage_tasks.php">
            <input type="hidden" name="create_task" value="1">
            <div class="form-group">
                <label for="title">Título:</label>
                <input type="text" name="title" placeholder="Título de la tarea" required>
            </div>
            <div class="form-group">
                <label for="description">Descripción:</label>
                <textarea name="description" placeholder="Descripción de la tarea" required></textarea>
            </div>
            <div class="form-group">
                <label for="start_date">Fecha de Inicio:</label>
                <input type="date" name="start_date" required>
            </div>
            <div class="form-group">
                <label for="due_date">Fecha Límite:</label>
                <input type="date" name="due_date" required>
            </div>
            <div class="form-group">
                <label for="priority">Prioridad:</label>
                <select name="priority" required>
                    <option value="">Selecciona la prioridad</option>
                    <option value="alta">Alta</option>
                    <option value="media">Media</option>
                    <option value="baja">Baja</option>
                </select>
            </div>
            <div class="form-group">
             <label for="status">Estado:</label>
                <select name="status" required>
                    <option value="">Selecciona el estado</option>
                    <option value="nueva">Nueva</option> <!-- Estado por defecto -->
                    <option value="pendiente">Pendiente</option>
                    <option value="en progreso">En Progreso</option>
                    <option value="completada">Completada</option>
                </select>
        </div>

            <div class="form-group">
                <label for="user_id">Usuario Asignado:</label>
                <select name="user_id" required>
                    <option value="">Selecciona un usuario</option>
                    <?php
                    $result_users->data_seek(0); // Reiniciar el puntero del resultado
                    while ($user = $result_users->fetch_assoc()): ?>
                        <option value="<?php echo $user['id']; ?>"><?php echo $user['username']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" class="btn-submit">Crear Tarea</button>
        </form>
    </div>
</div>

           <!-- Modal para editar tarea -->
<div id="editTaskModal" class="modal">
    <div class="modal-content">
        <span class="close-edit">&times;</span>
        <h2>Editar Tarea</h2>
        <form method="POST" action="manage_tasks.php">
            <input type="hidden" name="task_id" id="edit_task_id">
            <div class="form-group">
                <label for="edit_title">Título:</label>
                <input type="text" name="title" id="edit_title" placeholder="Título de la tarea" required>
            </div>
            <div class="form-group">
                <label for="edit_description">Descripción:</label>
                <textarea name="description" id="edit_description" placeholder="Descripción de la tarea" required></textarea>
            </div>
            <div class="form-group">
                <label for="edit_start_date">Fecha de Inicio:</label>
                <input type="date" name="start_date" id="edit_start_date" required>
            </div>
            <div class="form-group">
                <label for="edit_due_date">Fecha Límite:</label>
                <input type="date" name="due_date" id="edit_due_date" required>
            </div>
            <div class="form-group">
                <label for="edit_priority">Prioridad:</label>
                <select name="priority" id="edit_priority" required>
                    <option value="">Selecciona la prioridad</option>
                    <option value="alta">Alta</option>
                    <option value="media">Media</option>
                    <option value="baja">Baja</option>
                </select>
            </div>
                        <div class="form-group">
                <label for="status">Estado:</label>
                <select name="status" required>
                    <option value="">Selecciona el estado</option>
                    <option value="nueva">Nueva</option> <!-- Estado por defecto -->
                    <option value="pendiente">Pendiente</option>
                    <option value="en progreso">En Progreso</option>
                    <option value="completada">Completada</option>
                </select>
            </div>

            <div class="form-group">
                <label for="edit_user_id">Usuario Asignado:</label>
                <select name="user_id" id="edit_user_id" required>
                    <option value="">Selecciona un usuario</option>
                    <?php
                    // Reiniciar el puntero del resultado
                    $result_users->data_seek(0); 
                    while ($user = $result_users->fetch_assoc()): ?>
                        <option value="<?php echo $user['id']; ?>"><?php echo $user['username']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" name="edit_task" class="btn-submit">Guardar Cambios</button>
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
                        <a href="#" class="edit-btn" 
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
                        </a>
                        
    <button class="delete-btn" data-id="<?php echo $task['id']; ?>" title="Eliminar">
        <i class="fas fa-trash-alt"></i>
    </button>
</td>
<!-- Se agregó el estado para el modal de edición -->


                               
                            </button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
         document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function() {
            const taskId = this.getAttribute('data-id');

            if (confirm('¿Estás seguro de que quieres eliminar esta tarea?')) {
                fetch('delete_task.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id=${taskId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        // Eliminar la fila de la tabla
                        this.closest('tr').remove();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }
        });
    });

    var filterModal = document.getElementById("filterModal");
    var createTaskModal = document.getElementById("createTaskModal");
    var editTaskModal = document.getElementById("editTaskModal");
    var btnFilterModal = document.getElementById("filterModalBtn");
    var btnCreateTaskModal = document.getElementById("createTaskModalBtn");
    var spanCloseFilter = document.getElementsByClassName("close-filter")[0];
    var spanCloseCreate = document.getElementsByClassName("close-create")[0];
    var spanCloseEdit = document.getElementsByClassName("close-edit")[0];

    btnFilterModal.onclick = function() {
        filterModal.style.display = "block";
    }

    btnCreateTaskModal.onclick = function() {
        createTaskModal.style.display = "block";
    }

    spanCloseFilter.onclick = function() {
        filterModal.style.display = "none";
    }

    spanCloseCreate.onclick = function() {
        createTaskModal.style.display = "none";
    }

    spanCloseEdit.onclick = function() {
        editTaskModal.style.display = "none";
    }

    window.onclick = function(event) {
        if (event.target == filterModal) {
            filterModal.style.display = "none";
        }
        if (event.target == createTaskModal) {
            createTaskModal.style.display = "none";
        }
        if (event.target == editTaskModal) {
            editTaskModal.style.display = "none";
        }
    }

    // Lógica para editar tarea
    const editBtns = document.querySelectorAll('.edit-btn');
editBtns.forEach(btn => {
    btn.addEventListener('click', function() {
        const taskId = this.getAttribute('data-id');
        const title = this.getAttribute('data-title');
        const description = this.getAttribute('data-description');
        const startDate = this.getAttribute('data-start-date');
        const dueDate = this.getAttribute('data-due-date');
        const priority = this.getAttribute('data-priority');
        const userId = this.getAttribute('data-user-id');
        const status = this.getAttribute('data-status'); // Agrega esta línea

        document.getElementById('edit_task_id').value = taskId;
        document.getElementById('edit_title').value = title;
        document.getElementById('edit_description').value = description;
        document.getElementById('edit_start_date').value = startDate;
        document.getElementById('edit_due_date').value = dueDate;
        document.getElementById('edit_priority').value = priority;
        document.getElementById('edit_user_id').value = userId;
        document.querySelector('select[name="status"]').value = status; // Actualiza el estado en el select

        editTaskModal.style.display = "block";
    });
});

    </script>

</body>
</html>
