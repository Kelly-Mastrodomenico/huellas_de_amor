-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 02-03-2026 a las 01:12:51
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
-- Base de datos: `huellas_de_amor`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `apadrinamientos`
--

CREATE TABLE `apadrinamientos` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_usuario` int(10) UNSIGNED NOT NULL,
  `id_mascota` int(10) UNSIGNED NOT NULL,
  `plan` enum('basico','medio','completo') NOT NULL DEFAULT 'basico',
  `monto_mensual` decimal(8,2) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `casas_acogida`
--

CREATE TABLE `casas_acogida` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_usuario` int(10) UNSIGNED NOT NULL,
  `id_mascota` int(10) UNSIGNED DEFAULT NULL,
  `descripcion_hogar` text DEFAULT NULL,
  `tiene_jardin` tinyint(1) NOT NULL DEFAULT 0,
  `otros_animales` tinyint(1) NOT NULL DEFAULT 0,
  `disponible` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contacto`
--

CREATE TABLE `contacto` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `asunto` varchar(200) NOT NULL,
  `mensaje` text NOT NULL,
  `leido` tinyint(1) DEFAULT 0,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `contacto`
--

INSERT INTO `contacto` (`id`, `nombre`, `email`, `asunto`, `mensaje`, `leido`, `fecha`) VALUES
(1, 'Kelly Rodriguez Mastrodomenico', 'kelrodmas@alu.edu.gva.es', 'Consulta sobre adopcion', 'Puedo adoptar a dos mascotas?', 1, '2026-02-27 18:34:52');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `donaciones`
--

CREATE TABLE `donaciones` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_usuario` int(10) UNSIGNED DEFAULT NULL,
  `monto` decimal(8,2) NOT NULL,
  `concepto` varchar(255) DEFAULT NULL,
  `metodo_pago` varchar(100) DEFAULT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `certificado_pdf` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `donaciones`
--

