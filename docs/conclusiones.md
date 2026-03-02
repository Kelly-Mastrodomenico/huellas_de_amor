---
layout: default
title: Conclusiones
---

# 🎯 Conclusiones

← [Volver al índice](index.md)

---

## Resultados obtenidos

El proyecto **Huellas de Amor** ha sido desarrollado como proyecto intermodular de 2º DAW, integrando conocimientos de todos los módulos del ciclo. Se ha conseguido una plataforma web funcional y completa que cubre los tres perfiles de usuario definidos en los requisitos.

## Funcionalidades implementadas

| Funcionalidad | Estado |
|---|---|
| Catálogo de mascotas con filtros AJAX | ✅ Completado |
| Ficha de mascota con galería FancyBox | ✅ Completado |
| Sistema de solicitudes de adopción con seguimiento | ✅ Completado |
| Apadrinamiento con 3 planes de pago | ✅ Completado |
| Donaciones con pasarela simulada | ✅ Completado |
| Certificado PDF de adopción | ✅ Completado |
| Certificado PDF de donación | ✅ Completado |
| Panel de usuario completo | ✅ Completado |
| Registro con validación DNI | ✅ Completado |
| Panel de administración con estadísticas | ✅ Completado |
| CRUD completo de mascotas con fotos | ✅ Completado |
| Exportación PDF listado de mascotas | ✅ Completado |
| Gestión de solicitudes (aprobar/rechazar) | ✅ Completado |
| Gestión de noticias con TinyMCE | ✅ Completado |
| Gestión de donaciones con exportación PDF | ✅ Completado |
| Gestión de usuarios con cambio de rol | ✅ Completado |
| Mensajes de contacto con modal | ✅ Completado |
| Diseño responsive Mobile First con SASS/SCSS | ✅ Completado |
| Control de versiones con Git + GitHub | ✅ Completado |
| Despliegue en AWS EC2 | ✅ Completado |
| Documentación GitHub Pages | ✅ Completado |

## Aprendizajes

### Técnicos
- Dominio de PHP con PDO para consultas seguras
- Integración de librerías externas (FPDF, TinyMCE, FancyBox)
- Gestión de sesiones y autenticación con bcrypt
- Arquitectura SASS/SCSS modular con estructura 7-1
- Generación de PDFs con FPDF
- Peticiones AJAX con jQuery para filtros sin recargar página
- Despliegue en AWS EC2 con Apache + PHP + MySQL

### Metodológicos
- Planificación de un proyecto complejo desde cero
- Separación de responsabilidades (includes, templates, scss modular)
- Documentación técnica con Markdown en GitHub Pages
- Control de versiones con ramas y tags (v0.1 → v1.0)

## Dificultades encontradas

- **FPDF y UTF-8:** La librería no soporta UTF-8 nativamente — se resolvió con `iconv()` a windows-1252
- **SASS deprecations:** Las versiones nuevas deprecan `@import`, generando warnings al compilar
- **Gestión de ramas Git:** Conflictos al fusionar ramas — se resolvió con `--force` controlado
- **Rutas en subcarpetas:** Requieren rutas relativas distintas según la profundidad del archivo

## Posibles mejoras futuras

- Sistema de recuperación de contraseña por email
- Foro comunitario completamente funcional
- Galería de testimonios con fotos antes/después
- Integración con pasarela de pago real (Stripe)
- Notificaciones por email al cambiar estado de solicitud

---

← [Anterior: Uso](uso.md) | [Siguiente: Referencias →](referencias.md)