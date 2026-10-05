<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
exigir_sesion();

$familias = familias();
$titulo   = 'Nueva empresa';
require __DIR__ . '/cabecera.php';
?>

<h1 class="titulo-pagina">¿A qué familias profesionales pertenece la nueva empresa?</h1>
<p class="migas">Marca una o varias: se abrirá el cuestionario de alta con los ciclos de cada una.</p>

<?php if (!$familias): ?>
  <p class="vacio">No hay familias profesionales dadas de alta. <a href="familias.php">Añade una primero</a>.</p>
<?php else: ?>
<form method="get" action="cuestionario_empresa.php">
  <div class="rejilla-familias">
    <?php foreach ($familias as $f): ?>
      <label class="tarjeta-familia"><input type="checkbox" name="familias[]" value="<?= (int) $f['id'] ?>"> <?= e($f['nombre']) ?></label>
    <?php endforeach; ?>
  </div>
  <p style="margin-top:20px;"><button class="boton boton--primario" type="submit">Continuar</button></p>
</form>
<?php endif; ?>

<?php require __DIR__ . '/pie.php'; ?>
