---
layout: default
title: Arquitectura
---

# 🏗️ Arquitectura del Proyecto

[← Volver al índice](index.md)

---

## 🚀 Stack Tecnológico

### Frontend

| Tecnología | Uso |
| :--- | :--- |
| **HTML5** | Estructura semántica de las vistas |
| **SASS/SCSS** | Estilos modulares y mantenibles |
| **JavaScript** | Lógica de cliente y validaciones (Vanilla) |
| **jQuery + UI** | Interacciones AJAX y componentes como Datepicker |
| **Bootstrap 5** | Sistema de rejilla (Grid) y componentes responsive |
| **Font Awesome** | Iconografía del sitio |
| **FancyBox** | Visualización de galerías de mascotas |

### Backend

| Tecnología | Uso |
| :--- | :--- |
| **PHP 8.2** | Motor de lógica del lado del servidor |
| **MySQL 8.0** | Gestión de base de datos relacional |
| **PDO** | Capa de abstracción para consultas seguras |
| **FPDF** | Generación dinámica de certificados en PDF |
| **TinyMCE** | Editor de texto enriquecido para el panel admin |

---

## Estructura de archivos

```
HuellasDeAmor/
│
├── index.php                       ← Página principal
├── adoptar.php                     ← Catálogo de mascotas con filtros
├── apadrinar.php                   ← Programa de apadrinamiento
├── formulario_apadrinamiento.php   ← Formulario apadrinamiento
├── donaciones.php                  ← Página de donaciones
├── contacto.php                    ← Formulario de contacto
├── noticias.php                    ← Listado de noticias
├── login.php                       ← Inicio de sesión
├── logout.php                      ← Cerrar sesión
├── registro.php                    ← Registro de usuario
├── solicitud_adopcion.php          ← Formulario solicitud adopción
├── certificado_adopcion.php        ← PDF certificado adopción
├── certificado_donacion.php        ← PDF certificado donación
├── README.md                       ← Documentación principal
│
├── admin/
│   ├── panel.php                   ← Dashboard administrador
│   ├── mascotas.php                ← Listado de mascotas
│   ├── mascota_nueva.php           ← Añadir mascota
│   ├── mascota_editar.php          ← Editar mascota
│   ├── mascota_borrar.php          ← Borrar mascota
│   ├── exportar_mascotas.php       ← Exportar PDF mascotas
│   ├── solicitudes.php             ← Gestión solicitudes
│   ├── noticias.php                ← CRUD noticias
│   ├── donaciones.php              ← Gestión donaciones
│   ├── usuarios.php                ← Gestión usuarios
│   └── contacto.php                ← Mensajes de contacto
│
├── ajax/
│   └── buscar_mascotas.php         ← Búsqueda AJAX mascotas
│
├── assets/
│   └── img/
│       ├── Logo.png
│       └── Logotipo.png
│
├── css/
│   ├── style.css                   ← CSS compilado
│   └── style.css.map
│
├── db/
│   └── huellas_de_amor.sql         ← Script base de datos
│
├── docs/                           ← Documentación GitHub Pages
│   ├── _config.yml
│   ├── index.md
│   ├── introduccion.md
│   ├── arquitectura.md
│   ├── instalacion.md
│   ├── uso.md
│   ├── conclusiones.md
│   └── referencias.md
│
├── includes/
│   ├── conexion.php                ← Conexión PDO
│   ├── configuracion.php           ← Configuración global
│   ├── funciones.php               ← Funciones reutilizables
│   └── fpdf/                       ← Librería FPDF
│
├── js/
│   ├── ajax.js                     ← Peticiones AJAX
│   └── main.js                     ← JavaScript principal
│
├── mascotas/
│   └── detalle.php                 ← Ficha completa de mascota
│
├── noticias/
│   └── detalle.php                 ← Detalle de noticia
│
├── scss/
│   ├── main.scss                   ← Archivo principal SASS
│   ├── abstracts/
│   │   ├── _mixins.scss
│   │   └── _variables.scss
│   ├── base/
│   │   ├── _reset.scss
│   │   └── _tipografia.scss
│   ├── components/
│   │   ├── _buttons.scss
│   │   ├── _cards.scss
│   │   └── _forms.scss
│   ├── layout/
│   │   ├── _footer.scss
│   │   ├── _grid.scss
│   │   └── _header.scss
│   └── pages/
│       ├── _admin.scss
│       ├── _adoptar.scss
│       ├── _apadrinar.scss
│       ├── _contacto.scss
│       ├── _detalle.scss
│       ├── _donaciones.scss
│       ├── _home.scss
│       ├── _noticias.scss
│       ├── _panel_usuario.scss
│       └── _solicitud.scss
│
├── templates/
│   ├── header.php                  ← Cabecera pública
│   ├── footer.php                  ← Pie público
│   ├── header-admin.php            ← Cabecera administrador
│   └── footer-admin.php            ← Pie administrador
│
├── uploads/
│   ├── mascotas/                   ← Fotos de mascotas
│   ├── noticias/                   ← Imágenes de noticias
│   └── perfiles/                   ← Fotos de perfil de usuarios
│
└── usuario/
    ├── panel.php                   ← Panel del usuario
    └── editar_perfil.php           ← Editar perfil
```

## Base de datos

La base de datos `huellas_de_amor` contiene **16 tablas**:

| Tabla | Descripción |
|---|---|
| `usuarios` | Registro de usuarios con roles |
| `mascotas` | Animales disponibles para adopción |
| `fotos_mascotas` | Galería de fotos por mascota |
| `protectoras` | Organizaciones colaboradoras |
| `solicitudes_adopcion` | Solicitudes con estado y seguimiento |
| `apadrinamientos` | Programas de apadrinamiento activos |
| `donaciones` | Registro de donaciones económicas |
| `casas_acogida` | Hogares temporales registrados |
| `noticias` | Artículos y publicaciones |
| `eventos` | Eventos del refugio |
| `foro_categorias` | Categorías del foro |
| `foro_temas` | Hilos de conversación |
| `foro_posts` | Mensajes del foro |
| `testimonios` | Historias de adopciones exitosas |
| `favoritos` | Mascotas guardadas por usuarios |
| `contacto` | Mensajes del formulario de contacto |

## Paleta de colores

| Variable | Color | Uso |
|---|---|---|
| `--coral` | `#FF6B6B` | Botones principales, acentos |
| `--turquesa` | `#4ECDC4` | Secundario, headings |
| `--amarillo` | `#FFE66D` | Badges especiales |
| `--oscuro` | `#2C3E50` | Texto principal, nav |
| `--gris-claro` | `#F7F9FC` | Fondos alternos |

---

← [Anterior: Introducción](introduccion.md) | [Siguiente: Instalación →](instalacion.md)