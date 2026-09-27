<?php /** Campos del formulario simplificado. Requiere $em (valores) y $d (datos de la familia). */ ?>
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
<?php if ($d['ciclos']): ?>
  <label>Ciclos de los que puede acoger alumnado:</label>
  <div class="option-group">
    <?php foreach ($d['ciclos'] as $c): ?>
      <label class="option-item"><input type="checkbox" name="ciclos[]" value="<?= e($c) ?>"
        <?= marcado($c, $em['ciclos'] ?? null) ? 'checked' : '' ?>> <?= e($c) ?></label>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<div class="form-group" style="max-width:300px;margin-top:10px;">
  <label>Nº de alumnos que puede acoger:</label>
  <input type="number" name="plazas" min="0" max="999" value="<?= (int) ($em['plazas'] ?? 0) ?>">
</div>

<h2>4. Observaciones</h2>
<div class="form-group">
  <label>Requisitos, perfil deseado u otra información:</label>
  <textarea name="observaciones" oninput="autoGrow(this)"><?= e($em['observaciones'] ?? '') ?></textarea>
</div>
