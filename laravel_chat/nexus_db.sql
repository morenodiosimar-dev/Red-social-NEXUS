-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 12, 2026 at 05:09 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `nexus_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `comentarios`
--

CREATE TABLE `comentarios` (
  `id` int(11) NOT NULL,
  `publicacion_id` int(11) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `contenido` text DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comentarios`
--

INSERT INTO `comentarios` (`id`, `publicacion_id`, `usuario_id`, `contenido`, `fecha`) VALUES
(3, 3, 31, 'hermoisa boda', '2025-12-27 16:39:50'),
(6, 5, 31, 'Hola', '2025-12-27 21:34:46'),
(28, 18, 17, 'hola', '2025-12-29 01:34:27'),
(30, 16, 17, 'gola', '2025-12-29 16:07:23'),
(50, 18, 31, 'hola', '2025-12-29 20:43:16'),
(58, 18, 17, 'priueba 2', '2025-12-29 20:49:24'),
(59, 19, 8, 'que hubo', '2025-12-29 20:51:32'),
(61, 19, 8, 'bello sam', '2025-12-29 21:22:58'),
(62, 19, 8, 'belle', '2025-12-29 21:31:48'),
(63, 20, 31, 'bella', '2025-12-29 21:36:49'),
(64, 20, 17, 'hermosa la princesa', '2025-12-30 14:27:21'),
(66, 19, 31, 'sam', '2026-01-04 15:08:01'),
(69, 16, 17, 'hermosa keisy', '2026-01-04 15:56:06'),
(70, 5, 17, 'hermoso atardecer', '2026-01-04 16:10:12'),
(77, 20, 17, 'gfdgtg', '2026-01-08 19:51:23'),
(79, 21, 26, 'prert', '2026-01-08 19:53:51'),
(81, 22, 17, 'shsaidhiusfid', '2026-01-08 20:06:48'),
(82, 22, 17, 'fgfhgf', '2026-01-08 20:17:54'),
(84, 16, 31, 'njklnok', '2026-01-08 20:25:00'),
(85, 19, 31, 'pppppppppp', '2026-01-08 20:25:13'),
(96, 16, 35, 'hiak', '2026-01-13 15:22:16'),
(97, 37, 53, 'que bonito esta genial', '2026-01-13 19:34:08'),
(98, 39, 32, 'que bonitaaa', '2026-01-13 21:00:49'),
(99, 33, 32, 'nore', '2026-01-13 22:20:59'),
(100, 42, 55, 'lindo', '2026-01-13 22:39:47'),
(101, 43, 32, 'me gustoo', '2026-01-13 22:42:00'),
(102, 43, 32, 'me gustoo', '2026-01-13 22:45:47'),
(103, 39, 37, 'hola', '2026-01-15 15:34:20'),
(105, 40, 37, 'cool', '2026-01-15 15:36:19'),
(106, 39, 37, 'hola', '2026-01-15 15:44:55'),
(107, 45, 37, 'AMO EL FUTBOLT', '2026-01-15 15:57:45'),
(108, 48, 57, 'me gustan tus diseños', '2026-01-31 16:04:50'),
(109, 48, 39, 'Qué bonito', '2026-02-07 21:07:19'),
(110, 51, 58, 'me gusta', '2026-02-07 21:13:51'),
(111, 50, 39, 'Me gusta tus ideas', '2026-02-07 21:19:20');

-- --------------------------------------------------------

--
-- Table structure for table `interes`
--

CREATE TABLE `interes` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `interes`
--

INSERT INTO `interes` (`id`, `name`, `icon`, `category`) VALUES
(1, 'Películas', 'film-outline', '🎬 Entretenimiento'),
(2, 'Series de TV', 'tv-outline', '🎬 Entretenimiento'),
(3, 'Música', 'musical-notes-outline', '🎬 Entretenimiento'),
(4, 'Videojuegos', 'game-controller-outline', '🎬 Entretenimiento'),
(5, 'Libros / Lectura', 'library-outline', '🎬 Entretenimiento'),
(6, 'Podcasts', 'mic-outline', '🎬 Entretenimiento'),
(7, 'Viajes', 'airplane-outline', '🌍 Cultura y estilo de vida'),
(8, 'Gastronomía / Recetas', 'restaurant-outline', '🌍 Cultura y estilo de vida'),
(9, 'Moda', 'shirt-outline', '🌍 Cultura y estilo de vida'),
(10, 'Arte / Diseño', 'color-palette-outline', '🌍 Cultura y estilo de vida'),
(11, 'Fotografía', 'camera-outline', '🌍 Cultura y estilo de vida'),
(12, 'Historia', 'book-outline', '🌍 Cultura y estilo de vida'),
(13, 'Salud y fitness', 'barbell-outline', '💪 Bienestar'),
(14, 'Nutrición', 'nutrition-outline', '💪 Bienestar'),
(15, 'Meditación / Mindfulness', 'leaf-outline', '💪 Bienestar'),
(16, 'Desarrollo personal', 'person-outline', '💪 Bienestar'),
(17, 'Psicología', 'heart-outline', '💪 Bienestar'),
(18, 'Tecnología', 'laptop-outline', '💼 Negocios y aprendizaje'),
(19, 'Ciencia', 'flask-outline', '💼 Negocios y aprendizaje'),
(20, 'Finanzas / Inversiones', 'cash-outline', '💼 Negocios y aprendizaje'),
(21, 'Marketing / Emprendimiento', 'trending-up-outline', '💼 Negocios y aprendizaje'),
(22, 'Educación / Cursos', 'school-outline', '💼 Negocios y aprendizaje'),
(23, 'Idiomas', 'language-outline', '💼 Negocios y aprendizaje'),
(24, 'Fútbol', 'football-outline', '⚽ Deportes'),
(25, 'Baloncesto', 'basketball-outline', '⚽ Deportes'),
(26, 'Béisbol', 'baseball-outline', '⚽ Deportes'),
(27, 'Tenis', 'tennisball-outline', '⚽ Deportes'),
(28, 'Deportes extremos', 'bicycle-outline', '⚽ Deportes'),
(29, 'eSports', 'trophy-outline', '⚽ Deportes'),
(30, 'Medio ambiente / Sostenibilidad', 'leaf-outline', '🌱 Intereses especiales'),
(31, 'Animales / Mascotas', 'paw-outline', '🌱 Intereses especiales'),
(32, 'Política / Actualidad', 'newspaper-outline', '🌱 Intereses especiales'),
(33, 'Voluntariado / Impacto social', 'people-outline', '🌱 Intereses especiales'),
(34, 'DIY / Manualidades', 'construct-outline', '🌱 Intereses especiales');

-- --------------------------------------------------------

--
-- Table structure for table `mensajes`
--

