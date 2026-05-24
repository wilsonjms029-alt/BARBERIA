-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 05-01-2026 a las 17:53:20
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
  `hora_inicio` time DEFAULT '09:00:00',
  `hora_fin` time DEFAULT '17:00:00',
  `almuerzo_inicio` time DEFAULT '12:00:00',
  `almuerzo_fin` time DEFAULT '13:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `barberos`
--

INSERT INTO `barberos` (`id`, `nombre`, `activo`, `hora_inicio`, `hora_fin`, `almuerzo_inicio`, `almuerzo_fin`) VALUES
(1, 'Carlos', 1, '10:00:00', '17:00:00', '00:00:00', '01:00:00'),
(2, 'Ana Estilista', 1, '09:00:00', '17:00:00', '12:00:00', '13:00:00'),
(4, 'Deivy', 1, '09:00:00', '17:00:00', '12:00:00', '13:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `citas`
--

CREATE TABLE `citas` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `barbero_id` int(11) DEFAULT NULL,
  `cliente_nombre` varchar(100) DEFAULT NULL,
  `cliente_email` varchar(100) DEFAULT NULL,
  `cliente_telefono` varchar(20) DEFAULT NULL,
  `referencia_pago` varchar(50) DEFAULT NULL,
  `monto` decimal(10,2) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `hora` time DEFAULT NULL,
  `estado_pago` varchar(20) DEFAULT 'pendiente',
  `servicio` varchar(100) DEFAULT 'Corte Clásico',
  `metodo_pago` varchar(50) DEFAULT 'Pago Movil',
  `estrellas` int(11) DEFAULT NULL,
  `comentario` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `citas`
--

INSERT INTO `citas` (`id`, `usuario_id`, `barbero_id`, `cliente_nombre`, `cliente_email`, `cliente_telefono`, `referencia_pago`, `monto`, `fecha`, `hora`, `estado_pago`, `servicio`, `metodo_pago`, `estrellas`, `comentario`) VALUES
(4, NULL, 1, 'Wilson', 'wilsonjms029@gmail.com', NULL, '1010', 10.00, '2025-12-12', '11:00:00', 'verificado', 'Corte Clásico - $10', 'Pago Movil', NULL, NULL),
(7, NULL, 2, 'Wilson', NULL, '04243085918', '1212', NULL, '2025-12-11', '11:00:00', 'verificado', 'Corte Clásico', 'Pago Movil', NULL, NULL),
(8, NULL, 2, 'Wilson', NULL, '04243085918', '8978', NULL, '2025-12-11', '12:00:00', 'verificado', 'Barba y Corte', 'Pago Movil', NULL, NULL),
(9, NULL, 2, 'Wilson', NULL, '04243085918', '8978', NULL, '2025-12-11', '00:00:00', 'verificado', 'Barba y Corte', 'Pago Movil', NULL, NULL),
(10, NULL, 2, 'Wilson', NULL, '04243085918', '8978', NULL, '2025-12-12', '00:00:00', 'verificado', 'Barba y Corte', 'Pago Movil', NULL, NULL),
(11, NULL, 4, 'daniel', NULL, '04123456789', '8767', NULL, '2025-12-11', '10:00:00', 'verificado', 'Corte Clásico', 'Pago Movil', NULL, NULL),
(12, NULL, 4, 'Wilson', NULL, '04243085918', '9898', NULL, '2025-12-20', '10:00:00', 'verificado', 'Barba y Corte', 'Pago Movil', NULL, NULL),
(13, NULL, 1, 'ghj', NULL, 'gfhgj', 'PAGO EN SITIO', NULL, '2025-12-16', '11:30:00', 'verificado', 'Corte Clásico', 'divisas', NULL, NULL),
(14, NULL, 4, 'fergtbhgnj', NULL, 'jnhgbfd', 'PAGO EN SITIO', NULL, '2025-12-16', '12:00:00', 'verificado', 'Barba y Corte', 'efectivo_bs', NULL, NULL),
(15, NULL, 1, 'Wilson', NULL, '04243085918', '', NULL, '2025-12-16', '00:00:00', 'verificado', 'Barba y Corte', '', NULL, NULL),
(16, NULL, 1, 'Wilson', NULL, '04243085918', '9809', NULL, '2025-12-16', '12:30:00', 'verificado', 'Corte Clásico', 'movil', NULL, NULL),
(18, NULL, 1, 'Wilson', NULL, '04243085918', '1111', NULL, '2025-12-30', '13:30:00', 'verificado', 'Corte Clásico', 'movil', NULL, NULL),
(19, NULL, 4, 'Wilson', NULL, '04243085918', 'Wilson', NULL, '2025-12-28', '13:30:00', 'verificado', 'Barba y Corte', 'zelle', NULL, NULL),
(20, NULL, 4, 'Wilson', NULL, '04243085918', 'wilson', NULL, '2025-12-30', '13:30:00', 'pendiente', 'Barba y Corte', 'zelle', NULL, NULL),
(21, NULL, 1, 'Wilson', NULL, '04243085918', 'PAGO_SITIO', NULL, '2025-12-28', '10:00:00', 'verificado', 'Corte Clásico', 'efectivo_bs', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE `configuracion` (
  `id` int(11) NOT NULL,
  `clave` varchar(50) DEFAULT NULL,
  `valor` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`id`, `clave`, `valor`) VALUES
(1, 'banco_nombre', 'Banesco'),
(2, 'banco_numero', '0134'),
(3, 'banco_ci', '30322839'),
(4, 'banco_telefono', '04243085918'),
(5, 'zelle_email', 'pagos@barberia.com'),
(26, 'estado_movil', '1'),
(27, 'estado_zelle', '1'),
(28, 'estado_efectivo_bs', '1'),
(29, 'estado_divisas', '1');

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
(1, 'Corte Clásico', 10.00, 'https://images.unsplash.com/photo-1622286342621-4bd786c2447c?q=80&w=400', 1, 30),
(2, 'Barba y Corte', 15.00, 'https://images.unsplash.com/photo-1621605815971-fbc98d665033?q=80&w=400', 1, 30);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `telefono`, `fecha_registro`) VALUES
(1, 'Wilson', 'wilsonjms029@gmail.com', '$2y$10$V5/R7LuVmU9gJmo9IM365uIlNbqpP77rq1qBfe9IrDf9t5M//wAVO', '04243085918', '2025-12-10 20:31:35'),
(2, 'Admin', 'admin@barber.com', '$2y$10$YourHashHere', '0412000000', '2025-12-11 10:19:53');

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
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `clave` (`clave`);

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
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `barberos`
--
ALTER TABLE `barberos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `citas`
--
ALTER TABLE `citas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=142;

--
-- AUTO_INCREMENT de la tabla `servicios`
--
ALTER TABLE `servicios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `citas`
--
ALTER TABLE `citas`
  ADD CONSTRAINT `citas_ibfk_1` FOREIGN KEY (`barbero_id`) REFERENCES `barberos` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
