<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
exigir_sesion();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: empresas.php');
    exit;
}
comprobar_csrf();

$id = (int) ($_POST['id'] ?? 0);
db()->prepare('UPDATE empresas SET eliminada_en = NOW() WHERE id = ?')->execute([$id]);

header('Location: empresas.php?ok=' . rawurlencode('Empresa movida a la papelera. Se borrará en firme a los 3 días.'));
