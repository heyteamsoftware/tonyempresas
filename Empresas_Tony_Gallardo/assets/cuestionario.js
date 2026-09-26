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
});
