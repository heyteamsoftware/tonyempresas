<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
exigir_sesion();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: empresas.php');
    exit;
}
comprobar_csrf();

$id     = (int) ($_POST['id'] ?? 0);
$estado = (string) ($_POST['estado'] ?? '');

if ($id > 0 && in_array($estado, ESTADOS, true)) {
    db()->prepare('UPDATE empresas SET estado = ? WHERE id = ? AND eliminada_en IS NULL')->execute([$estado, $id]);
}

header('Location: ficha.php?id=' . $id . '&ok=' . rawurlencode('Estado cambiado a "' . $estado . '".'));
