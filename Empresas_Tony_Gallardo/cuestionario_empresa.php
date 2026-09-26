<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
require __DIR__ . '/datos_familias.php';
exigir_sesion();

/** Convierte la lista de checkboxes marcados (+ "Otros") en un texto guardable. */
function lista_desde_post(string $campo, string $otroCampo = ''): string
{
    $items = array_map('trim', (array) ($_POST[$campo] ?? []));
    $items = array_filter($items, fn($v) => $v !== '');
    if ($otroCampo !== '') {
        $otro = trim((string) ($_POST[$otroCampo] ?? ''));
        if ($otro !== '') {
            $items[] = 'Otros: ' . $otro;
        }
    }
    return implode('; ', $items);
}

/** ¿Está $opcion entre los elementos previamente guardados en $valorGuardado? */
function marcado(string $opcion, ?string $valorGuardado): bool
{
    if (!$valorGuardado) {
        return false;
    }
    return in_array($opcion, array_map('trim', explode(';', $valorGuardado)), true);
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$familiaId = isset($_GET['familia']) ? (int) $_GET['familia'] : 0;
$errores = [];

if ($id > 0) {
    $st = db()->prepare('SELECT * FROM empresas WHERE id = ?');
    $st->execute([$id]);
    $em = $st->fetch();
    if (!$em) {
        http_response_code(404);
        exit('Empresa no encontrada.');
    }
    $familiaId = (int) $em['familia_id'];
} else {
    $em = [
        'nombre' => '', 'nombre_comercial' => '', 'cif' => '', 'telefono' => '', 'email' => '',
        'web' => '', 'provincia' => '', 'localidad' => '', 'direccion' => '', 'cp' => '',
        'sector' => '', 'tamanio' => '', 'tipo_empresa' => '', 'instalaciones' => '',
        'colaborado_antes' => 0, 'tipo_colaboracion' => '', 'ciclos' => '', 'plazas' => 0,
        'actividades_formativas' => '', 'prl_evaluacion' => 0, 'prl_formacion' => 0, 'prl_epis' => 0,
        'contacto_nombre' => '', 'contacto_cargo' => '', 'contacto_telefono' => '',
        'horario' => '', 'jornada' => '', 'observaciones' => '',
    ];
}

$st = db()->prepare('SELECT id, nombre FROM familias WHERE id = ?');
$st->execute([$familiaId]);
$familia = $st->fetch();

if (!$familia) {
    http_response_code(404);
    exit('Selecciona una familia profesional válida desde "Nueva empresa".');
}

$d = datos_familia($familia['nombre']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    comprobar_csrf();

    $datos = [
        'nombre'            => trim((string) ($_POST['nombre'] ?? '')),
        'nombre_comercial'  => trim((string) ($_POST['nombre_comercial'] ?? '')) ?: null,
        'cif'               => trim((string) ($_POST['cif'] ?? '')) ?: null,
        'telefono'          => trim((string) ($_POST['telefono'] ?? '')) ?: null,
        'email'             => trim((string) ($_POST['email'] ?? '')) ?: null,
        'web'               => trim((string) ($_POST['web'] ?? '')) ?: null,
        'provincia'         => trim((string) ($_POST['provincia'] ?? '')) ?: null,
        'localidad'         => trim((string) ($_POST['localidad'] ?? '')) ?: null,
        'direccion'         => trim((string) ($_POST['direccion'] ?? '')) ?: null,
        'cp'                => trim((string) ($_POST['cp'] ?? '')) ?: null,
        'sector'            => trim((string) ($_POST['sector'] ?? '')) ?: null,
        'familia_id'        => (int) $familia['id'],
        'tamanio'           => trim((string) ($_POST['tamanio'] ?? '')) ?: null,
        'tipo_empresa'      => lista_desde_post('tipo_empresa', 'tipo_empresa_otro') ?: null,
        'instalaciones'     => lista_desde_post('instalaciones') ?: null,
        'colaborado_antes'  => isset($_POST['colaborado_antes']) ? 1 : 0,
        'tipo_colaboracion' => trim((string) ($_POST['tipo_colaboracion'] ?? '')) ?: null,
        'ciclos'            => lista_desde_post('ciclos') ?: null,
        'plazas'            => max(0, (int) ($_POST['plazas'] ?? 0)),
        'actividades_formativas' => lista_desde_post('actividades_formativas') ?: null,
        'prl_evaluacion'    => isset($_POST['prl_evaluacion']) ? 1 : 0,
        'prl_formacion'     => isset($_POST['prl_formacion']) ? 1 : 0,
        'prl_epis'          => isset($_POST['prl_epis']) ? 1 : 0,
        'contacto_nombre'   => trim((string) ($_POST['contacto_nombre'] ?? '')) ?: null,
        'contacto_cargo'    => trim((string) ($_POST['contacto_cargo'] ?? '')) ?: null,
        'contacto_telefono' => trim((string) ($_POST['contacto_telefono'] ?? '')) ?: null,
        'horario'           => trim((string) ($_POST['horario'] ?? '')) ?: null,
        'jornada'           => trim((string) ($_POST['jornada'] ?? '')) ?: null,
        'observaciones'     => trim((string) ($_POST['observaciones'] ?? '')) ?: null,
    ];

    if ($datos['nombre'] === '') {
        $errores[] = 'El nombre de la empresa / razón social es obligatorio.';
    }
    if ($datos['telefono'] === null) {
        $errores[] = 'El teléfono es obligatorio.';
    }
    if ($datos['email'] === null || !filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El correo electrónico no es válido.';
    }
    if ($datos['localidad'] === null) {
        $errores[] = 'El municipio/localidad es obligatorio.';
    }

    if (!$errores) {
        if ($id > 0) {
            $sets = implode(', ', array_map(fn($c) => "$c = :$c", array_keys($datos)));
            $datos['id'] = $id;
            db()->prepare("UPDATE empresas SET $sets WHERE id = :id")->execute($datos);
            $msg = 'Empresa actualizada correctamente.';
        } else {
            $datos['estado'] = 'Pendiente';
            $cols = implode(', ', array_keys($datos));
            $vals = ':' . implode(', :', array_keys($datos));
            db()->prepare("INSERT INTO empresas ($cols) VALUES ($vals)")->execute($datos);
            $id  = (int) db()->lastInsertId();
            $msg = 'Empresa registrada correctamente. Queda como "Pendiente" hasta que se revise.';
        }
        header('Location: ficha.php?id=' . $id . '&ok=' . rawurlencode($msg));
        exit;
    }
    $em = array_merge($em, $_POST);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= $id > 0 ? 'Editar empresa' : 'Alta de empresa colaboradora' ?> · <?= e($familia['nombre']) ?></title>
<link rel="stylesheet" href="assets/cuestionario.css">
<link rel="icon" type="image/svg+xml" href="assets/icono.svg">
<style>:root { --accent-color: <?= e($d['acento']) ?>; }</style>
</head>
<body class="cuestionario">

<div class="container">
  <div class="controls">
    <a class="volver" href="empresas.php?familia=<?= (int) $familia['id'] ?>">&larr; Volver</a>
    <div class="botones">
      <button class="btn-theme" type="button" onclick="toggleDarkMode()">🌓 Modo oscuro</button>
      <button class="btn-print" type="button" onclick="prepararEImprimir()">🖨️ Vista de impresión</button>
    </div>
  </div>

  <header class="header-layout" style="text-align:center;">
    <h1><?= $id > 0 ? 'Editar empresa colaboradora' : 'Cuestionario de Alta de Empresa Colaboradora' ?></h1>
    <p class="subtitle">Formación Profesional &ndash; <?= e($familia['nombre']) ?></p>
    <p class="req-info">Los campos con (*) son obligatorios.</p>
  </header>

  <?php if ($errores): ?>
    <div class="aviso" style="background:#fdeceb;color:#8c2118;padding:12px 16px;border-radius:8px;margin-bottom:16px;">
      <?php foreach ($errores as $err): ?><p style="margin:4px 0;"><?= e($err) ?></p><?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="post">
    <input type="hidden" name="csrf" value="<?= e(token_csrf()) ?>">

    <h2>1. Datos de identificación</h2>
    <div class="grid">
      <div class="form-group full-width"><label>Nombre de la empresa / Razón social <span class="req">(*)</span>:</label>
        <input type="text" name="nombre" class="fill-text" required value="<?= e($em['nombre']) ?>"></div>
      <div class="form-group"><label>Nombre comercial:</label>
        <input type="text" name="nombre_comercial" class="fill-text" value="<?= e($em['nombre_comercial'] ?? '') ?>"></div>
      <div class="form-group"><label>CIF/NIF/NIE:</label>
        <input type="text" name="cif" class="fill-text" value="<?= e($em['cif'] ?? '') ?>"></div>
    </div>

    <h2>2. Datos de contacto</h2>
    <div class="grid">
      <div class="form-group"><label>Teléfono <span class="req">(*)</span>:</label>
        <input type="tel" name="telefono" class="fill-text" required value="<?= e($em['telefono'] ?? '') ?>"></div>
      <div class="form-group"><label>Email <span class="req">(*)</span>:</label>
        <input type="email" name="email" class="fill-text" required value="<?= e($em['email'] ?? '') ?>"></div>
      <div class="form-group full-width"><label>Página web:</label>
        <input type="text" name="web" class="fill-text" value="<?= e($em['web'] ?? '') ?>"></div>
    </div>

    <h2>3. Ubicación y dirección</h2>
    <div class="grid">
      <div class="form-group"><label>Provincia / isla:</label>
        <input type="text" name="provincia" class="fill-text" value="<?= e($em['provincia'] ?? '') ?>"></div>
      <div class="form-group"><label>Municipio / localidad <span class="req">(*)</span>:</label>
        <input type="text" name="localidad" class="fill-text" required value="<?= e($em['localidad'] ?? '') ?>"></div>
      <div class="form-group"><label>Domicilio:</label>
        <input type="text" name="direccion" class="fill-text" value="<?= e($em['direccion'] ?? '') ?>"></div>
      <div class="form-group"><label>Código postal:</label>
        <input type="text" name="cp" class="fill-text" value="<?= e($em['cp'] ?? '') ?>"></div>
    </div>

    <h2>4. Información general de la empresa</h2>
    <div class="form-group"><label>Actividad principal de la empresa <span class="req">(*)</span>:</label>
      <input type="text" name="sector" class="fill-text" required value="<?= e($em['sector'] ?? '') ?>"></div>

    <?php if ($d['tipos_empresa']): ?>
    <label>Tipo de empresa:</label>
    <div class="option-group">
      <?php foreach ($d['tipos_empresa'] as $t): ?>
        <label class="option-item"><input type="checkbox" name="tipo_empresa[]" value="<?= e($t) ?>"
          <?= marcado($t, $em['tipo_empresa'] ?? null) ? 'checked' : '' ?>> <?= e($t) ?></label>
      <?php endforeach; ?>
    </div>
    <input type="text" name="tipo_empresa_otro" class="fill-text" placeholder="Otros (especificar)" style="margin-top:8px;">
    <?php endif; ?>

    <label style="margin-top:10px; display:block;">Tamaño de la empresa:</label>
    <div class="option-group">
      <?php foreach (['Micro (1-9)', 'Pequeña (10-49)', 'Mediana (50-249)', 'Gran empresa (250+)'] as $t): ?>
        <label class="option-item"><input type="radio" name="tamanio" value="<?= e($t) ?>"
          <?= ($em['tamanio'] ?? '') === $t ? 'checked' : '' ?>> <?= e($t) ?></label>
      <?php endforeach; ?>
    </div>

    <?php if ($d['instalaciones']): ?>
    <h2>5. Recursos e instalaciones</h2>
    <div class="option-group">
      <?php foreach ($d['instalaciones'] as $i): ?>
        <label class="option-item"><input type="checkbox" name="instalaciones[]" value="<?= e($i) ?>"
          <?= marcado($i, $em['instalaciones'] ?? null) ? 'checked' : '' ?>> <?= e($i) ?></label>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <h2>6. Experiencia en formación</h2>
    <div class="form-group">
      <label>¿Ha colaborado anteriormente con centros educativos?</label>
      <label class="option-item" style="max-width:220px;">
        <input type="checkbox" name="colaborado_antes" value="1" <?= !empty($em['colaborado_antes']) ? 'checked' : '' ?>> Sí
      </label>
      <input type="text" name="tipo_colaboracion" class="fill-text" style="margin-top:8px;"
        placeholder="En caso afirmativo, indique tipo (FCT, Dual…)" value="<?= e($em['tipo_colaboracion'] ?? '') ?>">
    </div>

    <?php if ($d['ciclos']): ?>
    <h2>7. Perfil de alumnado que puede acoger</h2>
    <div class="option-group">
      <?php foreach ($d['ciclos'] as $c): ?>
        <label class="option-item"><input type="checkbox" name="ciclos[]" value="<?= e($c) ?>"
          <?= marcado($c, $em['ciclos'] ?? null) ? 'checked' : '' ?>> <?= e($c) ?></label>
      <?php endforeach; ?>
    </div>
    <div class="form-group" style="max-width: 300px; margin-top:10px;">
      <label>Nº alumnos que puede acoger:</label>
      <input type="number" name="plazas" min="0" max="999" class="fill-text" value="<?= (int) ($em['plazas'] ?? 0) ?>">
    </div>
    <?php endif; ?>

    <?php if ($d['actividades']): ?>
    <h2>8. Actividades formativas que puede ofrecer</h2>
    <div class="option-group">
      <?php foreach ($d['actividades'] as $a): ?>
        <label class="option-item"><input type="checkbox" name="actividades_formativas[]" value="<?= e($a) ?>"
          <?= marcado($a, $em['actividades_formativas'] ?? null) ? 'checked' : '' ?>> <?= e($a) ?></label>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <h2>9. Prevención de riesgos laborales (PRL)</h2>
    <div class="option-group" style="grid-template-columns: 1fr;">
      <label class="option-item"><input type="checkbox" name="prl_evaluacion" value="1" <?= !empty($em['prl_evaluacion']) ? 'checked' : '' ?>> ¿Dispone de evaluación de riesgos actualizada?</label>
      <label class="option-item"><input type="checkbox" name="prl_formacion" value="1" <?= !empty($em['prl_formacion']) ? 'checked' : '' ?>> ¿Se facilitará formación en PRL al alumnado?</label>
      <label class="option-item"><input type="checkbox" name="prl_epis" value="1" <?= !empty($em['prl_epis']) ? 'checked' : '' ?>> ¿Se proporcionarán los EPI necesarios?</label>
    </div>

    <h2>10. Tutor de empresa</h2>
    <div class="grid">
      <div class="form-group"><label>Nombre y apellidos del tutor:</label>
        <input type="text" name="contacto_nombre" class="fill-text" value="<?= e($em['contacto_nombre'] ?? '') ?>"></div>
      <div class="form-group"><label>Cargo:</label>
        <input type="text" name="contacto_cargo" class="fill-text" value="<?= e($em['contacto_cargo'] ?? '') ?>"></div>
      <div class="form-group"><label>Teléfono del tutor:</label>
        <input type="tel" name="contacto_telefono" class="fill-text" value="<?= e($em['contacto_telefono'] ?? '') ?>"></div>
    </div>

    <h2>11. Disponibilidad y organización</h2>
    <div class="grid">
      <div class="form-group"><label>Horario de la empresa / periodo:</label>
        <input type="text" name="horario" class="fill-text" value="<?= e($em['horario'] ?? '') ?>"></div>
      <div class="form-group"><label>Jornada (mañana / tarde / partida):</label>
        <input type="text" name="jornada" class="fill-text" value="<?= e($em['jornada'] ?? '') ?>"></div>
    </div>

    <h2>12. Observaciones</h2>
    <div class="form-group">
      <label>Requisitos específicos o perfil deseado:</label>
      <textarea name="observaciones" class="fill-text" oninput="autoGrow(this)"><?= e($em['observaciones'] ?? '') ?></textarea>
    </div>

    <div style="page-break-inside: avoid; margin-top: 30px;">
      <h2>13. Declaración y compromiso</h2>
      <p style="font-size: 0.85rem;">La empresa declara su interés en colaborar en la formación del alumnado.</p>
    </div>

    <div class="form-group" style="margin-top:20px; display:flex; gap:12px;">
      <button class="btn-print" type="submit" style="font-size:1rem; padding:12px 28px;">
        <?= $id > 0 ? 'Guardar cambios' : 'Registrar empresa' ?>
      </button>
    </div>
  </form>
</div>

<script src="assets/cuestionario.js"></script>
</body>
</html>
