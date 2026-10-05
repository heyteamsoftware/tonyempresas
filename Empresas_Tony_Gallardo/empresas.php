<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
require __DIR__ . '/empresa_lib.php';
exigir_sesion();

$busqueda  = trim((string) ($_GET['q'] ?? ''));
$familiaId = isset($_GET['familia']) ? (int) $_GET['familia'] : 0;
$estado    = trim((string) ($_GET['estado'] ?? ''));

$empresas = buscar_empresas($busqueda, $familiaId, $estado);

// Agrupadas por familia profesional: una empresa de varias familias aparece en cada una.
$mapaFamilias = familias_por_empresa(array_column($empresas, 'id'));
$grupos = [];
foreach (familias() as $f) {
    $grupos[(int) $f['id']] = ['nombre' => $f['nombre'], 'familia_id' => (int) $f['id'], 'empresas' => []];
}
$sinFamilia = [];
foreach ($empresas as $em) {
    $fs = $mapaFamilias[(int) $em['id']] ?? [];
    if (!$fs) {
        $sinFamilia[] = $em;
        continue;
    }
    foreach ($fs as $f) {
        $grupos[$f['id']]['empresas'][] = $em;
    }
}
$grupos = array_filter($grupos, fn($g) => $g['empresas']);
if ($familiaId > 0) {
    $grupos = array_filter($grupos, fn($k) => $k === $familiaId, ARRAY_FILTER_USE_KEY);
} elseif ($sinFamilia) {
    $grupos['sin'] = ['nombre' => 'Sin familia asignada', 'familia_id' => null, 'empresas' => $sinFamilia];
}

$total  = count($empresas);
$plazas = array_sum(array_column($empresas, 'plazas'));

$titulo = 'Empresas';
require __DIR__ . '/cabecera.php';
?>

<?php if (!empty($_GET['ok'])): ?>
  <p class="aviso aviso--ok"><?= e((string) $_GET['ok']) ?></p>
<?php endif; ?>

<section class="resumen">
  <div class="tarjeta-dato"><strong><?= $total ?></strong><span>empresas</span></div>
  <div class="tarjeta-dato"><strong><?= count(array_filter($grupos, fn($g) => $g['familia_id'])) ?></strong><span>familias con empresas</span></div>
  <div class="tarjeta-dato"><strong><?= $plazas ?></strong><span>plazas ofertadas</span></div>
</section>

<form class="filtros" method="get">
  <input type="search" name="q" placeholder="Buscar por nombre, sector, localidad, ciclo…"
         value="<?= e($busqueda) ?>">
  <select name="familia">
    <option value="">Todas las familias</option>
    <?php foreach (familias() as $f): ?>
      <option value="<?= (int) $f['id'] ?>" <?= $familiaId === (int) $f['id'] ? 'selected' : '' ?>>
        <?= e($f['nombre']) ?>
      </option>
    <?php endforeach; ?>
  </select>
  <select name="estado">
    <option value="">Todos los estados</option>
    <?php foreach (ESTADOS as $es): ?>
      <option value="<?= e($es) ?>" <?= $estado === $es ? 'selected' : '' ?>><?= e($es) ?></option>
    <?php endforeach; ?>
  </select>
  <button class="boton boton--primario" type="submit">Filtrar</button>
  <a class="boton boton--plano" href="empresas.php">Limpiar</a>
  <a class="boton boton--plano" href="exportar.php?<?= e(http_build_query(['q' => $busqueda, 'familia' => $familiaId ?: '', 'estado' => $estado])) ?>">⬇️ Exportar a Excel</a>
</form>

<?php if ($total === 0): ?>
  <p class="vacio">No hay empresas que coincidan. <a href="nueva_empresa.php">Añade la primera</a>.</p>
<?php else: ?>
  <?php foreach ($grupos as $grupo): ?>
    <section class="familia">
      <h2 class="familia__titulo">
        <?= e($grupo['nombre']) ?>
        <span class="familia__conteo"><?= count($grupo['empresas']) ?></span>
        <?php if ($grupo['familia_id']): ?>
          <span class="familia__cuestionarios">
            <a href="cuestionario_alumnado.php?familia=<?= (int) $grupo['familia_id'] ?>" target="_blank">📋 Cuestionario alumnado</a>
            <a href="cuestionario_empresa.php?familia=<?= (int) $grupo['familia_id'] ?>" target="_blank">🏢 Cuestionario empresa</a>
          </span>
        <?php endif; ?>
      </h2>
      <div class="rejilla">
        <?php foreach ($grupo['empresas'] as $em): ?>
          <article class="tarjeta">
            <a class="tarjeta__enlace" href="ficha.php?id=<?= (int) $em['id'] ?>" aria-label="Ver ficha de <?= e($em['nombre']) ?>"></a>
            <div class="tarjeta__cabecera">
              <h3><?= e($em['nombre']) ?></h3>
              <span class="etiqueta etiqueta--<?= e(strtolower($em['estado'])) ?>"><?= e($em['estado']) ?></span>
            </div>
            <ul class="tarjeta__datos">
              <?php if ($em['sector']): ?><li>🏢 <?= e($em['sector']) ?></li><?php endif; ?>
              <?php if ($em['localidad']): ?><li>📍 <?= e($em['localidad']) ?></li><?php endif; ?>
              <?php if ($em['telefono']): ?><li>📞 <?= e($em['telefono']) ?></li><?php endif; ?>
              <?php if ($em['contacto_nombre']): ?><li>👤 <?= e($em['contacto_nombre']) ?></li><?php endif; ?>
              <li>🎓 <?= (int) $em['plazas'] ?> plazas<?= $em['convenio'] ? ' · convenio firmado' : '' ?></li>
              <?php $famsEmpresa = $mapaFamilias[(int) $em['id']] ?? []; ?>
              <?php if (count($famsEmpresa) > 1): ?>
                <li>🏷️ <?= e(implode(' · ', array_column($famsEmpresa, 'nombre'))) ?></li>
              <?php endif; ?>
            </ul>
            <div class="tarjeta__acciones">
              <a href="cuestionario_empresa.php?id=<?= (int) $em['id'] ?>">Editar</a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endforeach; ?>
<?php endif; ?>

<?php require __DIR__ . '/pie.php'; ?>
