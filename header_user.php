<header>
    <div class="container">
        <h1>Tareas</h1>
        <div class="user-info">
            <span><?php echo $_SESSION['user']; ?></span> <!-- Muestra el nombre del usuario conectado -->
            <a href="profile.php" class="settings-icon" title="Configuración">
                <i class="fas fa-cog"></i> <!-- Icono de engranaje -->
            </a>
            <a href="logout.php" class="logout-icon" title="Cerrar Sesión">
                <i class="fas fa-sign-out-alt"></i> <!-- Icono de cerrar sesión -->
            </a>
        </div>
    </div>
</header>
