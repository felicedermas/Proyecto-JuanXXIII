// ============================================================
//  editor-paginas.js
//  Editor de páginas sobre el propio sitio. Solo se carga para
//  quien tiene permiso de edición sobre la página que está viendo
//  (lo decide partials/paginas.php, que también deja en window.PAG
//  la página, el token y los bloques).
//
//  - Los textos editables vienen marcados con data-ed="clave".
//  - Cada sección trae un contenedor data-pb-wrap="sección" donde
//    van los mensajes, PDFs e información agregados.
//  Todo se guarda en pagina_editar.php.
// ============================================================
(function () {
  'use strict';
  var P = window.PAG;
  if (!P) return;

  var zona  = document.querySelector('main') || document.body;
  var hojas = Array.prototype.slice.call(zona.querySelectorAll('[data-ed]'));
  var originales = new Map();     // elemento → HTML con el que arrancó la edición
  var editando = false;
  var actual = null;              // texto con el foco
  var CLAVE_SEGUIR = 'pagEditar:' + P.slug;

  function crear(tag, clase, texto) {
    var n = document.createElement(tag);
    if (clase) n.className = clase;
    if (texto != null) n.textContent = texto;
    return n;
  }

  // ── Comunicación con el servidor ─────────────────────────
  function enviar(datos) {
    var fd = datos instanceof FormData ? datos : new FormData();
    if (!(datos instanceof FormData)) {
      Object.keys(datos).forEach(function (k) { fd.append(k, datos[k]); });
    }
    fd.append('csrf', P.csrf);
    fd.append('pagina', P.slug);
    return fetch(P.url, { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(function (r) {
        return r.json().catch(function () {
          return { ok: false, error: 'El servidor respondió algo inesperado (código ' + r.status + ').' };
        });
      })
      .catch(function () { return { ok: false, error: 'No se pudo conectar con el servidor.' }; });
  }

  /** Recarga quedándose en el mismo lugar y en modo edición. */
  function recargar() {
    try { sessionStorage.setItem(CLAVE_SEGUIR, String(window.scrollY)); } catch (e) {}
    sinAvisoAlSalir = true;
    location.reload();
  }

  // ── Barra flotante ───────────────────────────────────────
  var barra     = crear('div', 'pe-barra');
  var txt       = crear('span', 'pe-txt');
  var bEditar   = crear('button', 'pe-btn pe-btn--pri', '✎ Editar esta página');
  var bOriginal = crear('button', 'pe-btn', '↺ Texto original');
  var bCancelar = crear('button', 'pe-btn', 'Salir');
  var bGuardar  = crear('button', 'pe-btn pe-btn--pri', 'Guardar');
  var aPanel    = crear('a', 'pe-btn', 'Panel');
  aPanel.href = 'gestion_paginas';
  [bEditar, bOriginal, bCancelar, bGuardar].forEach(function (b) { b.type = 'button'; });
  bOriginal.title = 'Volver este texto a como está escrito originalmente en la página';
  barra.setAttribute('role', 'region');
  barra.setAttribute('aria-label', 'Edición de la página');
  [txt, bEditar, bOriginal, bCancelar, bGuardar, aPanel].forEach(function (n) { barra.appendChild(n); });
  document.body.appendChild(barra);

  function pendientes() {
    return hojas.filter(function (h) { return originales.has(h) && h.innerHTML !== originales.get(h); });
  }

  function pintarBarra() {
    var n = editando ? pendientes().length : 0;
    bEditar.hidden   = editando;
    aPanel.hidden    = editando;
    bCancelar.hidden = !editando;
    bGuardar.hidden  = !editando;
    bOriginal.hidden = !(editando && actual && actual.hasAttribute('data-ed-mod'));
    bGuardar.disabled = n === 0;
    bGuardar.textContent = n ? 'Guardar ' + n + ' cambio' + (n === 1 ? '' : 's') : 'Guardar';
    bCancelar.textContent = n ? 'Descartar' : 'Salir';
    if (!editando) {
      txt.innerHTML = 'Podés editar <b></b>';
      txt.querySelector('b').textContent = P.titulo;
    } else if (n) {
      txt.textContent = 'Hay cambios sin guardar.';
    } else {
      txt.textContent = 'Hacé clic en un texto para cambiarlo.';
    }
  }

  // ── Textos ───────────────────────────────────────────────
  function marcar(h) {
    h.classList.toggle('pe-pend', h.innerHTML !== originales.get(h));
    pintarBarra();
  }

  /** HTML a guardar: los íconos se mandan como marcas [[svg:n]], no como dibujo. */
  function serializar(h) {
    var copia = h.cloneNode(true);
    Array.prototype.forEach.call(copia.querySelectorAll('svg'), function (s) {
      var n = s.getAttribute('data-ed-svg');
      s.parentNode.replaceChild(document.createTextNode(n ? '[[svg:' + n + ']]' : ''), s);
    });
    return copia.innerHTML;
  }

  function alTeclear(ev) {
    if (ev.key === 'Enter') {
      // Enter = salto de línea dentro del mismo texto (no un párrafo nuevo)
      ev.preventDefault();
      document.execCommand('insertLineBreak');
    } else if (ev.key === 'Escape') {
      ev.target.blur();
    }
  }
  function alPegar(ev) {
    // Se pega como texto plano: nada de formatos traídos de Word o de otra web
    ev.preventDefault();
    var t = (ev.clipboardData || window.clipboardData).getData('text');
    document.execCommand('insertText', false, t.replace(/\s*\n\s*/g, ' '));
  }
  function alEscribir(ev) { marcar(ev.currentTarget); }
  function alEnfocar(ev)  { actual = ev.currentTarget; pintarBarra(); }

  /** Los botones (pestañas, desplegables) siguen funcionando: su texto se cambia con doble clic. */
  function editarBoton(ev) {
    var b = ev.currentTarget;
    var nodos = Array.prototype.filter.call(b.childNodes, function (n) { return n.nodeType === 3 && n.nodeValue.trim() !== ''; });
    var nuevo = window.prompt('Texto del botón:', b.textContent.replace(/\s+/g, ' ').trim());
    if (nuevo === null || !nodos.length) return;
    nodos[0].nodeValue = nuevo;
    nodos.slice(1).forEach(function (n) { n.parentNode.removeChild(n); });
    actual = b;
    marcar(b);
  }

  function entrar() {
    if (editando) return;
    editando = true;
    document.body.classList.add('pe-on');
    hojas.forEach(function (h) {
      originales.set(h, h.innerHTML);
      if (h.hasAttribute('data-ed-btn')) {
        h.title = 'Doble clic para cambiar el texto';
        h.addEventListener('dblclick', editarBoton);
        return;
      }
      h.contentEditable = 'true';
      h.spellcheck = true;
      h.addEventListener('keydown', alTeclear);
      h.addEventListener('paste', alPegar);
      h.addEventListener('input', alEscribir);
      h.addEventListener('focus', alEnfocar);
    });
    if (P.conBloques) armarBloques();
    pintarBarra();
  }

  function salir() {
    editando = false;
    actual = null;
    document.body.classList.remove('pe-on');
    hojas.forEach(function (h) {
      if (originales.has(h) && h.innerHTML !== originales.get(h)) h.innerHTML = originales.get(h);
      h.classList.remove('pe-pend');
      h.removeAttribute('contenteditable');
      h.removeEventListener('dblclick', editarBoton);
      h.removeEventListener('keydown', alTeclear);
      h.removeEventListener('paste', alPegar);
      h.removeEventListener('input', alEscribir);
      h.removeEventListener('focus', alEnfocar);
      if (h.hasAttribute('data-ed-btn')) h.removeAttribute('title');
    });
    originales.clear();
    Array.prototype.forEach.call(document.querySelectorAll('.pe-add, .pe-tools'), function (n) { n.parentNode.removeChild(n); });
    pintarBarra();
  }

  /** Guarda los textos cambiados. Resuelve true si quedó todo guardado. */
  function guardarTextos() {
    var lista = pendientes();
    if (!lista.length) return Promise.resolve(true);
    var tmp = document.createElement('div');
    var cambios = lista.map(function (h) {
      tmp.innerHTML = originales.get(h);
      return { clave: h.getAttribute('data-ed'), html: serializar(h), original: tmp.textContent.slice(0, 300) };
    });
    bGuardar.disabled = true;
    bGuardar.textContent = 'Guardando…';
    return enviar({ accion: 'textos', cambios: JSON.stringify(cambios) }).then(function (r) {
      if (!r.ok) { window.alert(r.error || 'No se pudo guardar.'); pintarBarra(); return false; }
      lista.forEach(function (h) { originales.set(h, h.innerHTML); });
      return true;
    });
  }

  bEditar.addEventListener('click', entrar);
  bGuardar.addEventListener('click', function () {
    guardarTextos().then(function (ok) { if (ok) recargar(); });
  });
  bCancelar.addEventListener('click', function () {
    if (pendientes().length && !window.confirm('¿Descartar los cambios que todavía no guardaste?')) return;
    salir();
  });
  bOriginal.addEventListener('click', function () {
    if (!actual) return;
    var otros = pendientes().filter(function (h) { return h !== actual; }).length;
    var msg = '¿Volver este texto a su versión original?' + (otros ? '\n\nLos demás cambios sin guardar se guardan antes.' : '');
    if (!window.confirm(msg)) return;
    var h = actual;
    h.innerHTML = originales.get(h);              // este no se guarda: se restaura
    guardarTextos().then(function (ok) {
      if (!ok) return;
      enviar({ accion: 'restaurar', clave: h.getAttribute('data-ed') }).then(function (r) {
        if (!r.ok) { window.alert(r.error); return; }
        recargar();
      });
    });
  });

  // Mientras se edita, los enlaces no navegan (si no, un clic para
  // corregir el texto de un botón te sacaría de la página).
  document.addEventListener('click', function (ev) {
    if (!editando) return;
    var a = ev.target.closest ? ev.target.closest('a') : null;
    if (a && zona.contains(a) && !ev.target.closest('.pe-tools')) ev.preventDefault();
  }, true);

  var sinAvisoAlSalir = false;
  window.addEventListener('beforeunload', function (ev) {
    if (sinAvisoAlSalir || !editando || !pendientes().length) return;
    ev.preventDefault();
    ev.returnValue = '';
  });

  // ── Bloques: mensaje / PDF / información ─────────────────
  var dlg = null;

  function armarBloques() {
    Array.prototype.forEach.call(document.querySelectorAll('[data-pb-wrap]'), function (w) {
      var b = crear('button', 'pe-add');
      b.type = 'button';
      b.appendChild(document.createTextNode('＋ Agregar mensaje, PDF o información'));
      b.appendChild(crear('small', '', 'en «' + w.getAttribute('data-pb-nombre') + '»'));
      b.addEventListener('click', function () { abrirDialogo(null, w); });
      w.appendChild(b);
    });
    Array.prototype.forEach.call(document.querySelectorAll('[data-pb-id]'), function (el) {
      var id = el.getAttribute('data-pb-id');
      var datos = P.bloques[id];
      if (!datos) return;
      var t = crear('div', 'pe-tools');
      function boton(texto, titulo, fn, clase) {
        var b = crear('button', clase || '', texto);
        b.type = 'button';
        b.title = titulo;
        b.setAttribute('aria-label', titulo);
        b.addEventListener('click', function (ev) { ev.preventDefault(); ev.stopPropagation(); fn(); });
        t.appendChild(b);
      }
      boton('Editar', 'Editar', function () { abrirDialogo(datos, el.parentNode); });
      boton('↑', 'Subir', function () { accionBloque({ accion: 'bloque_mover', id: id, dir: 'arriba' }); });
      boton('↓', 'Bajar', function () { accionBloque({ accion: 'bloque_mover', id: id, dir: 'abajo' }); });
      boton(datos.visible ? 'Ocultar' : 'Mostrar', datos.visible ? 'Ocultar del sitio (no se borra)' : 'Volver a mostrar en el sitio',
            function () { accionBloque({ accion: 'bloque_visible', id: id }); });
      boton('Eliminar', 'Eliminar', function () {
        if (!window.confirm('¿Eliminar este contenido? No se puede deshacer.')) return;
        accionBloque({ accion: 'bloque_borrar', id: id });
      }, 'pe-peligro');
      el.appendChild(t);
    });
  }

  /** Antes de cualquier acción que recarga, se guardan los textos pendientes. */
  function accionBloque(datos) {
    return guardarTextos().then(function (ok) {
      if (!ok) return false;
      return enviar(datos).then(function (r) {
        if (!r.ok) { window.alert(r.error || 'No se pudo completar la acción.'); return false; }
        recargar();
        return true;
      });
    });
  }

  function armarDialogo() {
    dlg = crear('dialog', 'pe-dlg');
    dlg.innerHTML =
      '<form novalidate>' +
        '<h2></h2><p class="pe-sub"></p>' +
        '<div class="pe-error" role="alert" hidden></div>' +
        '<div class="pe-tipos" role="radiogroup" aria-label="Qué querés agregar">' +
          '<label><input type="radio" name="tipo" value="mensaje"><span aria-hidden="true">📢</span>Mensaje<small>Un aviso destacado</small></label>' +
          '<label><input type="radio" name="tipo" value="pdf"><span aria-hidden="true">📄</span>Documento PDF<small>Para ver o descargar</small></label>' +
          '<label><input type="radio" name="tipo" value="info"><span aria-hidden="true">ℹ️</span>Información<small>Título, texto y enlace</small></label>' +
        '</div>' +
        '<div class="pe-campo" data-c="titulo"><label for="pe-titulo"></label><input type="text" id="pe-titulo" name="titulo" maxlength="160"></div>' +
        '<div class="pe-campo" data-c="archivo"><label for="pe-archivo">Archivo PDF</label><input type="file" id="pe-archivo" name="archivo" accept="application/pdf,.pdf"><span class="pe-ayuda"></span></div>' +
        '<div class="pe-campo" data-c="texto"><label for="pe-texto"></label><textarea id="pe-texto" name="texto" maxlength="5000"></textarea><span class="pe-ayuda">Las direcciones web y los correos que escribas quedan como enlaces.</span></div>' +
        '<div class="pe-campo" data-c="estilo"><label for="pe-estilo">Cómo se destaca</label><select id="pe-estilo" name="estilo"><option value="info">Aviso (azul)</option><option value="importante">Importante (rojo)</option></select></div>' +
        '<div class="pe-fila" data-c="enlace">' +
          '<div class="pe-campo"><label for="pe-enlace">Enlace <i>(opcional)</i></label><input type="text" id="pe-enlace" name="enlace" maxlength="300" placeholder="https://…  o  contacto"></div>' +
          '<div class="pe-campo"><label for="pe-enlace-txt">Texto del enlace</label><input type="text" id="pe-enlace-txt" name="enlace_texto" maxlength="80" placeholder="Más información"></div>' +
        '</div>' +
        '<div class="pe-campo" data-c="hasta"><label for="pe-hasta">Mostrar hasta <i>(opcional)</i></label><input type="date" id="pe-hasta" name="hasta"><span class="pe-ayuda">Pasado ese día deja de verse solo. Vacío = queda hasta que lo saques.</span></div>' +
        '<div class="pe-acciones"><button type="button" data-cerrar>Cancelar</button><button type="submit">Guardar</button></div>' +
      '</form>';
    document.body.appendChild(dlg);
    var f = dlg.querySelector('form');
    dlg.querySelector('[data-cerrar]').addEventListener('click', function () { dlg.close(); });
    Array.prototype.forEach.call(f.querySelectorAll('input[name=tipo]'), function (r) {
      r.addEventListener('change', function () { ajustarDialogo(r.value, false); });
    });
    f.addEventListener('submit', function (ev) {
      ev.preventDefault();
      var err = dlg.querySelector('.pe-error');
      var btn = f.querySelector('button[type=submit]');
      var archivo = f.archivo.files[0];
      err.hidden = true;
      if (archivo && archivo.size > P.pdfMax) {
        err.textContent = 'El PDF pesa más de ' + Math.round(P.pdfMax / 1048576) + ' MB. Probá con uno más liviano.';
        err.hidden = false;
        return;
      }
      var fd = new FormData(f);
      fd.append('accion', 'bloque_guardar');
      fd.append('id', dlg.dataset.id || '');
      fd.append('seccion', dlg.dataset.seccion || '');
      if (dlg.dataset.id) fd.set('tipo', dlg.dataset.tipo);
      btn.disabled = true;
      guardarTextos().then(function (ok) {
        if (!ok) { btn.disabled = false; return; }
        enviar(fd).then(function (r) {
          btn.disabled = false;
          if (!r.ok) { err.textContent = r.error || 'No se pudo guardar.'; err.hidden = false; return; }
          recargar();
        });
      });
    });
  }

  /** Muestra los campos que corresponden a cada tipo. */
  function ajustarDialogo(tipo, editandoBloque) {
    var f = dlg.querySelector('form');
    function ver(c, si) { f.querySelector('[data-c="' + c + '"]').hidden = !si; }
    ver('archivo', tipo === 'pdf');
    ver('estilo', tipo === 'mensaje');
    ver('enlace', tipo === 'info');
    f.querySelector('label[for="pe-titulo"]').innerHTML =
      tipo === 'mensaje' ? 'Título <i>(opcional)</i>' : (tipo === 'pdf' ? 'Nombre del documento <i>(si lo dejás vacío, se usa el del archivo)</i>' : 'Título');
    f.querySelector('label[for="pe-texto"]').innerHTML =
      tipo === 'mensaje' ? 'Mensaje' : (tipo === 'pdf' ? 'Descripción <i>(opcional)</i>' : 'Texto');
    f.querySelector('[data-c="archivo"] .pe-ayuda').textContent = editandoBloque
      ? 'Archivo actual: ' + (dlg.dataset.archivo || '—') + '. Elegí otro solo si querés reemplazarlo.'
      : 'Hasta ' + Math.round(P.pdfMax / 1048576) + ' MB.';
  }

  function abrirDialogo(datos, wrap) {
    if (!dlg) armarDialogo();
    var f = dlg.querySelector('form');
    f.reset();
    dlg.querySelector('.pe-error').hidden = true;
    var tipo = datos ? datos.tipo : 'mensaje';
    dlg.dataset.id      = datos ? datos.id : '';
    dlg.dataset.tipo    = tipo;
    dlg.dataset.seccion = wrap.getAttribute('data-pb-wrap');
    dlg.dataset.archivo = datos ? datos.archivo_nombre : '';
    dlg.querySelector('h2').textContent = datos ? 'Editar contenido' : 'Agregar a la página';
    dlg.querySelector('.pe-sub').textContent = 'Sección: ' + wrap.getAttribute('data-pb-nombre');
    dlg.querySelector('.pe-tipos').hidden = !!datos;        // el tipo no cambia al editar
    f.querySelector('input[name=tipo][value=' + tipo + ']').checked = true;
    if (datos) {
      f.titulo.value = datos.titulo;
      f.texto.value = datos.texto;
      f.estilo.value = datos.estilo;
      f.enlace.value = datos.enlace;
      f.enlace_texto.value = datos.enlace_texto;
      f.hasta.value = datos.hasta;
    }
    ajustarDialogo(tipo, !!datos);
    if (dlg.showModal) dlg.showModal(); else dlg.setAttribute('open', '');
  }

  // ── Arranque ─────────────────────────────────────────────
  pintarBarra();
  var seguir = null;
  try { seguir = sessionStorage.getItem(CLAVE_SEGUIR); sessionStorage.removeItem(CLAVE_SEGUIR); } catch (e) {}
  if (seguir !== null) {
    entrar();
    // 'instant': varias páginas tienen scroll suave por CSS y no llegarían al lugar
    var volver = function () { window.scrollTo({ top: parseInt(seguir, 10) || 0, behavior: 'instant' }); };
    volver();
    window.addEventListener('load', volver);
  } else if (/[?&]editar=1\b/.test(location.search)) {
    entrar();
  }
})();
