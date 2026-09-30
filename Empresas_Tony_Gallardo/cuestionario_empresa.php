<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
require __DIR__ . '/datos_familias.php';
require __DIR__ . '/empresa_lib.php';
exigir_sesion();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$familiaId = isset($_GET['familia']) ? (int) $_GET['familia'] : 0;
$errores = [];
$em = [];

if ($id > 0) {
    $st = db()->prepare('SELECT * FROM empresas WHERE id = ? AND eliminada_en IS NULL');
    $st->execute([$id]);
    $em = $st->fetch();
    if (!$em) {
        http_response_code(404);
        exit('Empresa no encontrada. Si está en la papelera, restáurala primero.');
    }
    $familiaId = (int) $em['familia_id'];
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
    $datos = datos_empresa_desde_post();
    $errores = errores_empresa($datos);

    $duplicada = empresa_duplicada_por_cif($datos['cif'], $id);
    if ($duplicada !== null) {
        $errores[] = 'Ya existe una empresa con ese CIF: "' . $duplicada . '". Si es la misma, edítala en vez de crear otra.';
    }

    if (!$errores) {
        $datos['familia_id'] = (int) $familia['id'];
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
        header('Location: ficha.php?id=' . $id . '&ok=' . rawurlencode($msg));
        exit;
    }
    $em = array_merge($em, $datos);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= $id > 0 ? 'Editar empresa' : 'Alta de empresa' ?> · <?= e($familia['nombre']) ?></title>
<link rel="stylesheet" href="assets/cuestionario.css">
<link rel="icon" type="image/svg+xml" href="assets/icono.svg">
<style>:root { --accent-color: <?= e($d['acento']) ?>; }</style>
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
    <p class="subtitle">Formación Profesional &ndash; <?= e($familia['nombre']) ?></p>
    <p class="req-info">Los campos con (*) son obligatorios.</p>
  </header>

  <?php if ($errores): ?>
    <div style="background:#fdeceb;color:#8c2118;padding:12px 16px;border-radius:8px;margin-bottom:16px;">
      <?php foreach ($errores as $err): ?><p style="margin:4px 0;"><?= e($err) ?></p><?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="post">
    <input type="hidden" name="csrf" value="<?= e(token_csrf()) ?>">
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

<script src="assets/cuestionario.js"></script>
</body>
</html>
