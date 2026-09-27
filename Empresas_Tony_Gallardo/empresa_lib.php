<?php
declare(strict_types=1);

function lista_desde_post(string $campo): string
{
    $items = array_filter(array_map('trim', (array) ($_POST[$campo] ?? [])), fn($v) => $v !== '');
    return implode('; ', $items);
}

function marcado(string $opcion, ?string $valorGuardado): bool
{
    if (!$valorGuardado) {
        return false;
    }
    return in_array($opcion, array_map('trim', explode(';', $valorGuardado)), true);
}

function texto_post(string $campo): ?string
{
    $v = trim((string) ($_POST[$campo] ?? ''));
    return $v !== '' ? $v : null;
}

/** Sólo los campos del formulario simplificado; el resto de columnas no se toca. */
function datos_empresa_desde_post(): array
{
    return [
        'nombre'          => trim((string) ($_POST['nombre'] ?? '')),
        'cif'             => texto_post('cif'),
        'sector'          => texto_post('sector'),
        'telefono'        => texto_post('telefono'),
        'email'           => texto_post('email'),
        'localidad'       => texto_post('localidad'),
        'direccion'       => texto_post('direccion'),
        'contacto_nombre' => texto_post('contacto_nombre'),
        'ciclos'          => lista_desde_post('ciclos') ?: null,
        'plazas'          => max(0, min(999, (int) ($_POST['plazas'] ?? 0))),
        'observaciones'   => texto_post('observaciones'),
    ];
}

function errores_empresa(array $d): array
{
    $err = [];
    if ($d['nombre'] === '') {
        $err[] = 'El nombre de la empresa es obligatorio.';
    }
    if ($d['sector'] === null) {
        $err[] = 'Indica la actividad principal de la empresa.';
    }
    if ($d['telefono'] === null) {
        $err[] = 'El teléfono es obligatorio.';
    }
    if ($d['email'] === null || !filter_var($d['email'], FILTER_VALIDATE_EMAIL)) {
        $err[] = 'El correo electrónico no es válido.';
    }
    if ($d['localidad'] === null) {
        $err[] = 'El municipio / localidad es obligatorio.';
    }
    return $err;
}

function admin_autorizado(): bool
{
    iniciar_sesion();
    return !empty($_SESSION['admin_autorizado']);
}

function exigir_admin(): void
{
    if (!admin_autorizado()) {
        header('Location: admin.php');
        exit;
    }
}

function ajuste(string $clave): ?string
{
    $st = db()->prepare('SELECT valor FROM ajustes WHERE clave = ?');
    $st->execute([$clave]);
    $v = $st->fetchColumn();
    return $v === false ? null : (string) $v;
}

function guardar_ajuste(string $clave, ?string $valor): void
{
    if ($valor === null) {
        db()->prepare('DELETE FROM ajustes WHERE clave = ?')->execute([$clave]);
        return;
    }
    db()->prepare('INSERT INTO ajustes (clave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)')
        ->execute([$clave, $valor]);
}

const DIAS_CADUCIDAD_ENLACE = 30;
const DIAS_CADUCIDAD_ACCESO = 3650; // 10 años (prácticamente sin caducidad)

/** Genera un token, lo guarda con fecha, y lo devuelve. */
function generar_token(string $clave): string
{
    $token = bin2hex(random_bytes(16));
    guardar_ajuste($clave, $token);
    guardar_ajuste($clave . '_fecha', date('Y-m-d H:i:s'));
    return $token;
}

function desactivar_token(string $clave): void
{
    guardar_ajuste($clave, null);
    guardar_ajuste($clave . '_fecha', null);
}

/** Token vigente (existe, coincide y no ha superado los días de caducidad indicados). */
function token_valido(string $clave, string $recibido, int $diasCaducidad = DIAS_CADUCIDAD_ENLACE): bool
{
    $guardado = ajuste($clave);
    if ($guardado === null || $recibido === '' || !hash_equals($guardado, $recibido)) {
        return false;
    }
    $fecha = ajuste($clave . '_fecha');
    if ($fecha !== null) {
        $limite = strtotime($fecha) + $diasCaducidad * 86400;
        if (time() > $limite) {
            return false;
        }
    }
    return true;
}

function token_registro_valido(string $token): bool
{
    return token_valido('token_registro', $token);
}

const DIAS_PAPELERA = 3;

/** Borra en firme lo que lleve más de DIAS_PAPELERA en la papelera. Barata: se llama en cada carga. */
function purgar_papelera(): void
{
    db()->prepare('DELETE FROM empresas WHERE eliminada_en IS NOT NULL AND eliminada_en < (NOW() - INTERVAL ' . DIAS_PAPELERA . ' DAY)')->execute();
}

function url_base_app(): string
{
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    return ($https ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
}
