# ⚙️ Guía de Instalación

## Requisitos previos

- XAMPP (PHP 8.2 + Apache + MySQL)
- Git
- Navegador moderno (Chrome, Firefox, Edge)

## Instalación local con XAMPP

### 1. Clonar el repositorio

```bash
git clone https://github.com/Kelly-Mastrodomenico/HuellasDeAmor.git
```

Mueve la carpeta a `C:\xampp\htdocs\HuellasDeAmor`

### 2. Crear la base de datos

1. Abre phpMyAdmin → `http://localhost/phpmyadmin`
2. Crea una base de datos llamada `huellas_de_amor`
3. Importa el archivo `db/huellas_de_amor.sql`

### 3. Configurar la conexión

Edita `includes/conexion.php`:

```php
$host     = 'localhost';
$dbname   = 'huellas_de_amor';
$usuario  = 'root';
$password = '';
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

Si modificas los archivos `.scss`:

```bash
# Instalar SASS
npm install -g sass

# Compilar
sass scss/main.scss css/style.css --watch
```

## Estructura de carpetas a crear

Si no existen, crea estas carpetas en la raíz:

```
uploads/
uploads/mascotas/
uploads/noticias/
uploads/perfiles/
```

Y dales permisos de escritura.

## Despliegue en AWS

Consulta la sección de despliegue para instrucciones de puesta en producción en AWS EC2 con Apache + PHP + MySQL.

---

[← Arquitectura](arquitectura.md) | [Guía de uso →](uso.md)