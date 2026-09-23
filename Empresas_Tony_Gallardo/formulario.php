<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
exigir_sesion();

$campos = [
    'nombre', 'cif', 'sector', 'ciclos', 'direccion', 'localidad',
    'provincia', 'cp', 'telefono', 'email', 'web', 'contacto_nombre',
    'contacto_cargo', 'contacto_telefono', 'contacto_email', 'observaciones',
];

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$em = array_fill_keys($campos, '');
$em['familia_id'] = null;
$em['plazas']     = 0;
$em['convenio']   = 0;
$em['estado']     = 'Activa';
$familiaNueva = '';
$errores = [];

if ($id > 0 && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $st = db()->prepare('SELECT * FROM empresas WHERE id = ?');
    $st->execute([$id]);
    $fila = $st->fetch();
    if (!$fila) {
        http_response_code(404);
        exit('Empresa no encontrada.');
    }
    $em = $fila;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    comprobar_csrf();
    $id = (int) ($_POST['id'] ?? 0);

    foreach ($campos as $c) {
        $em[$c] = trim((string) ($_POST[$c] ?? ''));
    }
    $em['familia_id'] = (int) ($_POST['familia_id'] ?? 0) ?: null;
    $em['plazas']     = max(0, (int) ($_POST['plazas'] ?? 0));
    $em['convenio']   = isset($_POST['convenio']) ? 1 : 0;
    $em['estado']     = in_array($_POST['estado'] ?? '', ESTADOS, true) ? $_POST['estado'] : 'Activa';
    $familiaNueva     = trim((string) ($_POST['familia_nueva'] ?? ''));

    // Familia escrita a mano: se crea (o se reutiliza si ya existe).
    if ($familiaNueva !== '') {
        $st = db()->prepare('SELECT id FROM familias WHERE nombre = ?');
        $st->execute([$familiaNueva]);
        $existente = $st->fetchColumn();
        if ($existente) {
            $em['familia_id'] = (int) $existente;
        } else {
            db()->prepare('INSERT INTO familias (nombre, orden) VALUES (?, 999)')->execute([$familiaNueva]);
            $em['familia_id'] = (int) db()->lastInsertId();
        }
    }

    if ($em['nombre'] === '') {
        $errores[] = 'El nombre de la empresa es obligatorio.';
    }
    foreach (['email' => 'de la empresa', 'contacto_email' => 'de la persona de contacto'] as $c => $q) {
        if ($em[$c] !== '' && !filter_var($em[$c], FILTER_VALIDATE_EMAIL)) {
            $errores[] = "El correo $q no tiene un formato válido.";
        }
    }

    if (!$errores) {
        $datos = [];
        foreach ($campos as $c) {
            $datos[$c] = $em[$c] !== '' ? $em[$c] : null;
        }
        $datos['familia_id'] = $em['familia_id'];
        $datos['plazas']     = $em['plazas'];
        $datos['convenio']   = $em['convenio'];
        $datos['estado']     = $em['estado'];

        if ($id > 0) {
            $sets = implode(', ', array_map(fn($c) => "$c = :$c", array_keys($datos)));
            $datos['id'] = $id;
            db()->prepare("UPDATE empresas SET $sets WHERE id = :id")->execute($datos);
            $msg = 'Empresa actualizada correctamente.';
        } else {
            $cols = implode(', ', array_keys($datos));
            $vals = ':' . implode(', :', array_keys($datos));
            db()->prepare("INSERT INTO empresas ($cols) VALUES ($vals)")->execute($datos);
            $id  = (int) db()->lastInsertId();
            $msg = 'Empresa añadida correctamente.';
        }
        header('Location: ficha.php?id=' . $id . '&ok=' . rawurlencode($msg));
        exit;
    }
}

$titulo = $id > 0 ? 'Editar empresa' : 'Nueva empresa';
require __DIR__ . '/cabecera.php';
?>

<h1 class="titulo-pagina"><?= e($titulo) ?></h1>

<?php if ($errores): ?>
  <div class="aviso aviso--error">
    <?php foreach ($errores as $err): ?><p><?= e($err) ?></p><?php endforeach; ?>
  </div>
