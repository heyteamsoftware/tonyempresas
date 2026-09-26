<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
require __DIR__ . '/datos_familias.php';
exigir_sesion();

$familiaId = isset($_GET['familia']) ? (int) $_GET['familia'] : 0;
$st = db()->prepare('SELECT id, nombre FROM familias WHERE id = ?');
$st->execute([$familiaId]);
$familia = $st->fetch();

if (!$familia) {
    http_response_code(404);
    exit('Familia no encontrada.');
}

$d = datos_familia($familia['nombre']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Cuestionario alumnado · <?= e($familia['nombre']) ?></title>
<link rel="stylesheet" href="assets/cuestionario.css">
<link rel="icon" type="image/svg+xml" href="assets/icono.svg">
<style>:root { --accent-color: <?= e($d['acento']) ?>; }</style>
</head>
<body class="cuestionario">

<div class="container">
  <div class="controls">
    <a class="volver" href="empresas.php?familia=<?= (int) $familia['id'] ?>">&larr; Volver</a>
    <div class="botones">
      <button class="btn-theme" type="button" onclick="toggleDarkMode()">🌓 Modo oscuro</button>
      <button class="btn-print" type="button" onclick="prepararEImprimir()">🖨️ PDF / Imprimir</button>
    </div>
  </div>

  <div class="header-layout">
    <header>
      <h1>Cuestionario de Perfil Profesional</h1>
      <div class="subtitle">Prácticas en empresa &ndash; <?= e($familia['nombre']) ?></div>
    </header>
  </div>

  <form id="mainForm">
    <h2>1. Datos personales</h2>
    <div class="grid">
      <div class="form-group full-width"><label>Nombre y apellidos:</label><input type="text" class="fill-text"><div class="print-proxy"></div></div>
      <div class="form-group"><label>DNI/NIE:</label><input type="text" class="fill-text"><div class="print-proxy"></div></div>
      <div class="form-group"><label>Fecha de nacimiento:</label><input type="date" class="fill-text"><div class="print-proxy"></div></div>
      <div class="form-group"><label>Teléfono de contacto:</label><input type="tel" class="fill-text"><div class="print-proxy"></div></div>
      <div class="form-group"><label>Email:</label><input type="email" class="fill-text"><div class="print-proxy"></div></div>
      <div class="form-group full-width"><label>Municipio de residencia:</label><input type="text" class="fill-text"><div class="print-proxy"></div></div>
    </div>

    <h2>2. Formación académica</h2>
    <?php if ($d['ciclos']): ?>
      <label>2.1. Ciclo formativo actual:</label>
      <div class="option-group">
        <?php foreach ($d['ciclos'] as $c): ?>
          <label class="option-item"><input type="radio" name="ciclo"> <?= e($c) ?></label>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <div class="grid" style="margin-top:10px;">
      <div class="form-group"><label>2.2. Curso:</label>
        <div class="option-group" style="grid-template-columns: 100px 100px;">
          <label class="option-item"><input type="radio" name="curso"> 1º</label>
          <label class="option-item"><input type="radio" name="curso"> 2º</label>
        </div>
      </div>
    </div>
    <label>2.3. Formación previa relevante (ESO, Bachillerato, otros):</label>
    <div class="form-group"><textarea class="fill-text" oninput="autoGrow(this)"></textarea><div class="print-proxy"></div></div>

    <label style="color: var(--accent-color);">2.4. Nivel de experiencia práctica:</label>
    <div class="option-group">
      <label class="option-item"><input type="radio" name="nt"> Sin experiencia</label>
      <label class="option-item"><input type="radio" name="nt"> Básica (tareas guiadas)</label>
      <label class="option-item"><input type="radio" name="nt"> Media (trabajos supervisados)</label>
      <label class="option-item"><input type="radio" name="nt"> Avanzada (trabajo autónomo)</label>
    </div>

    <?php if ($d['competencias']): ?>
    <h2>3. Competencias técnicas</h2>
    <table>
      <thead><tr><th>Competencia</th><th>Bajo</th><th>Medio</th><th>Alto</th></tr></thead>
      <tbody>
        <?php foreach ($d['competencias'] as $i => $c): ?>
          <tr>
            <td><?= e($c) ?></td>
            <td><input type="radio" name="c<?= $i ?>"></td>
            <td><input type="radio" name="c<?= $i ?>"></td>
            <td><input type="radio" name="c<?= $i ?>"></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>

    <h2>4. Experiencia previa</h2>
    <label>4.1. ¿Ha trabajado en el sector? <input type="radio" name="ex"> Sí <input type="radio" name="ex"> No</label>
    <div class="form-group" style="margin-top:10px;"><label>Especifique empresa y funciones:</label><textarea class="fill-text" oninput="autoGrow(this)"></textarea><div class="print-proxy"></div></div>

    <?php if ($d['areas_interes']): ?>
    <h2>5. Intereses profesionales</h2>
    <label>Indique áreas preferentes para las prácticas:</label>
    <div class="option-group">
      <?php foreach ($d['areas_interes'] as $a): ?>
        <label class="option-item"><input type="checkbox"> <?= e($a) ?></label>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <h2>6. Disponibilidad y movilidad</h2>
    <div class="grid">
      <div class="form-group"><label>¿Vehículo propio? <input type="checkbox"> Sí</label></div>
      <div class="form-group"><label>Horario preferente:</label>
        <div class="option-group" style="grid-template-columns: repeat(3, 1fr);">
          <label><input type="radio" name="h"> Mañana</label>
          <label><input type="radio" name="h"> Tarde</label>
          <label><input type="radio" name="h"> Indif.</label>
        </div>
      </div>
    </div>

    <h2>7. Competencias transversales</h2>
    <table>
      <thead><tr><th>Competencia</th><th>Bajo</th><th>Medio</th><th>Alto</th></tr></thead>
      <tbody>
        <tr><td>Trabajo en equipo</td><td><input type="radio" name="t1"></td><td><input type="radio" name="t1"></td><td><input type="radio" name="t1"></td></tr>
        <tr><td>Responsabilidad y puntualidad</td><td><input type="radio" name="t2"></td><td><input type="radio" name="t2"></td><td><input type="radio" name="t2"></td></tr>
        <tr><td>Capacidad de aprendizaje</td><td><input type="radio" name="t3"></td><td><input type="radio" name="t3"></td><td><input type="radio" name="t3"></td></tr>
      </tbody>
    </table>

    <h2>8. Prevención de riesgos laborales (PRL)</h2>
    <div class="grid">
      <div class="form-group"><label>¿Formación PRL recibida?</label>
        <div class="option-group" style="grid-template-columns: 1fr 1fr;">
          <label class="option-item"><input type="radio" name="pf"> Sí</label>
          <label class="option-item"><input type="radio" name="pf"> No</label>
        </div>
      </div>
      <div class="form-group"><label>Conocimiento de seguridad en el puesto:</label>
        <div class="option-group" style="grid-template-columns: 1fr 1fr 1fr;">
          <label class="option-item"><input type="radio" name="pn"> Bajo</label>
          <label class="option-item"><input type="radio" name="pn"> Medio</label>
          <label class="option-item"><input type="radio" name="pn"> Alto</label>
        </div>
      </div>
    </div>

    <h2>9. Información adicional</h2>
    <div class="form-group"><label>Proyectos propios, aficiones o habilidades destacables:</label><textarea class="fill-text" oninput="autoGrow(this)"></textarea><div class="print-proxy"></div></div>

    <h2>10. Compromiso del alumnado</h2>
    <div style="page-break-inside: avoid; margin-top: 10px;">
      <p style="font-size: 0.85rem; opacity: 0.8;">Declaro que la información aportada es veraz y me comprometo a participar activamente en el proceso de prácticas.</p>
      <div class="signature-box">
        <div><label>Fecha:</label><input type="text" class="fill-text" placeholder="__/__/20__"><div class="print-proxy"></div></div>
        <div class="sig-line">Firma del alumno/a</div>
      </div>
    </div>
  </form>
</div>

<script src="assets/cuestionario.js"></script>
</body>
</html>
