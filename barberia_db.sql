-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 24-05-2026 a las 02:59:06
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
-- Base de datos: `barberia_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `barberos`
--

CREATE TABLE `barberos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `activo` tinyint(4) DEFAULT 1,
  `foto_url` varchar(255) DEFAULT 'https://ui-avatars.com/api/?background=333&color=fff',
  `hora_inicio` time DEFAULT '09:00:00',
  `hora_fin` time DEFAULT '17:00:00',
  `almuerzo_inicio` time DEFAULT '12:00:00',
  `almuerzo_fin` time DEFAULT '13:00:00',
  `hora_descanso_inicio` time DEFAULT NULL,
  `hora_descanso_fin` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `barberos`
--

INSERT INTO `barberos` (`id`, `nombre`, `activo`, `foto_url`, `hora_inicio`, `hora_fin`, `almuerzo_inicio`, `almuerzo_fin`, `hora_descanso_inicio`, `hora_descanso_fin`) VALUES
(1, 'Joshy', 1, 'https://randomuser.me/api/portraits/men/32.jpg', '09:00:00', '17:00:00', '12:00:00', '13:00:00', NULL, NULL),
(2, 'Carlos', 1, 'https://randomuser.me/api/portraits/men/45.jpg', '09:00:00', '17:00:00', '12:00:00', '13:00:00', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `citas`
--

CREATE TABLE `citas` (
  `id` int(11) NOT NULL,
  `barbero_id` int(11) DEFAULT NULL,
  `cliente_nombre` varchar(100) DEFAULT NULL,
  `cliente_telefono` varchar(20) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `hora` time DEFAULT NULL,
  `estado_pago` varchar(20) DEFAULT 'pendiente',
  `servicio` varchar(100) DEFAULT 'Corte General',
  `metodo_pago` varchar(50) DEFAULT 'Efectivo',
  `referencia_pago` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `citas`
--

INSERT INTO `citas` (`id`, `barbero_id`, `cliente_nombre`, `cliente_telefono`, `fecha`, `hora`, `estado_pago`, `servicio`, `metodo_pago`, `referencia_pago`) VALUES
(1, 1, 'WILSON', '04243085918', '2026-01-16', '15:00:00', 'verificado', 'Barba VIP', 'movil', '9899'),
(2, 1, 'WILSON', '04243085918', '2026-01-16', '11:00:00', 'verificado', 'Corte Degradado', 'efectivo', 'SITIO'),
(3, 1, 'WILSON MARTINEZ', '04243085918', '2026-05-20', '10:30:00', 'verificado', 'Corte Degradado', 'movil', '3214'),
(4, 1, 'WILSON MARTINEZ', '04243085918', '2026-05-21', '13:30:00', 'verificado', 'Corte Degradado', 'movil', '9898');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `puntos` int(11) DEFAULT 0,
  `ultima_visita` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id`, `telefono`, `nombre`, `puntos`, `ultima_visita`) VALUES
(1, '04243085918', 'WILSON', 4, '2026-05-23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE `configuracion` (
  `clave` varchar(50) NOT NULL,
  `valor` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`clave`, `valor`) VALUES
('banco_ci', '30322839'),
('banco_nombre', 'Banesco'),
('banco_telefono', '04243085918'),
('estado_efectivo', '1'),
('estado_movil', '1'),
('estado_zelle', '1'),
('zelle_email', 'pagos@barberia.com');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicios`
--

CREATE TABLE `servicios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `imagen` text DEFAULT NULL,
  `activo` tinyint(4) DEFAULT 1,
  `duracion` int(11) DEFAULT 30
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `servicios`
--

INSERT INTO `servicios` (`id`, `nombre`, `precio`, `imagen`, `activo`, `duracion`) VALUES
(1, 'Corte Degradado', 15.00, 'https://images.unsplash.com/photo-1621605815971-fbc98d665033?w=400', 1, 30),
(2, 'Barba VIP', 10.00, 'https://images.unsplash.com/photo-1503951914875-452162b7f30a?w=400', 1, 30);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `rol` varchar(20) DEFAULT 'cliente',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `usuario`, `password`, `nombre`, `telefono`, `rol`, `fecha_registro`) VALUES
(1, 'admin', '$2y$10$vI8aWBnW3fID.ZQ4/zo1G.q1lRps.9cGLcZEiGDMVr5yUP1KUOYTa', 'Super Admin', '04120000000', 'admin', '2026-01-16 22:18:28');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `barberos`
--
ALTER TABLE `barberos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `citas`
--
ALTER TABLE `citas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `barbero_id` (`barbero_id`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `telefono` (`telefono`);

--
-- Indices de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  ADD PRIMARY KEY (`clave`);

--
-- Indices de la tabla `servicios`
--
ALTER TABLE `servicios`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `barberos`
--
ALTER TABLE `barberos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `citas`
--
ALTER TABLE `citas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `servicios`
--
ALTER TABLE `servicios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `citas`
--
ALTER TABLE `citas`
  ADD CONSTRAINT `citas_ibfk_1` FOREIGN KEY (`barbero_id`) REFERENCES `barberos` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
