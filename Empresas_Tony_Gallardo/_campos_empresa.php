<?php /** Campos del formulario simplificado. Requiere $em (valores), $bloquesFamilias (familias cuyos ciclos se ofrecen) e $idsSel (ids elegidos). */ ?>
<h2>1. La empresa</h2>
<div class="grid">
  <div class="form-group full-width"><label>Nombre o razón social <span class="req">(*)</span>:</label>
    <input type="text" name="nombre" required maxlength="160" value="<?= e($em['nombre'] ?? '') ?>"></div>
  <div class="form-group"><label>CIF / NIF:</label>
    <input type="text" name="cif" maxlength="20" value="<?= e($em['cif'] ?? '') ?>"></div>
  <div class="form-group"><label>Actividad principal <span class="req">(*)</span>:</label>
    <input type="text" name="sector" required maxlength="120" value="<?= e($em['sector'] ?? '') ?>"></div>
</div>

<h2>2. Contacto y ubicación</h2>
<div class="grid">
  <div class="form-group"><label>Persona de contacto / tutor:</label>
    <input type="text" name="contacto_nombre" maxlength="160" value="<?= e($em['contacto_nombre'] ?? '') ?>"></div>
  <div class="form-group"><label>Teléfono <span class="req">(*)</span>:</label>
    <input type="tel" name="telefono" required maxlength="40" value="<?= e($em['telefono'] ?? '') ?>"></div>
  <div class="form-group"><label>Correo electrónico <span class="req">(*)</span>:</label>
    <input type="email" name="email" required maxlength="160" value="<?= e($em['email'] ?? '') ?>"></div>
  <div class="form-group"><label>Municipio / localidad <span class="req">(*)</span>:</label>
    <input type="text" name="localidad" required maxlength="100" value="<?= e($em['localidad'] ?? '') ?>"></div>
  <div class="form-group full-width"><label>Dirección:</label>
    <input type="text" name="direccion" maxlength="200" value="<?= e($em['direccion'] ?? '') ?>"></div>
</div>

<h2>3. Prácticas del alumnado</h2>
<?php foreach ($bloquesFamilias as $bf):
    $ciclosFam = datos_familia($bf['nombre'])['ciclos'];
    if (!$ciclosFam) { continue; }
    $visible = in_array((int) $bf['id'], $idsSel, true);
?>
  <div class="ciclos-familia" data-familia="<?= (int) $bf['id'] ?>" <?= $visible ? '' : 'hidden' ?>>
    <label>Ciclos de <?= e($bf['nombre']) ?> de los que puede acoger alumnado:</label>
    <div class="option-group">
      <?php foreach ($ciclosFam as $c): ?>
        <label class="option-item"><input type="checkbox" name="ciclos[]" value="<?= e($c) ?>"
          <?= marcado($c, $em['ciclos'] ?? null) ? 'checked' : '' ?>> <?= e($c) ?></label>
      <?php endforeach; ?>
    </div>
  </div>
<?php endforeach; ?>
<div class="form-group" style="max-width:420px;margin-top:10px;">
  <label>Otro ciclo (si no está en la lista):</label>
  <input type="text" name="ciclos_otro" maxlength="120" placeholder="Escríbelo aquí"
         value="<?= e(texto_otro($em['ciclos'] ?? null)) ?>">
</div>
<div class="form-group" style="max-width:300px;margin-top:10px;">
  <label>Nº de alumnos que puede acoger:</label>
  <input type="number" name="plazas" min="0" max="999" value="<?= (int) ($em['plazas'] ?? 0) ?>">
</div>

<h2>4. Observaciones</h2>
<div class="form-group">
  <label>Requisitos, perfil deseado u otra información:</label>
  <textarea name="observaciones" oninput="autoGrow(this)"><?= e($em['observaciones'] ?? '') ?></textarea>
</div>
