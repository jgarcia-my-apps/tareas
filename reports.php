<?php
require_once 'db_connection.php'; // Conexión a la base de datos

// Consulta para obtener las tareas con el nombre del usuario
$query = "
    SELECT t.id, u.username, t.start_date, t.creation_date, t.task_name, t.status, t.created_at, t.updated_at, t.description, t.due_date, t.priority 
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
    $pdf = new FPDF('L', 'mm', 'A3'); // Orientación horizontal y tamaño A4
    $pdf->AddPage();
    
    // Configuración del título
    $pdf->SetFont('Arial', 'B', 16); // Fuente más grande para el título
    $pdf->Cell(0, 10, utf8_decode('Reporte de Tareas'), 0, 1, 'C'); // Título centrado
    $pdf->Ln(5); // Espacio debajo del título
    
    // Configuración de los encabezados
    $pdf->SetFont('Arial', 'B', 10); // Fuente más pequeña para los encabezados
    $pdf->Cell(10, 10, utf8_decode('ID'), 1, 0, 'C');
    $pdf->Cell(40, 10, utf8_decode('Nombre Tarea'), 1, 0, 'C');
    $pdf->Cell(30, 10, utf8_decode('Usuario'), 1, 0, 'C');
    $pdf->Cell(30, 10, utf8_decode('Fecha Inicio'), 1, 0, 'C');
    $pdf->Cell(30, 10, utf8_decode('Fecha Fin'), 1, 0, 'C');
    $pdf->Cell(30, 10, utf8_decode('Estado'), 1, 0, 'C');
    $pdf->Cell(20, 10, utf8_decode('Prioridad'), 1, 0, 'C');
    $pdf->Cell(40, 10, utf8_decode('Fecha de Creación'), 1, 0, 'C');
    $pdf->Cell(40, 10, utf8_decode('Última Actualización'), 1, 0, 'C');
    $pdf->Cell(50, 10, utf8_decode('Descripción'), 1, 1, 'C'); // 1 al final para saltar de línea después de esta celda

    // Datos de cada tarea
    $pdf->SetFont('Arial', '', 8);
    while ($row = $result->fetch_assoc()) {
        $pdf->Cell(10, 10, $row['id'], 1);
        $pdf->Cell(40, 10, utf8_decode($row['task_name']), 1);
        $pdf->Cell(30, 10, utf8_decode($row['username']), 1);
        $pdf->Cell(30, 10, $row['start_date'], 1);
        $pdf->Cell(30, 10, $row['creation_date'], 1);
        $pdf->Cell(30, 10, utf8_decode($row['status']), 1);
        $pdf->Cell(20, 10, utf8_decode($row['priority']), 1);
        $pdf->Cell(40, 10, $row['created_at'], 1);
        $pdf->Cell(40, 10, $row['updated_at'], 1);
        
        // Descripción en varias líneas
        $pdf->MultiCell(50, 10, utf8_decode($row['description']), 1);

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
 <!-- Botones de descarga -->
<a href="reports.php?download=pdf">Descargar PDF</a>
<a href="reports.php?download=excel">Descargar Excel</a>
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


<?php include 'footer.php'; ?>
</body>
</html>
