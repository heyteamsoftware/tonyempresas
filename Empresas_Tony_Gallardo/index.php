<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
iniciar_sesion();

if (sesion_valida()) {
    header('Location: empresas.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    comprobar_csrf();
    $pin = preg_replace('/\D/', '', (string) ($_POST['pin'] ?? ''));
    if (hash_equals(pin_del_dia(), (string) $pin)) {
        session_regenerate_id(true);
        $_SESSION['pin_dia'] = pin_del_dia();
        header('Location: empresas.php');
        exit;
    }
    $error = 'PIN incorrecto. Recuerda: es el día y el mes de hoy (DDMM).';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="assets/estilo.css">
</head>
<body class="portada">

<main class="acceso">
  <header class="acceso__cabecera">
    <div class="logo">TG</div>
    <h1><?= e(APP_NAME) ?></h1>
    <p class="subtitulo"><?= e(CENTRO) ?> · Directorio de empresas colaboradoras</p>
  </header>

  <form method="post" id="form-pin" class="teclado">
    <input type="hidden" name="csrf" value="<?= e(token_csrf()) ?>">
    <input type="hidden" name="pin" id="pin" value="">

    <label class="teclado__etiqueta">Introduce el PIN de hoy</label>

    <div class="puntos" id="puntos" aria-live="polite">
      <span class="punto"></span><span class="punto"></span>
      <span class="punto"></span><span class="punto"></span>
    </div>

    <?php if ($error !== ''): ?>
      <p class="aviso aviso--error"><?= e($error) ?></p>
    <?php endif; ?>

    <div class="teclas">
      <?php foreach (['1','2','3','4','5','6','7','8','9'] as $n): ?>
        <button type="button" class="tecla" data-num="<?= $n ?>"><?= $n ?></button>
      <?php endforeach; ?>
      <button type="button" class="tecla tecla--gris" data-accion="borrar">&#9003;</button>
      <button type="button" class="tecla" data-num="0">0</button>
      <button type="button" class="tecla tecla--ok" data-accion="entrar">&#10003;</button>
    </div>

    <p class="pista">El PIN son 4 cifras: día y mes de hoy (DDMM).</p>
  </form>
</main>

<footer class="pie-portada">Uso interno del profesorado · <?= e(CENTRO) ?></footer>

<script src="assets/pin.js"></script>
</body>
</html>