<?php endif; ?>

<form method="post" class="formulario">
  <input type="hidden" name="csrf" value="<?= e(token_csrf()) ?>">
  <input type="hidden" name="id" value="<?= (int) $id ?>">

  <fieldset>
    <legend>Datos de la empresa</legend>
    <label class="ancho-total">Nombre *
      <input name="nombre" required maxlength="160" value="<?= e($em['nombre']) ?>">
    </label>
    <label>CIF <input name="cif" maxlength="20" value="<?= e($em['cif']) ?>"></label>
    <label>Sector / actividad <input name="sector" maxlength="120" value="<?= e($em['sector']) ?>"></label>
    <label>Familia profesional
      <select name="familia_id">
        <option value="">— Sin asignar —</option>
        <?php foreach (familias() as $f): ?>
          <option value="<?= (int) $f['id'] ?>"
            <?= (int) $em['familia_id'] === (int) $f['id'] ? 'selected' : '' ?>>
            <?= e($f['nombre']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>…o crear una familia nueva
      <input name="familia_nueva" maxlength="120" placeholder="Escribe una familia que no esté en la lista"
             value="<?= e($familiaNueva) ?>">
    </label>
    <label>Estado
      <select name="estado">
        <?php foreach (ESTADOS as $es): ?>
          <option value="<?= e($es) ?>" <?= $em['estado'] === $es ? 'selected' : '' ?>><?= e($es) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label class="ancho-total">Ciclos formativos relacionados
      <input name="ciclos" maxlength="255" placeholder="Ej.: DAW, ASIR, SMR"
             value="<?= e($em['ciclos']) ?>">
    </label>
  </fieldset>

  <fieldset>
    <legend>Localización y contacto</legend>
    <label class="ancho-total">Dirección <input name="direccion" maxlength="200" value="<?= e($em['direccion']) ?>"></label>
    <label>Localidad <input name="localidad" maxlength="100" value="<?= e($em['localidad']) ?>"></label>
    <label>Provincia <input name="provincia" maxlength="100" value="<?= e($em['provincia']) ?>"></label>
    <label>Código postal <input name="cp" maxlength="10" value="<?= e($em['cp']) ?>"></label>
    <label>Teléfono <input name="telefono" maxlength="40" value="<?= e($em['telefono']) ?>"></label>
    <label>Correo <input type="email" name="email" maxlength="160" value="<?= e($em['email']) ?>"></label>
    <label>Web <input name="web" maxlength="200" placeholder="https://" value="<?= e($em['web']) ?>"></label>
  </fieldset>

  <fieldset>
    <legend>Persona de contacto</legend>
    <label>Nombre <input name="contacto_nombre" maxlength="160" value="<?= e($em['contacto_nombre']) ?>"></label>
    <label>Cargo <input name="contacto_cargo" maxlength="120" value="<?= e($em['contacto_cargo']) ?>"></label>
    <label>Teléfono <input name="contacto_telefono" maxlength="40" value="<?= e($em['contacto_telefono']) ?>"></label>
    <label>Correo <input type="email" name="contacto_email" maxlength="160" value="<?= e($em['contacto_email']) ?>"></label>
  </fieldset>

  <fieldset>
    <legend>Prácticas</legend>
    <label>Plazas ofertadas
      <input type="number" name="plazas" min="0" max="999" value="<?= (int) $em['plazas'] ?>">
    </label>
    <label class="casilla">
      <input type="checkbox" name="convenio" value="1" <?= $em['convenio'] ? 'checked' : '' ?>>
      Convenio firmado
    </label>
    <label class="ancho-total">Observaciones
      <textarea name="observaciones" rows="5"><?= e($em['observaciones']) ?></textarea>
    </label>
  </fieldset>

  <div class="formulario__acciones">
    <button class="boton boton--primario" type="submit">Guardar</button>
    <a class="boton boton--plano" href="<?= $id > 0 ? 'ficha.php?id=' . (int) $id : 'empresas.php' ?>">Cancelar</a>
  </div>
</form>

<?php require __DIR__ . '/pie.php'; ?>
