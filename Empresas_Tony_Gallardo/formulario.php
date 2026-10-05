<?php
declare(strict_types=1);

// Formulario antiguo (una sola familia). Ahora todo se hace desde cuestionario_empresa.php,
// que permite elegir varias familias; esta página sólo redirige para no romper enlaces guardados.
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
header('Location: ' . ($id > 0 ? 'cuestionario_empresa.php?id=' . $id : 'nueva_empresa.php'));
