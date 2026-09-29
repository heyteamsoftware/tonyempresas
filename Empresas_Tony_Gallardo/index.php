<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
require __DIR__ . '/empresa_lib.php';
iniciar_sesion();

if (sesion_valida()) {
    header('Location: empresas.php');
    exit;
}

$clave = (string) ($_GET['clave'] ?? '');

if ($clave !== '' && token_valido('token_acceso', $clave, DIAS_CADUCIDAD_ACCESO)) {
    session_regenerate_id(true);
    $_SESSION['autorizado'] = true;
    header('Location: empresas.php');
    exit;
}

// Retraso ante intentos fallidos, sin registrar nada (ni IP ni intentos): sólo
// encarece adivinar la clave por fuerza bruta.
if ($clave !== '') {
    usleep(random_int(300000, 800000));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="assets/estilo.css?v=<?= @filemtime(__DIR__ . '/assets/estilo.css') ?: '1' ?>">
<link rel="icon" type="image/svg+xml" href="assets/icono.svg">
</head>
<body class="portada">

<main class="acceso">
  <header class="acceso__cabecera">
    <div class="logo">TG</div>
    <h1><?= e(APP_NAME) ?></h1>
    <p class="subtitulo"><?= e(CENTRO) ?> · Directorio de empresas colaboradoras</p>
  </header>

  <div class="teclado">
    <p class="aviso aviso--error"><strong>Acceso restringido</strong><br>
      Esta aplicación es de uso interno. Entra con el enlace privado que te ha facilitado el centro.</p>
  </div>
</main>

<footer class="pie-portada">Uso interno del profesorado · <?= e(CENTRO) ?></footer>
</body>
</html>
