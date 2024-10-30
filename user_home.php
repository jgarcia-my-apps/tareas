<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Asegúrate de tener el ID del usuario en la sesión
$message = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : '';

// Obtener tareas solo para el usuario logueado
$sql_tasks = "SELECT * FROM tasks WHERE user_id = ?";
$stmt = $conn->prepare($sql_tasks);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_tasks = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Página de Usuario</title>
    <link rel="stylesheet" href="styles/user_home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
        }
        .main-content {
            padding: 20px;
        }
        h1 {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        .edit-btn {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .edit-btn:hover {
            background-color: #218838;
        }
        .modal_editar {
            display: none; /* Ocultar por defecto */
            position: fixed; /* Fijo en la ventana */
            z-index: 1000; /* Encima de otros elementos */
            left: 50%; /* Centrar horizontalmente */
            top: 50%; /* Centrar verticalmente */
            transform: translate(-50%, -50%); /* Ajustar para centrar realmente */
            width: 500px; /* Ancho del modal */
            background-color: white; /* Color de fondo */
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); /* Sombra */
            border-radius: 8px; /* Bordes redondeados */
        }
        .modal-content {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            width: 400px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            position: relative;
        }
        .close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 24px;
            cursor: pointer;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"],
        textarea,
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .btn-submit {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s;
        }
        .btn-submit:hover {
            background-color: #0056b3;
        }
        /* Estilos del modal */
.modal {
    display: none; /* Ocultar por defecto */
    position: fixed; /* Fijo en la ventana */
    z-index: 1000; /* Encima de otros elementos */
    left: 50%; /* Centrar horizontalmente */
    top: 50%; /* Centrar verticalmente */
    transform: translate(-50%, -50%); /* Ajustar para centrar realmente */
    width: 500px; /* Ancho del modal */
    background-color: white; /* Color de fondo */
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); /* Sombra */
    border-radius: 8px; /* Bordes redondeados */
}

/* Fondo del modal */
.modal-overlay {
    display: none; /* Ocultar por defecto */
    position: fixed; /* Fijo en la ventana */
    top: 0; /* Cubrir toda la ventana */
    left: 0;
    width: 100%; /* Ancho completo */
    height: 100%; /* Altura completa */
    background-color: rgba(0, 0, 0, 0.5); /* Fondo semitransparente */
    z-index: 999; /* Debajo del modal */
}

    </style>
</head>

<body>

    <?php include 'sidebar_users.php'; ?>
    <?php include 'header_user.php'; ?>

    <div class="main-content">
        <div class="container">
            <h1>Bienvenido, <?php echo $_SESSION['user']; ?>!</h1>
            <p>Aquí puedes gestionar tus tareas.</p>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Descripción</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
                <?php while ($task = $result_tasks->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $task['id']; ?></td>
                    <td><?php echo htmlspecialchars($task['task_name']); ?></td>
                    <td><?php echo htmlspecialchars($task['description']); ?></td>
                    <td><?php echo htmlspecialchars($task['status']); ?></td>
                    <td>
                        <button class="edit-btn" 
                                data-id="<?php echo $task['id']; ?>" 
                                data-title="<?php echo htmlspecialchars($task['task_name']); ?>" 
                                data-description="<?php echo htmlspecialchars($task['description']); ?>" 
                                data-status="<?php echo htmlspecialchars($task['status']); ?>">
                            Editar
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>

            <!-- Modal para editar tarea -->
            <div id="editTaskModal" style="display:none;" class="modal_editar">
                <div class="modal-content">
                    <span class="close" onclick="closeModal()">&times;</span>
                    <h2>Editar Tarea</h2>
                    <form id="editTaskForm" method="POST" action="edit_task.php">
                        <input type="hidden" id="edit_task_id" name="id">
                        <div class="form-group">
                            <label for="edit_title">Título:</label>
                            <input type="text" id="edit_title" name="title" readonly>
                        </div>
                        <div class="form-group">
                            <label for="edit_description">Descripción:</label>
                            <textarea id="edit_description" name="description" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="edit_status">Estado:</label>
                            <select id="edit_status" name="status">
                                <option value="Pendiente">Pendiente</option>
                                <option value="En progreso">En progreso</option>
                                <option value="Completada">Completada</option>
                            </select>
                        </div>
                        <button type="submit" class="btn-submit">Guardar Cambios</button>
                    </form>
                </div>
            </div>
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

    // Lógica para editar tarea
    const editBtns = document.querySelectorAll('.edit-btn');
    editBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const taskId = this.getAttribute('data-id');
            const title = this.getAttribute('data-title');
            const description = this.getAttribute('data-description');
            const status = this.getAttribute('data-status');

            document.getElementById('edit_task_id').value = taskId;
            document.getElementById('edit_title').value = title;
            document.getElementById('edit_description').value = description;
            document.getElementById('edit_status').value = status;

            document.getElementById('editTaskModal').style.display = "block";
        });
    });

    // Cerrar el modal al hacer clic fuera
    window.onclick = function(event) {
        if (event.target == document.getElementById('editTaskModal')) {
            document.getElementById('editTaskModal').style.display = "none";
        }
    };

    function closeModal() {
        document.getElementById('editTaskModal').style.display = "none";
    }
    </script>
</body>
</html>
