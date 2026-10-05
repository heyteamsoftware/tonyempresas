<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
require __DIR__ . '/empresa_lib.php';
exigir_sesion();

$aviso = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    comprobar_csrf();
    $accion = (string) ($_POST['accion'] ?? '');
    if ($accion === 'generar') {
        generar_token('token_registro');
        $aviso = 'Enlace generado. Los enlaces anteriores han dejado de funcionar.';
    } elseif ($accion === 'desactivar') {
        desactivar_token('token_registro');
        $aviso = 'Enlace desactivado. Nadie puede registrar empresas desde fuera hasta que generes uno nuevo.';
    }
}

$token = ajuste('token_registro');
$enlace = $token ? url_base_app() . '/registro.php?t=' . $token : null;
$fechaToken = ajuste('token_registro_fecha');

$titulo = 'Compartir formulario';
require __DIR__ . '/cabecera.php';
?>

<h1 class="titulo-pagina">Compartir el formulario con una empresa</h1>
<p class="migas">Cualquier persona con este enlace puede rellenar el formulario sin PIN. Los datos llegan como <strong>Pendiente</strong> para que los revise el profesorado.</p>

<?php if ($aviso): ?><p class="aviso aviso--ok"><?= e($aviso) ?></p><?php endif; ?>

<section class="panel">
  <?php if ($enlace): ?>
    <h2>Enlace para enviar a la empresa</h2>
    <div class="linea-form">
      <input id="enlace" type="text" readonly value="<?= e($enlace) ?>" style="flex:1 1 320px;">
      <button class="boton boton--primario" type="button" id="copiar">Copiar enlace</button>
    </div>
    <p class="meta">La empresa podrá elegir una o varias familias profesionales al abrirlo.
      <?php if ($fechaToken): ?>Caduca automáticamente el
        <?= e(date('d/m/Y', strtotime($fechaToken) + DIAS_CADUCIDAD_ENLACE * 86400)) ?>.<?php endif; ?></p>

    <h2 style="margin-top:20px;">Enlaces directos por familia</h2>
    <ul class="lista-enlaces">
      <?php foreach (familias() as $f): $u = $enlace . '&familia=' . (int) $f['id']; ?>
        <li><span><?= e($f['nombre']) ?></span>
          <button class="boton boton--plano copiar-fam" type="button" data-url="<?= e($u) ?>">Copiar</button></li>
      <?php endforeach; ?>
      <li><span><strong>Otras familias profesionales</strong> <small>(la empresa indica cuál)</small></span>
        <button class="boton boton--plano copiar-fam" type="button" data-url="<?= e($enlace . '&familia=otras') ?>">Copiar</button></li>
    </ul>

    <div class="linea-form" style="margin-top:20px;">
      <form method="post" onsubmit="return confirm('Los enlaces ya enviados dejarán de funcionar. ¿Generar uno nuevo?');">
        <input type="hidden" name="csrf" value="<?= e(token_csrf()) ?>">
        <input type="hidden" name="accion" value="generar">
        <button class="boton boton--plano" type="submit">Generar enlace nuevo</button>
      </form>
      <form method="post" onsubmit="return confirm('¿Desactivar el enlace? Nadie podrá usarlo.');">
        <input type="hidden" name="csrf" value="<?= e(token_csrf()) ?>">
        <input type="hidden" name="accion" value="desactivar">
        <button class="boton boton--peligro" type="submit">Desactivar</button>
      </form>
    </div>
  <?php else: ?>
    <h2>No hay ningún enlace activo</h2>
    <form method="post">
      <input type="hidden" name="csrf" value="<?= e(token_csrf()) ?>">
      <input type="hidden" name="accion" value="generar">
      <button class="boton boton--primario" type="submit">Generar enlace</button>
    </form>
  <?php endif; ?>
</section>

<script>
function copiar(texto, boton) {
  var ok = function () { var t = boton.textContent; boton.textContent = '¡Copiado!'; setTimeout(function () { boton.textContent = t; }, 1500); };
  if (navigator.clipboard) { navigator.clipboard.writeText(texto).then(ok); return; }
  var c = document.getElementById('enlace'); if (c) { c.select(); document.execCommand('copy'); ok(); }
}
var b = document.getElementById('copiar');
if (b) b.addEventListener('click', function () { copiar(document.getElementById('enlace').value, b); });
document.querySelectorAll('.copiar-fam').forEach(function (x) {
  x.addEventListener('click', function () { copiar(x.dataset.url, x); });
});
</script>

<?php require __DIR__ . '/pie.php'; ?>
