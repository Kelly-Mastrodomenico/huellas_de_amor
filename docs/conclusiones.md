# 🎯 Conclusiones

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
| Panel de usuario completo (solicitudes, donaciones, perfil) | ✅ Completado |
| Edición de perfil con foto | ✅ Completado |
| Registro con validación DNI (JS + PHP) | ✅ Completado |
| Panel de administración con estadísticas | ✅ Completado |
| CRUD completo de mascotas con fotos | ✅ Completado |
| Exportación PDF listado de mascotas con logo | ✅ Completado |
| Gestión de solicitudes (aprobar/rechazar con notas) | ✅ Completado |
| Gestión de noticias con editor TinyMCE | ✅ Completado |
| Gestión de donaciones con filtros y exportación PDF | ✅ Completado |
| Gestión de usuarios con cambio de rol | ✅ Completado |
| Mensajes de contacto con modal y marcar como leído | ✅ Completado |
| Diseño responsive Mobile First con SASS/SCSS | ✅ Completado |
| Validaciones JS (jQuery) + PHP en todos los formularios | ✅ Completado |
| Control de versiones con Git + GitHub | ✅ Completado |
| Documentación GitHub Pages | ✅ Completado |

## Aprendizajes

### Técnicos
- Dominio de PHP con PDO para consultas seguras con `prepare()` y `bindParam()`
- Integración de librerías externas (FPDF, TinyMCE, FancyBox, jQuery UI)
- Gestión de sesiones y autenticación con `password_hash()` y `password_verify()`
- Arquitectura SASS/SCSS modular con estructura 7-1
- Técnica Mobile First con breakpoints definidos
- Generación de PDFs con FPDF incluyendo logo, tablas y colores corporativos
- Peticiones AJAX con jQuery para filtros sin recargar página
- Control de versiones con ramas (main, develop, feature/*)

### Metodológicos
- Planificación de un proyecto complejo desde cero hasta producción
- Separación de responsabilidades (includes, templates, scss modular)
- Documentación técnica con Markdown publicada en GitHub Pages
- Uso de tags de versión en Git (v0.1, v0.2... v1.0)

## Dificultades encontradas

- **FPDF y UTF-8:** La librería FPDF no soporta UTF-8 nativamente, requiriendo conversión con `iconv()` a windows-1252. Se resolvió usando la misma función que en `exportar_mascotas.php`
- **SASS deprecations:** Las versiones nuevas de SASS deprecan `@import`, generando warnings al compilar. Son avisos, no errores, y el CSS compila correctamente
- **Responsive en tablas admin:** Adaptar las tablas del panel de administración a pantallas pequeñas requirió trabajo extra con `overflow-x: auto` y CSS Grid
- **Rutas en subcarpetas:** Los archivos dentro de `admin/`, `mascotas/`, `noticias/` y `usuario/` requieren rutas relativas distintas, gestionadas con detección dinámica en el header

## Posibles mejoras futuras

- Sistema de recuperación de contraseña por email real
- Foro comunitario completamente funcional
- Galería de testimonios de adopciones exitosas con antes/después
- Integración con pasarela de pago real (Stripe o PayPal)
- Sistema de notificaciones por email al cambiar estado de solicitud
- Aplicación móvil nativa con React Native

---

[← Guía de uso](uso.md) | [Referencias →](referencias.md)