<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'db_connection.php';

// Obtener usuarios
$sql_users = "SELECT * FROM users";
$result_users = $conn->query($sql_users);

// Mensaje de éxito o error
$message = '';
if (isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
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

    if (!empty($task_id) && !empty($title) && !empty($description) && !empty($start_date) && !empty($due_date) && !empty($priority) && !empty($user_id)) {
        $sql_edit = "UPDATE tasks SET user_id=?, task_name=?, description=?, start_date=?, due_date=?, priority=? WHERE id=?";
        $stmt = $conn->prepare($sql_edit);
        $stmt->bind_param("isssssi", $user_id, $title, $description, $start_date, $due_date, $priority, $task_id);
        $stmt->execute();

        header("Location: admin_tasks.php?message=Tarea editada correctamente");
        exit();
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
    </style>
</head>
<body>

    <?php include 'header_admin.php'; ?>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="container">
            <h1>ADMINISTRAR TAREAS</h1>

            <?php if ($message): ?>
                <div class="alert"><?php echo $message; ?></div>
            <?php endif; ?>

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

            <!-- Modal para editar tarea -->
            <div id="editTaskModal" class="modal">
                <div class="modal-content">
                    <span class="close-edit">&times;</span>
                    <h2>Editar Tarea</h2>
                    <form method="POST" action="manage_tasks.php">
                        <input type="hidden" name="task_id" id="edit_task_id">
                        <input type="text" name="title" id="edit_title" placeholder="Título de la tarea" required>
                        <textarea name="description" id="edit_description" placeholder="Descripción de la tarea" required></textarea>
                        <input type="date" name="start_date" id="edit_start_date" required>
                        <input type="date" name="due_date" id="edit_due_date" required>
                        <select name="priority" id="edit_priority" required>
                            <option value="">Selecciona la prioridad</option>
                            <option value="alta">Alta</option>
                            <option value="media">Media</option>
                            <option value="baja">Baja</option>
                        </select>
                        <select name="user_id" id="edit_user_id" required>
                            <option value="">Selecciona un usuario</option>
                            <?php
                            $result_users->data_seek(0); // Reiniciar el puntero del resultado
                            while ($user = $result_users->fetch_assoc()): ?>
                                <option value="<?php echo $user['id']; ?>"><?php echo $user['username']; ?></option>
                            <?php endwhile; ?>
                        </select>
                        <button type="submit" name="edit_task">Guardar Cambios</button>
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
                        <td><?php echo $task['task_name']; ?></td>
                        <td><?php echo $task['description']; ?></td>
                        <td>
                            <?php
                            $user_sql = "SELECT username FROM users WHERE id = " . $task['user_id'];
                            $user_result = $conn->query($user_sql);
                            $user = $user_result->fetch_assoc();
                            echo $user ? $user['username'] : 'No asignado';
                            ?>
                        </td>
                        <td><?php echo $task['start_date']; ?></td>
                        <td><?php echo $task['due_date']; ?></td>
                        <td><?php echo $task['priority']; ?></td>
                        <td><?php echo $task['status']; ?></td>
                        <td>
                            <a href="#" class="edit-btn" data-id="<?php echo $task['id']; ?>" data-title="<?php echo htmlspecialchars($task['task_name']); ?>" data-description="<?php echo htmlspecialchars($task['description']); ?>" data-start-date="<?php echo $task['start_date']; ?>" data-due-date="<?php echo $task['due_date']; ?>" data-priority="<?php echo $task['priority']; ?>" data-user-id="<?php echo $task['user_id']; ?>" title="Editar"><i class="fas fa-edit"></i></a>
                            <form action="delete_task.php" method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo $task['id']; ?>">
                                <button type="submit" title="Eliminar" onclick="return confirm('¿Estás seguro de que quieres eliminar esta tarea?');">
                                    <i class="fas fa-trash-alt"></i>
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
    var filterModal = document.getElementById("filterModal");
    var editTaskModal = document.getElementById("editTaskModal");
    var btnFilterModal = document.getElementById("filterModalBtn");
    var spanCloseFilter = document.getElementsByClassName("close-filter")[0];
    var spanCloseEdit = document.getElementsByClassName("close-edit")[0];

    btnFilterModal.onclick = function() {
        filterModal.style.display = "block";
    }

    spanCloseFilter.onclick = function() {
        filterModal.style.display = "none";
    }

    spanCloseEdit.onclick = function() {
        editTaskModal.style.display = "none";
    }

    window.onclick = function(event) {
        if (event.target == filterModal) {
            filterModal.style.display = "none";
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

            document.getElementById('edit_task_id').value = taskId;
            document.getElementById('edit_title').value = title;
            document.getElementById('edit_description').value = description;
            document.getElementById('edit_start_date').value = startDate;
            document.getElementById('edit_due_date').value = dueDate;
            document.getElementById('edit_priority').value = priority;
            document.getElementById('edit_user_id').value = userId;

            editTaskModal.style.display = "block";
        });
    });
    </script>

</body>
</html>
