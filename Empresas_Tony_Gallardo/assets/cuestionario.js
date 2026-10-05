function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
}

function autoGrow(el) {
    el.style.height = 'auto';
    el.style.height = (el.scrollHeight) + 'px';
}

function prepararEImprimir() {
    document.querySelectorAll('.fill-text').forEach(function (campo) {
        var proxy = campo.nextElementSibling;
        if (proxy && proxy.classList.contains('print-proxy')) {
            proxy.innerText = campo.value || '____________________';
        }
    });
    setTimeout(function () { window.print(); }, 200);
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('textarea').forEach(autoGrow);

    // Formulario de empresa con varias familias: sólo se muestran los ciclos de las familias marcadas.
    var checks = document.querySelectorAll('input[name="familias[]"][type="checkbox"]');
    if (!checks.length) { return; }
    function actualizarFamilias() {
        var marcadas = {};
        var nombres = [];
        checks.forEach(function (c) {
            if (c.checked) {
                marcadas[c.value] = true;
                nombres.push(c.parentNode.textContent.trim());
            }
        });
        document.querySelectorAll('.ciclos-familia').forEach(function (bloque) {
            bloque.hidden = !marcadas[bloque.dataset.familia];
        });
        var sub = document.getElementById('subtitulo-familias');
        if (sub) {
            sub.textContent = 'Formación Profesional' + (nombres.length ? ' – ' + nombres.join(' · ') : '');
        }
    }
    checks.forEach(function (c) { c.addEventListener('change', actualizarFamilias); });
    actualizarFamilias();
});
