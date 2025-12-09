-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 07, 2025 at 11:56 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `prueba123`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_actualizar_monto_mensual` (IN `p_id_privada` INT)   BEGIN
    DECLARE v_monto_base DECIMAL(10, 2);
    DECLARE v_costo_total_servicios DECIMAL(10, 2);
    DECLARE v_total_residentes INT;
    DECLARE v_nuevo_monto DECIMAL(10, 2);

    SELECT COUNT(*) INTO v_total_residentes
    FROM priv_usuarios
    WHERE id_privada = p_id_privada AND id_estatus = 1;

    IF v_total_residentes > 0 THEN
        SELECT monto_base INTO v_monto_base
        FROM priv_privadas
        WHERE id_privada = p_id_privada;

        -- --- ESTA ES LA LÓGICA CORREGIDA ---
        SELECT IFNULL(SUM(
            -- Prioriza el precio base (ej. cuota)
            -- Si es NULL, usa el precio del proveedor
            COALESCE(s.precio_base, pps.precio_proveedor)
        ), 0)
        INTO v_costo_total_servicios
        FROM priv_privada_servicios ps
        -- Une con la definición del servicio
        JOIN priv_servicios s ON ps.id_servicio_fk = s.id_servicio
        -- Une con la tabla de precios del proveedor
        LEFT JOIN priv_proveedor_servicios pps 
            ON ps.id_proveedor_fk = pps.id_proveedor_fk -- El proveedor que contrató la privada
            AND ps.id_servicio_fk = pps.id_servicio_fk -- Para el servicio específico
        WHERE ps.id_privada_fk = p_id_privada AND ps.id_estatus = 1;
        -- --- FIN DE LA LÓGICA CORREGIDA ---

        SET v_nuevo_monto = v_monto_base + (v_costo_total_servicios / v_total_residentes);

        UPDATE priv_privadas
        SET monto_mensual_residente = v_nuevo_monto
        WHERE id_privada = p_id_privada;
    ELSE
        UPDATE priv_privadas
        SET monto_mensual_residente = monto_base
        WHERE id_privada = p_id_privada;
    END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `priv_actividades_programadas`
--

CREATE TABLE `priv_actividades_programadas` (
  `programada_id` int NOT NULL,
  `public_id` char(36) NOT NULL DEFAULT (uuid()),
  `id_privada_fk` int NOT NULL,
  `actividad_tipo_id` int NOT NULL,
  `usuario_id_responsable` int DEFAULT NULL,
  `fecha_programada` date NOT NULL,
  `id_estatus` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `priv_actividades_reportes`
--

CREATE TABLE `priv_actividades_reportes` (
  `reporte_id` int NOT NULL,
  `public_id` char(36) NOT NULL DEFAULT (uuid()),
  `programada_id` int NOT NULL,
  `usuario_id_reporta` int NOT NULL,
  `fecha_reporte` datetime DEFAULT CURRENT_TIMESTAMP,
  `descripcion_ejecucion` text,
  `hubo_incidencia` tinyint(1) NOT NULL DEFAULT '0',
  `descripcion_incidencia` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `priv_actividades_tipos`
--

CREATE TABLE `priv_actividades_tipos` (
  `actividad_tipo_id` int NOT NULL,
  `id_servicio_fk` int NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `descripcion_default` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `priv_actividades_tipos`
--

INSERT INTO `priv_actividades_tipos` (`actividad_tipo_id`, `id_servicio_fk`, `nombre`, `descripcion_default`) VALUES
(1, 2, 'Reparación de Fuga', 'Inspección y reparación de fugas de agua.');

-- --------------------------------------------------------

--
-- Table structure for table `priv_avisos`
--

CREATE TABLE `priv_avisos` (
  `id_aviso` int NOT NULL,
  `public_id` char(36) NOT NULL DEFAULT (uuid()),
  `tipo` enum('Queja','Aviso','Sugerencia','Alerta') NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `contenido` text NOT NULL,
  `fecha_pub` date NOT NULL,
  `fecha_expir` date DEFAULT NULL,
  `id_info` int NOT NULL,
  `estatus` int DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `priv_avisos`
--

INSERT INTO `priv_avisos` (`id_aviso`, `public_id`, `tipo`, `titulo`, `contenido`, `fecha_pub`, `fecha_expir`, `id_info`, `estatus`) VALUES
(9, '26e9ab37-bc3f-11f0-9567-40c2ba844675', 'Aviso', 'Aviso de Junta', 'Se informa la realización de junta vecinal el día 15 de octubre.', '2025-10-12', '2025-10-15', 3, 1),
(10, '26e9b4fc-bc3f-11f0-9567-40c2ba844675', 'Queja', 'Queja por Ruido', 'Reportan altos niveles de ruido en la casa 3A durante la noche.', '2025-10-11', NULL, 6, 1),
(11, '26e9b81f-bc3f-11f0-9567-40c2ba844675', 'Sugerencia', 'Sugerencia de Seguridad', 'Proponen instalar cámaras extras en el acceso principal.', '2025-10-10', NULL, 4, 1),
(12, '26e9ba67-bc3f-11f0-9567-40c2ba844675', 'Alerta', 'Alerta de Lluvia', 'Se solicita tomar precauciones por lluvias intensas el viernes.', '2025-10-12', '2025-10-13', 3, 1),
(13, '26e9bd98-bc3f-11f0-9567-40c2ba844675', 'Aviso', 'ROBO A MANO ARMADA ANOCHE', '¡Anoche me asaltaron en la entrada de la privada, solcito se revisen las grabaciones de seguridad para dar con el responsable!', '2025-10-16', NULL, 3, 1),
(14, '26e9c020-bc3f-11f0-9567-40c2ba844675', 'Aviso', 'ROBO A MANO ARMADA ANOCHE SEGUNDA PARTE', 'Atraparon y mataron al ladrón c:', '2025-10-19', NULL, 3, 1),
(15, '26e9c243-bc3f-11f0-9567-40c2ba844675', 'Queja', 'Perro cagón', 'Algún papanatas dejó suelto a su perro y ha defecado en mi patio', '2025-10-22', NULL, 3, 1),
(16, '26e9c44a-bc3f-11f0-9567-40c2ba844675', 'Aviso', 'Prueba del aviso', 'AVISO SAMPLE TEXT AVISO SAMPLE TEXT', '2025-10-30', NULL, 23, 1),
(17, '25017347-c7df-11f0-80f2-700894154732', 'Aviso', 'Mi aviso', 'made by an skeleton', '2025-11-22', NULL, 23, 1),
(18, '28f69339-ce57-11f0-837a-700894154732', 'Aviso', 'Mi Aviso personalizado ', 'Admin News text', '2025-11-30', NULL, 16, 1);

-- --------------------------------------------------------

--
-- Table structure for table `priv_configuracion`
--

CREATE TABLE `priv_configuracion` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `priv_configuracion`
--

INSERT INTO `priv_configuracion` (`setting_key`, `setting_value`, `descripcion`) VALUES
('meses_retencion_avisos', '', 'Tiempo en meses que dura un aviso antes de ser borrado');

-- --------------------------------------------------------

--
-- Table structure for table `priv_correoprove`
--

CREATE TABLE `priv_correoprove` (
  `id_correo` int NOT NULL,
  `public_id` char(36) NOT NULL DEFAULT (uuid()),
  `correo` varchar(150) NOT NULL,
  `id_proveedor` int DEFAULT NULL,
  `id_estatus` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `priv_correoprove`
--

INSERT INTO `priv_correoprove` (`id_correo`, `public_id`, `correo`, `id_proveedor`, `id_estatus`) VALUES
(1, '31be7e74-c3c4-11f0-a5dd-700894154732', 'reparaciones@elrapido.com', 3, 1),
(2, '31bf3b52-c3c4-11f0-a5dd-700894154732', 'soporte@fibratotal.net', 4, 1),
(3, '31bf416e-c3c4-11f0-a5dd-700894154732', 'servicio@cleanco.mx', 5, 1);

-- --------------------------------------------------------

--
-- Table structure for table `priv_corresusuario`
--

CREATE TABLE `priv_corresusuario` (
  `id_correo` int NOT NULL,
  `public_id` char(36) NOT NULL DEFAULT (uuid()),
  `correo` varchar(150) NOT NULL,
  `id_info` int DEFAULT NULL,
  `id_estatus` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `priv_corresusuario`
--

INSERT INTO `priv_corresusuario` (`id_correo`, `public_id`, `correo`, `id_info`, `id_estatus`) VALUES
(1, '31c443ef-c3c4-11f0-a5dd-700894154732', 'carlos.reyes@email.com', 3, 1),
(2, '31c44c37-c3c4-11f0-a5dd-700894154732', 'laura.gomez@email.com', 4, 1),
(3, '31c4505c-c3c4-11f0-a5dd-700894154732', 'sofia.h@email.com', 6, 1),
(4, '31c45352-c3c4-11f0-a5dd-700894154732', 'javier.moralesito@email.com', 7, 1),
(5, '31c45658-c3c4-11f0-a5dd-700894154732', 'jmorales.trabajo@email.com', 7, 1),
(6, '31c4592d-c3c4-11f0-a5dd-700894154732', 'roberto.j@email.com', 9, 1),
(7, '31c45ca4-c3c4-11f0-a5dd-700894154732', 'mariano.cum@email.com', 10, 1),
(8, '31c46317-c3c4-11f0-a5dd-700894154732', 'diego.santos@email.com', 11, 1),
(9, '31c46711-c3c4-11f0-a5dd-700894154732', 'lucia.f@email.com', 12, 1),
(10, '31c46a2c-c3c4-11f0-a5dd-700894154732', 'andres.ramirez@email.com', 13, 1),
(11, '31c47035-c3c4-11f0-a5dd-700894154732', 'vale.torres@email.com', 14, 1),
(12, '31c4792c-c3c4-11f0-a5dd-700894154732', 'v.torres.dev@email.com', 14, 1),
(14, '31c47cb8-c3c4-11f0-a5dd-700894154732', 'alexcaamal471@gmail.com', 16, 1),
(15, '31c47fc1-c3c4-11f0-a5dd-700894154732', 'alexcaamal582@gmail.com', 16, 1),
(16, '31c4883f-c3c4-11f0-a5dd-700894154732', 'IMPOSTER@GMAIL.COM', 18, 1),
(17, '31c48c7d-c3c4-11f0-a5dd-700894154732', 'genericass@email.com', 18, 1),
(18, '31c48fa0-c3c4-11f0-a5dd-700894154732', 'Slay@email.com', 19, 1),
(19, '31c496a9-c3c4-11f0-a5dd-700894154732', 'genericass@email.com', 19, 1),
(22, '31c49b12-c3c4-11f0-a5dd-700894154732', 'huiwudiwegifgiu@gmail.com', 22, 1),
(23, '31c4a59d-c3c4-11f0-a5dd-700894154732', 'robertoolan2581@gmail.com', 23, 1),
(24, '31c4abbb-c3c4-11f0-a5dd-700894154732', 'robertoolan2581@gmail.com', 23, 1),
(27, '31c4aef3-c3c4-11f0-a5dd-700894154732', 'helldiver3@gmail.com', 25, 1),
(30, '31c4b1d7-c3c4-11f0-a5dd-700894154732', 'smexy@gmail.com', 27, 1),
(31, '31c4c82a-c3c4-11f0-a5dd-700894154732', 'delete@gmail.com', 28, 2),
(32, '31c4cdfe-c3c4-11f0-a5dd-700894154732', 'helldiver3@gmail.com', 28, 2),
(33, '31c4d162-c3c4-11f0-a5dd-700894154732', 'delete@gmail.com', 29, 2),
(34, '31c4d49e-c3c4-11f0-a5dd-700894154732', 'helldiver3@gmail.com', 29, 2),
(35, '31c4dbe3-c3c4-11f0-a5dd-700894154732', 'delete@gmail.com', 30, 2),
(36, '31c4e4e1-c3c4-11f0-a5dd-700894154732', 'helldiver3@gmail.com', 30, 1),
(38, '31c4ea53-c3c4-11f0-a5dd-700894154732', 'delete@gmail.com', 32, 1),
(41, '31c4f3b5-c3c4-11f0-a5dd-700894154732', 'fakeadmin@gmail.com', 34, 1),
(42, '31c4f693-c3c4-11f0-a5dd-700894154732', 'fortnite@gmail.com', 34, 1),
(43, '31c4f930-c3c4-11f0-a5dd-700894154732', 'metalsolid@gmail.com', 35, 2),
(44, '31c4fb7c-c3c4-11f0-a5dd-700894154732', 'invibleman@gmail.com', 35, 2),
(45, '31c4fdbd-c3c4-11f0-a5dd-700894154732', 'lapolixia@gmail.com', 36, 1),
(46, '31c5004d-c3c4-11f0-a5dd-700894154732', 'olancapo@gmail.com', 37, 1),
(57, '31c502d8-c3c4-11f0-a5dd-700894154732', 'metalguysolid@gmail.com', 44, 2),
(58, '31c50520-c3c4-11f0-a5dd-700894154732', 'metalguy2solid@gmail.com', 45, 1),
(59, '31c50753-c3c4-11f0-a5dd-700894154732', 'olancapo@gmail.com', 46, 1),
(60, '31c50886-c3c4-11f0-a5dd-700894154732', 'olancapo@gmail.com', 46, 1),
(61, '31c509ab-c3c4-11f0-a5dd-700894154732', 'metalsolid@gmail.com', 47, 1),
(62, '31c50ad5-c3c4-11f0-a5dd-700894154732', 'metalsolid@gmail.com', 47, 1),
(63, '31c50bfd-c3c4-11f0-a5dd-700894154732', 'tunaguy@gmail.com', 48, 1),
(64, '31c50d66-c3c4-11f0-a5dd-700894154732', 'metalguysolid@gmail.com', 49, 2),
(65, '31c50e9e-c3c4-11f0-a5dd-700894154732', 'metalguysolid@gmail.com', 49, 2),
(66, '26876697-c401-11f0-a5dd-700894154732', 'colaboratormail@gmail.com', 50, 2),
(67, '26876f93-c401-11f0-a5dd-700894154732', 'colaboratormail@gmail.com', 51, 1),
(68, '2687723f-c401-11f0-a5dd-700894154732', 'Mycolaborator1@gmail.com', 52, 1),
(69, '268773f9-c401-11f0-a5dd-700894154732', 'Mycolaborator1@gmail.com', 53, 1),
(70, '2687758e-c401-11f0-a5dd-700894154732', 'Mycolaborator1@gmail.com', 54, 1),
(71, '26877787-c401-11f0-a5dd-700894154732', 'Mycolaborator1@gmail.com', 55, 1),
(72, '26877928-c401-11f0-a5dd-700894154732', 'newestguy@email', 56, 1),
(73, 'd14d2fd6-c406-11f0-a5dd-700894154732', 'newestguy@email', 56, 1),
(74, 'f1b71039-c5ba-11f0-8d6c-700894154732', 'proveedor@gmail.com', 57, 2),
(75, '09c4963c-c8d1-11f0-97fc-700894154732', 'titanfallrocks@gmail.com', 58, 2),
(76, 'da853eac-c958-11f0-84a7-700894154732', 'Propresidentes1@gmail.com', 59, 1),
(77, '0df0e092-ca5e-11f0-9d2b-700894154732', 'Fallman1@gmail.com', 60, 1),
(78, '0df10d59-ca5e-11f0-9d2b-700894154732', 'proveedor@gmail.com', 60, 1),
(79, '4129973e-ca5e-11f0-9d2b-700894154732', 'titanfallrocks@Bmail.com', 61, 2),
(81, 'e151f811-ccc8-11f0-b222-40c2ba844675', 'Recentlynew@gmail.com', 61, 1),
(82, '0f506db5-ccca-11f0-b222-40c2ba844675', 'refecks@gmail.com', 63, 2);

-- --------------------------------------------------------

--
-- Table structure for table `priv_estatus`
--

CREATE TABLE `priv_estatus` (
  `id_estatus` int NOT NULL,
  `estatus` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `priv_estatus`
--

INSERT INTO `priv_estatus` (`id_estatus`, `estatus`) VALUES
(1, 'Activo'),
(2, 'Inactivo'),
(3, 'Pendiente'),
(4, 'Completado'),
(5, 'Moroso');

-- --------------------------------------------------------

--
-- Table structure for table `priv_fallos`
--

CREATE TABLE `priv_fallos` (
  `id_fallo` int NOT NULL,
  `accion` varchar(100) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `id_usuario` int NOT NULL,
  `id_estatus` int NOT NULL,
  `documento` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `priv_infousuario`
--

CREATE TABLE `priv_infousuario` (
  `id_info` int NOT NULL,
  `public_id` char(36) NOT NULL DEFAULT (uuid()),
  `nombres` varchar(100) DEFAULT NULL,
  `apellido_p` varchar(50) DEFAULT NULL,
  `apellido_m` varchar(50) DEFAULT NULL,
  `fecha_nac` date DEFAULT NULL,
  `es_propietario` enum('1','0') NOT NULL DEFAULT '0',
  `id_usuario` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `priv_infousuario`
--

INSERT INTO `priv_infousuario` (`id_info`, `public_id`, `nombres`, `apellido_p`, `apellido_m`, `fecha_nac`, `es_propietario`, `id_usuario`) VALUES
(3, '31cd8d89-c3c4-11f0-a5dd-700894154732', 'Carlos', 'Martin', 'Solis', '1988-03-15', '1', 2),
(4, '31cd941d-c3c4-11f0-a5dd-700894154732', 'Laura', 'Gomez', 'Paredes', '1990-07-22', '0', 2),
(5, '31cd96b7-c3c4-11f0-a5dd-700894154732', 'Mateo', 'Reyes', 'Gomez', '2015-01-30', '0', 2),
(6, '31cd98b1-c3c4-11f0-a5dd-700894154732', 'Sofi', 'Hernandez', 'Luna', '1995-05-25', '1', 3),
(7, '31cd9aca-c3c4-11f0-a5dd-700894154732', 'Javier', 'Morales', 'Rios', '1980-11-02', '1', 4),
(8, '31cd9d09-c3c4-11f0-a5dd-700894154732', 'Elena', 'Vazquez', 'Cruz', '1982-09-12', '0', 4),
(9, '31cd9ef1-c3c4-11f0-a5dd-700894154732', 'Roberto', 'Jimenez', 'Diaz', '1991-08-19', '1', 5),
(10, '31cda0ae-c3c4-11f0-a5dd-700894154732', 'Mariano', 'Castillo', 'Ortega', '1993-02-28', '1', 6),
(11, '31cda269-c3c4-11f0-a5dd-700894154732', 'Diego', 'Santos', 'Vega', '1992-04-10', '0', 6),
(12, '31cda423-c3c4-11f0-a5dd-700894154732', 'Lucia', 'Fernandez', 'Guillen', '1985-12-01', '1', 7),
(13, '31cda5f2-c3c4-11f0-a5dd-700894154732', 'Andres', 'Ramirez', 'Soto', '1979-06-18', '1', 8),
(14, '31cda7c2-c3c4-11f0-a5dd-700894154732', 'Valeria', 'Torres', 'Nieto', '1998-10-05', '1', 9),
(15, '31cda99b-c3c4-11f0-a5dd-700894154732', 'Miguel', 'Herrera', 'Ponce', '1997-03-09', '0', 9),
(16, '31cdab45-c3c4-11f0-a5dd-700894154732', 'Alex', 'Caamal', 'Solis', NULL, '0', 1),
(17, '31cdacf4-c3c4-11f0-a5dd-700894154732', 'Pedro', 'Gomez', 'Luna', '1999-10-10', '0', 4),
(18, '31cdae85-c3c4-11f0-a5dd-700894154732', 'Sussy', 'Imposter', 'AmongUs', NULL, '0', 12),
(19, '31cdb050-c3c4-11f0-a5dd-700894154732', 'SlayerDoom', 'DoomDoom', 'Guy', NULL, '0', 13),
(22, '31cdb184-c3c4-11f0-a5dd-700894154732', 'Onionboi', 'resses', 'Nuhuhhu', '2025-10-07', '1', 15),
(23, '31cdb311-c3c4-11f0-a5dd-700894154732', 'OnionGuy', 'For', 'TodayChallenge', '2025-09-30', '1', 16),
(25, '31cdb4e6-c3c4-11f0-a5dd-700894154732', 'Jonh ', 'HellDOVER', 'SUSYSY', NULL, '0', 18),
(27, '31cdb6df-c3c4-11f0-a5dd-700894154732', 'Under', 'Sleep', 'Guy', '2025-10-01', '1', 20),
(28, '31cdb8b2-c3c4-11f0-a5dd-700894154732', 'Generic', 'Colaborator', 'TestDel', NULL, '0', 21),
(29, '31cdba83-c3c4-11f0-a5dd-700894154732', 'Generic', 'Colaborator', 'TestDel', NULL, '0', 22),
(30, '31cdbc5c-c3c4-11f0-a5dd-700894154732', 'Another ', 'Provider', 'ForOnion', NULL, '0', 23),
(32, '31cdbe39-c3c4-11f0-a5dd-700894154732', 'Entry', 'Random', 'Guy', NULL, '1', 24),
(34, '31cdc1da-c3c4-11f0-a5dd-700894154732', 'Another', 'Repeated', 'Guy', NULL, '0', 26),
(35, '31cdc3a8-c3c4-11f0-a5dd-700894154732', 'Metal', 'GEAR', 'Solid', NULL, '0', 27),
(36, '31cdc56f-c3c4-11f0-a5dd-700894154732', 'Darikson', 'NoCap', 'LaPolixia', NULL, '0', 28),
(37, '31cdc744-c3c4-11f0-a5dd-700894154732', 'Myminitestguy', 'Test', 'Man', NULL, '0', 29),
(44, '31cdc93d-c3c4-11f0-a5dd-700894154732', 'Colaborator', 'Guy', 'Uh', NULL, '0', 34),
(45, '31cdcb1a-c3c4-11f0-a5dd-700894154732', 'AnotherColab', 'Guy', 'Test', NULL, '0', 35),
(46, '31cdccea-c3c4-11f0-a5dd-700894154732', 'yey', 'man', 'sus', '2003-01-02', '1', 36),
(47, '31cdcebb-c3c4-11f0-a5dd-700894154732', 'Metal', 'Guy', 'Solid', NULL, '1', 48),
(48, '31cdd08b-c3c4-11f0-a5dd-700894154732', 'Colaborador ', 'Generico', 'Guy', NULL, '0', 37),
(49, '31cdd339-c3c4-11f0-a5dd-700894154732', 'Colaborator', 'Guy', 'Uh', NULL, '0', 38),
(50, 'fce579d9-c3ff-11f0-a5dd-700894154732', 'AnotherSimple', 'Guy', 'For Today', NULL, '0', 39),
(51, 'fce5834c-c3ff-11f0-a5dd-700894154732', 'AnotherSimple', 'Guy', 'For Today', NULL, '0', 40),
(52, 'fce5939a-c3ff-11f0-a5dd-700894154732', 'MyNewest', 'Guy', 'Today', NULL, '0', 41),
(53, 'fce595c0-c3ff-11f0-a5dd-700894154732', 'MyNewest', 'Guy', 'Today', NULL, '0', 42),
(54, 'fce59895-c3ff-11f0-a5dd-700894154732', 'NEwManAmogus', 'Guy', 'Today', NULL, '0', 43),
(55, 'fce59a8f-c3ff-11f0-a5dd-700894154732', 'NEwManAmogus', 'Guy', 'Today', NULL, '0', 44),
(56, 'fce59c01-c3ff-11f0-a5dd-700894154732', 'YEs', 'Mann', 'Co', NULL, '0', 45),
(57, 'f1b6c0e9-c5ba-11f0-8d6c-700894154732', 'AnotherNewGuy', 'For44 ', 'Today', NULL, '0', 46),
(58, '09c454f3-c8d1-11f0-97fc-700894154732', 'Titanfall', 'Guy', 'Jarvis', NULL, '0', 47),
(59, 'da84de9f-c958-11f0-84a7-700894154732', 'My', 'Landlord', 'LA', '2001-01-24', '0', 48),
(60, '0df0a92f-ca5e-11f0-9d2b-700894154732', 'Jonh ', 'Tita', 'Fell', NULL, '0', 49),
(61, '412981a3-ca5e-11f0-9d2b-700894154732', 'Recently', 'Jonhson', 'SonofGuy', NULL, '0', 50),
(63, '0f50345a-ccca-11f0-b222-40c2ba844675', 'AnotherAnother', 'Referencial', '', NULL, '0', 52);

-- --------------------------------------------------------

--
-- Table structure for table `priv_info_usuario_registro`
--

CREATE TABLE `priv_info_usuario_registro` (
  `id_registro` int NOT NULL,
  `accion` varchar(100) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `id_usuario` int NOT NULL,
  `id_estatus` int NOT NULL,
  `documento` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `priv_metpagos`
--

CREATE TABLE `priv_metpagos` (
  `id_metstag` int NOT NULL,
  `metstag` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `priv_metpagos`
--

INSERT INTO `priv_metpagos` (`id_metstag`, `metstag`) VALUES
(1, 'Efectivo'),
(2, 'Tarjeta de débito'),
(3, 'Tarjeta de crédito'),
(4, 'Transferencia bancaria'),
(5, 'PayPal');

-- --------------------------------------------------------

--
-- Table structure for table `priv_modelocasa`
--

CREATE TABLE `priv_modelocasa` (
  `id_casa` int NOT NULL,
  `nom_modelo` varchar(100) NOT NULL,
  `descripcion` text,
  `id_privada` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `priv_pagos`
--

CREATE TABLE `priv_pagos` (
  `id_pago` int NOT NULL,
  `public_id` char(36) NOT NULL DEFAULT (uuid()),
  `cantidad` decimal(10,2) NOT NULL,
  `fecha_pago` date DEFAULT NULL,
  `fecha_corte` date DEFAULT NULL,
  `fecha_lim` date DEFAULT NULL,
  `id_metstag` int DEFAULT NULL,
  `id_usuario` int DEFAULT NULL,
  `id_estatuspago` int DEFAULT NULL,
  `id_privada` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `priv_pagos`
--

INSERT INTO `priv_pagos` (`id_pago`, `public_id`, `cantidad`, `fecha_pago`, `fecha_corte`, `fecha_lim`, `id_metstag`, `id_usuario`, `id_estatuspago`, `id_privada`) VALUES
(1, '31d03e15-c3c4-11f0-a5dd-700894154732', 850.00, '2025-08-05', NULL, NULL, 2, 2, 5, 1),
(2, '31d04358-c3c4-11f0-a5dd-700894154732', 850.00, '2025-09-03', NULL, NULL, 2, 2, 5, 1),
(4, '31d04511-c3c4-11f0-a5dd-700894154732', 850.00, '2025-08-10', NULL, NULL, 1, 3, 3, 1),
(5, '31d046fa-c3c4-11f0-a5dd-700894154732', 850.00, NULL, NULL, NULL, 1, 3, 3, 1),
(6, '31d04868-c3c4-11f0-a5dd-700894154732', 850.00, '2025-09-01', NULL, NULL, 4, 4, 5, 1),
(7, '31d04a43-c3c4-11f0-a5dd-700894154732', 700.00, '2025-09-18', NULL, NULL, 1, 4, 3, 1),
(8, '31d04d41-c3c4-11f0-a5dd-700894154732', 850.00, '2025-08-02', NULL, NULL, 3, 6, 1, 2),
(9, '31d04fd5-c3c4-11f0-a5dd-700894154732', 850.00, '2025-09-02', NULL, NULL, 3, 6, 1, 2),
(10, '31d05258-c3c4-11f0-a5dd-700894154732', 850.00, '2025-10-01', NULL, NULL, 3, 6, 1, 2),
(11, '31d054d2-c3c4-11f0-a5dd-700894154732', 550.00, '2025-09-20', NULL, NULL, 5, 8, 1, 2);

-- --------------------------------------------------------

--
-- Table structure for table `priv_pases_visitas`
--

CREATE TABLE `priv_pases_visitas` (
  `id_pase` int NOT NULL,
  `public_id` char(36) NOT NULL DEFAULT (uuid()),
  `id_visita` int NOT NULL,
  `codigo_acceso` varchar(255) NOT NULL,
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `fecha_expiracion` datetime NOT NULL,
  `estatus` int DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `priv_privadas`
--

CREATE TABLE `priv_privadas` (
  `id_privada` int NOT NULL,
  `public_id` char(36) NOT NULL DEFAULT (uuid()),
  `nombre` varchar(100) NOT NULL,
  `capacidad` int DEFAULT NULL,
  `id_estatus` int DEFAULT NULL,
  `diacorte` tinyint UNSIGNED NOT NULL DEFAULT '1' COMMENT 'Es el dia del mes en que se espera se haga el pago',
  `monto_base` decimal(10,2) NOT NULL DEFAULT '500.00',
  `monto_mensual_residente` decimal(10,2) DEFAULT NULL
) ;

--
-- Dumping data for table `priv_privadas`
--

INSERT INTO `priv_privadas` (`id_privada`, `public_id`, `nombre`, `capacidad`, `id_estatus`, `diacorte`, `monto_base`, `monto_mensual_residente`) VALUES
(1, 'f48446d9-b7af-11f0-81c8-40c2ba844675', 'Residencial Las Palmas', 150, 1, 1, 500.00, 562.00),
(2, 'f487b4f2-b7af-11f0-81c8-40c2ba844675', 'Villas del soles', 200, 1, 1, 500.00, 712.50);

--
-- Triggers `priv_privadas`
--
DELIMITER $$
CREATE TRIGGER `trg_before_base_price_modif` BEFORE UPDATE ON `priv_privadas` FOR EACH ROW BEGIN
    DECLARE v_costo_total_servicios DECIMAL(10, 2);
    DECLARE v_total_residentes INT;

    IF NEW.monto_base <> OLD.monto_base THEN
        SELECT COUNT(*) INTO v_total_residentes
        FROM priv_usuarios
        WHERE id_privada = NEW.id_privada AND id_estatus = 1;

        -- --- ESTA ES LA LÓGICA CORREGIDA ---
        SELECT IFNULL(SUM(
            COALESCE(s.precio_base, pps.precio_proveedor)
        ), 0)
        INTO v_costo_total_servicios
        FROM priv_privada_servicios ps
        JOIN priv_servicios s ON ps.id_servicio_fk = s.id_servicio
        LEFT JOIN priv_proveedor_servicios pps 
            ON ps.id_proveedor_fk = pps.id_proveedor_fk 
            AND ps.id_servicio_fk = pps.id_servicio_fk
        WHERE ps.id_privada_fk = NEW.id_privada AND ps.id_estatus = 1;
        -- --- FIN DE LA LÓGICA CORREGIDA ---

        IF v_total_residentes > 0 THEN
            SET NEW.monto_mensual_residente = NEW.monto_base + (v_costo_total_servicios / v_total_residentes);
        ELSE
            SET NEW.monto_mensual_residente = NEW.monto_base;
        END IF;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `priv_privada_servicios`
--

CREATE TABLE `priv_privada_servicios` (
  `id_privada_servicio` int NOT NULL,
  `public_id` char(36) NOT NULL DEFAULT (uuid()),
  `id_privada_fk` int NOT NULL,
  `id_servicio_fk` int NOT NULL,
  `id_proveedor_fk` int DEFAULT NULL,
  `fecha_asignacion` date DEFAULT NULL,
  `id_estatus` int DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `priv_privada_servicios`
--

INSERT INTO `priv_privada_servicios` (`id_privada_servicio`, `public_id`, `id_privada_fk`, `id_servicio_fk`, `id_proveedor_fk`, `fecha_asignacion`, `id_estatus`) VALUES
(1, '31d879ac-c3c4-11f0-a5dd-700894154732', 1, 1, 1, '2025-10-15', 1),
(3, '31d87fc0-c3c4-11f0-a5dd-700894154732', 1, 3, 3, '2025-10-15', 1);

-- --------------------------------------------------------

--
-- Table structure for table `priv_privada_servicios_registro`
--

CREATE TABLE `priv_privada_servicios_registro` (
  `id_registro` int NOT NULL,
  `accion` varchar(100) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `id_usuario` int NOT NULL,
  `id_estatus` int NOT NULL,
  `documento` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `priv_provedores_registro`
--

CREATE TABLE `priv_provedores_registro` (
  `id_registro` int NOT NULL,
  `accion` varchar(100) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `id_usuario` int NOT NULL,
  `id_estatus` int NOT NULL,
  `documento` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `priv_proveedor`
--

CREATE TABLE `priv_proveedor` (
  `id_proveedor` int NOT NULL,
  `public_id` char(36) NOT NULL DEFAULT (uuid()),
  `nombre_empresa` varchar(150) NOT NULL,
  `nombre_encargado` varchar(100) DEFAULT NULL,
  `id_servicio` int DEFAULT NULL,
  `id_estatus` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `priv_proveedor`
--

INSERT INTO `priv_proveedor` (`id_proveedor`, `public_id`, `nombre_empresa`, `nombre_encargado`, `id_servicio`, `id_estatus`) VALUES
(1, '31db0b09-c3c4-11f0-a5dd-700894154732', 'Jardinería Express', 'Roberto Verde', 1, 1),
(2, '31db0f4d-c3c4-11f0-a5dd-700894154732', 'Seguridad Privada Halcón', 'Laura Torres', 1, 1),
(3, '31db1162-c3c4-11f0-a5dd-700894154732', 'Plomería El Rápido', 'Mario Fontanero', 5, 1),
(4, '31db12b7-c3c4-11f0-a5dd-700894154732', 'Internet FibraTotal', 'Sofia Redes', 2, 1),
(5, '31db13f4-c3c4-11f0-a5dd-700894154732', 'Limpieza Profunda CleanCo', 'Luisa Limpio', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `priv_proveedor_servicios`
--

CREATE TABLE `priv_proveedor_servicios` (
  `id_proveedor_servicio` int NOT NULL,
  `public_id` char(36) NOT NULL DEFAULT (uuid()),
  `id_proveedor_fk` int NOT NULL,
  `id_servicio_fk` int NOT NULL,
  `precio_proveedor` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `priv_proveedor_servicios`
--

INSERT INTO `priv_proveedor_servicios` (`id_proveedor_servicio`, `public_id`, `id_proveedor_fk`, `id_servicio_fk`, `precio_proveedor`) VALUES
(1, '4ed4c8ea-c401-11f0-a5dd-700894154732', 3, 2, 700.00),
(2, '4ed4d16b-c401-11f0-a5dd-700894154732', 4, 3, 600.00);

-- --------------------------------------------------------

--
-- Table structure for table `priv_respaldo_op`
--

CREATE TABLE `priv_respaldo_op` (
  `id_respaldo` int NOT NULL,
  `documento` text,
  `fecha_hora_antes` datetime DEFAULT NULL,
  `fecha_hora_despues` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `priv_roles`
--

CREATE TABLE `priv_roles` (
  `id_rol` int NOT NULL,
  `rol` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `priv_roles`
--

INSERT INTO `priv_roles` (`id_rol`, `rol`) VALUES
(1, 'Administrador'),
(2, 'Usuario'),
(3, 'Proveedor'),
(4, 'Seguridad'),
(5, 'Colaborador');

-- --------------------------------------------------------

--
-- Table structure for table `priv_servicios`
--

CREATE TABLE `priv_servicios` (
  `id_servicio` int NOT NULL,
  `public_id` char(36) NOT NULL DEFAULT (uuid()),
  `nom_serv` varchar(100) NOT NULL,
  `precio_base` decimal(10,2) DEFAULT NULL,
  `id_proveedor` int DEFAULT NULL,
  `id_estatus` int DEFAULT NULL,
  `id_categoria_fk` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `priv_servicios`
--

INSERT INTO `priv_servicios` (`id_servicio`, `public_id`, `nom_serv`, `precio_base`, `id_proveedor`, `id_estatus`, `id_categoria_fk`) VALUES
(1, '508e2dda-c3c4-11f0-a5dd-700894154732', 'Cuota de Mantenimiento', 850.00, 1, 1, 1),
(2, '508e65ec-c3c4-11f0-a5dd-700894154732', 'Servicio General de Plomería', 800.00, 2, 1, 2),
(3, '508e6b2b-c3c4-11f0-a5dd-700894154732', 'Plan Internet 100mbps', 700.00, 3, 1, 3);

--
-- Triggers `priv_servicios`
--
DELIMITER $$
CREATE TRIGGER `trg_after_service_price_modif` AFTER UPDATE ON `priv_servicios` FOR EACH ROW BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_id_privada INT;
    DECLARE cur_privadas CURSOR FOR
        SELECT id_privada_fk FROM priv_privada_servicios
        WHERE id_servicio_fk = NEW.id_servicio;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    -- --- CÓDIGO CORREGIDO ---
    -- Comparamos 'precio_base' en lugar de 'precio'
    IF NEW.precio_base <> OLD.precio_base THEN
    -- --- FIN CÓDIGO CORREGIDO ---
        OPEN cur_privadas;
        read_loop: LOOP
            FETCH cur_privadas INTO v_id_privada;
            IF done THEN
                LEAVE read_loop;
            END IF;
            CALL sp_actualizar_monto_mensual(v_id_privada);
        END LOOP;
        CLOSE cur_privadas;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `priv_servicios_categorias`
--

CREATE TABLE `priv_servicios_categorias` (
  `id_categoria` int NOT NULL,
  `public_id` char(36) NOT NULL DEFAULT (uuid()),
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `priv_servicios_categorias`
--

INSERT INTO `priv_servicios_categorias` (`id_categoria`, `public_id`, `nombre`) VALUES
(1, '9562f59e-c401-11f0-a5dd-700894154732', 'Administrativo'),
(2, '95630083-c401-11f0-a5dd-700894154732', 'Plomería'),
(3, '9563031f-c401-11f0-a5dd-700894154732', 'Telecomunicaciones');

-- --------------------------------------------------------

--
-- Table structure for table `priv_servicios_registro`
--

CREATE TABLE `priv_servicios_registro` (
  `id_registro` int NOT NULL,
  `accion` varchar(100) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `id_usuario` int NOT NULL,
  `id_estatus` int NOT NULL,
  `documento` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `priv_statuspagos`
--

CREATE TABLE `priv_statuspagos` (
  `id_estatuspago` int NOT NULL,
  `estatuspago` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `priv_statuspagos`
--

INSERT INTO `priv_statuspagos` (`id_estatuspago`, `estatuspago`) VALUES
(1, 'Pagado'),
(2, 'Pendiente'),
(3, 'Vencido'),
(4, 'Cancelado');

-- --------------------------------------------------------

--
-- Table structure for table `priv_telprove`
--

CREATE TABLE `priv_telprove` (
  `id_telefono` int NOT NULL,
  `public_id` varchar(36) DEFAULT NULL,
  `telefono` varchar(20) NOT NULL,
  `id_proveedor` int DEFAULT NULL,
  `id_estatus` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `priv_telprove`
--

INSERT INTO `priv_telprove` (`id_telefono`, `public_id`, `telefono`, `id_proveedor`, `id_estatus`) VALUES
(1, '31cadeaa-c3c4-11f0-a5dd-700894154732', '9982345678', 3, 1),
(2, '31cae55d-c3c4-11f0-a5dd-700894154732', '8005001000', 4, 1),
(3, '31cae988-c3c4-11f0-a5dd-700894154732', '9988765432', 5, 1);

-- --------------------------------------------------------

--
-- Table structure for table `priv_telusuario`
--

CREATE TABLE `priv_telusuario` (
  `id_telefono` int NOT NULL,
  `public_id` varchar(36) DEFAULT NULL,
  `telefono` varchar(20) NOT NULL,
  `id_info` int DEFAULT NULL,
  `id_estatus` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `priv_telusuario`
--

INSERT INTO `priv_telusuario` (`id_telefono`, `public_id`, `telefono`, `id_info`, `id_estatus`) VALUES
(1, '31c7c3be-c3c4-11f0-a5dd-700894154732', '9981112230', 3, 1),
(3, '31c7cb34-c3c4-11f0-a5dd-700894154732', '9983334455', 4, 1),
(4, '31c7ce04-c3c4-11f0-a5dd-700894154732', '9982223344', 6, 1),
(5, '31c7d058-c3c4-11f0-a5dd-700894154732', '9983334455', 7, 1),
(6, '31c7d1e8-c3c4-11f0-a5dd-700894154732', '9984445566', 9, 1),
(7, '31c7d3e6-c3c4-11f0-a5dd-700894154732', '9985556677', 10, 1),
(8, '31c7d612-c3c4-11f0-a5dd-700894154732', '9986667788', 12, 1),
(9, '31c7d73e-c3c4-11f0-a5dd-700894154732', '9986667799', 12, 1),
(10, '31c7d86b-c3c4-11f0-a5dd-700894154732', '9987778899', 13, 1),
(11, '31c7db23-c3c4-11f0-a5dd-700894154732', '9988889900', 14, 1),
(12, '31c7de59-c3c4-11f0-a5dd-700894154732', '9988889911', 15, 1),
(14, '31c7e030-c3c4-11f0-a5dd-700894154732', '9983221560', 16, 1),
(15, '31c7e18e-c3c4-11f0-a5dd-700894154732', '9982354338', 16, 1),
(16, '31c7e2dc-c3c4-11f0-a5dd-700894154732', '1231231233', 18, 1),
(17, '31c7e407-c3c4-11f0-a5dd-700894154732', '1236667890', 19, 1),
(20, '31c7e537-c3c4-11f0-a5dd-700894154732', '0312413222', 22, 1),
(21, '31c7e658-c3c4-11f0-a5dd-700894154732', '9981565980', 23, 1),
(23, '31c7e777-c3c4-11f0-a5dd-700894154732', '1236667890', 25, 1),
(25, '31c7e8d8-c3c4-11f0-a5dd-700894154732', '9981565980', 27, 1),
(26, '31c7eaef-c3c4-11f0-a5dd-700894154732', '1236677880', 28, 2),
(27, '31c7ed13-c3c4-11f0-a5dd-700894154732', '1236677880', 29, 2),
(28, '31c7ef2b-c3c4-11f0-a5dd-700894154732', '1236677880', 30, 1),
(29, '31c7f240-c3c4-11f0-a5dd-700894154732', '1236667899', 30, 1),
(31, '31c7f47c-c3c4-11f0-a5dd-700894154732', '1236677880', 32, 1),
(33, '31c7f8b8-c3c4-11f0-a5dd-700894154732', '1234567790', 34, 1),
(34, '31c7facd-c3c4-11f0-a5dd-700894154732', '1236667890', 35, 2),
(35, '31c7fcd6-c3c4-11f0-a5dd-700894154732', '1231231231', 35, 2),
(36, '31c7fee6-c3c4-11f0-a5dd-700894154732', '1231231231', 36, 1),
(37, '31c800e8-c3c4-11f0-a5dd-700894154732', '1234567899', 37, 1),
(44, '31c802ee-c3c4-11f0-a5dd-700894154732', '1236667890', 44, 2),
(45, '31c804f1-c3c4-11f0-a5dd-700894154732', '1236667890', 45, 1),
(46, '31c8070a-c3c4-11f0-a5dd-700894154732', '1234567899', 46, 1),
(47, '31c8089f-c3c4-11f0-a5dd-700894154732', '1236667898', 47, 1),
(48, '31c80a23-c3c4-11f0-a5dd-700894154732', '1234512345', 48, 1),
(49, '31c80ba8-c3c4-11f0-a5dd-700894154732', '1236667890', 49, 1),
(50, NULL, '1234567890', 50, 1),
(51, NULL, '1234567890', 51, 1),
(52, NULL, '1234567890', 52, 1),
(53, NULL, '1234567890', 53, 1),
(54, NULL, '1234567890', 54, 1),
(55, NULL, '1234567890', 55, 1),
(56, NULL, '1234567890', 56, 1),
(57, NULL, '1234567890', 57, 1),
(58, NULL, '1234567890', 58, 1),
(59, NULL, '1234567888', 59, 1),
(60, NULL, '1234567890', 60, 1),
(61, NULL, '1234567999', 61, 1),
(63, NULL, '1234567890', 63, 1);

-- --------------------------------------------------------

--
-- Table structure for table `priv_usuarios`
--

CREATE TABLE `priv_usuarios` (
  `id_usuario` int NOT NULL,
  `public_id` char(36) NOT NULL DEFAULT (uuid()),
  `usuario` varchar(100) NOT NULL,
  `contrasenia` varchar(255) NOT NULL,
  `num_casa` varchar(50) DEFAULT NULL,
  `id_privada` int DEFAULT NULL,
  `id_estatus` int DEFAULT NULL,
  `id_rol` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `priv_usuarios`
--

INSERT INTO `priv_usuarios` (`id_usuario`, `public_id`, `usuario`, `contrasenia`, `num_casa`, `id_privada`, `id_estatus`, `id_rol`) VALUES
(1, '26e6e2d7-bc3f-11f0-9567-40c2ba844675', 'admin2', '$2y$10$n.61HQac9/EiAOGuL3xeoeROIWRv045DUC1Qci9XgPGO81UnFcndC', '11', 1, 1, 1),
(2, '26e752cd-bc3f-11f0-9567-40c2ba844675', 'user1', '$2y$10$wi6oNbWjoZ/BsnUPMYL6RO3qXMc/VlYhCeCTDngItpWJm.tdhbBbK', '1A', 1, 1, 4),
(3, '26e75725-bc3f-11f0-9567-40c2ba844675', 'user2', '$2y$10$0aBxhPqA46aOFM4O2VHUSuqU2HpmHby7xYmG8RFqNvxKplCmtAhxy', '45', 1, 1, 2),
(4, '26e759c4-bc3f-11f0-9567-40c2ba844675', 'user3', '$2y$10$1cnTdf.75IMKe3mCRgMhJ.qiXqaryqDjtzmocu6Ackw1MqIJMfFC6', '3A', 1, 1, 2),
(5, '26e75c22-bc3f-11f0-9567-40c2ba844675', 'user4', '$2y$10$SQWc.Qxm2qLujOQW6Pyip.uqfnDTYxiA7hToKFAhiY1XMYps5DXAa', '4A', 1, 1, 3),
(6, '26e75dac-bc3f-11f0-9567-40c2ba844675', 'user5', '$2y$10$nbS8LSWpjLI2foIF3N4qSOqsYXzEHDnQ.UuX1zPGRi7P8eCu1Atkm', '1B', 2, 1, 1),
(7, '26e75f13-bc3f-11f0-9567-40c2ba844675', 'user6', '$2y$10$xAQXwEDjcpLie/N0ORAsNefll99u0Ei4HtCy73qPrffzNPkmmhvyu', '2B', 2, 1, 3),
(8, '26e76078-bc3f-11f0-9567-40c2ba844675', 'user8', '$2y$10$7EYhiMfbYxWtGMjHKjQ3Ged.4fN7pvh/JK3sOXN45P638NiFj0k82', '4B', 2, 1, 2),
(9, '26e761cd-bc3f-11f0-9567-40c2ba844675', 'user7', '$2y$10$jz10Ejd0B/0.VvObyR6feel2l3icQeuupSZoMh050eQU5cGOPgfm.', '3B', 2, 1, 2),
(12, '26e76320-bc3f-11f0-9567-40c2ba844675', 'Amogus', '$2y$10$NH941BqS78DeJH1R3bmDdON9uRG9y8beWT9DDAZBML4S82JQDARaC', '0', 1, 1, 5),
(13, '26e76497-bc3f-11f0-9567-40c2ba844675', 'SlayerGuy', '$2y$10$nFd.7SFbzfcxSlGcrOjb8edDrSlEZG6ojk4.Q/4m/MqPTSrjsy8wG', NULL, 1, 1, 3),
(15, '26e765f6-bc3f-11f0-9567-40c2ba844675', 'UsuarioNormal', '$2y$10$Dw.VATOvEJPW1bm9YRO6MOO9g0TX3CLb9L2s1Up.P6WFUAJvR4Wfm', '33', 1, 1, 2),
(16, '26e7674c-bc3f-11f0-9567-40c2ba844675', 'onion', '$2y$10$5ygy86/oLhDbdJsmdCULgO7xGxd1ye8apD.AHq3LYRo7/4zThuTnq', '123', 1, 1, 2),
(18, '26e768cc-bc3f-11f0-9567-40c2ba844675', 'Agustust', '$2y$10$1vBH0mDOWUJjHPWPTHFrResEiJqa1uMg16H4.JGkXCyJec29YnVG.', NULL, 1, 1, 1),
(20, '26e76a2a-bc3f-11f0-9567-40c2ba844675', 'UnderBoi', '$2y$10$edjc5ROJ3cu00abBvTFJNucRlh3XPsK7THX7tb9J2sXriMhgDWj/e', '65', 1, 1, 2),
(21, '26e76b89-bc3f-11f0-9567-40c2ba844675', 'deleteGuy', '$2y$10$XGLLZN3nl/OmNtzhAhA6GuX2ZHUd2iVycbXxV3TaTPLv.CtfWfopi', NULL, 1, 2, 1),
(22, '26e76ce7-bc3f-11f0-9567-40c2ba844675', 'deleteGuy', '$2y$10$EtSMSjww1Qbg1D0waF4ntOCOlE0LLaltxqQS8XyOXVjASjKy8VUJG', NULL, 1, 2, 1),
(23, '26e76e38-bc3f-11f0-9567-40c2ba844675', 'FORONON', '$2y$10$7HuffDKFg0AxJ5x4w1uAs.QIrivuXA15C3jwpKJbTgvxGqJ8wOFx.', NULL, 1, 1, 3),
(24, '26e76f8a-bc3f-11f0-9567-40c2ba844675', 'zassygeimer', '$2y$10$Z7vURd1pwRVu1QAJgpeUKuATYsulIYJNj8bCkBEGyKY.08uLab5s.', '22', 1, 1, 2),
(26, '26e77248-bc3f-11f0-9567-40c2ba844675', 'Good', '$2y$10$Rr/P1hv0Np2DRwNfc.DimOly8hffaCIXnjjUvzfSbvCd0WyhnWsiG', NULL, 1, 1, 1),
(27, '26e7739e-bc3f-11f0-9567-40c2ba844675', 'MetalGS', '$2y$10$q.2fKph7LDx/3GZF.a0tw.np5akIng5K6UrYpK2AVLDxs4mrZSYfa', NULL, 1, 2, 3),
(28, '26e774f0-bc3f-11f0-9567-40c2ba844675', 'dariksongei', '$2y$10$0DvvsuZBV5A3bhb21ZnqS.KPo/dQ7lTQTO5cAigvqnw6NXkrYmSpm', NULL, 1, 1, 1),
(29, '2be7afed-bf6c-11f0-af4f-700894154732', 'anotherAdmin', '$2y$10$/ITskfC.sCJ6Q6cZjPiOaeNB7R1H5r4Ad8zDk6bylZL5/bSuihyiS', NULL, 1, 1, 1),
(34, '1b8f4f35-bf77-11f0-af4f-700894154732', 'GuyAdmin', '$2y$10$TfJ1R8QDN7aOfZYiUdDrlew7LhebR68A5L3mzB/8DX9lxyYwdluIG', NULL, 1, 2, 1),
(35, '5875b834-bf77-11f0-af4f-700894154732', 'AnotherColab', '$2y$10$WVi8xkT3pZIkyaAJYJrl3.kj.uYkkyglDYGgmKyYhD5Tbg1uK67Vu', NULL, 1, 1, 1),
(36, '870f202d-bf77-11f0-af4f-700894154732', 'OtherGuy', '$2y$10$hcT1xEej5TUZqp2thKtHuOKYXm6BOobzB5DjjglgK0Vz7XWUlfHWy', '10', 1, 1, 2),
(37, '940581e0-bfe9-11f0-abad-40c2ba844675', 'Colaborador1', '$2y$10$0jKnK/bJCqjGRGJAxXVEz.3fMkErEGH6NJ/IO3SGFNpgR91gV6nfi', NULL, 1, 1, 5),
(38, 'a5fe5c2b-bff5-11f0-abad-40c2ba844675', 'AnotherGuy', '$2y$10$RTW8oJx.feMzImeV7F1U5.tZGgtPOwprwt45tzM2A7wbQHEuiBkua', NULL, 1, 2, 1),
(39, '272c6f82-c3f4-11f0-a5dd-700894154732', 'Mycolaborator1', '$2y$10$imDo9w2aJ1NuTwrTh3IoceQgBynoMj5FrZVL7XNH0Y9.a5IbU3HOy', NULL, 1, 2, 5),
(40, '47389664-c3f4-11f0-a5dd-700894154732', 'Mycolaborator21', '$2y$10$HX90733XIoylabJQ7wXN8ucpUWPvLGixEFA9U1WO8FPx9MybGYzai', NULL, 1, 1, 5),
(41, 'e10b0609-c3f7-11f0-a5dd-700894154732', 'NEwguy', '$2y$10$GBOVE9nReKYIx.wD9C1o9e3Nyr3eF1RzmOj.9bws43/CElGpQJ.B6', NULL, NULL, 1, 1),
(42, 'cacfb8b1-c3f8-11f0-a5dd-700894154732', 'ddadsda', '$2y$10$jCbfUIJoLtUJcUSMahiaSuh3c9lJ2C/MGTBlhV.nV0SjUF4drdmf2', NULL, NULL, 1, 1),
(43, '8c10aab8-c3f9-11f0-a5dd-700894154732', 'UserUserNEw', '$2y$10$.SztrWd9MuPicITCuS5TmeY0k3conS68aGlYFa/DCZUEOnDJIMihi', NULL, NULL, 1, 1),
(44, '2c6fa561-c3fe-11f0-a5dd-700894154732', 'hehhe', '$2y$10$TNriBd.0p9Bo4ObMSbNaje6pWVUVm0gdIgA/6KnMUxGhOYmaY3J3y', NULL, NULL, 1, 1),
(45, '4aed8291-c3fe-11f0-a5dd-700894154732', 'newestguy', '$2y$10$yCBFX1t/6jEVCWKIGNkXI.QBuGaV/61YK84NqHUXjx8etujIHrc6O', NULL, 1, 1, 1),
(46, 'f1b5e6e8-c5ba-11f0-8d6c-700894154732', 'Proveedor1', '$2y$10$S5jucnoNa0Ehqj9bJ2rQPehdy3iBDUeJSZaVtukXkfBRAqwNOGb1C', NULL, 1, 2, 3),
(47, '09c35e10-c8d1-11f0-97fc-700894154732', 'Titanfall1', '$2y$10$IFsP5giKA2h7HVqpDlYrUOrFJSOf/As04HIdBkiAOZEEdSuJH/n9C', NULL, 1, 1, 1),
(48, 'da84b6ac-c958-11f0-84a7-700894154732', 'Propresident1', '$2y$10$etuN0PY52HvMeRydImGN5ePVCApPYxcg9phSGVyTuYVLy2Byw3KrG', '44', 1, 1, 2),
(49, '0df07728-ca5e-11f0-9d2b-700894154732', 'Fallman1', '$2y$10$0g821FTbBtM8fhy004hSM.fk9pFQs1jX290kpLn2XwpUnVwBOnUJO', NULL, 1, 1, 3),
(50, '41296644-ca5e-11f0-9d2b-700894154732', 'SonJong1', '$2y$10$Mkriiae4h9HwLV4qz31gFua05txupcEzWQlO8CGi2Aeb9XOPvhU8C', NULL, 1, 1, 3),
(52, '0f4fe5cd-ccca-11f0-b222-40c2ba844675', 'Reference1', '$2y$10$FX59BK7c0f2T4SDzZx9PC.8aI4cg/NNJ8EOpmTF9qMXaRbm6Jsnou', NULL, 1, 2, 1);

-- --------------------------------------------------------

--
-- Table structure for table `priv_usuario_registro`
--

CREATE TABLE `priv_usuario_registro` (
  `id_registro` int NOT NULL,
  `accion` varchar(100) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `id_usuario` int NOT NULL,
  `id_estatus` int NOT NULL,
  `documento` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `priv_visitas`
--

CREATE TABLE `priv_visitas` (
  `id_visita` int NOT NULL,
  `public_id` char(36) NOT NULL DEFAULT (uuid()),
  `nombre_visitante` varchar(100) NOT NULL,
  `apellido_visitante` varchar(100) NOT NULL,
  `tipo_visita` enum('Familiar','Amigo','Otro') NOT NULL,
  `id_usuario` int NOT NULL,
  `observaciones` text,
  `estatus` int DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `priv_visitas`
--

INSERT INTO `priv_visitas` (`id_visita`, `public_id`, `nombre_visitante`, `apellido_visitante`, `tipo_visita`, `id_usuario`, `observaciones`, `estatus`) VALUES
(1, '26eca4a2-bc3f-11f0-9567-40c2ba844675', 'Alex', 'Caamal', 'Familiar', 2, 'Nada', 1),
(2, '26ecaec0-bc3f-11f0-9567-40c2ba844675', 'yo', 'sussyimposter', 'Familiar', 16, 'Looks sus', 1),
(3, '82ed2ec6-c7e7-11f0-80f2-700894154732', 'Visita de Hunter', 'Hunter Gomez Bolaños', 'Familiar', 16, 'Es Gomez Bolaños', 1);

-- --------------------------------------------------------

--
-- Table structure for table `priv_visitas_registro`
--

CREATE TABLE `priv_visitas_registro` (
  `id_registro` int NOT NULL,
  `accion` varchar(100) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `id_usuario` int NOT NULL,
  `id_estatus` int NOT NULL,
  `documento` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `priv_actividades_programadas`
--
ALTER TABLE `priv_actividades_programadas`
  ADD PRIMARY KEY (`programada_id`),
  ADD UNIQUE KEY `public_id` (`public_id`),
  ADD KEY `id_privada_fk` (`id_privada_fk`),
  ADD KEY `actividad_tipo_id` (`actividad_tipo_id`),
  ADD KEY `usuario_id_responsable` (`usuario_id_responsable`),
  ADD KEY `id_estatus` (`id_estatus`);

--
-- Indexes for table `priv_actividades_reportes`
--
ALTER TABLE `priv_actividades_reportes`
  ADD PRIMARY KEY (`reporte_id`),
  ADD UNIQUE KEY `public_id` (`public_id`),
  ADD UNIQUE KEY `programada_id` (`programada_id`),
  ADD KEY `usuario_id_reporta` (`usuario_id_reporta`);

--
-- Indexes for table `priv_actividades_tipos`
--
ALTER TABLE `priv_actividades_tipos`
  ADD PRIMARY KEY (`actividad_tipo_id`),
  ADD UNIQUE KEY `uq_servicio_actividad` (`id_servicio_fk`,`nombre`);

--
-- Indexes for table `priv_avisos`
--
ALTER TABLE `priv_avisos`
  ADD PRIMARY KEY (`id_aviso`),
  ADD UNIQUE KEY `public_id` (`public_id`),
  ADD UNIQUE KEY `public_id_2` (`public_id`),
  ADD UNIQUE KEY `public_id_3` (`public_id`),
  ADD KEY `id_info` (`id_info`),
  ADD KEY `estatus` (`estatus`);

--
-- Indexes for table `priv_configuracion`
--
ALTER TABLE `priv_configuracion`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `priv_correoprove`
--
ALTER TABLE `priv_correoprove`
  ADD PRIMARY KEY (`id_correo`),
  ADD UNIQUE KEY `public_id` (`public_id`),
  ADD UNIQUE KEY `public_id_2` (`public_id`),
  ADD KEY `id_proveedor` (`id_proveedor`),
  ADD KEY `id_estatus` (`id_estatus`);

--
-- Indexes for table `priv_corresusuario`
--
ALTER TABLE `priv_corresusuario`
  ADD PRIMARY KEY (`id_correo`),
  ADD UNIQUE KEY `public_id` (`public_id`),
  ADD UNIQUE KEY `public_id_2` (`public_id`),
  ADD KEY `id_info` (`id_info`),
  ADD KEY `id_estatus` (`id_estatus`);

--
-- Indexes for table `priv_estatus`
--
ALTER TABLE `priv_estatus`
  ADD PRIMARY KEY (`id_estatus`);

--
-- Indexes for table `priv_fallos`
--
ALTER TABLE `priv_fallos`
  ADD PRIMARY KEY (`id_fallo`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_estatus` (`id_estatus`);