INSERT INTO `donaciones` (`id`, `id_usuario`, `monto`, `concepto`, `metodo_pago`, `fecha`, `certificado_pdf`) VALUES
(1, 2, 25.00, 'Donacion mensual general', 'tarjeta', '2026-02-25 22:26:11', NULL),
(2, 3, 50.00, 'Apoyo refugio Peludo Feliz', 'paypal', '2026-02-25 22:26:11', NULL),
(3, NULL, 10.00, 'Donacion anonima', 'tarjeta', '2026-02-25 22:26:11', NULL),
(4, 4, 10.00, 'Alimentacion animales', 'tarjeta', '2026-03-01 14:51:32', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `eventos`
--

CREATE TABLE `eventos` (
  `id` int(10) UNSIGNED NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_evento` datetime NOT NULL,
  `lugar` varchar(255) DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `cupo_maximo` int(10) UNSIGNED DEFAULT NULL,
  `id_admin` int(10) UNSIGNED DEFAULT NULL,
  `publicado` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `favoritos`
--

CREATE TABLE `favoritos` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_usuario` int(10) UNSIGNED NOT NULL,
  `id_mascota` int(10) UNSIGNED NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `foro_categorias`
--

CREATE TABLE `foro_categorias` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `icono` varchar(50) DEFAULT NULL,
  `orden` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `foro_categorias`
--

INSERT INTO `foro_categorias` (`id`, `nombre`, `descripcion`, `icono`, `orden`) VALUES
(1, 'Adopcion y Acogida', 'Consejos y experiencias sobre el proceso de adopcion', 'fa-heart', 1),
(2, 'Salud Animal', 'Preguntas sobre salud y veterinaria', 'fa-stethoscope', 2),
(3, 'Adiestramiento', 'Tecnicas para educar a tu mascota', 'fa-graduation-cap', 3),
(4, 'Historias de Exito', 'Comparte tu historia de adopcion exitosa', 'fa-star', 4),
(5, 'General', 'Cualquier otro tema sobre bienestar animal', 'fa-comments', 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `foro_posts`
--

CREATE TABLE `foro_posts` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_tema` int(10) UNSIGNED NOT NULL,
  `id_usuario` int(10) UNSIGNED NOT NULL,
  `contenido` text NOT NULL,
  `likes` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `fecha_post` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `foro_temas`
--

CREATE TABLE `foro_temas` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_categoria` int(10) UNSIGNED NOT NULL,
  `id_usuario` int(10) UNSIGNED NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `vistas` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `cerrado` tinyint(1) NOT NULL DEFAULT 0,
  `fijado` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fotos_mascotas`
--

CREATE TABLE `fotos_mascotas` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_mascota` int(10) UNSIGNED NOT NULL,
  `ruta_foto` varchar(255) NOT NULL,
  `es_principal` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `fotos_mascotas`
--

INSERT INTO `fotos_mascotas` (`id`, `id_mascota`, `ruta_foto`, `es_principal`) VALUES
(1, 1, 'uploads/mascotas/foto_69a4d26b2d6d15.47242077.jpg', 1),
(2, 2, 'uploads/mascotas/foto_69a4d1fb41a845.84047928.jpg', 1),
(3, 3, 'uploads/mascotas/foto_69a4d2fba31323.08478038.jpg', 1),
(4, 4, 'uploads/mascotas/foto_69a4d2cdd29481.88962225.jpg', 1),
(5, 5, 'uploads/mascotas/foto_69a4d34b507036.27482332.jpg', 1),
(6, 6, 'uploads/mascotas/foto_69a4d2aaa32091.23211770.jpg', 1),
(7, 7, 'uploads/mascotas/foto_69a4d3941c0d51.21765742.jpg', 1),
(8, 8, 'uploads/mascotas/foto_69a4d30bdcfc16.68433794.jpg', 1),
(9, 10, 'uploads/mascotas/foto_69a094c6a36fa7.67257431.jpg', 1),
(10, 11, 'uploads/mascotas/foto_69a0968db33447.69837368.jpg', 1),
(11, 9, 'uploads/mascotas/foto_69a4d071cefa53.02380808.jpg', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mascotas`
--

CREATE TABLE `mascotas` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `especie` enum('perro','gato','otro') NOT NULL DEFAULT 'perro',
  `raza` varchar(100) DEFAULT NULL,
  `edad_anios` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `edad_meses` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `sexo` enum('macho','hembra') NOT NULL DEFAULT 'macho',
  `tamanio` enum('pequeño','mediano','grande') NOT NULL DEFAULT 'mediano',
  `descripcion` text DEFAULT NULL,
  `caracter` varchar(255) DEFAULT NULL,
  `estado` enum('disponible','adoptado','acogida') NOT NULL DEFAULT 'disponible',
  `id_protectora` int(10) UNSIGNED DEFAULT NULL,
  `fecha_ingreso` date DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `mascotas`
--

INSERT INTO `mascotas` (`id`, `nombre`, `especie`, `raza`, `edad_anios`, `edad_meses`, `sexo`, `tamanio`, `descripcion`, `caracter`, `estado`, `id_protectora`, `fecha_ingreso`, `activo`) VALUES
(1, 'Luna', 'perro', 'Mestizo', 2, 0, 'hembra', 'mediano', 'Luna es una perra cariñosa y activa que adora los paseos.', 'Activo, cariñoso, jugueton', 'disponible', 1, '2024-10-15', 1),
(2, 'Oliver', 'gato', 'Europeo', 0, 6, 'macho', 'pequeño', 'Oliver es un gatito curioso y muy sociable.', 'Curioso, sociable, mimoso', 'disponible', 2, '2024-11-20', 1),
(3, 'Max', 'perro', 'Labrador', 4, 0, 'macho', 'grande', 'Max es un perro tranquilo y muy leal.', 'Tranquilo, leal, obediente', 'disponible', 1, '2024-09-01', 1),
(4, 'Bella', 'perro', 'Chihuahua', 1, 0, 'hembra', 'pequeño', 'Bella es pequeña pero llena de energia.', 'Activo, inteligente, valiente', 'disponible', 3, '2024-12-05', 1),
(5, 'Coco', 'perro', 'Beagle', 3, 0, 'hembra', 'mediano', 'Coco llego de la calle pero ya confia en las personas.', 'Tranquilo, timido, cariñoso', 'disponible', 2, '2024-08-20', 1),
(6, 'Simba', 'gato', 'Siames', 0, 2, 'macho', 'pequeño', 'Simba es muy activo y jugueton.', 'Muy activo, jugueton, independiente', 'acogida', 3, '2025-01-10', 1),
(7, 'Bruno', 'perro', 'Pastor Aleman', 5, 0, 'macho', 'grande', 'Bruno ya encontro su hogar. Historia de exito.', 'Leal, protector, inteligente', 'adoptado', 1, '2023-06-01', 1),
(8, 'Mimi', 'gato', 'Persa', 2, 0, 'hembra', 'pequeño', 'Mimi llego asustada pero hoy es la reina de su casa.', 'Tranquilo, cariñoso, independiente', 'adoptado', 2, '2023-09-15', 1),
(9, 'Kiba', 'perro', 'Husky Siberiano', 10, 5, 'macho', 'mediano', 'Ya esta desparasitado y vacunado', 'Es un perro muy cariñoso y le encanta la compañia.', 'disponible', 3, '2026-02-26', 1),
(10, 'Manguito', 'gato', 'Mestizo', 1, 7, 'macho', 'mediano', 'Se encuentra desparacitado, vacunado y esterilizado', 'Es amigable y le encanta la compañia', 'disponible', 1, '2026-02-26', 0),
(11, 'Lulito', 'gato', 'Mestizo', 1, 8, 'macho', 'pequeño', 'Se encuentra esterilizado y vacunado', 'Es muy jugueton y gloton', 'adoptado', 2, '2026-02-26', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `noticias`
--

CREATE TABLE `noticias` (
  `id` int(10) UNSIGNED NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `contenido` longtext DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `id_admin` int(10) UNSIGNED DEFAULT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `publicada` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_publicacion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `noticias`
--

INSERT INTO `noticias` (`id`, `titulo`, `slug`, `contenido`, `imagen`, `id_admin`, `categoria`, `publicada`, `fecha_publicacion`) VALUES
(1, 'Jornada de adopcion este sabado en Valencia', 'jornada-de-adopcion-este-sabado-en-valencia', '<p>Este sabado organizamos una gran jornada de adopcion. Mas de 30 mascotas buscan familia.</p>', 'uploads/noticias/foto_69a4d4325dc465.69233440.png', 1, 'Eventos', 1, '2026-03-02 01:05:06'),
(2, 'La Ley de Bienestar Animal española', 'la-ley-de-bienestar-animal-espanola', '<div class=\"ca-article-detail__content\">\r\n<div class=\"ca-mb-md ca-text-lead\">\r\n<p>La Ley de Bienestar Animal espa&ntilde;ola ya es una realidad. El BOE define en ella una serie de normas que aplican, sobre todo, a los propietarios de las mascotas y tambi&eacute;n, a la legislaci&oacute;n sobre la comercializaci&oacute;n de animales dom&eacute;sticos.</p>\r\n</div>\r\n</div>\r\n<div class=\"ca-article-detail__content\">\r\n<div class=\"ca-mb-md\">\r\n<p>Y es que con la &ldquo;LBA&rdquo; se regulan y unifican diferentes materias, que hasta ahora se defin&iacute;an de forma independiente en cada una de las comunidades aut&oacute;nomas. Qu&eacute;date, en las siguientes l&iacute;neas te contamos de forma clara y breve, todo lo que necesitas saber sobre esta ley, que <strong>entr&oacute; en vigor el 29 de septiembre de 2023</strong>.</p>\r\n</div>\r\n</div>', 'uploads/noticias/foto_69a4d3e7a05143.93942300.jpg', 1, 'Leyes', 1, '2026-03-02 01:03:51'),
(3, 'Más de 4.000 perros utilizados para experimentos científicos con drogas buscan un hogar de adopción', 'mas-de-4000-perros-utilizados-para-experimentos-cientificos-con-drogas-buscan-un-hogar-de-adopcion', '<p>Los animales eran criados para venderse a laboratorios cient&iacute;ficos. Pasar&aacute;n un reconocimiento m&eacute;dico y ser&aacute;n vacunados antes de ser puestos en adopci&oacute;n.</p>\r\n<p>M&aacute;s de 4.000 cachorros de la raza beagle han sido rescatados de un criadero de Virginia, Estados Unidos, donde eran criados para, posteriormente, venderlos a laboratorios que los utilizaban para<strong> experimentar con drogas</strong>. Ahora, estos animales est&aacute;n buscando un <strong><a title=\"hogar de adopci&oacute;n\" href=\"https://www.antena3.com/noticias/deportes/futbol/jugadores-zenit-saltan-campo-perros-apoyar-adopcion-caninos_2021120561aca9eb45438900013b6523.html\" target=\"_blank\" rel=\"noopener\">hogar de adopci&oacute;n</a>.</strong></p>\r\n<p>En mayo, el Departamento de Justicia de Estados Unidos demand&oacute; a la compa&ntilde;&iacute;a Envigo RMS por <a title=\"maltrato animal\" href=\"https://www.antena3.com/noticias/sociedad/belarra-vaticina-que-futura-ley-derechos-animales-acabara-problema-maltrato-abandono_2022080162e81b3e6fb9230001a29a0e.html\" target=\"_blank\" rel=\"noopener\">maltrato animal</a>. En el criadero, los perros eran <strong>sacrificados por cualquier problema de salud</strong> en lugar de recibir <strong>atenci&oacute;n veterinaria</strong>, se negaba el alimento a las hembras gestantes y <strong>la comida que recib&iacute;an conten&iacute;a gusanos, moho y heces.</strong></p>\r\n<p>En junio, la empresa matriz, Inotiv Inc, <strong>asegur&oacute; que cerrar&iacute;a sus instalaciones</strong>. \"Vamos a tardar 60 d&iacute;as en sacar a todos estos animales, y vamos a trabajar con nuestros socios de<a title=\"refugios y rescates de todo el pa&iacute;s\" href=\"https://www.antena3.com/noticias/sociedad/refugio-mas-200-animales-exoticos-refugiados-volcan-palma_20211029617bd01034d4be00018db400.html\" target=\"_blank\" rel=\"noopener\"><strong>refugios y rescates de todo el pa&iacute;s</strong></a>para conseguir que estos perros finalmente <strong>tengan un hogar donde los amen</strong>\", explic&oacute; Kitty Block, presidenta y directora ejecutiva de la Humane Society de Estados Unidos.</p>', 'uploads/noticias/foto_69a4d40a683a90.29936796.jpg', 1, 'Adopciones', 1, '2026-03-02 01:04:26'),
(4, 'Mañana se abre el plazo de voluntariado de la Campaña de Esterilización de Gatos', 'manana-se-abre-el-plazo-de-voluntariado-de-la-campana-de-esterilizacion-de-gatos', '<div>La <em>Campa&ntilde;a de Esterilizaci&oacute;n de Gatos Comunitarios 2026</em> comenzar&aacute; en el mes de febrero en La Nuc&iacute;a, y para ello, <strong>la concejal&iacute;a de Protecci&oacute;n Animal abre ma&ntilde;ana el plazo de inscripci&oacute;n y/o renovaci&oacute;n de voluntarias y voluntarios</strong> para esta acci&oacute;n. Se requieren voluntari@s altruistas, que trasladen los gatos a las cl&iacute;nicas veterinarias nucieras concertadas para la esterilizaci&oacute;n de los felinos.</div>\r\n<div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Todas las personas interesadas pueden inscribirse en la concejal&iacute;a de Protecci&oacute;n Animal de La Nuc&iacute;a ubicada en la Ext. Administrativa de Bello Horizonte (661 372 931) <strong>del 15 al 30 de enero, es imprescindible estar empadronado en La Nuc&iacute;a.</strong></div>\r\n<div>&nbsp;</div>\r\n<div>&nbsp;</div>\r\n<div><strong><span style=\"font-size: 14px;\">&iquest;C&oacute;mo hacerte voluntari@?</span></strong></div>\r\n<div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;La concejal&iacute;a de Protecci&oacute;n Animal de La Nuc&iacute;a tiene su sede en la Extensi&oacute;n Administrativa de Bello Horizonte (calle Berl&iacute;n n&ordm;1). <strong>Todas las personas interesadas formar parte del voluntariado de esta <em>Campa&ntilde;a de Esterilizaci&oacute;n de Gatos Comunitarios</em> 2026 deben inscribirse all&iacute; y rellenar el alta de voluntariado (necesario estar empadronado en La Nuc&iacute;a)</strong>. All&iacute; se informar&aacute; a los voluntarios sobre el alta de voluntariado, el funcionamiento de la campa&ntilde;a, la solicitud de jaulas y los compromisos como voluntari@ de la campa&ntilde;a.&nbsp;</div>\r\n<div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;El plazo ser&aacute; del 15 al 30 de enero. Para cualquier consulta llamar al tel&eacute;fono 661 372 931, mail proteccionanimal@lanucia.es o v&iacute;a whatsapp en el 627 856 711.</div>\r\n<div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Desde la concejal&iacute;a de Protecci&oacute;n Animal <strong>se recuerda que ser voluntari@ conlleva el compromiso de la efectiva realizaci&oacute;n de las labores de control y esterilizaci&oacute;n felina mediante el sistema CER</strong> (Captura, Esterilizaci&oacute;n y Retorno) y que no es sin&oacute;nimo de alimentador.&nbsp;</div>', 'uploads/noticias/foto_69a4d3d03d6b66.63204714.jpg', 1, 'Eventos', 1, '2026-03-02 01:03:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `protectoras`
--

CREATE TABLE `protectoras` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `ciudad` varchar(100) NOT NULL,
  `provincia` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(200) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `protectoras`
--

INSERT INTO `protectoras` (`id`, `nombre`, `ciudad`, `provincia`, `telefono`, `email`, `descripcion`, `logo`, `activa`) VALUES
(1, 'Protectora Amigos del Alma', 'Valencia', 'Valencia', '963111222', 'info@amigosdelalma.org', 'Llevamos mas de 15 años rescatando y acogiendo animales abandonados en la Comunitat Valenciana.', NULL, 1),
(2, 'Refugio Peludo Feliz', 'Madrid', 'Madrid', '912345678', 'contacto@peludofeliz.org', 'Refugio sin animo de lucro dedicado al rescate de perros y gatos en situacion de riesgo.', NULL, 1),
(3, 'Asociacion Huella Viva', 'Alicante', 'Alicante', '965222333', 'huellaviva@gmail.com', 'Pequeña asociacion de voluntarios apasionados por el bienestar animal.', NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes_adopcion`
--

CREATE TABLE `solicitudes_adopcion` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_usuario` int(10) UNSIGNED NOT NULL,
  `id_mascota` int(10) UNSIGNED NOT NULL,
  `estado` enum('pendiente','aprobada','rechazada') NOT NULL DEFAULT 'pendiente',
  `motivacion` text DEFAULT NULL,
  `situacion_hogar` varchar(255) DEFAULT NULL,
  `tiene_animales` tinyint(1) NOT NULL DEFAULT 0,
  `notas_admin` text DEFAULT NULL,
  `fecha_solicitud` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `solicitudes_adopcion`
--

INSERT INTO `solicitudes_adopcion` (`id`, `id_usuario`, `id_mascota`, `estado`, `motivacion`, `situacion_hogar`, `tiene_animales`, `notas_admin`, `fecha_solicitud`) VALUES
(1, 4, 9, 'pendiente', 'Quiero compañia y su personalidad encaja con la mia, seremos muy felices', 'casa_sin_jardin', 1, '', '2026-02-27 16:21:17'),
(2, 4, 11, 'aprobada', 'Quiero agregar un nuevo integrante a la familia, tambien tengo otras mascotas y entre mas, es mejor', 'piso_sin_terraza', 1, '', '2026-02-27 16:44:43');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `testimonios`
--

CREATE TABLE `testimonios` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_usuario` int(10) UNSIGNED NOT NULL,
  `id_mascota` int(10) UNSIGNED DEFAULT NULL,
  `texto` text NOT NULL,
  `foto_antes` varchar(255) DEFAULT NULL,
  `foto_despues` varchar(255) DEFAULT NULL,
  `aprobado` tinyint(1) NOT NULL DEFAULT 0,
  `fecha` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `testimonios`
--

INSERT INTO `testimonios` (`id`, `id_usuario`, `id_mascota`, `texto`, `foto_antes`, `foto_despues`, `aprobado`, `fecha`) VALUES
(1, 2, 7, 'Adoptar a Bruno cambio mi vida. Ahora tengo un compañero fiel para mis caminatas. Gracias Huellas de Amor.', NULL, NULL, 1, '2026-02-25 22:26:11'),
(2, 3, 8, 'Mimi llego asustada pero con amor hoy es la reina de casa. Gracias por hacer esto posible.', NULL, NULL, 1, '2026-02-25 22:26:11');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellidos` varchar(150) NOT NULL,
  `email` varchar(200) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `dni` varchar(10) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `cp` varchar(10) DEFAULT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL,
  `rol` enum('visitante','registrado','admin') NOT NULL DEFAULT 'registrado',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `apellidos`, `email`, `contrasena`, `telefono`, `dni`, `direccion`, `ciudad`, `cp`, `foto_perfil`, `rol`, `activo`, `fecha_registro`) VALUES
(1, 'Admin', 'Huellas de Amor', 'admin@huellasdeamor.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, NULL, NULL, 'admin', 1, '2026-02-25 22:26:11'),
(2, 'Carlos', 'Mendoza Garcia', 'carlos@ejemplo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '600123456', NULL, NULL, 'Valencia', NULL, NULL, 'registrado', 1, '2026-02-25 22:26:11'),
(3, 'Elena', 'Rodriguez Lopez', 'elena@ejemplo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '611234567', NULL, NULL, 'Madrid', NULL, NULL, 'registrado', 1, '2026-02-25 22:26:11'),
(4, 'Kelly', 'Rodriguez Mastrodomenico', 'kelrodmas@alu.edu.gva.es', '$2y$10$cjwwSfUWFXoYrKJ2YOQeTuGDziUZGx76G1putqd/tan7BvcM3UHUK', '627097064', NULL, 'Calle Pablo Picasso, 14', 'Crevillente', '03330', NULL, 'registrado', 1, '2026-02-26 18:24:04');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `apadrinamientos`
--
ALTER TABLE `apadrinamientos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_mascota` (`id_mascota`);

--
-- Indices de la tabla `casas_acogida`
--
ALTER TABLE `casas_acogida`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_mascota` (`id_mascota`);

--
-- Indices de la tabla `contacto`
--
ALTER TABLE `contacto`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `donaciones`
--
ALTER TABLE `donaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `eventos`
--
ALTER TABLE `eventos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_admin` (`id_admin`);

--
-- Indices de la tabla `favoritos`
--
ALTER TABLE `favoritos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unico_favorito` (`id_usuario`,`id_mascota`),
  ADD KEY `id_mascota` (`id_mascota`);

--
-- Indices de la tabla `foro_categorias`
--
ALTER TABLE `foro_categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `foro_posts`
--
ALTER TABLE `foro_posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_tema` (`id_tema`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `foro_temas`
--
ALTER TABLE `foro_temas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_categoria` (`id_categoria`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `fotos_mascotas`
--
ALTER TABLE `fotos_mascotas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_mascota` (`id_mascota`);

--
-- Indices de la tabla `mascotas`
--
ALTER TABLE `mascotas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_protectora` (`id_protectora`);

--
-- Indices de la tabla `noticias`
--
ALTER TABLE `noticias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `id_admin` (`id_admin`);

--
-- Indices de la tabla `protectoras`
--
ALTER TABLE `protectoras`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `solicitudes_adopcion`
--
ALTER TABLE `solicitudes_adopcion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_mascota` (`id_mascota`);

--
-- Indices de la tabla `testimonios`
--
ALTER TABLE `testimonios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_mascota` (`id_mascota`);

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
-- AUTO_INCREMENT de la tabla `apadrinamientos`
--
ALTER TABLE `apadrinamientos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `casas_acogida`
--
ALTER TABLE `casas_acogida`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `contacto`
--
ALTER TABLE `contacto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `donaciones`
--
ALTER TABLE `donaciones`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `eventos`
--
ALTER TABLE `eventos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `favoritos`
--
ALTER TABLE `favoritos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `foro_categorias`
--
ALTER TABLE `foro_categorias`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `foro_posts`
--
ALTER TABLE `foro_posts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `foro_temas`
--
ALTER TABLE `foro_temas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `fotos_mascotas`
--
ALTER TABLE `fotos_mascotas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `mascotas`
--
ALTER TABLE `mascotas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `noticias`
--
ALTER TABLE `noticias`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `protectoras`
--
ALTER TABLE `protectoras`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `solicitudes_adopcion`
--
ALTER TABLE `solicitudes_adopcion`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `testimonios`
--
ALTER TABLE `testimonios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `apadrinamientos`
--
ALTER TABLE `apadrinamientos`
  ADD CONSTRAINT `apadrinamientos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `apadrinamientos_ibfk_2` FOREIGN KEY (`id_mascota`) REFERENCES `mascotas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `casas_acogida`
--
ALTER TABLE `casas_acogida`
  ADD CONSTRAINT `casas_acogida_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `casas_acogida_ibfk_2` FOREIGN KEY (`id_mascota`) REFERENCES `mascotas` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `donaciones`
--
ALTER TABLE `donaciones`
  ADD CONSTRAINT `donaciones_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `eventos`
--
ALTER TABLE `eventos`
  ADD CONSTRAINT `eventos_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `favoritos`
--
ALTER TABLE `favoritos`
  ADD CONSTRAINT `favoritos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favoritos_ibfk_2` FOREIGN KEY (`id_mascota`) REFERENCES `mascotas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `foro_posts`
--
ALTER TABLE `foro_posts`
  ADD CONSTRAINT `foro_posts_ibfk_1` FOREIGN KEY (`id_tema`) REFERENCES `foro_temas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `foro_posts_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `foro_temas`
--
ALTER TABLE `foro_temas`
  ADD CONSTRAINT `foro_temas_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `foro_categorias` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `foro_temas_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `fotos_mascotas`
--
ALTER TABLE `fotos_mascotas`
  ADD CONSTRAINT `fotos_mascotas_ibfk_1` FOREIGN KEY (`id_mascota`) REFERENCES `mascotas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `mascotas`
--
ALTER TABLE `mascotas`
  ADD CONSTRAINT `mascotas_ibfk_1` FOREIGN KEY (`id_protectora`) REFERENCES `protectoras` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `noticias`
--
ALTER TABLE `noticias`
  ADD CONSTRAINT `noticias_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `solicitudes_adopcion`
--
ALTER TABLE `solicitudes_adopcion`
  ADD CONSTRAINT `solicitudes_adopcion_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `solicitudes_adopcion_ibfk_2` FOREIGN KEY (`id_mascota`) REFERENCES `mascotas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `testimonios`
--
ALTER TABLE `testimonios`
  ADD CONSTRAINT `testimonios_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `testimonios_ibfk_2` FOREIGN KEY (`id_mascota`) REFERENCES `mascotas` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
