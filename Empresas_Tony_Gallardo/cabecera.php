<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/empresa_lib.php';
exigir_sesion();
purgar_papelera();
registrar_visitante();
$titulo = $titulo ?? APP_NAME;
$pendientes = total_pendientes();
$visitantes = total_visitantes();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($titulo) ?> · <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="assets/estilo.css?v=<?= @filemtime(__DIR__ . '/assets/estilo.css') ?: '1' ?>">
<link rel="icon" type="image/svg+xml" href="assets/icono.svg">
</head>
<body>
<header class="barra">
  <a class="barra__marca" href="empresas.php">
    <span class="logo logo--pequeno">TG</span>
    <span>
      <strong><?= e(APP_NAME) ?></strong>
      <small><?= e(CENTRO) ?></small>
    </span>
  </a>
  <nav class="barra__nav">
    <a href="empresas.php">Empresas</a>
    <?php if ($pendientes > 0): ?>
      <a href="empresas.php?estado=Pendiente" class="aviso-contador" title="Empresas pendientes de revisar"><?= $pendientes ?> pendiente<?= $pendientes === 1 ? '' : 's' ?></a>
    <?php endif; ?>
    <a href="familias.php">Familias</a>
    <a href="compartir.php">Compartir enlace</a>
    <a href="papelera.php">Papelera</a>
    <a href="admin.php">Administración</a>
    <a href="nueva_empresa.php" class="boton boton--primario">+ Nueva empresa</a>
    <a href="salir.php" class="boton boton--plano">Salir</a>
    <span class="contador-visitas" title="Personas distintas que han entrado en la aplicación">
      <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7z"/></svg>
      <strong><?= (int) $visitantes ?></strong>
    </span>
  </nav>
</header>
<main class="contenedor">
