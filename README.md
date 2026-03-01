# Huellas de Amor 🐾

Plataforma web de adopción y apadrinamiento de mascotas

![Estado](https://img.shields.io/badge/Estado-En%20Desarrollo-yellow)
![Versión](https://img.shields.io/badge/Versión-1.0-blue)
![Licencia](https://img.shields.io/badge/Licencia-MIT-green)
![PHP](https://img.shields.io/badge/PHP-8.2-purple)
![MySQL](https://img.shields.io/badge/MySQL-8.0-blue)

## 📋 Descripción

**Huellas de Amor** es una plataforma web intermodular diseñada para centralizar protectoras, refugios y veterinarias en un único espacio digital, facilitando la adopción responsable de animales rescatados. Permite a los usuarios explorar mascotas disponibles, solicitar adopciones, apadrinar animales y realizar donaciones.

## 👥 Autora

**Kelly Rodríguez Mastrodomenico**  
2º DAW - Curso 2025-2026  
I.E.S. Macià Àbela  
Tutor: Carlos Fernández

## 🛠️ Tecnologías Utilizadas

### Frontend
- HTML5 semántico
- CSS3 (Flexbox, Grid, Variables CSS, Animaciones)
- JavaScript Vanilla + jQuery + jQuery UI
- Bootstrap 5
- Font Awesome 6
- Google Fonts (Nunito + Lato)
- FancyBox (galería de imágenes)
- SASS/SCSS (preprocesador CSS)

### Backend
- PHP 8.2 (mezclado con HTML)
- MySQL 8.0
- PDO (conexión segura con prepare + bindParam)
- FPDF (generación de certificados PDF)

### DevOps
- Git + GitHub (control de versiones)
- GitHub Pages (documentación)
- AWS (despliegue en producción)

## 🚀 Características Principales

### Público general
- ✅ Catálogo de mascotas con filtros (especie, raza, edad, tamaño, sexo)
- ✅ Detalle de mascota con galería de fotos
- ✅ Noticias y eventos del refugio
- ✅ Formulario de contacto
- ✅ Protectoras colaboradoras

### Usuario registrado
- ✅ Solicitud de adopción con seguimiento
- ✅ Apadrinamiento de mascotas (3 planes)
- ✅ Donaciones con pasarela simulada
- ✅ Certificados PDF (adopción y donación)
- ✅ Panel personal con historial completo
- ✅ Edición de perfil con foto

### Administrador
- ✅ Panel de control con estadísticas
- ✅ CRUD completo de mascotas
- ✅ Gestión de solicitudes de adopción
- ✅ Gestión de usuarios (roles, activar/desactivar)
- ✅ Gestión de noticias con editor TinyMCE
- ✅ Gestión de donaciones con exportación PDF
- ✅ Mensajes de contacto con modal de respuesta

## 📱 Responsive Design

Técnica Mobile First con breakpoints:

| Dispositivo | Breakpoint | Menú |
|---|---|---|
| 📱 Móvil | hasta 667px | Hamburguesa |
| 📱 Tablet Vertical | 668px+ | Hamburguesa |
| 💻 Tablet Horizontal | 1024px+ | Horizontal |
| 🖥️ Desktop | 1200px+ | Horizontal completo |

## 🗄️ Base de Datos

Base de datos `huellas_de_amor` con 16 tablas:
`usuarios`, `mascotas`, `fotos_mascotas`, `protectoras`, `solicitudes_adopcion`, `apadrinamientos`, `donaciones`, `casas_acogida`, `noticias`, `eventos`, `foro_categorias`, `foro_temas`, `foro_posts`, `testimonios`, `favoritos`, `contacto`

## ⚙️ Instalación local

Consulta la [Guía de Instalación](docs/instalacion.md) completa.

```bash
# Clonar el repositorio
git clone https://github.com/Kelly-Mastrodomenico/HuellasDeAmor.git

# Importar la base de datos
mysql -u root -p huellas_de_amor < db/huellas_de_amor.sql

# Configurar conexión en includes/conexion.php
# Arrancar XAMPP y abrir http://localhost/HuellasDeAmor
```

## 📚 Documentación

Consulta la documentación completa en [GitHub Pages](https://Kelly-Mastrodomenico.github.io/HuellasDeAmor/)

- [Introducción](docs/introduccion.md)
- [Arquitectura](docs/arquitectura.md)
- [Instalación](docs/instalacion.md)
- [Guía de uso](docs/uso.md)
- [Conclusiones](docs/conclusiones.md)
- [Referencias](docs/referencias.md)

## 🌿 Ramas Git

```
main        ← versión estable
develop     ← desarrollo activo
feature/*   ← nuevas funcionalidades
hotfix/*    ← correcciones urgentes
```

## 📄 Licencia

Este proyecto está bajo la Licencia MIT.

## 🔗 Enlaces

- [Repositorio GitHub](https://github.com/Kelly-Mastrodomenico/huellas_de_amor)
- [Documentación GitHub Pages](https://Kelly-Mastrodomenico.github.io/huellas_de_amor/)

---
Desarrollado con ❤️ para el CFGS Desarrollo de Aplicaciones Web — I.E.S. Macià Àbela