<?php
declare(strict_types=1);

function lista_desde_post(string $campo, string $campoOtro = ''): string
{
    $items = array_filter(array_map('trim', (array) ($_POST[$campo] ?? [])), fn($v) => $v !== '');
    if ($campoOtro !== '') {
        $otro = trim((string) ($_POST[$campoOtro] ?? ''));
        if ($otro !== '') {
            $items[] = 'Otros: ' . $otro;
        }
    }
    return implode('; ', $items);
}

function marcado(string $opcion, ?string $valorGuardado): bool
{
    if (!$valorGuardado) {
        return false;
    }
    return in_array($opcion, array_map('trim', explode(';', $valorGuardado)), true);
}

/** Texto guardado en el "Otros" de una lista (el elemento que empieza por "Otros: "). */
function texto_otro(?string $valorGuardado): string
{
    if (!$valorGuardado) {
        return '';
    }
    foreach (explode(';', $valorGuardado) as $item) {
        $item = trim($item);
        if (str_starts_with($item, 'Otros: ')) {
            return substr($item, strlen('Otros: '));
        }
    }
    return '';
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
        'ciclos'          => lista_desde_post('ciclos', 'ciclos_otro') ?: null,
        'plazas'          => max(0, min(999, (int) ($_POST['plazas'] ?? 0))),
        'observaciones'   => texto_post('observaciones'),
    ];
}

/** Empresas (no en papelera) según los mismos filtros del listado: texto, familia y estado. */
function buscar_empresas(string $busqueda, int $familiaId, string $estado): array
{
    $sql = 'SELECT e.* FROM empresas e WHERE e.eliminada_en IS NULL';
    $params = [];

    if ($busqueda !== '') {
        // MySQL con sentencias preparadas reales no admite repetir el mismo marcador con
        // nombre varias veces en una consulta: hace falta uno distinto por cada aparición.
        $sql .= ' AND (e.nombre LIKE :b1 OR e.sector LIKE :b2 OR e.localidad LIKE :b3
                       OR e.ciclos LIKE :b4 OR e.contacto_nombre LIKE :b5)';
        $comodin = '%' . $busqueda . '%';
        $params['b1'] = $comodin;
        $params['b2'] = $comodin;
        $params['b3'] = $comodin;
        $params['b4'] = $comodin;
        $params['b5'] = $comodin;
    }
    if ($familiaId > 0) {
        $sql .= ' AND EXISTS (SELECT 1 FROM empresa_familias ef WHERE ef.empresa_id = e.id AND ef.familia_id = :f)';
        $params['f'] = $familiaId;
    }
    if (in_array($estado, ESTADOS, true)) {
        $sql .= ' AND e.estado = :e';
        $params['e'] = $estado;
    }
    $sql .= ' ORDER BY e.nombre';

    $st = db()->prepare($sql);
    $st->execute($params);
    return $st->fetchAll();
}

/** Familias (id, nombre) de una empresa, en el orden del catálogo. */
function familias_de_empresa(int $empresaId): array
{
    $st = db()->prepare(
        'SELECT f.id, f.nombre FROM empresa_familias ef
         JOIN familias f ON f.id = ef.familia_id
         WHERE ef.empresa_id = ? ORDER BY f.orden, f.nombre'
    );
    $st->execute([$empresaId]);
    return $st->fetchAll();
}

/** [empresa_id => [[id, nombre], ...]] para varias empresas de una vez. */
function familias_por_empresa(array $empresaIds): array
{
    $empresaIds = array_values(array_unique(array_map('intval', $empresaIds)));
    if (!$empresaIds) {
        return [];
    }
    $marcas = implode(',', array_fill(0, count($empresaIds), '?'));
    $st = db()->prepare(
        "SELECT ef.empresa_id, f.id, f.nombre FROM empresa_familias ef
         JOIN familias f ON f.id = ef.familia_id
         WHERE ef.empresa_id IN ($marcas) ORDER BY f.orden, f.nombre"
    );
    $st->execute($empresaIds);
    $mapa = [];
    foreach ($st->fetchAll() as $r) {
        $mapa[(int) $r['empresa_id']][] = ['id' => (int) $r['id'], 'nombre' => $r['nombre']];
    }
    return $mapa;
}

/** Sólo los ids que existen en el catálogo, sin repetir y en el orden del catálogo. */
function ids_familias_validos(array $ids): array
{
    $ids = array_map('intval', $ids);
    $validos = [];
    foreach (familias() as $f) {
        if (in_array((int) $f['id'], $ids, true)) {
            $validos[] = (int) $f['id'];
        }
    }
    return $validos;
}

/**
 * Familias elegidas en una petición: familias[]=1&familias[]=2, familias=1,2
 * o la antigua familia=1; el valor "otras" marca "Otras familias profesionales".
 * @return array{ids: list<int>, otras: bool}
 */
function familias_desde_request(array $src): array
{
    $crudo = [];
    foreach (['familias', 'familia'] as $clave) {
        if (!isset($src[$clave])) {
            continue;
        }
        foreach ((array) $src[$clave] as $valor) {
            foreach (explode(',', (string) $valor) as $x) {
                $crudo[] = trim($x);
            }
        }
    }
    return [
        'ids'   => ids_familias_validos(array_filter($crudo, fn($x) => ctype_digit($x))),
        'otras' => in_array('otras', $crudo, true),
    ];
}

/** Sustituye las familias de la empresa; empresas.familia_id queda como la primera (principal). */
function guardar_familias_empresa(int $empresaId, array $ids): void
{
    $ids = ids_familias_validos($ids);
    $pdo = db();
    $pdo->prepare('DELETE FROM empresa_familias WHERE empresa_id = ?')->execute([$empresaId]);
    $ins = $pdo->prepare('INSERT INTO empresa_familias (empresa_id, familia_id) VALUES (?, ?)');
    foreach ($ids as $fid) {
        $ins->execute([$empresaId, $fid]);
    }
    $pdo->prepare('UPDATE empresas SET familia_id = ? WHERE id = ?')->execute([$ids[0] ?? null, $empresaId]);
}

/** De los ciclos marcados, sólo los que pertenecen a alguna de las familias elegidas. Requiere datos_familias.php. */
function ciclos_permitidos(array $marcados, array $idsFamilias): array
{
    $permitidos = [];
    foreach (familias() as $f) {
        if (in_array((int) $f['id'], $idsFamilias, true)) {
            $permitidos = array_merge($permitidos, datos_familia($f['nombre'])['ciclos']);
        }
    }
    return array_values(array_intersect($marcados, $permitidos));
}

/** Nombre de la empresa que ya tiene ese CIF, o null si no hay ninguna (fuera de $idActual). */
function empresa_duplicada_por_cif(?string $cif, int $idActual = 0): ?string
{
    $cif = trim((string) $cif);
    if ($cif === '') {
        return null;
    }
    $sql = 'SELECT nombre FROM empresas WHERE UPPER(cif) = UPPER(?) AND eliminada_en IS NULL';
    $params = [$cif];
    if ($idActual > 0) {
        $sql .= ' AND id != ?';
        $params[] = $idActual;
    }
    $st = db()->prepare($sql);
    $st->execute($params);
    $nombre = $st->fetchColumn();
    return $nombre !== false ? (string) $nombre : null;
}

/** Nº de empresas pendientes de revisar (sin contar la papelera). */
function total_pendientes(): int
{
    return (int) db()->query(
        "SELECT COUNT(*) FROM empresas WHERE estado = 'Pendiente' AND eliminada_en IS NULL"
    )->fetchColumn();
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
