<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
require __DIR__ . '/empresa_lib.php';
exigir_sesion();
exigir_admin();

$aviso = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    comprobar_csrf();
    if (($_POST['accion'] ?? '') === 'generar') {
        generar_token('token_acceso');
        $aviso = 'Enlace de acceso regenerado. El enlace anterior ha dejado de funcionar: quien lo tuviera guardado deberá usar el nuevo.';
    }
}

$token = ajuste('token_acceso');
$enlace = $token ? url_base_app() . '/index.php?clave=' . $token : null;
$fecha  = ajuste('token_acceso_fecha');

$titulo = 'Enlace de acceso';
require __DIR__ . '/cabecera.php';
?>

<h1 class="titulo-pagina">Enlace de acceso a la aplicación</h1>
<p class="migas">Compártelo solo con el profesorado que deba entrar. Quien lo tenga, tiene acceso completo.</p>

<?php if ($aviso): ?><p class="aviso aviso--ok"><?= e($aviso) ?></p><?php endif; ?>

<section class="panel">
  <?php if ($enlace): ?>
    <div class="linea-form">
      <input id="enlace" type="text" readonly value="<?= e($enlace) ?>" style="flex:1 1 320px;">
      <button class="boton boton--primario" type="button" id="copiar">Copiar enlace</button>
    </div>
    <p class="meta">
      <?php if ($fecha): ?>Caduca automáticamente el
        <?= e(date('d/m/Y', strtotime($fecha) + DIAS_CADUCIDAD_ACCESO * 86400)) ?>
        (a los <?= DIAS_CADUCIDAD_ACCESO ?> días de generarse);<?php endif; ?>
      si eso ocurre, vuelve a esta pantalla desde una sesión ya iniciada para generar uno nuevo.
      Quien ya haya entrado no se ve afectado: la sesión dura 30 días aparte.
    </p>
  <?php else: ?>
    <p>No hay enlace de acceso guardado (raro, ya que has entrado con uno). Genera uno nuevo.</p>
  <?php endif; ?>

  <form method="post" style="margin-top:16px;" onsubmit="return confirm('El enlace actual dejará de funcionar en cuanto generes uno nuevo. ¿Continuar?');">
    <input type="hidden" name="csrf" value="<?= e(token_csrf()) ?>">
    <input type="hidden" name="accion" value="generar">
    <button class="boton boton--plano" type="submit">Generar enlace nuevo</button>
  </form>
</section>

<script>
var b = document.getElementById('copiar');
if (b) b.addEventListener('click', function () {
  var c = document.getElementById('enlace');
  var listo = function () { var t = b.textContent; b.textContent = '¡Copiado!'; setTimeout(function () { b.textContent = t; }, 1500); };
  if (navigator.clipboard) { navigator.clipboard.writeText(c.value).then(listo); }
  else { c.select(); document.execCommand('copy'); listo(); }
});
</script>

<?php require __DIR__ . '/pie.php'; ?>
