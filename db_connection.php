<?php
$servername = "localhost"; // Cambia esto si es necesario
$username = "root"; // Cambia esto a tu usuario de la base de datos
$password = ""; // Cambia esto a tu contraseña de la base de datos
$dbname = "tareas"; // Cambia esto a tu nombre de la base de datos

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>
