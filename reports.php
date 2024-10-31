<?php
require_once 'db_connection.php'; // Conexión a la base de datos

// Consulta para obtener las tareas con el nombre del usuario
$query = "
    SELECT t.id, u.username, t.start_date, t.task_name, t.status, t.created_at, t.updated_at, t.description, t.due_date, t.priority 
    FROM tasks t 
    JOIN users u ON t.user_id = u.id
";
$result = $conn->query($query);

// Verificar la conexión y si hay tareas
if (!$result) {
    die("Error en la consulta: " . $conn->error);
}

// Descargar en PDF o Excel
if (isset($_GET['download'])) {
    $format = $_GET['download'];
    if ($format == 'pdf') {
        generatePDF($result);
    } elseif ($format == 'excel') {
        generateExcel($result);
    }
}

// Función para generar el PDF
function generatePDF($result)
{
    require('fpdf186/fpdf.php');
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 12);

    // Encabezados
    $pdf->Cell(20, 10, 'ID', 1);
    $pdf->Cell(30, 10, 'Usuario', 1);
    $pdf->Cell(30, 10, 'Fecha Inicio', 1);
    $pdf->Cell(50, 10, 'Nombre Tarea', 1);
    $pdf->Cell(30, 10, 'Estado', 1);
    $pdf->Cell(30, 10, 'Prioridad', 1);
    $pdf->Ln();

    // Datos
    while ($row = $result->fetch_assoc()) {
        $pdf->Cell(20, 10, $row['id'], 1);
        $pdf->Cell(30, 10, $row['username'], 1); // Cambiar a 'username'
        $pdf->Cell(30, 10, $row['start_date'], 1);
        $pdf->Cell(50, 10, $row['task_name'], 1);
        $pdf->Cell(30, 10, $row['status'], 1);
        $pdf->Cell(30, 10, $row['priority'], 1);
        $pdf->Ln();
    }
    $pdf->Output('D', 'reporte_tareas.pdf');
    exit;
}

// Función para generar el archivo Excel
function generateExcel($result)
{
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=reporte_tareas.xls");

    echo "ID\tUsuario\tFecha Inicio\tNombre Tarea\tEstado\tPrioridad\n";
    while ($row = $result->fetch_assoc()) {
        echo "{$row['id']}\t{$row['username']}\t{$row['start_date']}\t{$row['task_name']}\t{$row['status']}\t{$row['priority']}\n"; // Cambiar a 'username'
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Tareas</title>
    <link rel="stylesheet" href="styles/admin_users.css">
</head>
<body>
<?php include 'header.php'; ?>
   

<!-- Botón para ir a la gestión de tareas -->
<a href="http://localhost/tareas/manage_tasks.php" style="text-decoration: none;">
    <button style="padding: 10px 15px; background-color: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer;">
        Ir a Gestión de Tareas
    </button>
</a>
 <center><h1>REPORTE DE TAREAS</h1></center>
<table>

    <thead>
        <tr>
            <th>ID</th>
            <th>Usuario</th>
            <th>Fecha de Inicio</th>
            <th>Nombre de Tarea</th>
            <th>Estado</th>
            <th>Fecha de Creación</th>
            <th>Última Actualización</th>
            <th>Descripción</th>
            <th>Fecha de Vencimiento</th>
            <th>Prioridad</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['username']; ?></td> <!-- Cambiar a 'username' -->
            <td><?php echo $row['start_date']; ?></td>
            <td><?php echo $row['task_name']; ?></td>
            <td><?php echo $row['status']; ?></td>
            <td><?php echo $row['created_at']; ?></td>
            <td><?php echo $row['updated_at']; ?></td>
            <td><?php echo $row['description']; ?></td>
            <td><?php echo $row['due_date']; ?></td>
            <td><?php echo $row['priority']; ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<!-- Botones de descarga -->
<a href="reports.php?download=pdf">Descargar PDF</a>
<a href="reports.php?download=excel">Descargar Excel</a>
<?php include 'footer.php'; ?>
</body>
</html>
