# Empresas Tony Gallardo

Directorio interno de empresas colaboradoras del **CIFP Tony Gallardo**, organizado por
familias profesionales. Uso exclusivo del profesorado.

## Acceso

No hay cuentas de usuario. La portada muestra un teclado numérico grande y el PIN de
entrada es **el día y el mes de hoy en formato DDMM** (por ejemplo, el 23 de septiembre
el PIN es `2309`). La sesión caduca automáticamente al cambiar el día.

## Funcionalidad

- Listado de empresas **agrupado por familia profesional**.
- Buscador por nombre, sector, localidad, ciclo o persona de contacto, con filtros por
  familia y estado.
- Ficha completa de cada empresa: datos generales, localización, persona de contacto,
  plazas de prácticas, convenio y observaciones.
- Alta, edición y borrado de empresas por cualquier profesor que haya entrado.
- Gestión de familias profesionales: se pueden añadir, renombrar y eliminar, tanto desde
  la pantalla *Familias* como escribiendo una nueva al dar de alta una empresa.

## Instalación

1. Crear la base de datos ejecutando `Empresas_Tony_Gallardo/sql/esquema.sql`
   (sustituyendo `CONTRASENA_AQUI` por la contraseña real).
2. Copiar `credenciales.ejemplo.php` a `credenciales.php` y poner los datos reales de MySQL.
3. Copiar el contenido de `Empresas_Tony_Gallardo/` a `/var/www/html/Empresas_Tony_Gallardo`.

## Requisitos

PHP 8.0 o superior con la extensión PDO MySQL, y MySQL/MariaDB.
