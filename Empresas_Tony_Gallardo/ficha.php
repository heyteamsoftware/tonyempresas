<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
exigir_sesion();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$st = db()->prepare('SELECT e.*, f.nombre AS familia_nombre
                     FROM empresas e LEFT JOIN familias f ON f.id = e.familia_id
                     WHERE e.id = ?');
$st->execute([$id]);
$em = $st->fetch();

if (!$em) {
    http_response_code(404);
    exit('Empresa no encontrada.');
}

$titulo = $em['nombre'];
require __DIR__ . '/cabecera.php';

$fila = function (string $etiqueta, ?string $valor, ?string $enlace = null): void {
    if ($valor === null || $valor === '') {
        return;
    }
    echo '<div class="dato"><dt>' . e($etiqueta) . '</dt><dd>';
    echo $enlace ? '<a href="' . e($enlace) . '">' . e($valor) . '</a>' : e($valor);
    echo '</dd></div>';
};
?>

<?php if (!empty($_GET['ok'])): ?>
  <p class="aviso aviso--ok"><?= e((string) $_GET['ok']) ?></p>
<?php endif; ?>

<div class="ficha__cabecera">
  <div>
    <p class="migas"><a href="empresas.php">Empresas</a> ›
      <?= e($em['familia_nombre'] ?? 'Sin familia asignada') ?></p>
    <h1 class="titulo-pagina"><?= e($em['nombre']) ?></h1>
    <p>
      <span class="etiqueta etiqueta--<?= e(strtolower($em['estado'])) ?>"><?= e($em['estado']) ?></span>
      <?php if ($em['convenio']): ?><span class="etiqueta etiqueta--convenio">Convenio firmado</span><?php endif; ?>
    </p>
  </div>
  <div class="ficha__acciones">
    <a class="boton boton--primario" href="formulario.php?id=<?= (int) $em['id'] ?>">Editar</a>
    <form method="post" action="eliminar.php"
          onsubmit="return confirm('¿Seguro que quieres eliminar esta empresa? No se puede deshacer.');">
      <input type="hidden" name="csrf" value="<?= e(token_csrf()) ?>">
      <input type="hidden" name="id" value="<?= (int) $em['id'] ?>">
      <button class="boton boton--peligro" type="submit">Eliminar</button>
    </form>
  </div>
</div>

<div class="paneles">
  <section class="panel">
    <h2>Datos generales</h2>
    <dl>
      <?php
      $fila('Familia profesional', $em['familia_nombre']);
      $fila('Sector / actividad', $em['sector']);
      $fila('CIF', $em['cif']);
      $fila('Ciclos relacionados', $em['ciclos']);
      $fila('Plazas ofertadas', (string) $em['plazas']);
      ?>
    </dl>
  </section>

  <section class="panel">
    <h2>Localización y contacto</h2>
    <dl>
      <?php
      $dir = trim(implode(', ', array_filter([$em['direccion'], $em['cp'], $em['localidad'], $em['provincia']])));
      $fila('Dirección', $dir !== '' ? $dir : null);
      $fila('Teléfono', $em['telefono'], $em['telefono'] ? 'tel:' . $em['telefono'] : null);
      $fila('Correo', $em['email'], $em['email'] ? 'mailto:' . $em['email'] : null);
      $web = $em['web'] ? (preg_match('~^https?://~i', $em['web']) ? $em['web'] : 'https://' . $em['web']) : null;
      $fila('Web', $em['web'], $web);
      ?>
    </dl>
  </section>

  <section class="panel">
    <h2>Persona de contacto</h2>
    <dl>
      <?php
      $fila('Nombre', $em['contacto_nombre']);
      $fila('Cargo', $em['contacto_cargo']);
      $fila('Teléfono', $em['contacto_telefono'], $em['contacto_telefono'] ? 'tel:' . $em['contacto_telefono'] : null);
      $fila('Correo', $em['contacto_email'], $em['contacto_email'] ? 'mailto:' . $em['contacto_email'] : null);
      ?>
    </dl>
  </section>

  <?php if ($em['observaciones']): ?>
    <section class="panel panel--ancho">
      <h2>Observaciones</h2>
      <p class="texto-largo"><?= nl2br(e($em['observaciones'])) ?></p>
    </section>
  <?php endif; ?>
</div>

<p class="meta">Añadida el <?= e(date('d/m/Y', strtotime($em['creado_en']))) ?> ·
   última modificación el <?= e(date('d/m/Y H:i', strtotime($em['actualizado_en']))) ?></p>

<?php require __DIR__ . '/pie.php'; ?>
