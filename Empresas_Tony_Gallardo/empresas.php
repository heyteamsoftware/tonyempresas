<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
require __DIR__ . '/empresa_lib.php';
exigir_sesion();

$busqueda  = trim((string) ($_GET['q'] ?? ''));
$familiaId = isset($_GET['familia']) ? (int) $_GET['familia'] : 0;
$estado    = trim((string) ($_GET['estado'] ?? ''));

$empresas = buscar_empresas($busqueda, $familiaId, $estado);

// Agrupadas por familia profesional (guardamos también el id para los cuestionarios).
$grupos = [];
foreach ($empresas as $em) {
    $clave = $em['familia_nombre'] ?? 'Sin familia asignada';
    if (!isset($grupos[$clave])) {
        $grupos[$clave] = ['familia_id' => $em['familia_id'], 'empresas' => []];
    }
    $grupos[$clave]['empresas'][] = $em;
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
  <div class="tarjeta-dato"><strong><?= count($grupos) ?></strong><span>familias con empresas</span></div>
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
  <p class="vacio">No hay empresas que coincidan. <a href="formulario.php">Añade la primera</a>.</p>
<?php else: ?>
  <?php foreach ($grupos as $nombreFamilia => $grupo): ?>
    <section class="familia">
      <h2 class="familia__titulo">
        <?= e($nombreFamilia) ?>
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
            <div class="tarjeta__cabecera">
              <h3><a href="ficha.php?id=<?= (int) $em['id'] ?>"><?= e($em['nombre']) ?></a></h3>
              <span class="etiqueta etiqueta--<?= e(strtolower($em['estado'])) ?>"><?= e($em['estado']) ?></span>
            </div>
            <ul class="tarjeta__datos">
              <?php if ($em['sector']): ?><li>🏢 <?= e($em['sector']) ?></li><?php endif; ?>
              <?php if ($em['localidad']): ?><li>📍 <?= e($em['localidad']) ?></li><?php endif; ?>
              <?php if ($em['telefono']): ?><li>📞 <?= e($em['telefono']) ?></li><?php endif; ?>
              <?php if ($em['contacto_nombre']): ?><li>👤 <?= e($em['contacto_nombre']) ?></li><?php endif; ?>
              <li>🎓 <?= (int) $em['plazas'] ?> plazas<?= $em['convenio'] ? ' · convenio firmado' : '' ?></li>
            </ul>
            <div class="tarjeta__acciones">
              <a href="ficha.php?id=<?= (int) $em['id'] ?>">Ver ficha</a>
              <?php if ($em['familia_id']): ?>
                <a href="cuestionario_empresa.php?id=<?= (int) $em['id'] ?>">Editar</a>
              <?php else: ?>
                <a href="formulario.php?id=<?= (int) $em['id'] ?>">Editar</a>
              <?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endforeach; ?>
<?php endif; ?>

<?php require __DIR__ . '/pie.php'; ?>
