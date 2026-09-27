<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
require __DIR__ . '/empresa_lib.php';
exigir_sesion();
purgar_papelera();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    comprobar_csrf();
    $id = (int) ($_POST['id'] ?? 0);
    if (($_POST['accion'] ?? '') === 'restaurar') {
        db()->prepare('UPDATE empresas SET eliminada_en = NULL WHERE id = ?')->execute([$id]);
    } elseif (($_POST['accion'] ?? '') === 'eliminar_definitivo') {
        db()->prepare('DELETE FROM empresas WHERE id = ? AND eliminada_en IS NOT NULL')->execute([$id]);
    }
    header('Location: papelera.php');
    exit;
}

$lista = db()->query(
    "SELECT e.id, e.nombre, e.eliminada_en, f.nombre AS familia_nombre
     FROM empresas e LEFT JOIN familias f ON f.id = e.familia_id
     WHERE e.eliminada_en IS NOT NULL
     ORDER BY e.eliminada_en DESC"
)->fetchAll();

$titulo = 'Papelera';
require __DIR__ . '/cabecera.php';
?>

<h1 class="titulo-pagina">Papelera</h1>
<p class="migas">Las empresas eliminadas se borran en firme automáticamente a los <?= DIAS_PAPELERA ?> días.</p>

<?php if (!$lista): ?>
  <p class="vacio">La papelera está vacía.</p>
<?php else: ?>
<table class="tabla">
  <thead><tr><th>Empresa</th><th>Familia</th><th>Eliminada el</th><th>Se borra en firme</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($lista as $em): ?>
    <?php $limite = strtotime($em['eliminada_en']) + DIAS_PAPELERA * 86400; ?>
    <tr>
      <td><?= e($em['nombre']) ?></td>
      <td><?= e($em['familia_nombre'] ?? 'Sin familia') ?></td>
      <td><?= e(date('d/m/Y H:i', strtotime($em['eliminada_en']))) ?></td>
      <td><?= e(date('d/m/Y H:i', $limite)) ?></td>
      <td style="display:flex; gap:8px;">
        <form method="post">
          <input type="hidden" name="csrf" value="<?= e(token_csrf()) ?>">
          <input type="hidden" name="accion" value="restaurar">
          <input type="hidden" name="id" value="<?= (int) $em['id'] ?>">
          <button class="boton boton--primario" type="submit">Restaurar</button>
        </form>
        <form method="post" onsubmit="return confirm('Esto borra la empresa para siempre, sin posibilidad de recuperarla. ¿Continuar?');">
          <input type="hidden" name="csrf" value="<?= e(token_csrf()) ?>">
          <input type="hidden" name="accion" value="eliminar_definitivo">
          <input type="hidden" name="id" value="<?= (int) $em['id'] ?>">
          <button class="boton boton--peligro" type="submit">Eliminar ya</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>

<?php require __DIR__ . '/pie.php'; ?>