CREATE TABLE `mensajes` (
  `id` int(11) NOT NULL,
  `usuario` varchar(100) NOT NULL,
  `sala` varchar(100) NOT NULL,
  `mensaje` text NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mensajes`
--

INSERT INTO `mensajes` (`id`, `usuario`, `sala`, `mensaje`, `fecha`) VALUES
(1, '37', '35-37', 'hola', '2026-01-13 17:06:01'),
(2, '55', '32-55', 'hola como estas', '2026-01-13 23:06:56'),
(3, '55', '32-55', 'hola', '2026-01-13 23:07:01'),
(4, '55', '32-55', 'o', '2026-01-13 23:10:13'),
(5, '55', '32-55', 'kk', '2026-01-13 23:10:16'),
(6, '55', '32-55', 'norelis nahomi', '2026-01-13 23:12:02'),
(7, '32', '32-55', 'cesar graterol', '2026-01-13 23:12:17'),
(8, '32', '32-55', 'cesarrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrd dddddddddddddd', '2026-01-13 23:13:00'),
(9, '32', '32-55', 'sssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssss', '2026-01-13 23:13:12'),
(10, '55', '32-55', 'noreeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee', '2026-01-13 23:15:34'),
(11, '55', '32-55', 'noreeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee', '2026-01-13 23:16:43'),
(12, '55', '32-55', 'wwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwww', '2026-01-13 23:16:46'),
(13, '55', '32-55', 'eeee', '2026-01-13 23:16:54'),
(14, '55', '32-55', 'lll', '2026-01-13 23:16:57'),
(15, '32', '32-46', 'noreeeeeeeeeeeeeeeeeeee', '2026-01-13 23:19:41'),
(16, '32', '31-32', 'hola como estas', '2026-01-28 17:22:03'),
(17, '32', '31-32', 'que tal', '2026-01-28 17:22:12'),
(18, '31', '31-32', 'hola nore', '2026-01-28 17:23:07'),
(19, '31', '31-32', 'que hubo', '2026-01-28 17:23:22'),
(20, '32', '32-45', 'hol', '2026-01-28 17:24:26'),
(21, '32', '32-45', 'Hola', '2026-01-28 17:24:46'),
(22, '43', '43-58', 'Hola como estas?', '2026-01-31 16:14:37'),
(23, '43', '43-58', 'me gusta tu perfil es genial', '2026-01-31 16:14:50'),
(24, '58', '43-58', 'holaaaa', '2026-01-31 16:15:01'),
(25, '58', '43-58', 'me alegro mucho que te guste, sigueme y veras todos mis diseños', '2026-01-31 16:15:25'),
(26, '58', '43-58', '!!!!', '2026-01-31 16:15:53'),
(27, '43', '26-43', 'hola raul que tal', '2026-01-31 16:18:50'),
(28, '43', '43-56', 'sofia que gusto', '2026-01-31 16:19:08'),
(29, '43', '39-43', 'hola señora', '2026-01-31 16:19:25'),
(30, '43', '8-43', 'zulimaar', '2026-01-31 16:19:38'),
(31, '43', '7-43', 'richar', '2026-01-31 16:19:45'),
(32, '43', '41-43', 'ygnacio', '2026-01-31 16:19:54');

-- --------------------------------------------------------

--
-- Table structure for table `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `emisor_id` int(11) NOT NULL,
  `publicacion_id` int(11) DEFAULT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `leido` tinyint(1) DEFAULT 0,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notificaciones`
--

INSERT INTO `notificaciones` (`id`, `usuario_id`, `emisor_id`, `publicacion_id`, `tipo`, `leido`, `fecha`) VALUES
(1, 31, 17, 16, 'reaccion', 1, '2025-12-29 16:07:06'),
(2, 31, 17, 16, 'comentario', 1, '2025-12-29 16:07:23'),
(3, 31, 17, 16, 'republicar', 1, '2025-12-29 16:40:42'),
(4, 31, 17, 16, 'comentario', 1, '2025-12-29 16:59:30'),
(5, 31, 17, 16, 'comentario', 1, '2025-12-29 17:50:22'),
(7, 17, 31, 19, 'reaccion', 1, '2025-12-29 18:27:25'),
(10, 17, 31, 19, 'comentario', 1, '2025-12-29 18:59:44'),
(11, 17, 31, 19, 'comentario', 1, '2025-12-29 19:00:19'),
(12, 17, 31, 19, 'comentario', 1, '2025-12-29 19:01:46'),
(13, 17, 31, 19, 'comentario', 1, '2025-12-29 20:07:15'),
(14, 31, 17, 18, 'comentario', 1, '2025-12-29 20:08:59'),
(15, 31, 17, 18, 'comentario', 1, '2025-12-29 20:43:46'),
(16, 17, 31, 19, 'comentario', 1, '2025-12-29 20:45:53'),
(17, 17, 31, 19, 'comentario', 1, '2025-12-29 20:48:05'),
(18, 31, 17, 18, 'comentario', 1, '2025-12-29 20:49:24'),
(19, 17, 8, 19, 'comentario', 1, '2025-12-29 20:51:32'),
(21, 17, 8, 19, 'comentario', 1, '2025-12-29 21:22:58'),
(22, 17, 8, 19, 'comentario', 1, '2025-12-29 21:31:48'),
(23, 31, 17, 20, 'reaccion', 1, '2025-12-29 21:37:19'),
(24, 31, 17, 20, 'comentario', 1, '2025-12-30 14:27:22'),
(25, 17, 31, 19, 'comentario', 1, '2026-01-04 15:07:23'),
(26, 17, 31, 19, 'comentario', 1, '2026-01-04 15:08:01'),
(27, 31, 17, 18, 'comentario', 1, '2026-01-04 15:49:25'),
(28, 31, 17, 16, 'reaccion', 1, '2026-01-04 15:53:30'),
(29, 31, 17, 16, 'comentario', 1, '2026-01-04 15:56:06'),
(30, 31, 17, 5, 'comentario', 1, '2026-01-04 16:10:12'),
(31, 17, 31, 19, 'comentario', 1, '2026-01-04 16:12:51'),
(32, 17, 31, 19, 'comentario', 1, '2026-01-04 16:46:34'),
(33, 17, 31, NULL, 'seguir', 1, '2026-01-05 20:18:11'),
(34, 31, 17, NULL, 'seguir', 1, '2026-01-05 20:18:56'),
(35, 31, 17, NULL, 'seguir', 1, '2026-01-05 20:27:10'),
(36, 31, 17, NULL, 'seguir', 1, '2026-01-05 20:46:33'),
(37, 31, 17, NULL, 'seguir', 1, '2026-01-05 20:46:35'),
(38, 31, 17, NULL, 'seguir', 1, '2026-01-05 20:46:37'),
(39, 31, 17, NULL, 'seguir', 1, '2026-01-05 20:51:03'),
(40, 31, 17, 14, 'reaccion', 1, '2026-01-05 20:55:52'),
(41, 31, 17, 18, 'repost', 1, '2026-01-05 23:57:49'),
(42, 31, 17, 5, 'repost', 1, '2026-01-06 00:14:15'),
(44, 31, 17, 4, 'repost', 1, '2026-01-06 00:18:24'),
(47, 31, 26, NULL, 'seguir', 1, '2026-01-06 01:38:48'),
(48, 26, 31, NULL, 'seguir', 1, '2026-01-06 03:02:15'),
(49, 17, 26, 19, 'reaccion', 1, '2026-01-07 17:22:29'),
(50, 17, 26, 19, 'reaccion', 1, '2026-01-07 17:23:07'),
(51, 17, 26, 19, 'reaccion', 1, '2026-01-07 17:33:00'),
(52, 17, 26, 21, 'reaccion', 1, '2026-01-07 17:38:21'),
(53, 17, 31, 21, 'reaccion', 1, '2026-01-07 17:38:44'),
(54, 31, 17, 18, 'comentario', 1, '2026-01-07 17:39:39'),
(55, 14, 31, NULL, 'seguir', 0, '2026-01-07 17:41:08'),
(56, 26, 17, 22, 'reaccion', 1, '2026-01-07 21:35:07'),
(57, 26, 17, 22, 'reaccion', 1, '2026-01-07 21:35:24'),
(58, 26, 17, 22, 'reaccion', 1, '2026-01-07 21:35:28'),
(59, 26, 17, 22, 'reaccion', 1, '2026-01-07 21:35:30'),
(60, 26, 17, 22, 'reaccion', 1, '2026-01-07 22:04:18'),
(61, 26, 31, 22, 'reaccion', 1, '2026-01-07 22:04:45'),
(62, 17, 26, 21, 'republicar', 1, '2026-01-07 22:33:49'),
(63, 26, 17, 22, 'comentario', 1, '2026-01-08 18:56:35'),
(64, 26, 17, 22, 'comentario', 1, '2026-01-08 20:05:55'),
(65, 26, 17, 22, 'comentario', 1, '2026-01-08 20:06:48'),
(66, 26, 17, 22, 'comentario', 1, '2026-01-08 20:17:54'),
(67, 31, 17, 20, 'comentario', 1, '2026-01-08 20:21:08'),
(68, 17, 31, 19, 'comentario', 1, '2026-01-08 20:25:16'),
(69, 26, 31, 22, 'comentario', 1, '2026-01-08 20:33:53'),
(70, 26, 17, 22, 'comentario', 1, '2026-01-08 20:39:34'),
(71, 26, 17, 22, 'comentario', 1, '2026-01-08 20:39:46'),
(72, 26, 17, 22, 'comentario', 1, '2026-01-08 20:40:24'),
(73, 26, 17, 22, 'comentario', 1, '2026-01-08 20:58:09'),
(74, 17, 35, 19, 'reaccion', 0, '2026-01-13 15:08:21'),
(75, 38, 35, NULL, 'seguir', 1, '2026-01-13 15:08:44'),
(76, 37, 35, NULL, 'seguir', 1, '2026-01-13 15:19:45'),
(77, 31, 35, 16, 'comentario', 1, '2026-01-13 15:22:16'),
(78, 17, 35, NULL, 'seguir', 0, '2026-01-13 15:39:42'),
(79, 49, 37, NULL, 'seguir', 0, '2026-01-13 16:11:15'),
(80, 35, 37, NULL, 'seguir', 1, '2026-01-13 16:11:20'),
(81, 31, 42, 18, 'reaccion', 1, '2026-01-13 17:23:41'),
(82, 42, 53, 37, 'reaccion', 0, '2026-01-13 19:34:01'),
(83, 42, 53, 37, 'comentario', 0, '2026-01-13 19:34:08'),
(84, 37, 38, NULL, 'seguir', 1, '2026-01-13 19:38:03'),
(85, 37, 53, 39, 'reaccion', 1, '2026-01-13 19:54:50'),
(86, 26, 53, NULL, 'seguir', 0, '2026-01-13 20:01:32'),
(87, 37, 53, NULL, 'seguir', 1, '2026-01-13 20:01:40'),
(88, 42, 53, NULL, 'seguir', 0, '2026-01-13 20:01:42'),
(89, 37, 32, 34, 'reaccion', 1, '2026-01-13 21:00:40'),
(90, 37, 32, 39, 'comentario', 1, '2026-01-13 21:00:49'),
(91, 37, 32, NULL, 'seguir', 1, '2026-01-13 21:00:55'),
(92, 53, 32, 40, 'reaccion', 0, '2026-01-13 21:52:31'),
(93, 42, 32, 38, 'reaccion', 0, '2026-01-13 21:52:35'),
(94, 42, 32, 37, 'reaccion', 0, '2026-01-13 21:52:41'),
(95, 42, 32, 36, 'reaccion', 0, '2026-01-13 21:52:46'),
(96, 37, 32, 39, 'reaccion', 1, '2026-01-13 22:13:23'),
(97, 35, 32, 33, 'comentario', 1, '2026-01-13 22:20:59'),
(98, 40, 32, NULL, 'seguir', 0, '2026-01-13 22:25:02'),
(99, 53, 32, NULL, 'seguir', 0, '2026-01-13 22:25:03'),
(100, 1, 32, NULL, 'seguir', 0, '2026-01-13 22:25:04'),
(101, 32, 54, 42, 'reaccion', 1, '2026-01-13 22:36:06'),
(102, 32, 55, 42, 'reaccion', 1, '2026-01-13 22:39:42'),
(103, 32, 55, 42, 'comentario', 1, '2026-01-13 22:39:47'),
(104, 37, 55, NULL, 'seguir', 1, '2026-01-13 22:40:51'),
(105, 54, 55, NULL, 'seguir', 0, '2026-01-13 22:41:23'),
(106, 37, 55, 39, 'reaccion', 1, '2026-01-13 22:41:30'),
(107, 55, 32, 43, 'reaccion', 1, '2026-01-13 22:41:43'),
(108, 55, 32, 43, 'comentario', 1, '2026-01-13 22:42:00'),
(109, 55, 32, NULL, 'seguir', 1, '2026-01-13 22:42:19'),
(110, 55, 32, 43, 'comentario', 1, '2026-01-13 22:45:47'),
(111, 55, 32, 44, 'reaccion', 1, '2026-01-13 22:47:06'),
(112, 53, 37, 40, 'comentario', 0, '2026-01-15 15:36:19'),
(113, 32, 37, NULL, 'seguir', 0, '2026-01-15 15:36:25'),
(114, 14, 37, NULL, 'seguir', 0, '2026-01-15 15:43:51'),
(115, 38, 37, 45, 'reaccion', 1, '2026-01-15 15:57:39'),
(116, 38, 37, 45, 'comentario', 1, '2026-01-15 15:57:45'),
(117, 35, 56, 33, 'reaccion', 1, '2026-01-24 13:00:51'),
(118, 58, 57, 50, 'reaccion', 1, '2026-01-31 16:04:37'),
(119, 58, 57, 49, 'reaccion', 1, '2026-01-31 16:04:40'),
(120, 58, 57, 48, 'comentario', 1, '2026-01-31 16:04:50'),
(121, 58, 57, NULL, 'seguir', 1, '2026-01-31 16:04:57'),
(122, 57, 58, 51, 'reaccion', 1, '2026-01-31 16:07:46'),
(123, 58, 38, 48, 'reaccion', 1, '2026-02-07 21:05:41'),
(124, 58, 39, 48, 'reaccion', 1, '2026-02-07 21:07:02'),
(125, 58, 39, 48, 'comentario', 1, '2026-02-07 21:07:19'),
(126, 57, 58, 51, 'comentario', 0, '2026-02-07 21:13:51'),
(127, 41, 58, NULL, 'seguir', 0, '2026-02-07 21:14:11'),
(128, 55, 58, 44, 'reaccion', 0, '2026-02-07 21:17:17'),
(129, 55, 58, NULL, 'seguir', 0, '2026-02-07 21:17:24'),
(130, 57, 39, 51, 'reaccion', 0, '2026-02-07 21:18:53'),
(131, 58, 39, 50, 'reaccion', 0, '2026-02-07 21:19:00'),
(132, 58, 39, 50, 'comentario', 0, '2026-02-07 21:19:20');

-- --------------------------------------------------------

--
-- Table structure for table `publicaciones`
--

CREATE TABLE `publicaciones` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tipo_perfil` enum('personal','contenido') NOT NULL,
  `interes_id` int(11) DEFAULT NULL,
  `caption` text DEFAULT NULL,
  `ruta_archivo` varchar(255) NOT NULL,
  `tipo_archivo` varchar(50) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `publicaciones`
--

INSERT INTO `publicaciones` (`id`, `usuario_id`, `tipo_perfil`, `interes_id`, `caption`, `ruta_archivo`, `tipo_archivo`, `fecha_creacion`) VALUES
(3, 31, 'personal', NULL, 'boda tia', 'uploads/1766808753_100_3896.jpg', 'image/jpeg', '2025-12-27 04:12:33'),
(4, 31, 'personal', NULL, 'Panchito', 'uploads/1766810809_AGC_20240418_213236678.jpg', 'image/jpeg', '2025-12-27 04:46:49'),
(5, 31, 'contenido', NULL, 'Atardecer', 'uploads/1766810926_AGC_20240405_182008202.jpg', 'image/jpeg', '2025-12-27 04:48:46'),
(7, 8, 'personal', NULL, 'Siembra', 'uploads/1766941941_AGC_20240327_080432294.jpg', 'image/jpeg', '2025-12-28 17:12:21'),
(8, 8, 'contenido', NULL, '', 'uploads/1766949700_100_3904.jpg', 'image/jpeg', '2025-12-28 19:21:40'),
(14, 31, 'contenido', NULL, '', 'uploads/1766953155_AGC_20240427_205202354.jpg', 'image/jpeg', '2025-12-28 20:19:15'),
(16, 31, 'contenido', NULL, '', 'uploads/1766970534_AGC_20240405_150901239.jpg', 'image/jpeg', '2025-12-29 01:08:54'),
(18, 31, 'contenido', NULL, 'petete', 'uploads/1766971477_AGC_20240405_175126461.jpg', 'image/jpeg', '2025-12-29 01:24:37'),
(19, 17, 'contenido', NULL, 'perrito', 'uploads/1766972334_AGC_20240718_100258855.jpg', 'image/jpeg', '2025-12-29 01:38:54'),
(20, 31, 'personal', NULL, 'keisy', 'uploads/1767044181_AGC_20240405_150901239.jpg', 'image/jpeg', '2025-12-29 21:36:21'),
(21, 17, 'personal', NULL, 'Bonita', 'uploads/1767807251_AGC_20240713_191038812.jpg', 'image/jpeg', '2026-01-07 17:34:11'),
(22, 26, 'personal', NULL, '', 'uploads/1767808399_WhatsApp Image 2025-10-30 at 8.33.29 PM.jpeg', 'image/jpeg', '2026-01-07 17:53:19'),
(23, 35, 'contenido', NULL, '', 'uploads/1768316689_coche-de-carreras-desde-arriba-lindo-auto-dibujos-animados-con-sombras-vehículo-deportivo-urbano-moderno-una-las-colecciones-o-176545794.webp', 'image/webp', '2026-01-13 15:04:49'),
(24, 35, 'contenido', NULL, '', 'uploads/1768316987_WhatsApp Image 2025-09-22 at 11.46.46 AM.jpeg', 'image/jpeg', '2026-01-13 15:09:47'),
(25, 35, 'contenido', NULL, '', 'uploads/1768317418_premium_photo-1683619761468-b06992704398.jpg', 'image/jpeg', '2026-01-13 15:16:58'),
(26, 35, 'contenido', NULL, '', 'uploads/1768317569_IMG-20250618-WA0027.jpg', 'image/jpeg', '2026-01-13 15:19:29'),
(27, 35, 'contenido', NULL, '', 'uploads/1768317828_WhatsApp Image 2025-11-27 at 7.36.33 PM (1).JPG', 'image/jpeg', '2026-01-13 15:23:48'),
(28, 35, 'contenido', NULL, '', 'uploads/1768318164_Captura de pantalla 2025-11-16 151608.png', 'image/png', '2026-01-13 15:29:24'),
(29, 35, 'contenido', NULL, '', 'uploads/1768318451_Captura de pantalla 2025-11-14 204225.png', 'image/png', '2026-01-13 15:34:11'),
(30, 35, 'contenido', NULL, '', 'uploads/1768318799_Captura de pantalla 2025-11-14 204225.png', 'image/png', '2026-01-13 15:40:00'),
(31, 35, 'contenido', 33, '', 'uploads/1768319514_Captura de pantalla 2025-11-29 140436.png', 'image/png', '2026-01-13 15:51:54'),
(32, 35, 'contenido', 16, '', 'uploads/1768319613_Captura de pantalla 2025-11-14 204225.png', 'image/png', '2026-01-13 15:53:33'),
(33, 35, 'contenido', 18, '', 'uploads/1768319766_Captura de pantalla 2025-11-22 213615.png', 'image/png', '2026-01-13 15:56:06'),
(34, 37, 'contenido', NULL, '', 'uploads/1768320721_WhatsApp Image 2025-12-10 at 12.40.36 PM.jpeg', 'image/jpeg', '2026-01-13 16:12:01'),
(35, 42, 'contenido', NULL, '', 'uploads/1768325140_premium_photo-1683619761468-b06992704398.jpg', 'image/jpeg', '2026-01-13 17:25:40'),
(36, 42, 'contenido', NULL, '', 'uploads/1768325256_WhatsApp Image 2025-10-29 at 3.26.15 PM (2).jpeg', 'image/jpeg', '2026-01-13 17:27:36'),
(37, 42, 'contenido', 29, '', 'uploads/1768325422_coche-de-carreras-desde-arriba-lindo-auto-dibujos-animados-con-sombras-vehículo-deportivo-urbano-moderno-una-las-colecciones-o-176545794.webp', 'image/webp', '2026-01-13 17:30:22'),
(38, 42, 'contenido', 16, '', 'uploads/1768325526_WhatsApp Image 2025-11-27 at 7.36.33 PM (1).JPG', 'image/jpeg', '2026-01-13 17:32:06'),
(39, 37, 'contenido', 11, '', 'uploads/1768329168_WhatsApp Image 2025-11-30 at 1.44.15 PM.jpeg', 'image/jpeg', '2026-01-13 18:32:48'),
(40, 53, 'contenido', 8, '', 'uploads/1768334399_premium_photo-1683619761468-b06992704398.jpg', 'image/jpeg', '2026-01-13 19:59:59'),
(42, 32, 'contenido', 11, '', 'uploads/1768342293_WhatsApp Image 2025-11-27 at 7.15.50 PM (1).jpeg', 'image/jpeg', '2026-01-13 22:11:33'),
(43, 55, 'contenido', 11, 'un fondo', 'uploads/1768344042_d9e14c1cdb391aa7f2a3e29c2cca3247.jpg', 'image/jpeg', '2026-01-13 22:40:42'),
(44, 55, 'contenido', 16, '', 'uploads/1768344411_banner-o-cinta-feliz-graduacion-nino-sosteniendo-trofeo-papel-cute-kawaii-chibi-cartoon_380474-588.avif', 'image/avif', '2026-01-13 22:46:51'),
(45, 38, 'contenido', 24, '', 'uploads/1768492615_futbol.jpg', 'image/jpeg', '2026-01-15 15:56:55'),
(46, 38, 'contenido', 11, '', 'uploads/1769259501_WhatsApp Image 2026-01-22 at 10.22.58 PM.JPG', 'image/jpeg', '2026-01-24 12:58:21'),
(47, 31, 'personal', NULL, '', 'uploads/1769630703_Grabación de pantalla 2025-11-17 115249.mp4', 'video/mp4', '2026-01-28 20:05:03'),
(48, 58, 'contenido', 9, 'Diseños', 'uploads/1769875412_d7b07956-8702-4b6d-acb8-43813c84bfff.jpg', 'image/jpeg', '2026-01-31 16:03:32'),
(49, 58, 'contenido', 9, 'Desfile', 'uploads/1769875438_COLECCIÓN-DE-MODAS-fall-winter-By-Paola-Castillo.jpg', 'image/jpeg', '2026-01-31 16:03:58'),
(50, 58, 'contenido', 9, '', 'uploads/1769875454_images.jpg', 'image/jpeg', '2026-01-31 16:04:14'),
(51, 57, 'contenido', 10, '', 'uploads/1769875629_images (1).jpg', 'image/jpeg', '2026-01-31 16:07:09'),
(52, 58, 'personal', NULL, '', 'uploads/1769875717_75044753-group-of-friends-are-sharing-a-slice-of-cake-at-a-birthday-party-one-person-is-using-his-smart.jpg', 'image/jpeg', '2026-01-31 16:08:37'),
(53, 57, 'personal', NULL, '', 'uploads/1769875909_e897cd42ee3069f69c055f8ceba0f8c0.jpg', 'image/jpeg', '2026-01-31 16:11:49'),
(54, 58, 'contenido', 3, '', 'uploads/1769877483_Guitarras-scaled-e1726851322582.jpg', 'image/jpeg', '2026-01-31 16:38:04'),
(55, 58, 'contenido', 5, '', 'uploads/1770498724_images (3).jpg', 'image/jpeg', '2026-02-07 21:12:04');

-- --------------------------------------------------------

--
-- Table structure for table `reacciones`
--

CREATE TABLE `reacciones` (
  `id` int(11) NOT NULL,
  `publicacion_id` int(11) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reacciones`
--

INSERT INTO `reacciones` (`id`, `publicacion_id`, `usuario_id`, `fecha`) VALUES
(2, 4, 31, '2025-12-27 15:11:50'),
(5, 5, 31, '2025-12-27 17:24:32'),
(7, 3, 31, '2025-12-27 21:47:30'),
(13, 7, 8, '2025-12-28 17:13:53'),
(14, 5, 8, '2025-12-28 17:13:57'),
(15, 7, 31, '2025-12-28 17:27:07'),
(17, 8, 31, '2025-12-28 20:16:04'),
(19, 18, 8, '2025-12-29 01:28:33'),
(20, 18, 31, '2025-12-29 01:28:53'),
(21, 18, 17, '2025-12-29 01:34:21'),
(22, 3, 17, '2025-12-29 01:34:51'),
(25, 19, 31, '2025-12-29 18:27:25'),
(26, 20, 31, '2025-12-29 21:36:39'),
(27, 20, 17, '2025-12-29 21:37:19'),
(28, 16, 17, '2026-01-04 15:53:30'),
(30, 14, 17, '2026-01-05 20:55:52'),
(33, 19, 26, '2026-01-07 17:33:00'),
(34, 21, 26, '2026-01-07 17:38:21'),
(35, 21, 31, '2026-01-07 17:38:44'),
(37, 21, 17, '2026-01-07 21:35:16'),
(41, 19, 17, '2026-01-07 22:02:54'),
(42, 22, 17, '2026-01-07 22:04:18'),
(43, 22, 31, '2026-01-07 22:04:45'),
(44, 19, 35, '2026-01-13 15:08:21'),
(45, 34, 37, '2026-01-13 17:03:51'),
(46, 18, 42, '2026-01-13 17:23:41'),
(47, 37, 53, '2026-01-13 19:34:01'),
(48, 39, 53, '2026-01-13 19:54:50'),
(49, 34, 32, '2026-01-13 21:00:40'),
(50, 40, 32, '2026-01-13 21:52:31'),
(51, 38, 32, '2026-01-13 21:52:35'),
(52, 37, 32, '2026-01-13 21:52:41'),
(53, 36, 32, '2026-01-13 21:52:46'),
(54, 39, 32, '2026-01-13 22:13:23'),
(55, 42, 54, '2026-01-13 22:36:06'),
(58, 42, 55, '2026-01-13 22:39:42'),
(59, 39, 55, '2026-01-13 22:41:30'),
(60, 43, 32, '2026-01-13 22:41:43'),
(61, 44, 32, '2026-01-13 22:47:06'),
(62, 45, 37, '2026-01-15 15:57:39'),
(63, 33, 56, '2026-01-24 13:00:51'),
(64, 50, 57, '2026-01-31 16:04:37'),
(65, 49, 57, '2026-01-31 16:04:40'),
(66, 51, 58, '2026-01-31 16:07:46'),
(67, 48, 58, '2026-02-07 21:04:44'),
(68, 48, 38, '2026-02-07 21:05:41'),
(69, 48, 39, '2026-02-07 21:07:02'),
(70, 44, 58, '2026-02-07 21:17:17'),
(71, 51, 39, '2026-02-07 21:18:53'),
(72, 50, 39, '2026-02-07 21:19:00');

-- --------------------------------------------------------

--
-- Table structure for table `republicaciones`
--

CREATE TABLE `republicaciones` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `publicacion_id` int(11) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `republicaciones`
--

INSERT INTO `republicaciones` (`id`, `usuario_id`, `publicacion_id`, `fecha`) VALUES
(1, 31, 16, '2026-01-04 18:25:46'),
(2, 31, 19, '2026-01-04 18:28:43'),
(3, 17, 8, '2026-01-04 18:31:36'),
(4, 31, 20, '2026-01-04 20:30:45'),
(6, 31, 8, '2026-01-05 19:57:31'),
(7, 17, 14, '2026-01-05 20:55:56'),
(8, 31, 7, '2026-01-05 21:04:22'),
(9, 17, 18, '2026-01-05 23:57:49'),
(10, 17, 5, '2026-01-06 00:14:15'),
(12, 17, 4, '2026-01-06 00:18:24'),
(13, 17, 2, '2026-01-06 00:28:49'),
(15, 26, 21, '2026-01-07 22:33:49'),
(16, 35, 20, '2026-01-13 15:08:31'),
(17, 53, 37, '2026-01-13 19:34:11'),
(18, 32, 40, '2026-01-13 21:02:35'),
(19, 32, 7, '2026-01-13 21:06:40'),
(20, 32, 39, '2026-01-13 22:13:39'),
(21, 55, 42, '2026-01-13 22:41:04'),
(22, 32, 43, '2026-01-13 22:41:45'),
(23, 37, 45, '2026-01-15 15:58:00'),
(24, 56, 33, '2026-01-24 13:00:54'),
(25, 57, 50, '2026-01-31 16:20:44');

-- --------------------------------------------------------

--
-- Table structure for table `seguidores`
--

CREATE TABLE `seguidores` (
  `id` int(11) NOT NULL,
  `seguidor_id` int(11) NOT NULL,
  `seguido_id` int(11) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `seguidores`
--

INSERT INTO `seguidores` (`id`, `seguidor_id`, `seguido_id`, `fecha`) VALUES
(1, 31, 17, '2026-01-05 20:18:10'),
(7, 17, 31, '2026-01-05 20:51:03'),
(8, 26, 31, '2026-01-06 01:38:48'),
(10, 31, 14, '2026-01-07 17:41:08'),
(11, 35, 38, '2026-01-13 15:08:44'),
(12, 35, 37, '2026-01-13 15:19:45'),
(14, 37, 49, '2026-01-13 16:11:15'),
(15, 37, 35, '2026-01-13 16:11:20'),
(16, 38, 37, '2026-01-13 19:38:03'),
(17, 53, 26, '2026-01-13 20:01:32'),
(18, 53, 37, '2026-01-13 20:01:40'),
(19, 53, 42, '2026-01-13 20:01:42'),
(20, 32, 37, '2026-01-13 21:00:55'),
(21, 32, 40, '2026-01-13 22:25:02'),
(22, 32, 53, '2026-01-13 22:25:03'),
(23, 32, 1, '2026-01-13 22:25:04'),
(24, 55, 37, '2026-01-13 22:40:51'),
(25, 55, 54, '2026-01-13 22:41:23'),
(26, 32, 55, '2026-01-13 22:42:19'),
(27, 37, 32, '2026-01-15 15:36:25'),
(28, 37, 14, '2026-01-15 15:43:51'),
(29, 57, 58, '2026-01-31 16:04:57'),
(30, 58, 41, '2026-02-07 21:14:11'),
(31, 58, 55, '2026-02-07 21:17:24');

-- --------------------------------------------------------

--
-- Table structure for table `user_interests`
--

CREATE TABLE `user_interests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `interes_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_interests`
--

INSERT INTO `user_interests` (`id`, `user_id`, `interes_id`) VALUES
(7, 35, 1),
(8, 35, 2),
(9, 35, 3),
(10, 35, 5),
(11, 35, 7),
(12, 35, 9),
(13, 35, 10),
(14, 35, 12),
(15, 35, 18),
(16, 35, 19),
(17, 35, 22),
(18, 35, 24),
(19, 35, 26),
(20, 35, 31),
(21, 37, 29),
(22, 37, 24),
(23, 37, 16),
(24, 37, 14),
(25, 37, 13),
(26, 37, 8),
(27, 37, 7),
(28, 37, 3),
(29, 37, 1),
(30, 37, 6),
(31, 37, 2),
(32, 37, 4),
(33, 37, 31),
(34, 37, 19),
(35, 37, 22),
(36, 37, 20),
(37, 37, 23),
(38, 37, 21),
(39, 37, 18),
(40, 52, 25),
(41, 52, 16),
(42, 52, 10),
(43, 42, 26),
(44, 42, 11),
(45, 42, 3),
(46, 42, 22),
(47, 53, 26),
(48, 53, 24),
(49, 53, 10),
(50, 38, 28),
(51, 38, 29),
(52, 38, 24),
(53, 38, 16),
(54, 38, 13),
(55, 38, 10),
(56, 38, 8),
(57, 38, 12),
(58, 38, 7),
(59, 38, 3),
(60, 38, 6),
(61, 38, 2),
(62, 38, 4),
(63, 38, 31),
(64, 38, 33),
(65, 38, 19),
(66, 38, 22),
(67, 38, 20),
(68, 38, 23),
(69, 38, 21),
(70, 38, 18),
(71, 32, 26),
(72, 32, 24),
(73, 32, 10),
(74, 32, 12),
(75, 32, 1),
(76, 54, 28),
(77, 54, 29),
(78, 54, 24),
(79, 54, 16),
(80, 54, 13),
(81, 54, 10),
(82, 54, 11),
(83, 54, 8),
(84, 54, 12),
(85, 54, 7),
(86, 54, 3),
(87, 54, 1),
(88, 54, 6),
(89, 54, 2),
(90, 54, 4),
(91, 54, 31),
(92, 54, 19),
(93, 54, 22),
(94, 54, 20),
(95, 54, 23),
(96, 54, 21),
(97, 54, 18),
(98, 55, 28),
(99, 55, 29),
(100, 55, 24),
(101, 55, 16),
(102, 55, 17),
(103, 55, 13),
(104, 55, 10),
(105, 55, 11),
(106, 55, 8),
(107, 55, 12),
(108, 55, 7),
(109, 55, 3),
(110, 55, 1),
(111, 55, 6),
(112, 55, 4),
(113, 55, 31),
(114, 56, 11),
(115, 56, 31),
(116, 56, 18),
(117, 31, 25),
(118, 31, 29),
(119, 31, 24),
(120, 31, 16),
(121, 31, 15),
(122, 31, 13),
(123, 31, 11),
(124, 31, 9),
(125, 31, 7),
(126, 31, 3),
(127, 31, 1),
(128, 31, 6),
(129, 31, 2),
(130, 31, 31),
(131, 31, 23),
(132, 31, 21),
(133, 31, 18),
(134, 57, 19),
(135, 57, 22),
(136, 57, 20),
(137, 58, 25),
(138, 58, 16),
(139, 58, 19),
(140, 39, 26),
(141, 39, 16),
(142, 39, 17),
(143, 39, 10),
(144, 39, 8),
(145, 39, 12),
(146, 39, 5),
(147, 39, 1),
(148, 39, 2),
(149, 39, 31),
(150, 39, 34),
(151, 39, 19),
(152, 39, 22),
(153, 39, 18);

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellido` varchar(50) NOT NULL,
  `fechaN` date DEFAULT NULL,
  `sexo` varchar(20) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `contraseña` varchar(255) NOT NULL,
  `foto_perfil` varchar(255) DEFAULT 'default.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `apellido`, `fechaN`, `sexo`, `correo`, `telefono`, `contraseña`, `foto_perfil`) VALUES
(1, '[value-2]', '[value-3]', '0000-00-00', '[value-5]', '[value-6]', '[value-7]', '[value-8]', 'default.png'),
(7, 'richar', 'Moreno', '2025-11-06', 'Masculino', 'mopreno@gmail.com', '0423389', '$2y$10$16GWyvlmiz0OAgGfKqOPt.qAAjE7wl6XZrccmKK8oYkctGfzUXM8y', 'default.png'),
(8, 'Zulimar', 'Camacaro', '1078-08-26', 'Femenino', 'zulimar@gmail.com', '04125220998', '$2y$10$vf6WxnIsYnenDK36FyasIOenltKjedyF71aZejy.YIsckg915MAKq', 'uploads/perfil_81766971787.jpg'),
(14, 'Ericks', 'Rumbo', '2004-01-01', 'Masculino', 'rumboericks01@gmail.com', '04124673769', '$2y$10$fbpHfTZsCTKU3n.SaxTaE.vOuxlEoHs6dH8u7k76UqAWcS90AePLW', 'default.png'),
(17, 'Karlimar', 'Peña', '2025-05-21', 'Femenino', 'karlimar@gmail.com', '04129323389', '$2y$10$eoUNBGSzU6hQ.GXtH/LY5uY6mWVm/mw4UriGPdaXuIZn5uC8h3WNS', 'uploads/perfil_171767723487.jpg'),
(26, 'Raul', 'Camacaro', '2025-12-10', 'Masculino', 'raul@gmail.com', '04129323389', '$2y$10$rj7Up18z0ORQuYu3EpKhGOW92eF0.f9wwoJbz72xNuGZqH4QAWi8G', 'default.png'),
(31, 'Diosi', 'Moreno', '2004-12-09', 'Femenino', 'morenodiosimar@gmail.com', '04129323389', '$2y$10$umU0nwxi/1yCpENcEhj8y.nmboNanpXtxGgaYXJgqwLdaHWPVa4.W', 'uploads/perfil_311766971559.jpg'),
(32, 'Norelis', 'Gonzalez', '2003-04-17', 'Femenino', 'norelis@gmail.com', '04145682072', '$2y$10$ppzAIq6rxijwpExLf8zXru1og0jHL4y0WKGBKeJgdXasZTcaW7jZ2', 'uploads/perfil_321768345571.jpg'),
(35, 'Norelis ', 'Gonzalez', '2003-04-17', 'Femenino', 'nore@gmail.com', '04145682072', '$2y$10$uOn4tVIaj5a8SSgWX3zetOmWiNHqXOseBOUZmB19cBcxzsrqWbz6a', 'default.png'),
(37, 'Cesar', 'Graterol', '2004-10-23', 'Masculino', 'cesar@gmail.com', '04145682072', '$2y$10$M.Y82dcCG5BLaAUUJ/IeH.JGzxx8TlZwHvxnrTs1aFlq8NVYKMQLW', 'uploads/perfil_371768320665.jpg'),
(38, 'Daniel', 'Graterol', '2004-10-23', 'Masculino', 'cesarg@gmail.com', '04145682072', '$2y$10$ivsVpGpNmUly//.Yvzjs5.tM0kK.AbIqWeL1SUmFXL17jh4bAkX6S', 'default.png'),
(39, 'Edith', 'Aranguren', '1969-11-09', 'Femenino', 'edith@gmail.com', '04145682072', '$2y$10$G/lV3bfDS/v4V3qKnlw.Yu7PuTBirHEaY3L6x06fgROD5xvUZs.Gm', 'default.png'),
(40, 'Nahomi', 'Aranguren', '2003-01-17', 'Femenino', 'nnga23@gmail.com', '04145682072', '$2y$10$ZpDRyULGVZPauflyMA14ue2VuAl1aZhT/iLzjaAG8txV9SpVNvRn6', 'default.png'),
(41, 'Ygnacio', 'Gonzalez', '1969-10-30', 'Masculino', 'ygnacio@gmail.com', '04145682072', '$2y$10$V43uYHjtedvK22Ogy5NyM.Y2VWfgbU4wq1goja9b5ENnF1fIXRJ8q', 'default.png'),
(42, 'Noheli', 'Gonzalez', '1998-04-22', 'Femenino', 'maria@gmail.com', '04145682072', '$2y$10$SX4p7UTPD3hCzSWWd.FibOAxRXrzMWtbSiB0Rp1qMnqW26Sj/0h22', 'default.png'),
(43, 'Maria', 'Gonzalez', '1998-04-22', 'Femenino', 'mariag@gmail.com', '04245682072', '$2y$10$bcLuJoG5gMMvQyP2alWxsOE0R.S.G52xd5g/Z9g6Uk84COmtlSTt2', 'default.png'),
(44, 'Nora', 'Gonza', '2003-04-17', 'Femenino', 'norelisgonza@gmail.com', '04145682072', '$2y$10$GpKBPhGyS/vCmvKmrYS12u1jJx4d2Urfm6rPlHVa2NDXW2Pt3CZIm', 'default.png'),
(45, 'Nore', 'Gonzalez', '2003-01-17', 'Femenino', 'norelisgonzalez@gmail.com', '04145682072', '$2y$10$Ayled/PS2nbVAMvUKosJpu/WBjbSw4blMZGTGibCe7OYXonuRim6O', 'default.png'),
(46, 'Nore', 'Gonzalez', '2003-01-17', 'Femenino', 'norelisgonzalez22@gmail.com', '04145682072', '$2y$10$E3ec.U5SOBt52IQ2WFAArunn7.C6X1wiZ0zOCS9/lQi3//JZphU.O', 'default.png'),
(47, 'Nore', 'Gonzalez', '2003-01-17', 'Femenino', 'norelisgonzalez223@gmail.com', '04145682072', '$2y$10$uvIm9nXn2vAGxh/165R1l.E5ymRi08BQCtLltMaO.75xWbo5hvxP2', 'default.png'),
(48, 'Noreg', 'Gonzalez', '2003-01-17', 'Femenino', 'norelisgonzalez2234@gmail.com', '04145682072', '$2y$10$2n/zXS6jzRXBiHzqpn.7VeO57730W0a6LSkRXdZlyYN3Bo5rcTCDi', 'default.png'),
(49, 'Noreg', 'Gonzalez', '2003-01-17', 'Femenino', 'nn@gmail.com', '04145682072', '$2y$10$dpu8xCnLiCxAQh7GMWTM9OOvBcd5z0MQj4cyWL4B9HaJ/mmM1hauW', 'default.png'),
(50, 'Norena', 'Gonzalez', '2003-01-17', 'Femenino', 'nnga@gmail.com', '04145682072', '$2y$10$wDmL2OOKJG8hpRTH8YQUautQEfwVtnni.pkyLxTMh3oExARdQ4p5e', 'default.png'),
(51, 'Leli', 'Gonzalez', '2003-04-17', 'Femenino', 'norelisnn@gmail.com', '04145682072', '$2y$10$AQxnVdbPXRaCasPE7jPK6uIOC0ldM638w.yAGuR.buucZwW1fttIa', 'default.png'),
(52, 'Chimuela', 'Gonzalez', '2009-01-10', 'Femenino', 'naho@gmail.com', '04145682072', '$2y$10$0KQqNze2qgNt4IKTgWjKNuI3V.l6GSiIpCyBEehWsSn86/OUZZ/a6', 'default.png'),
(53, 'Nore', 'Gonza', '2003-01-03', 'Femenino', 'nore152@gmail.com', '04145682072', '$2y$10$9ZiQlPmNX.zSUkjRws771OaeaKQzcrYij188rWs7it8c/VKbz2CMe', 'default.png'),
(54, 'Cesarg', 'Graterol', '2004-10-23', 'Masculino', 'cesargg@gmail.com', '04145682072', '$2y$10$b0nFB08pTbcN/ZmwC18swey5tnpKZDkpO2PYPLoTXkHa0IYzPsz72', 'default.png'),
(55, 'Cesard', 'Graterol', '2004-10-23', 'Masculino', 'cesarggda@gmail.com', '04145682072', '$2y$10$jQrZ.8zxqv3C6dfPN.TOZe37tWrJRITQYbEGrCzPP/UxWPxJGWrxq', 'uploads/perfil_551768344456.jpg'),
(56, 'Sofia', 'Gonzalez', '2005-01-01', 'Femenino', 'sofi@gmail.com', '04145682072', '$2y$10$8JI5VcFHDYqWSrdo6Qer8u1ixTPcQdyl6hv2KdNWY5NKR2waGTZCe', 'default.png'),
(57, 'Maria', 'Gonzalez', '1999-01-01', 'Femenino', 'marian@gmail.com', '04145682072', '$2y$10$QHv3lSAEOxoA5hsmVKZUeuh4wnueznkOQrxZNjrWSmbL5E50y6xce', 'uploads/perfil_571769875964.jpg'),
(58, 'Anabel', 'Perez', '2004-01-03', 'Femenino', 'ana@gmail.com', '04145682072', '$2y$10$fZ1sjIh.6B08QnBjMrJE..QWM5dP7cOKxz6vOCqIpej8GYx7NT9EG', 'default.png');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `comentarios`
--
ALTER TABLE `comentarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `publicacion_id` (`publicacion_id`);

--
-- Indexes for table `interes`
--
ALTER TABLE `interes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mensajes`
--
ALTER TABLE `mensajes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `emisor_id` (`emisor_id`),
  ADD KEY `publicacion_id` (`publicacion_id`);

--
-- Indexes for table `publicaciones`
--
ALTER TABLE `publicaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `fk_publicaciones_interes` (`interes_id`);

--
-- Indexes for table `reacciones`
--
ALTER TABLE `reacciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `publicacion_id` (`publicacion_id`);

--
-- Indexes for table `republicaciones`
--
ALTER TABLE `republicaciones`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `seguidores`
--
ALTER TABLE `seguidores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `seguidor_id` (`seguidor_id`,`seguido_id`),
  ADD KEY `seguido_id` (`seguido_id`);

--
-- Indexes for table `user_interests`
--
ALTER TABLE `user_interests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `interes_id` (`interes_id`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `comentarios`
--
ALTER TABLE `comentarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- AUTO_INCREMENT for table `interes`
--
ALTER TABLE `interes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `mensajes`
--
ALTER TABLE `mensajes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=133;

--
-- AUTO_INCREMENT for table `publicaciones`
--
ALTER TABLE `publicaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `reacciones`
--
ALTER TABLE `reacciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `republicaciones`
--
ALTER TABLE `republicaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `seguidores`
--
ALTER TABLE `seguidores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `user_interests`
--
ALTER TABLE `user_interests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=154;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comentarios`
--
ALTER TABLE `comentarios`
  ADD CONSTRAINT `comentarios_ibfk_1` FOREIGN KEY (`publicacion_id`) REFERENCES `publicaciones` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `notificaciones_ibfk_2` FOREIGN KEY (`emisor_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `notificaciones_ibfk_3` FOREIGN KEY (`publicacion_id`) REFERENCES `publicaciones` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `publicaciones`
--
ALTER TABLE `publicaciones`
  ADD CONSTRAINT `fk_publicaciones_interes` FOREIGN KEY (`interes_id`) REFERENCES `interes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `publicaciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Constraints for table `reacciones`
--
ALTER TABLE `reacciones`
  ADD CONSTRAINT `reacciones_ibfk_1` FOREIGN KEY (`publicacion_id`) REFERENCES `publicaciones` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `seguidores`
--
ALTER TABLE `seguidores`
  ADD CONSTRAINT `seguidores_ibfk_1` FOREIGN KEY (`seguidor_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `seguidores_ibfk_2` FOREIGN KEY (`seguido_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_interests`
--
ALTER TABLE `user_interests`
  ADD CONSTRAINT `user_interests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `user_interests_ibfk_2` FOREIGN KEY (`interes_id`) REFERENCES `interes` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
