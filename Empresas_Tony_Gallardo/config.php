<?php
declare(strict_types=1);

// Credenciales reales: fuera del repositorio (copiar credenciales.ejemplo.php).
require __DIR__ . '/credenciales.php';

const APP_NAME = 'Empresas Tony Gallardo';
const CENTRO   = 'CIFP Tony Gallardo';

date_default_timezone_set('Atlantic/Canary');

function pin_del_dia(): string
{
    return date('dm');
}

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
        session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
        session_start();
    }
}

// La sesión sólo vale para el día cuyo PIN se introdujo.
function sesion_valida(): bool
{
    iniciar_sesion();
    return isset($_SESSION['pin_dia']) && $_SESSION['pin_dia'] === pin_del_dia();
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
