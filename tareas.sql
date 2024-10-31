-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 31-10-2024 a las 04:26:39
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `tareas`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `message` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `is_read`, `created_at`) VALUES
(13, 10, 'La tarea \'oooooo\' ha sido marcada como completada.', 0, '2024-10-31 02:12:15');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `start_date` date DEFAULT NULL,
  `task_name` varchar(255) NOT NULL,
  `status` enum('Nueva','Pendiente','asignada','completada','En Progreso') DEFAULT 'asignada',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `description` text DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `priority` enum('alta','media','baja') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tasks`
--

INSERT INTO `tasks` (`id`, `user_id`, `start_date`, `task_name`, `status`, `created_at`, `updated_at`, `description`, `due_date`, `priority`) VALUES
(41, 29, '2024-10-30', 'adads', 'Pendiente', '2024-10-30 20:54:59', '2024-10-31 02:40:12', 'editada y lista la tarea', '2024-10-30', 'media'),
(42, 29, '2024-10-30', 'adads', 'completada', '2024-10-30 20:57:56', '2024-10-31 01:27:50', 'adadasd', '2024-10-30', 'alta'),
(43, 10, '2024-10-30', 'adads', 'asignada', '2024-10-30 20:58:49', '2024-10-30 20:58:49', 'adadasd', '2024-10-30', 'alta'),
(44, 10, '2024-10-30', 'adads', 'asignada', '2024-10-30 20:58:54', '2024-10-30 20:58:54', 'adadasd', '2024-10-30', 'alta'),
(45, 30, '2024-10-30', 'adadd', 'asignada', '2024-10-30 21:32:29', '2024-10-30 22:06:28', 'adad', '2024-10-18', 'media'),
(46, 29, '2024-10-30', 'oooooo', 'completada', '2024-10-31 01:38:23', '2024-10-31 02:12:15', 'completada', '2024-10-30', 'baja'),
(47, 29, '2024-10-30', 'ultima', 'asignada', '2024-10-31 02:51:22', '2024-10-31 02:51:22', 'prueba', '2024-10-30', 'baja'),
(48, 29, '2024-10-30', 'ultima', 'asignada', '2024-10-31 02:52:56', '2024-10-31 02:52:56', 'prueba', '2024-10-30', 'baja'),
(49, 29, '2024-10-30', 'ultima', 'asignada', '2024-10-31 03:01:02', '2024-10-31 03:01:02', 'prueba', '2024-10-30', 'baja');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `photo` varchar(255) DEFAULT 'uploads/default-avatar.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `profile_picture`, `password`, `role`, `created_at`, `photo`) VALUES
(10, 'admin', 'user@example.com', NULL, '$2y$10$/B.88Er3dLDdHIzK76SgI..wHSSYEqlXLhibHCy.gqqGTN2ntPzUq', 'admin', '2024-10-28 02:24:18', 'uploads/'),
(29, 'Jose', 'prueba@gmail.com', NULL, '$2y$10$jGIZeBLwq85zS4cfT8skgO/sJqRwgEefsTVT37su1qazHu/IlFIjS', 'user', '2024-10-29 19:32:54', 'uploads/default-avatar.png'),
(30, 'user', 'gestarsoft@gmail.com', NULL, '$2y$10$15Uptjhy6WK8PFeyi7/mPOBpcsNI4LkBF7QFtiVU5BqGMF3JxRw6G', 'user', '2024-10-29 19:57:03', 'uploads/default-avatar.png');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
