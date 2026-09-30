<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
require __DIR__ . '/empresa_lib.php';
exigir_sesion();

$busqueda  = trim((string) ($_GET['q'] ?? ''));
$familiaId = isset($_GET['familia']) ? (int) $_GET['familia'] : 0;
$estado    = trim((string) ($_GET['estado'] ?? ''));

$empresas = buscar_empresas($busqueda, $familiaId, $estado);

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="empresas_' . date('Y-m-d') . '.csv"');

$salida = fopen('php://output', 'w');
// BOM para que Excel detecte UTF-8 y no rompa los acentos.
fwrite($salida, "\xEF\xBB\xBF");

fputcsv($salida, [
    'Nombre', 'Familia profesional', 'Estado', 'Sector / actividad', 'CIF',
    'Ciclos', 'Plazas', 'Convenio', 'Dirección', 'Localidad', 'Provincia',
    'Código postal', 'Teléfono', 'Email', 'Web',
    'Persona de contacto', 'Cargo contacto', 'Teléfono contacto', 'Email contacto',
    'Observaciones',
], ';');

foreach ($empresas as $em) {
    fputcsv($salida, [
        $em['nombre'],
        $em['familia_nombre'] ?? 'Sin familia asignada',
        $em['estado'],
        $em['sector'],
        $em['cif'],
        str_replace('; ', ' | ', (string) $em['ciclos']),
        $em['plazas'],
        $em['convenio'] ? 'Sí' : 'No',
        $em['direccion'],
        $em['localidad'],
        $em['provincia'],
        $em['cp'],
        $em['telefono'],
        $em['email'],
        $em['web'],
        $em['contacto_nombre'],
        $em['contacto_cargo'],
        $em['contacto_telefono'],
        $em['contacto_email'],
        $em['observaciones'],
    ], ';');
}

fclose($salida);
