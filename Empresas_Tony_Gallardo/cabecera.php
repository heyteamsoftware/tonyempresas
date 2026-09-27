<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/empresa_lib.php';
exigir_sesion();
purgar_papelera();
$titulo = $titulo ?? APP_NAME;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($titulo) ?> · <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="assets/estilo.css">
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
    <a href="familias.php">Familias</a>
    <a href="compartir.php">Compartir enlace</a>
    <a href="papelera.php">Papelera</a>
    <a href="admin.php">Administración</a>
    <a href="nueva_empresa.php" class="boton boton--primario">+ Nueva empresa</a>
    <a href="salir.php" class="boton boton--plano">Salir</a>
  </nav>
</header>
<main class="contenedor">
