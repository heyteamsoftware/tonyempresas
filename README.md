# Empresas Tony Gallardo

Directorio interno de empresas colaboradoras del **CIFP Tony Gallardo**, organizado por
familias profesionales. Uso exclusivo del profesorado.

## Acceso

No hay cuentas de usuario ni contraseña en la portada. La entrada es **solo por enlace
privado**: quien lo abre queda con sesión iniciada 30 días en ese dispositivo. El enlace
se ve y se regenera desde **Administración** (protegida además con su propia contraseña),
dentro de la app ya iniciada.

## Funcionalidad

- Listado de empresas **agrupado por familia profesional**, con buscador y filtros por
  familia y estado.
- Ficha completa de cada empresa, alta y edición por cualquier profesor con acceso.
- Gestión de familias profesionales: añadir, renombrar y eliminar.
- Cuestionarios imprimibles de perfil de alumnado y de alta de empresa, personalizados
  por familia profesional.
- **Enlace de registro externo**: se puede compartir con una empresa para que rellene
  sus propios datos sin necesidad de PIN ni cuenta. Entra como "Pendiente" hasta que el
  profesorado la revisa. Incluye aviso de protección de datos (RGPD).
- **Papelera**: las empresas eliminadas no se borran al momento, quedan 3 días
  recuperables antes de borrarse en firme automáticamente.
- **Administración**: sección con contraseña propia donde se gestiona el enlace de
  acceso a la aplicación (verlo, copiarlo, regenerarlo).

## Instalación

1. Crear la base de datos ejecutando `Empresas_Tony_Gallardo/sql/esquema.sql`
   (sustituyendo `CONTRASENA_AQUI` por la contraseña real).
2. Copiar `credenciales.ejemplo.php` a `credenciales.php` y poner los datos reales de
   MySQL y la contraseña de la sección Administración.
3. Copiar el contenido de `Empresas_Tony_Gallardo/` a `/var/www/html/Empresas_Tony_Gallardo`.
4. Generar el primer enlace de acceso directamente en la tabla `ajustes` (clave
   `token_acceso`, con `token_acceso_fecha` a la fecha actual), ya que sin él nadie puede
   entrar todavía. Desde ese primer enlace, la propia app permite regenerarlo cuando haga
   falta.
5. Asegurarse de que la carpeta `/var/www/.empresas_sesiones` existe, es propiedad de
   `www-data` y tiene permisos `700` (guarda las sesiones fuera de la carpeta pública).

## Requisitos

PHP 8.0 o superior con la extensión PDO MySQL, y MySQL/MariaDB.
