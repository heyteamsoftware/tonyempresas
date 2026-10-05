<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
require __DIR__ . '/datos_familias.php';
require __DIR__ . '/empresa_lib.php';
iniciar_sesion();

$token = (string) ($_GET['t'] ?? $_POST['t'] ?? '');
$esPost = $_SERVER['REQUEST_METHOD'] === 'POST';
$seleccion = familias_desde_request($esPost ? $_POST : $_GET);
$idsSel = $seleccion['ids'];
$esOtras = $seleccion['otras'];
$haySeleccion = $idsSel || $esOtras;
$errores = [];
$em = [];

function pagina_publica(string $titulo, string $acento, callable $contenido): void
{
    ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($titulo) ?> · <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="assets/cuestionario.css?v=<?= @filemtime(__DIR__ . '/assets/cuestionario.css') ?: '1' ?>">
<link rel="icon" type="image/svg+xml" href="assets/icono.svg">
<style>
:root { --accent-color: <?= e($acento) ?>; }
.mensaje { text-align: center; padding: 30px 10px; }
.familias-lista { display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 12px; margin-top: 16px; }
.familias-lista label { position: relative; display: block; padding: 18px 14px; border: 2px solid var(--border-color); border-radius: 10px; text-align: center; font-weight: 600; color: var(--text-color); cursor: pointer; margin: 0; }
.familias-lista label:hover { border-color: var(--accent-color); }
.familias-lista input { position: absolute; opacity: 0; pointer-events: none; }
.familias-lista label:has(input:checked) { border-color: var(--accent-color); background: rgba(20, 57, 94, .08); }
.familias-lista label:has(input:checked)::after { content: "✓"; position: absolute; top: 6px; right: 12px; color: var(--accent-color); font-weight: 700; }
.trampa { position: absolute; left: -9999px; height: 0; overflow: hidden; }
</style>
</head>
<body class="cuestionario">
<div class="container">
  <div class="controls"><span><strong><?= e(APP_NAME) ?></strong> · <?= e(CENTRO) ?></span>
    <div class="botones"><button class="btn-theme" type="button" onclick="toggleDarkMode()">🌓 Modo oscuro</button></div></div>
  <?php $contenido(); ?>
</div>
<script src="assets/cuestionario.js?v=<?= @filemtime(__DIR__ . '/assets/cuestionario.js') ?: '1' ?>"></script>
</body>
</html>
<?php
}

if (!token_registro_valido($token)) {
    http_response_code(403);
    pagina_publica('Enlace no válido', '#475569', function () {
        echo '<div class="mensaje"><h1>Enlace no válido</h1><p>Este enlace ha caducado o no es correcto. Solicita uno nuevo al centro educativo.</p></div>';
    });
    exit;
}

if (isset($_GET['gracias'])) {
    pagina_publica('Registro enviado', '#15803d', function () {
        echo '<div class="mensaje"><h1>¡Gracias!</h1><p>Hemos recibido los datos de su empresa. El centro los revisará y se pondrá en contacto con ustedes.</p></div>';
    });
    exit;
}

// Paso 1: elegir una o varias familias profesionales.
if (!$esPost && (!$haySeleccion || isset($_GET['elegir']))) {
    $todas = familias();
    $sinElegir = isset($_GET['elegido']) && !$haySeleccion;
    pagina_publica('Registro de empresa', '#14395e', function () use ($todas, $token, $idsSel, $esOtras, $sinElegir) {
        ?>
        <header class="header-layout"><h1>Registro de empresa colaboradora</h1>
          <p class="subtitle">Seleccione la o las familias profesionales con las que desea colaborar</p></header>
        <?php if ($sinElegir): ?>
          <div style="background:#fdeceb;color:#8c2118;padding:12px 16px;border-radius:8px;margin-bottom:16px;">Marque al menos una familia profesional para continuar.</div>
        <?php endif; ?>
        <p style="font-size:0.9rem;opacity:.8;">Puede marcar varias si su empresa trabaja con más de una.</p>
        <form method="get" action="registro.php">
          <input type="hidden" name="t" value="<?= e($token) ?>">
          <input type="hidden" name="elegido" value="1">
          <div class="familias-lista">
            <?php foreach ($todas as $f): ?>
              <label><input type="checkbox" name="familias[]" value="<?= (int) $f['id'] ?>"
                <?= in_array((int) $f['id'], $idsSel, true) ? 'checked' : '' ?>> <?= e($f['nombre']) ?></label>
            <?php endforeach; ?>
            <label><input type="checkbox" name="familias[]" value="otras" <?= $esOtras ? 'checked' : '' ?>> Otras familias profesionales</label>
          </div>
          <div class="form-group" style="margin-top:24px;">
            <button class="btn-print" type="submit" style="font-size:1rem;padding:12px 28px;border:none;border-radius:6px;color:#fff;cursor:pointer;font-weight:600;">Continuar</button>
          </div>
        </form>
        <?php
    });
    exit;
}

// Paso 2: formulario de la empresa para las familias elegidas.
$todas = familias();
$filasSel = array_values(array_filter($todas, fn($f) => in_array((int) $f['id'], $idsSel, true)));
$acento = $filasSel ? datos_familia($filasSel[0]['nombre'])['acento'] : '#14395e';
$nombresSel = array_column($filasSel, 'nombre');
if ($esOtras) {
    $nombresSel[] = 'Otras familias profesionales';
}

