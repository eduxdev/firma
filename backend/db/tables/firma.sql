-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 18-07-2025 a las 02:31:10
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
-- Base de datos: `firma`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `formularios_consentimiento`
--

CREATE TABLE `formularios_consentimiento` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) DEFAULT NULL,
  `apellido` varchar(255) DEFAULT NULL,
  `menor_edad` varchar(10) DEFAULT NULL,
  `nombre_tutor` varchar(255) DEFAULT NULL,
  `telefono_tutor` varchar(20) DEFAULT NULL,
  `relacion` varchar(100) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `edad` int(11) DEFAULT NULL,
  `genero` varchar(20) DEFAULT NULL,
  `correo` varchar(255) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `estado` varchar(100) DEFAULT NULL,
  `zipcode` varchar(10) DEFAULT NULL,
  `telefono_casa` varchar(20) DEFAULT NULL,
  `telefono_celular` varchar(20) DEFAULT NULL,
  `telefono_trabajo` varchar(20) DEFAULT NULL,
  `contacto_emergencia` varchar(255) DEFAULT NULL,
  `telefono_emergencia` varchar(20) DEFAULT NULL,
  `quejas` text DEFAULT NULL,
  `otros_quejas` text DEFAULT NULL,
  `afirmaciones` text DEFAULT NULL,
  `otros_afirmaciones` text DEFAULT NULL,
  `embarazada` varchar(10) DEFAULT NULL,
  `diabetico` varchar(10) DEFAULT NULL,
  `fumador` varchar(10) DEFAULT NULL,
  `drogas` varchar(10) DEFAULT NULL,
  `drogas_frecuencia` text DEFAULT NULL,
  `renal` varchar(10) DEFAULT NULL,
  `insuficiencia` varchar(10) DEFAULT NULL,
  `anticoagulantes` varchar(10) DEFAULT NULL,
  `cancer` varchar(10) DEFAULT NULL,
  `alergico` varchar(10) DEFAULT NULL,
  `medicamento_alergico` text DEFAULT NULL,
  `condicion_medica` varchar(10) DEFAULT NULL,
  `condicion_explicacion` text DEFAULT NULL,
  `medicamentos_recetados` text DEFAULT NULL,
  `medicamentos_venta_libre` text DEFAULT NULL,
  `suplementos` text DEFAULT NULL,
  `firma_paciente` mediumtext DEFAULT NULL,
  `firma_doctor` mediumtext DEFAULT NULL,
  `estado_revision` enum('pendiente','aprobado','rechazado') DEFAULT 'pendiente',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_revision` timestamp NULL DEFAULT NULL,
  `comentarios_doctor` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `apellido` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('doctor','admin') DEFAULT 'doctor',
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `ultima_sesion` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `apellido`, `email`, `password`, `rol`, `activo`, `fecha_creacion`, `ultima_sesion`) VALUES
(3, 'Administrador', '', 'admin@gmail.com', 'administrador', 'admin', 1, '2025-06-06 01:44:06', '2025-06-06 02:08:10');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `formularios_consentimiento`
--
ALTER TABLE `formularios_consentimiento`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `unique_email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `formularios_consentimiento`
--
ALTER TABLE `formularios_consentimiento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
