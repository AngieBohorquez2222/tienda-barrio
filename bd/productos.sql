-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 27-06-2026 a las 15:19:29
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `tienda_barrio`gi

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `imagen` varchar(255) NOT NULL DEFAULT '/tienda-barrio/img/productos/default.svg',
  `precio` decimal(10,2) NOT NULL CHECK (`precio` >= 0),
  `stock` int(11) NOT NULL CHECK (`stock` >= 0),
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `imagen`, `precio`, `stock`, `creado_en`, `actualizado_en`) VALUES
(1, 'Malta PONY MALTA go x6und (1200 ml)', '/tienda-barrio/img/productos/uploads/prod_20260624225009_cb7fbe71.webp', 8430.00, 10, '2026-06-24 20:50:09', '2026-06-24 20:50:09'),
(2, 'Cerveza CLUB COLOMBIA lata dorada (1980 ml)', '/tienda-barrio/img/productos/uploads/prod_20260624225156_6997b201.webp', 22200.00, 20, '2026-06-24 20:51:56', '2026-06-24 20:51:56'),
(3, 'Cerveza Lata Sixpack AGUILA 1980 ml', '/tienda-barrio/img/productos/uploads/prod_20260624225257_ea5d9db1.webp', 21600.00, 22, '2026-06-24 20:52:57', '2026-06-24 20:52:57'),
(4, 'Cerveza POKER lata (3630 ml)', '/tienda-barrio/img/productos/uploads/prod_20260624225350_9e4292e6.webp', 30900.00, 22, '2026-06-24 20:53:50', '2026-06-24 20:53:50'),
(5, 'Cerveza CLUB COLOMBIA lata dorada (1980 ml)', '/tienda-barrio/img/productos/uploads/prod_20260624225442_e27f8dc4.webp', 22200.00, 22, '2026-06-24 20:54:42', '2026-06-24 20:54:42'),
(6, 'Gaseosa COCA COLA familiar (3000 ml)', '/tienda-barrio/img/productos/uploads/prod_20260624225636_41598da3.webp', 8475.00, 20, '2026-06-24 20:56:36', '2026-06-24 20:56:36'),
(7, 'Gaseosa COCA COLA original (1500 ml)', '/tienda-barrio/img/productos/uploads/prod_20260624225751_59cdc0ab.webp', 5212.00, 10, '2026-06-24 20:57:51', '2026-06-24 20:57:51'),
(8, 'Soda BRETANA botella (1500 ml)', '/tienda-barrio/img/productos/uploads/prod_20260624225839_ab2cd4ec.webp', 3500.00, 5, '2026-06-24 20:58:39', '2026-06-24 20:58:39'),
(9, 'Gaseosa POSTOBON manzana + colombiana + uva (7750 ml)', '/tienda-barrio/img/productos/uploads/prod_20260624225951_5b84bbb6.webp', 18050.00, 9, '2026-06-24 20:59:51', '2026-06-24 20:59:51'),
(10, 'Gaseosa COLOMBIANA botella (3125 ml)', '/tienda-barrio/img/productos/uploads/prod_20260624230112_9c5d3bee.webp', 7720.00, 8, '2026-06-24 21:01:12', '2026-06-24 21:01:12'),
(11, 'Gaseosa POSTOBON manzana botella (3125 ml)', '/tienda-barrio/img/productos/uploads/prod_20260624230220_8a8b1bd4.webp', 7720.00, 11, '2026-06-24 21:02:20', '2026-06-24 21:02:20'),
(12, 'Aceite FRESCAMPO vegetal multiusos (3000 ml)', '/tienda-barrio/img/productos/uploads/prod_20260624230332_cb16d475.webp', 19950.00, 12, '2026-06-24 21:03:32', '2026-06-24 21:03:32'),
(13, 'Arroz DIANA blanco vitamor (5000 gr)', '/tienda-barrio/img/productos/uploads/prod_20260624230514_eb13545b.webp', 19950.00, 9, '2026-06-24 21:05:14', '2026-06-24 21:05:14'),
(14, 'Garbanzo DIANA cuidadosamente seleccionados (500 gr)', '/tienda-barrio/img/productos/uploads/prod_20260624230614_1bdb2120.webp', 3650.00, 5, '2026-06-24 21:06:14', '2026-06-24 21:06:14'),
(15, 'Café NESCAFE tradición (170 gr)', '/tienda-barrio/img/productos/uploads/prod_20260624230712_d7c4da48.webp', 31950.00, 7, '2026-06-24 21:07:12', '2026-06-24 21:07:12'),
(16, 'Sal REFISAL alta pureza (1000 gr)', '/tienda-barrio/img/productos/uploads/prod_20260624230808_ad9a7316.webp', 2910.00, 17, '2026-06-24 21:08:08', '2026-06-24 21:08:08'),
(17, 'Salsa de tomate FRUCO + mayonesa (1 und)', '/tienda-barrio/img/productos/uploads/prod_20260624230904_865ab48b.webp', 18050.00, 4, '2026-06-24 21:09:04', '2026-06-24 21:09:04'),
(18, 'Harina PAN maíz blanco (1000 gr)', '/tienda-barrio/img/productos/uploads/prod_20260624231009_04009034.webp', 3630.00, 8, '2026-06-24 21:10:09', '2026-06-24 21:10:09'),
(19, 'Azúcar MANUELITA alta pureza (1000 gr)', '/tienda-barrio/img/productos/uploads/prod_20260624231105_f91db279.webp', 5510.00, 20, '2026-06-24 21:11:05', '2026-06-24 21:11:05'),
(20, 'Pastas DORIA corriente (500 gr)', '/tienda-barrio/img/productos/uploads/prod_20260624231207_bfafccb8.webp', 2990.00, 22, '2026-06-24 21:12:07', '2026-06-24 21:12:07'),
(21, 'Chocolate CORONA tradicional pastillado (450 gr)', '/tienda-barrio/img/productos/uploads/prod_20260624231303_f1ada0f9.webp', 12480.00, 19, '2026-06-24 21:13:03', '2026-06-24 21:13:03'),
(22, 'Lenteja FRESCAMPO granos seleccionados (500 gr)', '/tienda-barrio/img/productos/uploads/prod_20260624231407_8e935f8c.webp', 2100.00, 13, '2026-06-24 21:14:07', '2026-06-24 21:14:07'),
(23, 'Galletas SALTIN NOEL tradicional x6 tacos (531 gr)', '/tienda-barrio/img/productos/uploads/prod_20260624231504_374e4e04.webp', 10200.00, 7, '2026-06-24 21:15:04', '2026-06-24 21:15:04'),
(24, 'Avena En Hojuela Extracontenido QUAKER 1100 gr', '/tienda-barrio/img/productos/uploads/prod_20260624231708_1cd061aa.webp', 9120.00, 7, '2026-06-24 21:17:08', '2026-06-24 21:17:08'),
(25, 'Aromática FRESCAMPO yerbabuena (15 gr)', '/tienda-barrio/img/productos/uploads/prod_20260624231823_763428aa.webp', 3180.00, 5, '2026-06-24 21:18:23', '2026-06-24 21:18:23'),
(26, 'Huevos AA KIKES Rojo Pet (30 und)', '/tienda-barrio/img/productos/uploads/prod_20260624231949_65adfb3d.webp', 16650.00, 22, '2026-06-24 21:19:39', '2026-06-24 21:19:49'),
(27, 'Leche ALQUERIA semidescremada deslactosada sixpack (6000 ml)', '/tienda-barrio/img/productos/uploads/prod_20260624232115_42753077.webp', 28602.00, 27, '2026-06-24 21:21:15', '2026-06-24 21:21:15'),
(28, 'Margarina LA FINA mesa y cocina (500 gr)', '/tienda-barrio/img/productos/uploads/prod_20260624232207_1ce29735.webp', 10700.00, 12, '2026-06-24 21:22:07', '2026-06-24 21:22:07'),
(29, 'Jamón COLANTA seleccionado (450 gr)', '/tienda-barrio/img/productos/uploads/prod_20260624232333_9afe42ef.webp', 15795.00, 12, '2026-06-24 21:23:33', '2026-06-24 21:23:33'),
(30, 'Queso crema COLANTA fresco semiblando (400 gr)', '/tienda-barrio/img/productos/uploads/prod_20260624232430_9accd79b.webp', 7950.00, 5, '2026-06-24 21:24:30', '2026-06-24 21:24:30'),
(31, 'Crema de leche COLANTA semientera (175 gr)', '/tienda-barrio/img/productos/uploads/prod_20260624232651_0f1422b9.webp', 4520.00, 7, '2026-06-24 21:26:51', '2026-06-24 21:26:51'),
(32, 'Lavaplatos líquido AXION limón (1500 ml)', '/tienda-barrio/img/productos/uploads/prod_20260624232804_3fb58b53.webp', 11992.00, 12, '2026-06-24 21:28:04', '2026-06-24 21:28:04'),
(33, 'Papel Higienico Extra Pack 15 FAMILIA Papel Higiénico (1 und)', '/tienda-barrio/img/productos/uploads/prod_20260624232908_199e32ab.webp', 30690.00, 20, '2026-06-24 21:29:08', '2026-06-24 21:29:08'),
(34, 'Detergente en polvo ARIEL triple poder (2000 gr)', '/tienda-barrio/img/productos/uploads/prod_20260624233043_7ccd40c2.webp', 21417.00, 19, '2026-06-24 21:30:43', '2026-06-24 21:30:43'),
(35, 'Blanqueador CLOROX pack limpia y desinfecta (2260 ml)', '/tienda-barrio/img/productos/uploads/prod_20260624233152_6c29205c.webp', 8550.00, 15, '2026-06-24 21:31:52', '2026-06-24 21:31:52'),
(36, 'Jabón de barra REY original (900 gr)', '/tienda-barrio/img/productos/uploads/prod_20260624233245_690aaf97.webp', 9110.00, 22, '2026-06-24 21:32:45', '2026-06-24 21:32:45'),
(37, 'Suavizante SUAVITEL cuidado superior fresca primavera (2000 ml)', '/tienda-barrio/img/productos/uploads/prod_20260624233359_15dcbbb2.webp', 14700.00, 22, '2026-06-24 21:33:59', '2026-06-24 21:33:59'),
(38, 'Toallas de cocina FAMILIA green triple hoja (135 und)', '/tienda-barrio/img/productos/uploads/prod_20260624233519_6e2c38c9.webp', 10400.00, 7, '2026-06-24 21:35:19', '2026-06-24 21:35:19'),
(39, 'Servilletas FAMILIA medianas (100 und)', '/tienda-barrio/img/productos/uploads/prod_20260624233716_b6aa2c7f.webp', 4420.00, 7, '2026-06-24 21:37:16', '2026-06-24 21:37:16'),
(40, 'Limpiapisos FABULOSO antibacterial lavanda (2000 ml)', '/tienda-barrio/img/productos/uploads/prod_20260624233813_48e360e8.webp', 10800.00, 5, '2026-06-24 21:38:13', '2026-06-24 21:38:13'),
(41, 'Esponja ETERNA doble uso máxima duración (2 und)', '/tienda-barrio/img/productos/uploads/prod_20260624234005_9a2f5646.webp', 4303.00, 5, '2026-06-24 21:40:05', '2026-06-24 21:40:21'),
(42, 'Arroz DIANA blanco vitamor (500 gr)', '/tienda-barrio/img/productos/uploads/prod_20260624234205_bbc42bc1.webp', 2110.00, 22, '2026-06-24 21:42:05', '2026-06-24 21:42:05'),
(43, 'Bolsa para basura BASURELA residencia negra 65x90 cm', '/tienda-barrio/img/productos/uploads/prod_20260624234432_4895b315.webp', 3940.00, 12, '2026-06-24 21:44:32', '2026-06-24 21:44:32'),
(44, 'Comida para perros CHUNKY adulto sabor a pollo (2000 gr)', '/tienda-barrio/img/productos/uploads/prod_20260624234625_c9b7dd6a.webp', 22550.00, 5, '2026-06-24 21:46:25', '2026-06-24 21:46:25'),
(45, 'Alimento Agility Gatos Adultos 1,5Kg', '/tienda-barrio/img/productos/uploads/prod_20260624234832_fdb4bf28.webp', 43000.00, 5, '2026-06-24 21:48:32', '2026-06-24 21:48:32'),
(46, 'Filete de pechuga FRIKO HF marinado (850 gr)', '/tienda-barrio/img/productos/uploads/prod_20260624234934_5fb63476.webp', 26080.00, 12, '2026-06-24 21:49:34', '2026-06-24 21:49:34'),
(47, 'Salchicha de pollo RICA x30und (500 gr)', '/tienda-barrio/img/productos/uploads/prod_20260624235251_cbaacf5e.webp', 14300.00, 12, '2026-06-24 21:52:51', '2026-06-24 21:52:51'),
(48, 'Carne de cerdo económica (Gr a $ 14,01)', '/tienda-barrio/img/productos/uploads/prod_20260624235553_42cc80d0.webp', 14010.00, 22, '2026-06-24 21:55:53', '2026-06-24 21:55:53'),
(49, 'Lomo de cerdo o cañón congelado porcionado (Gr a $ 17,98)', '/tienda-barrio/img/productos/uploads/prod_20260624235759_20356b4e.webp', 8992.00, 12, '2026-06-24 21:57:59', '2026-06-24 21:57:59'),
(50, 'Tilapia o mojarra SMN fresca (Gr a $ 15,92)', '/tienda-barrio/img/productos/uploads/prod_20260624235901_8ca21b4d.webp', 7960.00, 22, '2026-06-24 21:59:01', '2026-06-24 21:59:18'),
(51, 'Muslos de Pollo Marinado FRESCAMPO 750 gr', '/tienda-barrio/img/productos/uploads/prod_20260625000742_e2aa599f.webp', 10320.00, 7, '2026-06-24 22:07:11', '2026-06-24 22:07:42');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
