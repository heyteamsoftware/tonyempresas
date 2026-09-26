<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
exigir_sesion();

$familias = familias();
$titulo   = 'Nueva empresa';
require __DIR__ . '/cabecera.php';
?>

<h1 class="titulo-pagina">¿A qué familia profesional pertenece la nueva empresa?</h1>
<p class="migas">Se abrirá el cuestionario de alta específico de esa familia.</p>

<?php if (!$familias): ?>
  <p class="vacio">No hay familias profesionales dadas de alta. <a href="familias.php">Añade una primero</a>.</p>
<?php else: ?>
<div class="rejilla-familias">
  <?php foreach ($familias as $f): ?>
    <a class="tarjeta-familia" href="cuestionario_empresa.php?familia=<?= (int) $f['id'] ?>">
      <?= e($f['nombre']) ?>
    </a>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require __DIR__ . '/pie.php'; ?>
