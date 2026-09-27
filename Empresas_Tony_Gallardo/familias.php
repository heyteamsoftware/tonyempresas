<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
exigir_sesion();

$errores = [];
$aviso   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    comprobar_csrf();
    $accion = (string) ($_POST['accion'] ?? '');

    if ($accion === 'crear') {
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        if ($nombre === '') {
            $errores[] = 'Escribe el nombre de la familia.';
        } else {
            $st = db()->prepare('SELECT COUNT(*) FROM familias WHERE nombre = ?');
            $st->execute([$nombre]);
            if ($st->fetchColumn() > 0) {
                $errores[] = 'Esa familia ya existe.';
            } else {
                db()->prepare('INSERT INTO familias (nombre, orden) VALUES (?, 999)')->execute([$nombre]);
                $aviso = 'Familia añadida.';
            }
        }
    } elseif ($accion === 'renombrar') {
        $fid    = (int) ($_POST['id'] ?? 0);
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        if ($fid > 0 && $nombre !== '') {
            db()->prepare('UPDATE familias SET nombre = ? WHERE id = ?')->execute([$nombre, $fid]);
            $aviso = 'Familia renombrada.';
        }
    } elseif ($accion === 'eliminar') {
        $fid = (int) ($_POST['id'] ?? 0);
        db()->prepare('DELETE FROM familias WHERE id = ?')->execute([$fid]);
        $aviso = 'Familia eliminada. Sus empresas quedan sin familia asignada.';
    }
}

$lista = db()->query(
    'SELECT f.id, f.nombre, COUNT(e.id) AS total
     FROM familias f LEFT JOIN empresas e ON e.familia_id = f.id AND e.eliminada_en IS NULL
     GROUP BY f.id, f.nombre, f.orden
     ORDER BY f.orden, f.nombre'
)->fetchAll();

$titulo = 'Familias profesionales';
require __DIR__ . '/cabecera.php';
?>

<h1 class="titulo-pagina">Familias profesionales</h1>

<?php if ($aviso): ?><p class="aviso aviso--ok"><?= e($aviso) ?></p><?php endif; ?>
<?php foreach ($errores as $err): ?><p class="aviso aviso--error"><?= e($err) ?></p><?php endforeach; ?>

<form method="post" class="linea-form">
  <input type="hidden" name="csrf" value="<?= e(token_csrf()) ?>">
  <input type="hidden" name="accion" value="crear">
  <input name="nombre" maxlength="120" placeholder="Nueva familia profesional" required>
  <button class="boton boton--primario" type="submit">Añadir</button>
</form>

<table class="tabla">
  <thead><tr><th>Familia</th><th>Empresas</th><th>Cuestionarios</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($lista as $f): ?>
    <tr>
      <td>
        <form method="post" class="linea-form linea-form--compacta">
          <input type="hidden" name="csrf" value="<?= e(token_csrf()) ?>">
          <input type="hidden" name="accion" value="renombrar">
          <input type="hidden" name="id" value="<?= (int) $f['id'] ?>">
          <input name="nombre" maxlength="120" value="<?= e($f['nombre']) ?>">
          <button class="boton boton--plano" type="submit">Guardar</button>
        </form>
      </td>
      <td><a href="empresas.php?familia=<?= (int) $f['id'] ?>"><?= (int) $f['total'] ?></a></td>
      <td class="tabla__cuestionarios">
        <a href="cuestionario_alumnado.php?familia=<?= (int) $f['id'] ?>" target="_blank">📋 Alumnado</a>
        <a href="cuestionario_empresa.php?familia=<?= (int) $f['id'] ?>" target="_blank">🏢 Empresa</a>
      </td>
      <td>
        <form method="post"
              onsubmit="return confirm('¿Eliminar la familia? Las empresas asociadas quedarán sin familia.');">
          <input type="hidden" name="csrf" value="<?= e(token_csrf()) ?>">
          <input type="hidden" name="accion" value="eliminar">
          <input type="hidden" name="id" value="<?= (int) $f['id'] ?>">
          <button class="boton boton--peligro" type="submit">Eliminar</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<?php require __DIR__ . '/pie.php'; ?>
