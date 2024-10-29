<?php
// insert_users.php

// Conexión a la base de datos
include 'db_connection.php'; // Asegúrate de tener el archivo de conexión

// Datos para el usuario
$username_user = 'usuario';
$password_user = password_hash('123', PASSWORD_DEFAULT);
$role_user = 'user';

// Datos para el administrador
$username_admin = 'admin';
$password_admin = password_hash('123', PASSWORD_DEFAULT);
$role_admin = 'admin';

// Inserción de usuario
$sql_user = "INSERT INTO users (username, password, role, created_at) VALUES (?, ?, ?, NOW())";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("sss", $username_user, $password_user, $role_user);
$stmt_user->execute();

// Inserción de administrador
$sql_admin = "INSERT INTO users (username, password, role, created_at) VALUES (?, ?, ?, NOW())";
$stmt_admin = $conn->prepare($sql_admin);
$stmt_admin->bind_param("sss", $username_admin, $password_admin, $role_admin);
$stmt_admin->execute();

// Cierra la conexión
$stmt_user->close();
$stmt_admin->close();
$conn->close();

echo "Usuarios insertados con éxito.";
?>
