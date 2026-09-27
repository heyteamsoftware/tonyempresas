<?php
declare(strict_types=1);

// Credenciales reales: fuera del repositorio (copiar credenciales.ejemplo.php).
require __DIR__ . '/credenciales.php';

const APP_NAME  = 'Empresas Tony Gallardo';
const CENTRO    = 'CIFP Tony Gallardo';
const EMAIL_RGPD = 'secretaria-35015887@gobiernodecanarias.org';

date_default_timezone_set('Atlantic/Canary');

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
    }
    return $pdo;
}

function iniciar_sesion(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        // Carpeta propia (fuera de la web) para las sesiones: la limpieza automática
        // del servidor (compartida con otras apps) borra sesiones inactivas a los
        // 24 minutos, lo que dejaría sin efecto la cookie de 30 días. Con carpeta y
        // caducidad propias, la sesión dura lo que decimos.
        $carpeta = '/var/www/.empresas_sesiones';
        if (is_dir($carpeta) && is_writable($carpeta)) {
            session_save_path($carpeta);
        }
        $treintaDias = 60 * 60 * 24 * 30;
        ini_set('session.gc_maxlifetime', (string) $treintaDias);
        ini_set('session.gc_probability', '1');
        ini_set('session.gc_divisor', '1000');
        session_set_cookie_params([
            'lifetime'  => $treintaDias,
            'path'      => '/',
            'httponly'  => true,
            'secure'    => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'samesite'  => 'Lax',
        ]);
        session_start();
    }
}

function sesion_valida(): bool
{
    iniciar_sesion();
    return !empty($_SESSION['autorizado']);
}

function exigir_sesion(): void
{
    if (!sesion_valida()) {
        header('Location: index.php');
        exit;
    }
}

function token_csrf(): string
{
    iniciar_sesion();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function comprobar_csrf(): void
{
    iniciar_sesion();
    $enviado = $_POST['csrf'] ?? '';
    if (!is_string($enviado) || empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $enviado)) {
        http_response_code(400);
        exit('Token de seguridad no válido. Vuelve atrás y reinténtalo.');
    }
}

function e(?string $texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

const ESTADOS = ['Activa', 'Pendiente', 'Inactiva'];

/** Familias profesionales dadas de alta, ordenadas para los desplegables. */
function familias(): array
{
    static $cache = null;
    if ($cache === null) {
        $cache = db()->query('SELECT id, nombre FROM familias ORDER BY orden, nombre')->fetchAll();
    }
    return $cache;
}
