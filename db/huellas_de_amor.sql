SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- TABLA: usuarios
-- Guarda todos los usuarios: visitantes, registrados y admins
-- ============================================================
CREATE TABLE IF NOT EXISTS `usuarios` (
    `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nombre`         VARCHAR(100)  NOT NULL,
    `apellidos`      VARCHAR(150)  NOT NULL,
    `email`          VARCHAR(200)  NOT NULL UNIQUE,
    `contrasena`     VARCHAR(255)  NOT NULL,
    `telefono`       VARCHAR(20)   DEFAULT NULL,
    `direccion`      VARCHAR(255)  DEFAULT NULL,
    `ciudad`         VARCHAR(100)  DEFAULT NULL,
    `cp`             VARCHAR(10)   DEFAULT NULL,
    `foto_perfil`    VARCHAR(255)  DEFAULT NULL,
    `rol`            ENUM('visitante','registrado','admin') NOT NULL DEFAULT 'registrado',
    `activo`         TINYINT(1)    NOT NULL DEFAULT 1,
    `fecha_registro` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: protectoras
-- Refugios y protectoras colaboradoras
-- ============================================================
CREATE TABLE IF NOT EXISTS `protectoras` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nombre`      VARCHAR(200) NOT NULL,
    `ciudad`      VARCHAR(100) NOT NULL,
    `provincia`   VARCHAR(100) DEFAULT NULL,
    `telefono`    VARCHAR(20)  DEFAULT NULL,
    `email`       VARCHAR(200) DEFAULT NULL,
    `descripcion` TEXT         DEFAULT NULL,
    `logo`        VARCHAR(255) DEFAULT NULL,
    `activa`      TINYINT(1)   NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: mascotas
-- Todas las mascotas del sistema
-- ============================================================
CREATE TABLE IF NOT EXISTS `mascotas` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nombre`        VARCHAR(100) NOT NULL,
    `especie`       ENUM('perro','gato','otro') NOT NULL DEFAULT 'perro',
    `raza`          VARCHAR(100) DEFAULT NULL,
    `edad_anios`    TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `edad_meses`    TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `sexo`          ENUM('macho','hembra') NOT NULL DEFAULT 'macho',
    `tamanio`       ENUM('pequeño','mediano','grande') NOT NULL DEFAULT 'mediano',
    `descripcion`   TEXT DEFAULT NULL,
    `caracter`      VARCHAR(255) DEFAULT NULL,
    `estado`        ENUM('disponible','adoptado','acogida') NOT NULL DEFAULT 'disponible',
    `id_protectora` INT UNSIGNED DEFAULT NULL,
    `fecha_ingreso` DATE DEFAULT NULL,
    `activo`        TINYINT(1) NOT NULL DEFAULT 1,
    FOREIGN KEY (`id_protectora`) REFERENCES `protectoras`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: fotos_mascotas
-- Una mascota puede tener varias fotos, una es la principal
-- ============================================================
CREATE TABLE IF NOT EXISTS `fotos_mascotas` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_mascota`   INT UNSIGNED NOT NULL,
    `ruta_foto`    VARCHAR(255) NOT NULL,
    `es_principal` TINYINT(1)   NOT NULL DEFAULT 0,
    FOREIGN KEY (`id_mascota`) REFERENCES `mascotas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: solicitudes_adopcion
-- Solicitudes que hacen los usuarios registrados
-- ============================================================
CREATE TABLE IF NOT EXISTS `solicitudes_adopcion` (
    `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_usuario`      INT UNSIGNED NOT NULL,
    `id_mascota`      INT UNSIGNED NOT NULL,
    `estado`          ENUM('pendiente','aprobada','rechazada') NOT NULL DEFAULT 'pendiente',
    `motivacion`      TEXT DEFAULT NULL,
    `situacion_hogar` VARCHAR(255) DEFAULT NULL,
    `tiene_animales`  TINYINT(1) NOT NULL DEFAULT 0,
    `notas_admin`     TEXT DEFAULT NULL,
    `fecha_solicitud` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`id_mascota`) REFERENCES `mascotas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: apadrinamientos
-- Un usuario puede apadrinar una mascota con un plan mensual
-- ============================================================
CREATE TABLE IF NOT EXISTS `apadrinamientos` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_usuario`    INT UNSIGNED NOT NULL,
    `id_mascota`    INT UNSIGNED NOT NULL,
    `plan`          ENUM('basico','medio','completo') NOT NULL DEFAULT 'basico',
    `monto_mensual` DECIMAL(8,2) NOT NULL,
    `fecha_inicio`  DATE NOT NULL,
    `activo`        TINYINT(1) NOT NULL DEFAULT 1,
    FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`id_mascota`) REFERENCES `mascotas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: donaciones
-- id_usuario puede ser NULL si dona alguien sin cuenta
-- ============================================================
CREATE TABLE IF NOT EXISTS `donaciones` (
    `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_usuario`      INT UNSIGNED DEFAULT NULL,
    `monto`           DECIMAL(8,2) NOT NULL,
    `concepto`        VARCHAR(255) DEFAULT NULL,
    `metodo_pago`     VARCHAR(100) DEFAULT NULL,
    `fecha`           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `certificado_pdf` VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: casas_acogida
-- Usuarios que se ofrecen como casa de acogida temporal
-- ============================================================
CREATE TABLE IF NOT EXISTS `casas_acogida` (
    `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_usuario`        INT UNSIGNED NOT NULL,
    `id_mascota`        INT UNSIGNED DEFAULT NULL,
    `descripcion_hogar` TEXT DEFAULT NULL,
    `tiene_jardin`      TINYINT(1) NOT NULL DEFAULT 0,
    `otros_animales`    TINYINT(1) NOT NULL DEFAULT 0,
    `disponible`        TINYINT(1) NOT NULL DEFAULT 1,
    `fecha_registro`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`id_mascota`) REFERENCES `mascotas`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: noticias
-- Publicadas por el admin
-- ============================================================
CREATE TABLE IF NOT EXISTS `noticias` (
    `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `titulo`            VARCHAR(255) NOT NULL,
    `slug`              VARCHAR(255) NOT NULL UNIQUE,
    `contenido`         LONGTEXT DEFAULT NULL,
    `imagen`            VARCHAR(255) DEFAULT NULL,
    `id_admin`          INT UNSIGNED DEFAULT NULL,
    `categoria`         VARCHAR(100) DEFAULT NULL,
    `publicada`         TINYINT(1) NOT NULL DEFAULT 0,
    `fecha_publicacion` DATETIME DEFAULT NULL,
    FOREIGN KEY (`id_admin`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: eventos
-- Jornadas de adopcion, ferias, charlas...
-- ============================================================
CREATE TABLE IF NOT EXISTS `eventos` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `titulo`       VARCHAR(255) NOT NULL,
    `descripcion`  TEXT DEFAULT NULL,
    `fecha_evento` DATETIME NOT NULL,
    `lugar`        VARCHAR(255) DEFAULT NULL,
    `imagen`       VARCHAR(255) DEFAULT NULL,
    `cupo_maximo`  INT UNSIGNED DEFAULT NULL,
    `id_admin`     INT UNSIGNED DEFAULT NULL,
    `publicado`    TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (`id_admin`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: foro_categorias
-- Secciones del foro
-- ============================================================
CREATE TABLE IF NOT EXISTS `foro_categorias` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nombre`      VARCHAR(100) NOT NULL,
    `descripcion` TEXT DEFAULT NULL,
    `icono`       VARCHAR(50)  DEFAULT NULL,
    `orden`       INT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: foro_temas
-- Hilos del foro creados por usuarios
-- ============================================================
CREATE TABLE IF NOT EXISTS `foro_temas` (
    `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_categoria`   INT UNSIGNED NOT NULL,
    `id_usuario`     INT UNSIGNED NOT NULL,
    `titulo`         VARCHAR(255) NOT NULL,
    `vistas`         INT UNSIGNED NOT NULL DEFAULT 0,
    `cerrado`        TINYINT(1)   NOT NULL DEFAULT 0,
    `fijado`         TINYINT(1)   NOT NULL DEFAULT 0,
    `fecha_creacion` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`id_categoria`) REFERENCES `foro_categorias`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`id_usuario`)   REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: foro_posts
-- Mensajes dentro de cada tema del foro
-- ============================================================
CREATE TABLE IF NOT EXISTS `foro_posts` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_tema`    INT UNSIGNED NOT NULL,
    `id_usuario` INT UNSIGNED NOT NULL,
    `contenido`  TEXT NOT NULL,
    `likes`      INT UNSIGNED NOT NULL DEFAULT 0,
    `fecha_post` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`id_tema`)    REFERENCES `foro_temas`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id`)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: testimonios
-- Historia de exito enviada por el adoptante (requiere aprobacion admin)
-- ============================================================
CREATE TABLE IF NOT EXISTS `testimonios` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_usuario`   INT UNSIGNED NOT NULL,
    `id_mascota`   INT UNSIGNED DEFAULT NULL,
    `texto`        TEXT NOT NULL,
    `foto_antes`   VARCHAR(255) DEFAULT NULL,
    `foto_despues` VARCHAR(255) DEFAULT NULL,
    `aprobado`     TINYINT(1)   NOT NULL DEFAULT 0,
    `fecha`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`id_mascota`) REFERENCES `mascotas`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: favoritos
