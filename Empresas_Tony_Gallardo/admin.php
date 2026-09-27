<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
require __DIR__ . '/empresa_lib.php';
exigir_sesion();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    comprobar_csrf();
    if (hash_equals(CONTRASENA_ADMIN, (string) ($_POST['contrasena'] ?? ''))) {
        session_regenerate_id(true);
        $_SESSION['admin_autorizado'] = true;
    } else {
        usleep(random_int(300000, 800000));
        $error = 'Contraseña incorrecta.';
    }
}

$titulo = 'Administración';
require __DIR__ . '/cabecera.php';
?>

<h1 class="titulo-pagina">Administración</h1>

<?php if (!admin_autorizado()): ?>
  <p class="migas">Esta sección requiere una contraseña adicional.</p>
  <?php if ($error !== ''): ?><p class="aviso aviso--error"><?= e($error) ?></p><?php endif; ?>
  <section class="panel" style="max-width:360px;">
    <form method="post">
      <input type="hidden" name="csrf" value="<?= e(token_csrf()) ?>">
      <div class="form-group">
        <label for="contrasena">Contraseña</label>
        <input type="password" id="contrasena" name="contrasena" required autofocus
               style="width:100%;padding:9px 12px;border:1px solid var(--borde);border-radius:8px;font-size:15px;">
      </div>
      <button class="boton boton--primario" type="submit" style="margin-top:12px;">Entrar</button>
    </form>
  </section>
<?php else: ?>
  <p class="migas">Zona restringida del profesorado con acceso administrativo.</p>
  <div class="rejilla-familias">
    <a class="tarjeta-familia" href="acceso.php">Enlace de acceso a la aplicación</a>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/pie.php'; ?>