if ($esPost) {
    comprobar_csrf();

    // Campo trampa para bots: si viene relleno, se finge éxito sin guardar nada.
    if (trim((string) ($_POST['sitio_web_extra'] ?? '')) !== '') {
        header('Location: registro.php?t=' . urlencode($token) . '&gracias=1');
        exit;
    }

    $_POST['ciclos'] = ciclos_permitidos((array) ($_POST['ciclos'] ?? []), $idsSel);
    $datos = datos_empresa_desde_post();
    $errores = errores_empresa($datos);

    if (!$haySeleccion) {
        $errores[] = 'Seleccione al menos una familia profesional.';
    }

    $duplicada = empresa_duplicada_por_cif($datos['cif']);
    if ($duplicada !== null) {
        $errores[] = 'Ya hay una empresa registrada con ese CIF. Si cree que es un error, contacte con el centro.';
    }

    $familiaOtra = trim((string) ($_POST['familia_otra'] ?? ''));
    if ($esOtras) {
        if ($familiaOtra === '') {
            $errores[] = 'Indique a qué otra familia profesional pertenece su actividad.';
        } else {
            $datos['observaciones'] = trim('Familia profesional indicada por la empresa: ' . $familiaOtra
                . ($datos['observaciones'] ? "\n" . $datos['observaciones'] : ''));
        }
    }

    if (!$errores && empty($_POST['acepto_privacidad'])) {
        $errores[] = 'Debe leer y aceptar la información sobre protección de datos para continuar.';
    }

    if (!$errores) {
        $datos['familia_id'] = $idsSel[0] ?? null;
        $datos['estado'] = 'Pendiente';
        $datos['origen'] = 'externo';
        $cols = implode(', ', array_keys($datos));
        $vals = ':' . implode(', :', array_keys($datos));
        db()->prepare("INSERT INTO empresas ($cols) VALUES ($vals)")->execute($datos);
        guardar_familias_empresa((int) db()->lastInsertId(), $idsSel);
        header('Location: registro.php?t=' . urlencode($token) . '&gracias=1');
        exit;
    }
    $em = $datos;
}

$bloquesFamilias = $filasSel;
$enlaceCambiar = 'registro.php?' . http_build_query([
    't' => $token, 'elegir' => 1, 'familias' => array_merge($idsSel, $esOtras ? ['otras'] : []),
]);

pagina_publica('Registro de empresa', $acento, function () use ($token, $errores, $em, $esOtras, $idsSel, $nombresSel, $bloquesFamilias, $enlaceCambiar) {
    ?>
  <header class="header-layout" style="text-align:center;">
    <h1>Registro de empresa colaboradora</h1>
    <p class="subtitle">Formación Profesional &ndash; <?= e(implode(' · ', $nombresSel)) ?></p>
    <p class="req-info">Los campos con (*) son obligatorios. <a href="<?= e($enlaceCambiar) ?>">Cambiar familias</a></p>
  </header>

  <?php if ($errores): ?>
    <div style="background:#fdeceb;color:#8c2118;padding:12px 16px;border-radius:8px;margin-bottom:16px;">
      <?php foreach ($errores as $err): ?><p style="margin:4px 0;"><?= e($err) ?></p><?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="post">
    <input type="hidden" name="csrf" value="<?= e(token_csrf()) ?>">
    <input type="hidden" name="t" value="<?= e($token) ?>">
    <?php foreach ($idsSel as $fid): ?><input type="hidden" name="familias[]" value="<?= (int) $fid ?>"><?php endforeach; ?>
    <?php if ($esOtras): ?><input type="hidden" name="familias[]" value="otras"><?php endif; ?>
    <div class="trampa" aria-hidden="true"><label>No rellenar<input type="text" name="sitio_web_extra" tabindex="-1" autocomplete="off"></label></div>
    <?php if ($esOtras): ?>
      <div class="form-group"><label>¿A qué otra familia profesional pertenece su actividad? <span class="req">(*)</span>:</label>
        <input type="text" name="familia_otra" required maxlength="120" value="<?= e((string) ($_POST['familia_otra'] ?? '')) ?>"
          placeholder="Ej.: Informática y Comunicaciones, Administración y Gestión…"></div>
    <?php endif; ?>
    <?php require __DIR__ . '/_campos_empresa.php'; ?>

    <h2>Protección de datos</h2>
    <div style="font-size:0.85rem; line-height:1.5; background:rgba(0,0,0,0.03); padding:14px 16px; border-radius:8px;">
      <p><strong>Responsable:</strong> <?= e(CENTRO) ?>.</p>
      <p><strong>Finalidad:</strong> gestionar la colaboración de su empresa con el centro para la Formación Profesional (prácticas del alumnado, FCT/Dual) y mantener el directorio de empresas colaboradoras.</p>
      <p><strong>Legitimación:</strong> consentimiento de la persona interesada al enviar este formulario.</p>
      <p><strong>Destinatarios:</strong> no se ceden datos a terceros, salvo obligación legal.</p>
      <p><strong>Derechos:</strong> puede ejercer sus derechos de acceso, rectificación, supresión y demás reconocidos por el RGPD escribiendo a
        <a href="mailto:<?= e(EMAIL_RGPD) ?>"><?= e(EMAIL_RGPD) ?></a>.</p>
    </div>
    <label class="option-item" style="max-width:520px; margin-top:12px;">
      <input type="checkbox" name="acepto_privacidad" value="1" required> He leído y acepto la información sobre protección de datos. <span class="req">(*)</span>
    </label>

    <div class="form-group" style="margin-top:24px;">
      <button class="btn-print" type="submit" style="font-size:1rem;padding:12px 28px;border:none;border-radius:6px;color:#fff;cursor:pointer;font-weight:600;">Enviar datos</button>
    </div>
  </form>
    <?php
});
