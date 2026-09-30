<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
exigir_sesion();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$st = db()->prepare('SELECT e.*, f.nombre AS familia_nombre
                     FROM empresas e LEFT JOIN familias f ON f.id = e.familia_id
                     WHERE e.id = ? AND e.eliminada_en IS NULL');
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

// Para campos con varios valores separados por ";" (ciclos, tipo de empresa...):
// una lista, uno por línea, en vez de embutirlo todo en una sola línea de texto.
$filaLista = function (string $etiqueta, ?string $valor): void {
    if ($valor === null || trim($valor) === '') {
        return;
    }
    $items = array_filter(array_map('trim', explode(';', $valor)), fn($v) => $v !== '');
    if (!$items) {
        return;
    }
    echo '<div class="dato dato--lista"><dt>' . e($etiqueta) . '</dt><dd><ul class="lista-valores">';
    foreach ($items as $item) {
        echo '<li>' . e($item) . '</li>';
    }
    echo '</ul></dd></div>';
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
      <?php if (($em['origen'] ?? '') === 'externo'): ?><span class="etiqueta etiqueta--convenio">Recibida por enlace externo</span><?php endif; ?>
    </p>
  </div>
  <div class="ficha__acciones">
    <?php if ($em['familia_id']): ?>
      <a class="boton boton--primario" href="cuestionario_empresa.php?id=<?= (int) $em['id'] ?>">Editar</a>
    <?php else: ?>
      <a class="boton boton--primario" href="formulario.php?id=<?= (int) $em['id'] ?>">Editar</a>
    <?php endif; ?>
    <?php
    // Activa/Inactiva es un interruptor (un solo botón); Pendiente es aparte.
    $destino = $em['estado'] === 'Activa' ? 'Inactiva' : 'Activa';
    $colorDestino = $destino === 'Activa' ? 'boton--exito' : 'boton--peligro';
    ?>
    <form method="post" action="cambiar_estado.php" style="display:inline;">
      <input type="hidden" name="csrf" value="<?= e(token_csrf()) ?>">
      <input type="hidden" name="id" value="<?= (int) $em['id'] ?>">
      <input type="hidden" name="estado" value="<?= e($destino) ?>">
      <button class="boton <?= $colorDestino ?>" type="submit">Marcar como <?= e($destino) ?></button>
    </form>
    <?php if ($em['estado'] !== 'Pendiente'): ?>
      <form method="post" action="cambiar_estado.php" style="display:inline;">
        <input type="hidden" name="csrf" value="<?= e(token_csrf()) ?>">
        <input type="hidden" name="id" value="<?= (int) $em['id'] ?>">
        <input type="hidden" name="estado" value="Pendiente">
        <button class="boton boton--aviso" type="submit">Marcar como Pendiente</button>
      </form>
    <?php endif; ?>
    <a class="boton boton--plano" href="formulario.php?id=<?= (int) $em['id'] ?>">Cambiar familia</a>
    <button class="boton boton--plano" type="button" onclick="window.print()">🖨️ Imprimir</button>
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
      $fila('Nombre comercial', $em['nombre_comercial'] ?? null);
      $fila('Familia profesional', $em['familia_nombre']);
      $fila('Sector / actividad', $em['sector']);
      $fila('CIF', $em['cif']);
      $fila('Tamaño de empresa', $em['tamanio'] ?? null);
      $filaLista('Tipo de empresa', $em['tipo_empresa'] ?? null);
      ?>
    </dl>
  </section>

  <section class="panel">
    <h2>Prácticas de alumnado</h2>
    <dl>
      <?php
      $filaLista('Ciclos que puede acoger', $em['ciclos']);
      $fila('Plazas ofertadas', (string) $em['plazas']);
      $filaLista('Actividades formativas', $em['actividades_formativas'] ?? null);
      $filaLista('Instalaciones', $em['instalaciones'] ?? null);
      $fila('¿Colaboró antes con centros?', !empty($em['colaborado_antes']) ? 'Sí' : null);
      $fila('Tipo de colaboración previa', $em['tipo_colaboracion'] ?? null);
      ?>
    </dl>
  </section>

  <section class="panel">
    <h2>Organización y PRL</h2>
    <dl>
      <?php
      $fila('Horario / periodo', $em['horario'] ?? null);
      $fila('Jornada', $em['jornada'] ?? null);
      $fila('Evaluación de riesgos actualizada', !empty($em['prl_evaluacion']) ? 'Sí' : null);
      $fila('Formará en PRL al alumnado', !empty($em['prl_formacion']) ? 'Sí' : null);
      $fila('Proporcionará los EPI necesarios', !empty($em['prl_epis']) ? 'Sí' : null);
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