--
-- Indexes for table `priv_infousuario`
--
ALTER TABLE `priv_infousuario`
  ADD PRIMARY KEY (`id_info`),
  ADD UNIQUE KEY `public_id` (`public_id`),
  ADD UNIQUE KEY `public_id_2` (`public_id`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indexes for table `priv_info_usuario_registro`
--
ALTER TABLE `priv_info_usuario_registro`
  ADD PRIMARY KEY (`id_registro`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_estatus` (`id_estatus`);

--
-- Indexes for table `priv_metpagos`
--
ALTER TABLE `priv_metpagos`
  ADD PRIMARY KEY (`id_metstag`);

--
-- Indexes for table `priv_modelocasa`
--
ALTER TABLE `priv_modelocasa`
  ADD PRIMARY KEY (`id_casa`),
  ADD KEY `id_privada` (`id_privada`);

--
-- Indexes for table `priv_pagos`
--
ALTER TABLE `priv_pagos`
  ADD PRIMARY KEY (`id_pago`),
  ADD UNIQUE KEY `public_id` (`public_id`),
  ADD UNIQUE KEY `public_id_2` (`public_id`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_estatuspago` (`id_estatuspago`),
  ADD KEY `id_privada` (`id_privada`);

--
-- Indexes for table `priv_pases_visitas`
--
ALTER TABLE `priv_pases_visitas`
  ADD PRIMARY KEY (`id_pase`),
  ADD UNIQUE KEY `codigo_acceso` (`codigo_acceso`),
  ADD UNIQUE KEY `public_id` (`public_id`),
  ADD UNIQUE KEY `public_id_2` (`public_id`),
  ADD KEY `estatus` (`estatus`),
  ADD KEY `id_visita` (`id_visita`);

--
-- Indexes for table `priv_privadas`
--
ALTER TABLE `priv_privadas`
  ADD PRIMARY KEY (`id_privada`),
  ADD UNIQUE KEY `public_id` (`public_id`),
  ADD UNIQUE KEY `public_id_2` (`public_id`),
  ADD UNIQUE KEY `public_id_3` (`public_id`),
  ADD KEY `id_estatus` (`id_estatus`);

--
-- Indexes for table `priv_privada_servicios`
--
ALTER TABLE `priv_privada_servicios`
  ADD PRIMARY KEY (`id_privada_servicio`),
  ADD UNIQUE KEY `public_id` (`public_id`),
  ADD UNIQUE KEY `public_id_2` (`public_id`),
  ADD KEY `fk_privada_servicios_privada` (`id_privada_fk`),
  ADD KEY `fk_privada_servicios_servicio` (`id_servicio_fk`),
  ADD KEY `id_proveedor_fk` (`id_proveedor_fk`);

--
-- Indexes for table `priv_privada_servicios_registro`
--
ALTER TABLE `priv_privada_servicios_registro`
  ADD PRIMARY KEY (`id_registro`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_estatus` (`id_estatus`);

--
-- Indexes for table `priv_provedores_registro`
--
ALTER TABLE `priv_provedores_registro`
  ADD PRIMARY KEY (`id_registro`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_estatus` (`id_estatus`);

--
-- Indexes for table `priv_proveedor`
--
ALTER TABLE `priv_proveedor`
  ADD PRIMARY KEY (`id_proveedor`),
  ADD UNIQUE KEY `public_id` (`public_id`),
  ADD UNIQUE KEY `public_id_2` (`public_id`),
  ADD KEY `id_estatus` (`id_estatus`);

--
-- Indexes for table `priv_proveedor_servicios`
--
ALTER TABLE `priv_proveedor_servicios`
  ADD PRIMARY KEY (`id_proveedor_servicio`),
  ADD UNIQUE KEY `uq_proveedor_servicio` (`id_proveedor_fk`,`id_servicio_fk`),
  ADD UNIQUE KEY `public_id` (`public_id`),
  ADD UNIQUE KEY `public_id_2` (`public_id`),
  ADD KEY `id_servicio_fk` (`id_servicio_fk`);

--
-- Indexes for table `priv_respaldo_op`
--
ALTER TABLE `priv_respaldo_op`
  ADD PRIMARY KEY (`id_respaldo`);

--
-- Indexes for table `priv_roles`
--
ALTER TABLE `priv_roles`
  ADD PRIMARY KEY (`id_rol`);

--
-- Indexes for table `priv_servicios`
--
ALTER TABLE `priv_servicios`
  ADD PRIMARY KEY (`id_servicio`),
  ADD UNIQUE KEY `public_id` (`public_id`),
  ADD UNIQUE KEY `public_id_2` (`public_id`),
  ADD KEY `id_proveedor` (`id_proveedor`),
  ADD KEY `id_estatus` (`id_estatus`),
  ADD KEY `id_categoria_fk` (`id_categoria_fk`);

--
-- Indexes for table `priv_servicios_categorias`
--
ALTER TABLE `priv_servicios_categorias`
  ADD PRIMARY KEY (`id_categoria`),
  ADD UNIQUE KEY `nombre` (`nombre`),
  ADD UNIQUE KEY `public_id` (`public_id`),
  ADD UNIQUE KEY `public_id_2` (`public_id`);

--
-- Indexes for table `priv_servicios_registro`
--
ALTER TABLE `priv_servicios_registro`
  ADD PRIMARY KEY (`id_registro`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_estatus` (`id_estatus`);

--
-- Indexes for table `priv_statuspagos`
--
ALTER TABLE `priv_statuspagos`
  ADD PRIMARY KEY (`id_estatuspago`);

--
-- Indexes for table `priv_telprove`
--
ALTER TABLE `priv_telprove`
  ADD PRIMARY KEY (`id_telefono`),
  ADD UNIQUE KEY `public_id` (`public_id`),
  ADD KEY `id_proveedor` (`id_proveedor`),
  ADD KEY `id_estatus` (`id_estatus`);

--
-- Indexes for table `priv_telusuario`
--
ALTER TABLE `priv_telusuario`
  ADD PRIMARY KEY (`id_telefono`),
  ADD UNIQUE KEY `public_id` (`public_id`),
  ADD KEY `id_info` (`id_info`),
  ADD KEY `id_estatus` (`id_estatus`);

--
-- Indexes for table `priv_usuarios`
--
ALTER TABLE `priv_usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `public_id` (`public_id`),
  ADD UNIQUE KEY `public_id_2` (`public_id`),
  ADD UNIQUE KEY `public_id_3` (`public_id`),
  ADD KEY `id_estatus` (`id_estatus`),
  ADD KEY `id_rol` (`id_rol`),
  ADD KEY `id_privada` (`id_privada`);

--
-- Indexes for table `priv_usuario_registro`
--
ALTER TABLE `priv_usuario_registro`
  ADD PRIMARY KEY (`id_registro`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_estatus` (`id_estatus`);

--
-- Indexes for table `priv_visitas`
--
ALTER TABLE `priv_visitas`
  ADD PRIMARY KEY (`id_visita`),
  ADD UNIQUE KEY `public_id` (`public_id`),
  ADD UNIQUE KEY `public_id_2` (`public_id`),
  ADD UNIQUE KEY `public_id_3` (`public_id`),
  ADD KEY `estatus` (`estatus`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indexes for table `priv_visitas_registro`
--
ALTER TABLE `priv_visitas_registro`
  ADD PRIMARY KEY (`id_registro`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_estatus` (`id_estatus`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `priv_actividades_programadas`
--
ALTER TABLE `priv_actividades_programadas`
  MODIFY `programada_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `priv_actividades_reportes`
--
ALTER TABLE `priv_actividades_reportes`
  MODIFY `reporte_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `priv_actividades_tipos`
--
ALTER TABLE `priv_actividades_tipos`
  MODIFY `actividad_tipo_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `priv_avisos`
--
ALTER TABLE `priv_avisos`
  MODIFY `id_aviso` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `priv_correoprove`
--
ALTER TABLE `priv_correoprove`
  MODIFY `id_correo` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `priv_corresusuario`
--
ALTER TABLE `priv_corresusuario`
  MODIFY `id_correo` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT for table `priv_estatus`
--
ALTER TABLE `priv_estatus`
  MODIFY `id_estatus` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `priv_fallos`
--
ALTER TABLE `priv_fallos`
  MODIFY `id_fallo` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `priv_infousuario`
--
ALTER TABLE `priv_infousuario`
  MODIFY `id_info` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `priv_info_usuario_registro`
--
ALTER TABLE `priv_info_usuario_registro`
  MODIFY `id_registro` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `priv_metpagos`
--
ALTER TABLE `priv_metpagos`
  MODIFY `id_metstag` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `priv_modelocasa`
--
ALTER TABLE `priv_modelocasa`
  MODIFY `id_casa` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `priv_pagos`
--
ALTER TABLE `priv_pagos`
  MODIFY `id_pago` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `priv_pases_visitas`
--
ALTER TABLE `priv_pases_visitas`
  MODIFY `id_pase` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `priv_privadas`
--
ALTER TABLE `priv_privadas`
  MODIFY `id_privada` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `priv_privada_servicios`
--
ALTER TABLE `priv_privada_servicios`
  MODIFY `id_privada_servicio` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `priv_privada_servicios_registro`
--
ALTER TABLE `priv_privada_servicios_registro`
  MODIFY `id_registro` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `priv_provedores_registro`
--
ALTER TABLE `priv_provedores_registro`
  MODIFY `id_registro` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `priv_proveedor`
--
ALTER TABLE `priv_proveedor`
  MODIFY `id_proveedor` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `priv_proveedor_servicios`
--
ALTER TABLE `priv_proveedor_servicios`
  MODIFY `id_proveedor_servicio` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `priv_respaldo_op`
--
ALTER TABLE `priv_respaldo_op`
  MODIFY `id_respaldo` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `priv_roles`
--
ALTER TABLE `priv_roles`
  MODIFY `id_rol` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `priv_servicios`
--
ALTER TABLE `priv_servicios`
  MODIFY `id_servicio` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `priv_servicios_categorias`
--
ALTER TABLE `priv_servicios_categorias`
  MODIFY `id_categoria` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `priv_servicios_registro`
--
ALTER TABLE `priv_servicios_registro`
  MODIFY `id_registro` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `priv_statuspagos`
--
ALTER TABLE `priv_statuspagos`
  MODIFY `id_estatuspago` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `priv_telprove`
--
ALTER TABLE `priv_telprove`
  MODIFY `id_telefono` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `priv_telusuario`
--
ALTER TABLE `priv_telusuario`
  MODIFY `id_telefono` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `priv_usuarios`
--
ALTER TABLE `priv_usuarios`
  MODIFY `id_usuario` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `priv_usuario_registro`
--
ALTER TABLE `priv_usuario_registro`
  MODIFY `id_registro` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `priv_visitas`
--
ALTER TABLE `priv_visitas`
  MODIFY `id_visita` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `priv_visitas_registro`
--
ALTER TABLE `priv_visitas_registro`
  MODIFY `id_registro` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `priv_actividades_programadas`
--
ALTER TABLE `priv_actividades_programadas`
  ADD CONSTRAINT `priv_actividades_programadas_ibfk_1` FOREIGN KEY (`id_privada_fk`) REFERENCES `priv_privadas` (`id_privada`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `priv_actividades_programadas_ibfk_2` FOREIGN KEY (`actividad_tipo_id`) REFERENCES `priv_actividades_tipos` (`actividad_tipo_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `priv_actividades_programadas_ibfk_3` FOREIGN KEY (`usuario_id_responsable`) REFERENCES `priv_usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `priv_actividades_programadas_ibfk_4` FOREIGN KEY (`id_estatus`) REFERENCES `priv_estatus` (`id_estatus`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `priv_actividades_reportes`
--
ALTER TABLE `priv_actividades_reportes`
  ADD CONSTRAINT `priv_actividades_reportes_ibfk_1` FOREIGN KEY (`programada_id`) REFERENCES `priv_actividades_programadas` (`programada_id`),
  ADD CONSTRAINT `priv_actividades_reportes_ibfk_2` FOREIGN KEY (`usuario_id_reporta`) REFERENCES `priv_usuarios` (`id_usuario`);

--
-- Constraints for table `priv_actividades_tipos`
--
ALTER TABLE `priv_actividades_tipos`
  ADD CONSTRAINT `priv_actividades_tipos_ibfk_1` FOREIGN KEY (`id_servicio_fk`) REFERENCES `priv_servicios` (`id_servicio`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `priv_avisos`
--
ALTER TABLE `priv_avisos`
  ADD CONSTRAINT `priv_avisos_ibfk_1` FOREIGN KEY (`id_info`) REFERENCES `priv_infousuario` (`id_info`),
  ADD CONSTRAINT `priv_avisos_ibfk_2` FOREIGN KEY (`estatus`) REFERENCES `priv_estatus` (`id_estatus`);

--
-- Constraints for table `priv_correoprove`
--
ALTER TABLE `priv_correoprove`
  ADD CONSTRAINT `priv_correoprove_ibfk_1` FOREIGN KEY (`id_proveedor`) REFERENCES `priv_proveedor` (`id_proveedor`),
  ADD CONSTRAINT `priv_correoprove_ibfk_2` FOREIGN KEY (`id_estatus`) REFERENCES `priv_estatus` (`id_estatus`);

--
-- Constraints for table `priv_corresusuario`
--
ALTER TABLE `priv_corresusuario`
  ADD CONSTRAINT `priv_corresusuario_ibfk_1` FOREIGN KEY (`id_info`) REFERENCES `priv_infousuario` (`id_info`),
  ADD CONSTRAINT `priv_corresusuario_ibfk_2` FOREIGN KEY (`id_estatus`) REFERENCES `priv_estatus` (`id_estatus`);

--
-- Constraints for table `priv_fallos`
--
ALTER TABLE `priv_fallos`
  ADD CONSTRAINT `priv_fallos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `priv_usuarios` (`id_usuario`),
  ADD CONSTRAINT `priv_fallos_ibfk_2` FOREIGN KEY (`id_estatus`) REFERENCES `priv_estatus` (`id_estatus`);

--
-- Constraints for table `priv_infousuario`
--
ALTER TABLE `priv_infousuario`
  ADD CONSTRAINT `priv_infousuario_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `priv_usuarios` (`id_usuario`);

--
-- Constraints for table `priv_info_usuario_registro`
--
ALTER TABLE `priv_info_usuario_registro`
  ADD CONSTRAINT `priv_info_usuario_registro_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `priv_usuarios` (`id_usuario`),
  ADD CONSTRAINT `priv_info_usuario_registro_ibfk_2` FOREIGN KEY (`id_estatus`) REFERENCES `priv_estatus` (`id_estatus`);

--
-- Constraints for table `priv_modelocasa`
--
ALTER TABLE `priv_modelocasa`
  ADD CONSTRAINT `priv_modelocasa_ibfk_1` FOREIGN KEY (`id_privada`) REFERENCES `priv_privadas` (`id_privada`);

--
-- Constraints for table `priv_pagos`
--
ALTER TABLE `priv_pagos`
  ADD CONSTRAINT `priv_pagos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `priv_usuarios` (`id_usuario`),
  ADD CONSTRAINT `priv_pagos_ibfk_2` FOREIGN KEY (`id_estatuspago`) REFERENCES `priv_estatus` (`id_estatus`),
  ADD CONSTRAINT `priv_pagos_ibfk_3` FOREIGN KEY (`id_privada`) REFERENCES `priv_privadas` (`id_privada`);

--
-- Constraints for table `priv_pases_visitas`
--
ALTER TABLE `priv_pases_visitas`
  ADD CONSTRAINT `priv_pases_visitas_ibfk_1` FOREIGN KEY (`estatus`) REFERENCES `priv_estatus` (`id_estatus`),
  ADD CONSTRAINT `priv_pases_visitas_ibfk_2` FOREIGN KEY (`id_visita`) REFERENCES `priv_visitas` (`id_visita`);

--
-- Constraints for table `priv_privadas`
--
ALTER TABLE `priv_privadas`
  ADD CONSTRAINT `priv_privadas_ibfk_1` FOREIGN KEY (`id_estatus`) REFERENCES `priv_estatus` (`id_estatus`);

--
-- Constraints for table `priv_privada_servicios`
--
ALTER TABLE `priv_privada_servicios`
  ADD CONSTRAINT `fk_privada_servicios_privada` FOREIGN KEY (`id_privada_fk`) REFERENCES `priv_privadas` (`id_privada`),
  ADD CONSTRAINT `fk_privada_servicios_servicio` FOREIGN KEY (`id_servicio_fk`) REFERENCES `priv_servicios` (`id_servicio`),
  ADD CONSTRAINT `priv_privada_servicios_ibfk_1` FOREIGN KEY (`id_proveedor_fk`) REFERENCES `priv_proveedor` (`id_proveedor`);

--
-- Constraints for table `priv_privada_servicios_registro`
--
ALTER TABLE `priv_privada_servicios_registro`
  ADD CONSTRAINT `priv_privada_servicios_registro_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `priv_usuarios` (`id_usuario`),
  ADD CONSTRAINT `priv_privada_servicios_registro_ibfk_2` FOREIGN KEY (`id_estatus`) REFERENCES `priv_estatus` (`id_estatus`);

--
-- Constraints for table `priv_provedores_registro`
--
ALTER TABLE `priv_provedores_registro`
  ADD CONSTRAINT `priv_provedores_registro_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `priv_usuarios` (`id_usuario`),
  ADD CONSTRAINT `priv_provedores_registro_ibfk_2` FOREIGN KEY (`id_estatus`) REFERENCES `priv_estatus` (`id_estatus`);

--
-- Constraints for table `priv_proveedor`
--
ALTER TABLE `priv_proveedor`
  ADD CONSTRAINT `priv_proveedor_ibfk_1` FOREIGN KEY (`id_estatus`) REFERENCES `priv_estatus` (`id_estatus`);

--
-- Constraints for table `priv_proveedor_servicios`
--
ALTER TABLE `priv_proveedor_servicios`
  ADD CONSTRAINT `priv_proveedor_servicios_ibfk_1` FOREIGN KEY (`id_proveedor_fk`) REFERENCES `priv_proveedor` (`id_proveedor`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `priv_proveedor_servicios_ibfk_2` FOREIGN KEY (`id_servicio_fk`) REFERENCES `priv_servicios` (`id_servicio`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `priv_servicios`
--
ALTER TABLE `priv_servicios`
  ADD CONSTRAINT `priv_servicios_ibfk_1` FOREIGN KEY (`id_proveedor`) REFERENCES `priv_proveedor` (`id_proveedor`),
  ADD CONSTRAINT `priv_servicios_ibfk_2` FOREIGN KEY (`id_estatus`) REFERENCES `priv_estatus` (`id_estatus`),
  ADD CONSTRAINT `priv_servicios_ibfk_3` FOREIGN KEY (`id_categoria_fk`) REFERENCES `priv_servicios_categorias` (`id_categoria`);

--
-- Constraints for table `priv_servicios_registro`
--
ALTER TABLE `priv_servicios_registro`
  ADD CONSTRAINT `priv_servicios_registro_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `priv_usuarios` (`id_usuario`),
  ADD CONSTRAINT `priv_servicios_registro_ibfk_2` FOREIGN KEY (`id_estatus`) REFERENCES `priv_estatus` (`id_estatus`);

--
-- Constraints for table `priv_telprove`
--
ALTER TABLE `priv_telprove`
  ADD CONSTRAINT `priv_telprove_ibfk_1` FOREIGN KEY (`id_proveedor`) REFERENCES `priv_proveedor` (`id_proveedor`),
  ADD CONSTRAINT `priv_telprove_ibfk_2` FOREIGN KEY (`id_estatus`) REFERENCES `priv_estatus` (`id_estatus`);

--
-- Constraints for table `priv_telusuario`
--
ALTER TABLE `priv_telusuario`
  ADD CONSTRAINT `priv_telusuario_ibfk_1` FOREIGN KEY (`id_info`) REFERENCES `priv_infousuario` (`id_info`),
  ADD CONSTRAINT `priv_telusuario_ibfk_2` FOREIGN KEY (`id_estatus`) REFERENCES `priv_estatus` (`id_estatus`);

--
-- Constraints for table `priv_usuarios`
--
ALTER TABLE `priv_usuarios`
  ADD CONSTRAINT `priv_usuarios_ibfk_1` FOREIGN KEY (`id_estatus`) REFERENCES `priv_estatus` (`id_estatus`),
  ADD CONSTRAINT `priv_usuarios_ibfk_2` FOREIGN KEY (`id_rol`) REFERENCES `priv_roles` (`id_rol`),
  ADD CONSTRAINT `priv_usuarios_ibfk_3` FOREIGN KEY (`id_privada`) REFERENCES `priv_privadas` (`id_privada`);

--
-- Constraints for table `priv_usuario_registro`
--
ALTER TABLE `priv_usuario_registro`
  ADD CONSTRAINT `priv_usuario_registro_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `priv_usuarios` (`id_usuario`),
  ADD CONSTRAINT `priv_usuario_registro_ibfk_2` FOREIGN KEY (`id_estatus`) REFERENCES `priv_estatus` (`id_estatus`);

--
-- Constraints for table `priv_visitas`
--
ALTER TABLE `priv_visitas`
  ADD CONSTRAINT `priv_visitas_ibfk_1` FOREIGN KEY (`estatus`) REFERENCES `priv_estatus` (`id_estatus`),
  ADD CONSTRAINT `priv_visitas_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `priv_usuarios` (`id_usuario`);

--
-- Constraints for table `priv_visitas_registro`
--
ALTER TABLE `priv_visitas_registro`
  ADD CONSTRAINT `priv_visitas_registro_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `priv_usuarios` (`id_usuario`),
  ADD CONSTRAINT `priv_visitas_registro_ibfk_2` FOREIGN KEY (`id_estatus`) REFERENCES `priv_estatus` (`id_estatus`);

DELIMITER $$
--
-- Events
--
CREATE DEFINER=`root`@`localhost` EVENT `limpiar_avisos_antiguos` ON SCHEDULE EVERY 1 DAY STARTS '2025-11-17 09:54:57' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
    -- 1. Declarar una variable para guardar el valor de la configuración
    DECLARE v_meses_retencion INT;

    -- 2. Leer el valor de la tabla 'priv_configuracion' y guardarlo en la variable
    -- Usamos COALESCE para poner un valor por defecto (ej. 6) 
    -- si por alguna razón la fila no existiera.
    SELECT COALESCE(
        (SELECT CAST(setting_value AS UNSIGNED) 
         FROM priv_configuracion 
         WHERE setting_key = 'meses_retencion_avisos'), 
        6  -- Valor por defecto si no encuentra nada
    ) INTO v_meses_retencion;

    -- 3. Usar esa variable en la sentencia DELETE
    DELETE FROM priv_avisos
    WHERE fecha_pub <= DATE_SUB(CURDATE(), INTERVAL v_meses_retencion MONTH);
END$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
