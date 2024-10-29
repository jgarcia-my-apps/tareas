<!-- sidebar.php -->
<style>
    /* Estilos para el sidebar */
    .sidebar {
        width: 250px; /* Ancho fijo para el sidebar */
        height: 100vh; /* Altura completa de la ventana */
        background-color: #007BFF; /* Color de fondo del sidebar */
        color: white; /* Color del texto */
        padding: 20px; /* Espaciado interno */
        border-radius: 5px 0 0 5px; /* Bordes redondeados solo en el lado izquierdo */
        box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1); /* Sombra ligera a la derecha */
        position: fixed; /* Fijo para que no se desplace con el scroll */
    }

    .sidebar h2 {
        margin-bottom: 15px; /* Espacio inferior en encabezados del sidebar */
    }

    .sidebar ul {
        list-style: none; /* Sin viñetas */
        padding: 0; /* Sin padding */
    }

    .sidebar li {
        margin: 10px 0; /* Espacio entre los elementos de la lista */
    }

    .sidebar a {
        color: white; /* Color de los enlaces en el sidebar */
        text-decoration: none; /* Sin subrayado */
        display: block; /* Que ocupe todo el ancho */
    }

    .sidebar a:hover {
        text-decoration: underline; /* Subrayar al pasar el mouse */
    }
</style>

<div class="sidebar">
    <h2>Panel de Control</h2>
    <ul>       
        <li><a href="list_tasks.php">Mis Tareas</a></li>      
        <li><a href="logout.php">Cerrar Sesión</a></li>
    </ul>
</div>
