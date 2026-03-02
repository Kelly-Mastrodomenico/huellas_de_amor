---
layout: default
title: Instalación
---

# ⚙️ Guía de Instalación

← [Volver al índice](index.md)

---

## Requisitos previos

- XAMPP (PHP 8.2 + Apache + MySQL)
- Git
- Navegador moderno (Chrome, Firefox, Edge)

## Instalación local con XAMPP

### 1. Clonar el repositorio

```bash
git clone https://github.com/Kelly-Mastrodomenico/huellas_de_amor.git
```

Mueve la carpeta a `C:\xampp\htdocs\HuellasDeAmor`

### 2. Crear la base de datos

1. Abre phpMyAdmin → `http://localhost/phpmyadmin`
2. Crea una base de datos llamada `huellas_de_amor`
3. Importa el archivo `db/huellas_de_amor.sql`

### 3. Configurar la conexión

Edita `includes/configuracion.php`:

```php
define('DB_HOST',    'localhost');
define('DB_NAME',    'huellas_de_amor');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('BASE_URL',   '/HuellasDeAmor/');
define('BASE_PATH',  'C:/xampp/htdocs/HuellasDeAmor');
```

### 4. Arrancar XAMPP

1. Abre XAMPP Control Panel
2. Inicia **Apache** y **MySQL**
3. Accede a `http://localhost/HuellasDeAmor`

### 5. Credenciales de prueba

| Rol | Email | Contraseña |
|---|---|---|
| Admin | admin@huellasdeamor.com | password |
| Usuario | kelrodmas@alu.edu.gva.es | 123456 |

## Compilar SASS (opcional)

```bash
npm install -g sass
sass scss/main.scss css/style.css --watch
```

## Despliegue en AWS EC2

La aplicación está desplegada en **AWS EC2** con Ubuntu 22.04 + Apache + MySQL + PHP 8.2.

**URL de producción:** [http://3.220.231.221](http://3.220.231.221)

```bash
# Configuración en producción (includes/configuracion.php)
define('DB_HOST',    'localhost');
define('DB_NAME',    'huellas_de_amor');
define('DB_USER',    'huellas');
define('DB_PASS',    'huellas2026');
define('BASE_URL',   '/');
define('BASE_PATH',  '/var/www/html');
```

---

← [Anterior: Arquitectura](arquitectura.md) | [Siguiente: Uso →](uso.md)