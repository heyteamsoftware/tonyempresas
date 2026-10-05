<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
require __DIR__ . '/datos_familias.php';
require __DIR__ . '/empresa_lib.php';
exigir_sesion();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$errores = [];
$em = [];
$todas = familias();

if ($id > 0) {
    $st = db()->prepare('SELECT * FROM empresas WHERE id = ? AND eliminada_en IS NULL');
    $st->execute([$id]);
    $em = $st->fetch();
    if (!$em) {
        http_response_code(404);
        exit('Empresa no encontrada. Si está en la papelera, restáurala primero.');
    }
    $idsSel = array_map(fn($f) => (int) $f['id'], familias_de_empresa($id));
} else {
    $idsSel = familias_desde_request($_GET)['ids'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    comprobar_csrf();
    $idsSel = ids_familias_validos((array) ($_POST['familias'] ?? []));
    $_POST['ciclos'] = ciclos_permitidos((array) ($_POST['ciclos'] ?? []), $idsSel);

    $datos = datos_empresa_desde_post();
    $errores = errores_empresa($datos);
    if (!$idsSel) {
        $errores[] = 'Selecciona al menos una familia profesional.';
    }

    $duplicada = empresa_duplicada_por_cif($datos['cif'], $id);
    if ($duplicada !== null) {
        $errores[] = 'Ya existe una empresa con ese CIF: "' . $duplicada . '". Si es la misma, edítala en vez de crear otra.';
    }

    if (!$errores) {
        $datos['familia_id'] = $idsSel[0];
        if ($id > 0) {
            // El estado sólo se puede cambiar editando una empresa ya existente
            // (al darla de alta siempre empieza "Pendiente", se revisa después).
            $estadoPost = (string) ($_POST['estado'] ?? '');
            $datos['estado'] = in_array($estadoPost, ESTADOS, true) ? $estadoPost : $em['estado'];
            $sets = implode(', ', array_map(fn($c) => "$c = :$c", array_keys($datos)));
            $datos['id'] = $id;
            db()->prepare("UPDATE empresas SET $sets WHERE id = :id AND eliminada_en IS NULL")->execute($datos);
            $msg = 'Empresa actualizada correctamente.';
        } else {
            $datos['estado'] = 'Pendiente';
            $datos['origen'] = 'interno';
            $cols = implode(', ', array_keys($datos));
            $vals = ':' . implode(', :', array_keys($datos));
            db()->prepare("INSERT INTO empresas ($cols) VALUES ($vals)")->execute($datos);
            $id  = (int) db()->lastInsertId();
            $msg = 'Empresa registrada correctamente. Queda como "Pendiente" hasta que se revise.';
        }
        guardar_familias_empresa($id, $idsSel);
        header('Location: ficha.php?id=' . $id . '&ok=' . rawurlencode($msg));
        exit;
    }
    $em = array_merge($em, $datos);
}

$filasSel = array_values(array_filter($todas, fn($f) => in_array((int) $f['id'], $idsSel, true)));
$acento = $filasSel ? datos_familia($filasSel[0]['nombre'])['acento'] : '#14395e';
$nombresSel = implode(' · ', array_column($filasSel, 'nombre'));
$bloquesFamilias = $todas;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= $id > 0 ? 'Editar empresa' : 'Alta de empresa' ?> · <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="assets/cuestionario.css?v=<?= @filemtime(__DIR__ . '/assets/cuestionario.css') ?: '1' ?>">
<link rel="icon" type="image/svg+xml" href="assets/icono.svg">
<style>:root { --accent-color: <?= e($acento) ?>; }</style>
</head>
<body class="cuestionario">

<div class="container">
  <div class="controls">
    <a class="volver" href="<?= $id > 0 ? 'ficha.php?id=' . $id : 'nueva_empresa.php' ?>">&larr; Volver</a>
    <div class="botones">
      <button class="btn-theme" type="button" onclick="toggleDarkMode()">🌓 Modo oscuro</button>
    </div>
  </div>

  <header class="header-layout" style="text-align:center;">
    <h1><?= $id > 0 ? 'Editar empresa' : 'Alta de empresa colaboradora' ?></h1>
    <p class="subtitle" id="subtitulo-familias">Formación Profesional<?= $nombresSel !== '' ? ' &ndash; ' . e($nombresSel) : '' ?></p>
    <p class="req-info">Los campos con (*) son obligatorios.</p>
  </header>

  <?php if ($errores): ?>
    <div style="background:#fdeceb;color:#8c2118;padding:12px 16px;border-radius:8px;margin-bottom:16px;">
      <?php foreach ($errores as $err): ?><p style="margin:4px 0;"><?= e($err) ?></p><?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="post">
    <input type="hidden" name="csrf" value="<?= e(token_csrf()) ?>">

    <h2>Familias profesionales <span class="req">(*)</span></h2>
    <p style="font-size:0.85rem;opacity:.8;margin:0 0 6px;">Puede pertenecer a varias: marca todas las que correspondan.</p>
    <div class="option-group">
      <?php foreach ($todas as $f): ?>
        <label class="option-item"><input type="checkbox" name="familias[]" value="<?= (int) $f['id'] ?>"
          <?= in_array((int) $f['id'], $idsSel, true) ? 'checked' : '' ?>> <?= e($f['nombre']) ?></label>
      <?php endforeach; ?>
    </div>

    <?php require __DIR__ . '/_campos_empresa.php'; ?>

    <?php if ($id > 0): ?>
    <h2>Estado</h2>
    <div class="form-group" style="max-width:260px;">
      <label>Estado de la empresa
        <select name="estado">
          <?php foreach (ESTADOS as $es): ?>
            <option value="<?= e($es) ?>" <?= ($em['estado'] ?? '') === $es ? 'selected' : '' ?>><?= e($es) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
    </div>
    <?php endif; ?>

    <div class="form-group" style="margin-top:24px;">
      <button class="btn-print" type="submit" style="font-size:1rem;padding:12px 28px;border:none;border-radius:6px;color:#fff;cursor:pointer;font-weight:600;">
        <?= $id > 0 ? 'Guardar cambios' : 'Registrar empresa' ?>
      </button>
    </div>
  </form>
</div>

<script src="assets/cuestionario.js?v=<?= @filemtime(__DIR__ . '/assets/cuestionario.js') ?: '1' ?>"></script>
</body>
</html>