-- Mascotas guardadas por un usuario (no puede duplicarse)
-- ============================================================
CREATE TABLE IF NOT EXISTS `favoritos` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_usuario` INT UNSIGNED NOT NULL,
    `id_mascota` INT UNSIGNED NOT NULL,
    `fecha`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unico_favorito` (`id_usuario`, `id_mascota`),
    FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`id_mascota`) REFERENCES `mascotas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- DATOS DE EJEMPLO
-- Para poder ver la web funcionando desde el primer dia
-- ============================================================

-- Admin (contrasena: Admin1234!)
INSERT INTO `usuarios` (`nombre`, `apellidos`, `email`, `contrasena`, `rol`) VALUES
('Admin', 'Huellas de Amor', 'admin@huellasdeamor.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Usuarios de prueba (contrasena: password)
INSERT INTO `usuarios` (`nombre`, `apellidos`, `email`, `contrasena`, `telefono`, `ciudad`, `rol`) VALUES
('Carlos',  'Mendoza Garcia',   'carlos@ejemplo.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '600123456', 'Valencia', 'registrado'),
('Elena',   'Rodriguez Lopez',  'elena@ejemplo.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '611234567', 'Madrid',   'registrado');

-- Protectoras
INSERT INTO `protectoras` (`nombre`, `ciudad`, `provincia`, `telefono`, `email`, `descripcion`) VALUES
('Protectora Amigos del Alma', 'Valencia', 'Valencia', '963111222', 'info@amigosdelalma.org',
 'Llevamos mas de 15 años rescatando y acogiendo animales abandonados en la Comunitat Valenciana.'),
('Refugio Peludo Feliz',       'Madrid',   'Madrid',   '912345678', 'contacto@peludofeliz.org',
 'Refugio sin animo de lucro dedicado al rescate de perros y gatos en situacion de riesgo.'),
('Asociacion Huella Viva',     'Alicante', 'Alicante', '965222333', 'huellaviva@gmail.com',
 'Pequeña asociacion de voluntarios apasionados por el bienestar animal.');

-- Mascotas de ejemplo
INSERT INTO `mascotas` (`nombre`, `especie`, `raza`, `edad_anios`, `edad_meses`, `sexo`, `tamanio`, `descripcion`, `caracter`, `estado`, `id_protectora`, `fecha_ingreso`) VALUES
('Luna',   'perro', 'Mestizo',           2, 0, 'hembra', 'mediano', 'Luna es una perra cariñosa y activa que adora los paseos.', 'Activo, cariñoso, jugueton',          'disponible', 1, '2024-10-15'),
('Oliver', 'gato',  'Europeo',           0, 6, 'macho',  'pequeño', 'Oliver es un gatito curioso y muy sociable.',              'Curioso, sociable, mimoso',            'disponible', 2, '2024-11-20'),
('Max',    'perro', 'Labrador',          4, 0, 'macho',  'grande',  'Max es un perro tranquilo y muy leal.',                    'Tranquilo, leal, obediente',           'disponible', 1, '2024-09-01'),
('Bella',  'perro', 'Chihuahua',         1, 0, 'hembra', 'pequeño', 'Bella es pequeña pero llena de energia.',                  'Activo, inteligente, valiente',        'disponible', 3, '2024-12-05'),
('Coco',   'perro', 'Beagle',            3, 0, 'hembra', 'mediano', 'Coco llego de la calle pero ya confia en las personas.',   'Tranquilo, timido, cariñoso',          'disponible', 2, '2024-08-20'),
('Simba',  'gato',  'Siames',            0, 2, 'macho',  'pequeño', 'Simba es muy activo y jugueton.',                          'Muy activo, jugueton, independiente',  'disponible', 3, '2025-01-10'),
('Bruno',  'perro', 'Pastor Aleman',     5, 0, 'macho',  'grande',  'Bruno ya encontro su hogar. Historia de exito.',           'Leal, protector, inteligente',         'adoptado',   1, '2023-06-01'),
('Mimi',   'gato',  'Persa',             2, 0, 'hembra', 'pequeño', 'Mimi llego asustada pero hoy es la reina de su casa.',     'Tranquilo, cariñoso, independiente',   'adoptado',   2, '2023-09-15');

-- Fotos de las mascotas (imagenes de ejemplo con picsum)
INSERT INTO `fotos_mascotas` (`id_mascota`, `ruta_foto`, `es_principal`) VALUES
(1, 'https://picsum.photos/400/300?random=11', 1),
(2, 'https://picsum.photos/400/300?random=12', 1),
(3, 'https://picsum.photos/400/300?random=13', 1),
(4, 'https://picsum.photos/400/300?random=14', 1),
(5, 'https://picsum.photos/400/300?random=15', 1),
(6, 'https://picsum.photos/400/300?random=16', 1),
(7, 'https://picsum.photos/400/300?random=17', 1),
(8, 'https://picsum.photos/400/300?random=18', 1);

-- Testimonios aprobados
INSERT INTO `testimonios` (`id_usuario`, `id_mascota`, `texto`, `aprobado`) VALUES
(2, 7, 'Adoptar a Bruno cambio mi vida. Ahora tengo un compañero fiel para mis caminatas. Gracias Huellas de Amor.', 1),
(3, 8, 'Mimi llego asustada pero con amor hoy es la reina de casa. Gracias por hacer esto posible.', 1);

-- Categorias del foro
INSERT INTO `foro_categorias` (`nombre`, `descripcion`, `icono`, `orden`) VALUES
('Adopcion y Acogida',  'Consejos y experiencias sobre el proceso de adopcion', 'fa-heart',          1),
('Salud Animal',        'Preguntas sobre salud y veterinaria',                  'fa-stethoscope',    2),
('Adiestramiento',      'Tecnicas para educar a tu mascota',                    'fa-graduation-cap', 3),
('Historias de Exito',  'Comparte tu historia de adopcion exitosa',             'fa-star',           4),
('General',             'Cualquier otro tema sobre bienestar animal',           'fa-comments',       5);

-- Noticias de ejemplo
INSERT INTO `noticias` (`titulo`, `slug`, `contenido`, `id_admin`, `categoria`, `publicada`, `fecha_publicacion`) VALUES
('Jornada de adopcion este sabado en Valencia',
 'jornada-adopcion-sabado-valencia',
 'Este sabado organizamos una gran jornada de adopcion. Mas de 30 mascotas buscan familia.',
 1, 'Eventos', 1, NOW()),
('Consejos para la primera semana con tu mascota',
 'consejos-primera-semana-mascota',
 'Adoptar una mascota requiere preparacion. Aqui te contamos todo lo que necesitas saber.',
 1, 'Consejos', 1, NOW()),
('Campana de esterilizacion gratuita en marzo',
 'campana-esterilizacion-gratuita-marzo',
 'Durante marzo varias clinicas veterinarias ofrecen esterilizaciones gratuitas.',
 1, 'Salud', 1, NOW());

-- Donaciones de ejemplo
INSERT INTO `donaciones` (`id_usuario`, `monto`, `concepto`, `metodo_pago`) VALUES
(2,    25.00, 'Donacion mensual general',    'tarjeta'),
(3,    50.00, 'Apoyo refugio Peludo Feliz',  'paypal'),
(NULL, 10.00, 'Donacion anonima',            'tarjeta');

SET FOREIGN_KEY_CHECKS = 1;