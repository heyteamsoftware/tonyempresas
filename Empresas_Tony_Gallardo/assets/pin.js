(function () {
  var LARGO = 4;
  var valor = '';
  var campo = document.getElementById('pin');
  var form = document.getElementById('form-pin');
  var puntos = document.querySelectorAll('#puntos .punto');

  function pintar() {
    puntos.forEach(function (p, i) {
      p.classList.toggle('punto--lleno', i < valor.length);
    });
    campo.value = valor;
  }

  function anadir(n) {
    if (valor.length >= LARGO) return;
    valor += n;
    pintar();
    if (valor.length === LARGO) {
      setTimeout(function () { form.submit(); }, 150);
    }
  }

  function borrar() {
    valor = valor.slice(0, -1);
    pintar();
  }

  document.querySelectorAll('.tecla').forEach(function (b) {
    b.addEventListener('click', function () {
      var accion = b.dataset.accion;
      if (accion === 'borrar') return borrar();
      if (accion === 'entrar') return form.submit();
      anadir(b.dataset.num);
    });
  });

  document.addEventListener('keydown', function (ev) {
    if (ev.key >= '0' && ev.key <= '9') { anadir(ev.key); ev.preventDefault(); }
    else if (ev.key === 'Backspace') { borrar(); ev.preventDefault(); }
    else if (ev.key === 'Enter') { form.submit(); }
  });

  pintar();
})();
