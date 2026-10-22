/* t_shirtparaguay · interacciones de la tienda (sin dependencias) */
(function () {
  'use strict';
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));
  const S = window.SHOP || {};
  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // Ventanas de confirmación y avisos con SweetAlert2 (en lugar de los del navegador)
  const swalBase = {
    customClass: { popup: 'sa-popup', confirmButton: 'btn', cancelButton: 'btn soft', actions: 'sa-actions' },
    buttonsStyling: false,
    reverseButtons: true,
    showClass: { popup: reduce ? '' : 'swal2-show' },
  };
  // ask('¿Borrar?', { danger: true, ok: 'Borrar' }) → Promise<boolean>
  window.ask = (text, o = {}) => {
    if (!window.Swal) return Promise.resolve(window.confirm(text));
    return Swal.fire({
      ...swalBase,
      icon: o.danger ? 'warning' : 'question',
      title: o.title || text,
      text: o.title ? text : undefined,
      showCancelButton: true,
      confirmButtonText: o.ok || 'Sí, seguir',
      cancelButtonText: o.cancel || 'Cancelar',
      customClass: { ...swalBase.customClass, confirmButton: o.danger ? 'btn danger' : 'btn accent' },
      focusCancel: !!o.danger,
    }).then(r => r.isConfirmed);
  };
  // notice('Texto', 'error' | 'success' | 'info' | 'warning')
  window.notice = (text, icon = 'info', title) => {
    if (!window.Swal) { window.alert(text); return Promise.resolve(); }
    return Swal.fire({ ...swalBase, icon, title: title || text, text: title ? text : undefined, confirmButtonText: 'Entendido', customClass: { ...swalBase.customClass, confirmButton: 'btn accent' } });
  };

  async function api(url, opts = {}) {
    const res = await fetch(url, {
      credentials: 'same-origin',
      ...opts,
      headers: { 'X-CSRF-TOKEN': S.csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', ...(opts.body && !(opts.body instanceof FormData) ? { 'Content-Type': 'application/json' } : {}), ...(opts.headers || {}) },
    });
    if (res.status === 401) { location.href = S.loginUrl; throw new Error('login'); }
    const data = await res.json().catch(() => ({}));
    if (!res.ok) { const e = new Error(data.message || 'Algo salió mal. Probá de nuevo.'); e.data = data; throw e; }
    return data;
  }

  // ---------- Aviso ----------
  let toastT;
  function toast(msg, err) {
    const t = $('#toast'); if (!t) return;
    t.textContent = msg; t.classList.toggle('err', !!err); t.classList.add('show');
    clearTimeout(toastT); toastT = setTimeout(() => t.classList.remove('show'), 2600);
  }
  window.toast = toast;

  // ---------- Modo claro / oscuro ----------
  $$('.theme-toggle').forEach(b => b.addEventListener('click', () => {
    const next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', next);
    try { localStorage.setItem('theme', next); } catch (e) {}
  }));

  // ---------- Encabezado ----------
  const header = $('#header');
  if (header) {
    const wa = $('.wa-float');
    const onScroll = () => { header.classList.toggle('scrolled', window.scrollY > 8); wa?.classList.toggle('show', window.scrollY > 360 || !$('#slider')); };
    window.addEventListener('scroll', onScroll, { passive: true }); onScroll();
  }

  // ---------- Paneles (menú, buscador, carrito) ----------
  let lastFocus = null;
  function openPanel(id) {
    const el = document.getElementById(id); if (!el) return;
    lastFocus = document.activeElement;
    el.classList.add('open'); el.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    if (id === 'cart') loadDrawer();
    if (id === 'search') setTimeout(() => $('[data-search-input]')?.focus(), 80);
  }
  function closePanels() {
    $$('.mnav.open, .drawer.open, .search-pop.open, .modal.open').forEach(el => { el.classList.remove('open'); el.setAttribute('aria-hidden', 'true'); });
    document.body.style.overflow = '';
    lastFocus?.focus?.();
  }
  document.addEventListener('click', e => {
    const o = e.target.closest('[data-open]');
    if (o) { e.preventDefault(); openPanel(o.dataset.open); return; }
    if (e.target.closest('[data-close]')) closePanels();
  });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closePanels(); });

  // ---------- Carrito ----------
  function setCount(n) {
    $$('[data-cart-count]').forEach(b => { b.textContent = n || ''; b.dataset.n = n; });
  }
  async function loadDrawer(data) {
    const body = $('[data-drawer-body]'); if (!body) return;
    try {
      data = data || await api(S.drawerUrl);
      body.innerHTML = data.html; setCount(data.count);
    } catch (e) { body.innerHTML = '<p style="padding:20px">No se pudo cargar el carrito.</p>'; }
  }
  window.addToCart = async function (variantId, qty = 1, openDrawer = true) {
    if (!variantId) { toast('Elegí un talle', true); return false; }
    try {
      const d = await api(S.addUrl, { method: 'POST', body: JSON.stringify({ variant_id: variantId, qty }) });
      setCount(d.count);
      if (openDrawer) openPanel('cart'); else toast(d.message);
      return true;
    } catch (e) { toast(e.message, true); return false; }
  };
  document.addEventListener('click', async e => {
    const q = e.target.closest('[data-quick-add]');
    if (q) { e.preventDefault(); q.disabled = true; await addToCart(q.dataset.quickAdd); q.disabled = false; }
  });
  // Cambiar cantidad y quitar dentro del carrito lateral
  document.addEventListener('click', async e => {
    const b = e.target.closest('[data-line-qty], [data-line-rm]'); if (!b || !b.closest('.drawer')) return;
    e.preventDefault();
    const url = b.dataset.url;
    const opts = b.dataset.lineRm !== undefined
      ? { method: 'DELETE' }
      : { method: 'PATCH', body: JSON.stringify({ qty: +b.dataset.lineQty }) };
    try { loadDrawer(await api(url, opts)); } catch (err) { toast(err.message, true); }
  });

  // ---------- Favoritos ----------
  document.addEventListener('click', async e => {
    const f = e.target.closest('[data-fav]'); if (!f) return;
    e.preventDefault();
    try {
      const d = await api(f.dataset.fav, { method: 'POST' });
      $$(`[data-fav="${f.dataset.fav}"]`).forEach(x => x.classList.toggle('on', d.favorite));
      toast(d.favorite ? 'Guardado en favoritos' : 'Quitado de favoritos');
    } catch (err) { if (err.message !== 'login') toast(err.message, true); }
  });

  // ---------- Buscador ----------
  const si = $('[data-search-input]'), sr = $('[data-search-results]');
  if (si && sr) {
    let t;
    si.addEventListener('input', () => {
      clearTimeout(t);
      t = setTimeout(async () => {
        const q = si.value.trim();
        if (q.length < 2) { sr.innerHTML = ''; return; }
        const items = await api(S.searchUrl + '?q=' + encodeURIComponent(q)).catch(() => []);
        sr.innerHTML = items.length
          ? items.map(i => `<a href="${i.url}"><img src="${i.image}" alt=""><span><b>${esc(i.name)}</b><br><span style="color:var(--ink-2)">${i.price}</span></span></a>`).join('')
          : '<p style="padding:8px;color:var(--ink-2)">No encontramos nada con eso. Probá con otra palabra.</p>';
      }, 180);
    });
  }
  function esc(s) { return String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c])); }

  // ---------- Aparición al hacer scroll ----------
  if ('IntersectionObserver' in window && !reduce) {
    const io = new IntersectionObserver(es => es.forEach(en => { if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); } }), { rootMargin: '0px 0px -8% 0px' });
    $$('.reveal').forEach(el => io.observe(el));
  } else $$('.reveal').forEach(el => el.classList.add('in'));

  // ---------- Slider principal ----------
  const slider = $('#slider');
  if (slider) {
    const slides = $$('.slide', slider);
    const dots = $$('.dots button', slider);
    const tag = $('.tagline span', slider);
    const thumb = $('.next-thumb img', slider);
    const interval = +slider.dataset.interval || 6500;
    slider.style.setProperty('--dur', interval + 'ms');
    let cur = 0, timer = null, paused = false;

    function go(n) {
      if (slides.length < 2) return;
      n = (n + slides.length) % slides.length;
      if (n === cur) return;
      const prev = slides[cur], next = slides[n];
      prev.classList.remove('on'); prev.classList.add('out'); prev.setAttribute('aria-hidden', 'true');
      $$('a', prev).forEach(a => a.tabIndex = -1);
      setTimeout(() => prev.classList.remove('out'), 700);
      next.classList.add('on'); next.removeAttribute('aria-hidden');
      $$('a', next).forEach(a => a.removeAttribute('tabindex'));
      slider.style.setProperty('--bg-slide', next.dataset.bg);
      slider.style.setProperty('--acc', next.dataset.acc);
      if (tag) {
        tag.classList.add('swap');
        setTimeout(() => { tag.textContent = next.dataset.tagline || ''; tag.classList.remove('swap'); }, 260);
      }
      if (thumb) {
        const after = slides[(n + 1) % slides.length];
        thumb.style.opacity = 0;
        setTimeout(() => { thumb.src = after.dataset.thumb; thumb.style.opacity = 1; }, 250);
      }
      dots.forEach((d, i) => {
        d.classList.toggle('on', i === n); d.classList.toggle('done', i < n);
        const bar = d.querySelector('i'); bar.style.animation = 'none'; void bar.offsetWidth; bar.style.animation = '';
      });
      cur = n;
      schedule();
    }
    function schedule() {
      clearTimeout(timer);
      if (!paused && !reduce) timer = setTimeout(() => go(cur + 1), interval);
    }
    function pause(p) { paused = p; slider.classList.toggle('paused', p); if (p) clearTimeout(timer); else schedule(); }

    $$('[data-next]', slider).forEach(b => b.addEventListener('click', () => go(cur + 1)));
    $$('[data-prev]', slider).forEach(b => b.addEventListener('click', () => go(cur - 1)));
    dots.forEach(d => d.addEventListener('click', () => go(+d.dataset.go)));
    // No se pausa con el mouse encima (el slider ocupa casi toda la pantalla y parecía trabado);
    // solo al usar el teclado adentro o con la pestaña oculta.
    slider.addEventListener('focusin', e => { if (e.target.matches(':focus-visible')) pause(true); });
    slider.addEventListener('focusout', () => pause(false));
    slider.addEventListener('keydown', e => { if (e.key === 'ArrowRight') go(cur + 1); if (e.key === 'ArrowLeft') go(cur - 1); });
    document.addEventListener('visibilitychange', () => pause(document.hidden));

    // Deslizar con el dedo
    let x0 = null, y0 = null;
    slider.addEventListener('touchstart', e => { x0 = e.touches[0].clientX; y0 = e.touches[0].clientY; }, { passive: true });
    slider.addEventListener('touchend', e => {
      if (x0 === null) return;
      const dx = e.changedTouches[0].clientX - x0, dy = e.changedTouches[0].clientY - y0;
      if (Math.abs(dx) > 45 && Math.abs(dx) > Math.abs(dy)) go(cur + (dx < 0 ? 1 : -1));
      x0 = null;
    });

    // Leve movimiento de la prenda siguiendo el mouse
    if (!reduce && window.matchMedia('(hover: hover)').matches) {
      slider.addEventListener('mousemove', e => {
        const r = slider.getBoundingClientRect();
        const dx = (e.clientX - r.left) / r.width - .5, dy = (e.clientY - r.top) / r.height - .5;
        const art = $('.slide.on .art', slider);
        if (art) art.style.transform = `translate(${dx * -14}px, ${dy * -10}px)`;
      });
    }
    $$('.slide .art', slider).forEach(a => a.style.transition = 'transform .6s cubic-bezier(.22,1,.36,1)');
    schedule();
  }

  // ---------- Ficha de producto ----------
  const pdp = $('[data-pdp]');
  if (pdp) {
    const variants = JSON.parse(pdp.dataset.variants || '[]');
    const stage = $('.stage img', pdp);
    const stageBox = $('.stage', pdp);
    const thumbs = $$('.thumbs button', pdp);
    const sizeBtns = $$('[data-size]', pdp);
    const colorBtns = $$('[data-color]', pdp);
    const hint = $('.stock-hint', pdp);
    const colorName = $('[data-color-name]', pdp);
    const sizeName = $('[data-size-name]', pdp);
    const qtyIn = $('[data-qty]', pdp);
    let color = colorBtns.length ? +colorBtns[0].dataset.color : null;
    let size = +pdp.dataset.preSize || null;

    const find = (s, c) => variants.find(v => v.size === s && (c === null || v.color === c));

    function show(i) {
      const b = thumbs[i]; if (!b) return;
      thumbs.forEach(t => t.classList.toggle('on', t === b));
      stage.style.opacity = .3;
      setTimeout(() => { stage.src = b.dataset.src; stage.style.opacity = 1; }, 120);
    }
    thumbs.forEach((b, i) => b.addEventListener('click', () => show(i)));
    stageBox?.addEventListener('click', e => {
      if (window.matchMedia('(hover: none)').matches) return;
      stageBox.classList.toggle('zoom');
      if (stageBox.classList.contains('zoom')) move(e);
    });
    function move(e) {
      const r = stageBox.getBoundingClientRect();
      stage.style.transformOrigin = `${(e.clientX - r.left) / r.width * 100}% ${(e.clientY - r.top) / r.height * 100}%`;
    }
    stageBox?.addEventListener('mousemove', e => { if (stageBox.classList.contains('zoom')) move(e); });
    stageBox?.addEventListener('mouseleave', () => stageBox.classList.remove('zoom'));

    function refresh() {
      colorBtns.forEach(b => b.classList.toggle('on', +b.dataset.color === color));
      if (colorName) colorName.textContent = colorBtns.find(b => +b.dataset.color === color)?.dataset.name || '';
      sizeBtns.forEach(b => {
        const v = find(+b.dataset.size, color);
        b.classList.toggle('out', !v || v.stock <= 0);
        b.classList.toggle('on', +b.dataset.size === size);
        b.setAttribute('aria-pressed', +b.dataset.size === size);
      });
      const v = size ? find(size, color) : null;
      if (sizeName) sizeName.textContent = size ? (sizeBtns.find(b => +b.dataset.size === size)?.textContent || '') : '';
      if (!size) { hint.textContent = 'Elegí tu talle.'; hint.className = 'stock-hint'; }
      else if (!v || v.stock <= 0) { hint.textContent = 'Este talle está agotado en este color. Consultanos por WhatsApp si vuelve.'; hint.className = 'stock-hint low'; }
      else if (v.stock <= 3) { hint.textContent = `¡Quedan solo ${v.stock}!`; hint.className = 'stock-hint low'; }
      else { hint.textContent = `Hay ${v.stock} disponibles. Sale en 24 a 48 h hábiles.`; hint.className = 'stock-hint'; }
      $$('[data-pick-size]', pdp).forEach(c => c.classList.toggle('on', +c.dataset.pickSize === size && (c.dataset.pickColor === '' ? color === null : +c.dataset.pickColor === color)));
      const ok = v && v.stock > 0;
      $$('[data-add]').forEach(b => { b.disabled = !ok && !!size; });
      if (qtyIn && v) { qtyIn.max = Math.max(1, v.stock); if (+qtyIn.value > v.stock) qtyIn.value = Math.max(1, v.stock); }
      // fotos del color elegido
      if (color !== null) {
        let firstIdx = -1;
        thumbs.forEach((t, i) => {
          const tc = t.dataset.colorId ? +t.dataset.colorId : null;
          const vis = tc === null || tc === color;
          t.hidden = !vis;
          if (vis && firstIdx < 0) firstIdx = i;
        });
        return firstIdx;
      }
      return -1;
    }
    colorBtns.forEach(b => b.addEventListener('click', () => { color = +b.dataset.color; const i = refresh(); if (i >= 0) show(i); }));
    sizeBtns.forEach(b => b.addEventListener('click', () => { size = +b.dataset.size; refresh(); }));
    // Tabla de disponibles: tocar una celda elige ese talle y color
    $$('[data-pick-size]', pdp).forEach(c => c.addEventListener('click', () => {
      size = +c.dataset.pickSize;
      const prev = color;
      if (c.dataset.pickColor !== '') color = +c.dataset.pickColor;
      const i = refresh(); if (i >= 0 && color !== prev) show(i);
    }));
    $$('[data-step]', pdp).forEach(b => b.addEventListener('click', () => {
      qtyIn.value = Math.min(+qtyIn.max || 50, Math.max(1, (+qtyIn.value || 1) + +b.dataset.step));
    }));
    $$('[data-add]').forEach(b => b.addEventListener('click', async () => {
      if (!size) { toast('Elegí un talle', true); $('.size-row')?.scrollIntoView({ behavior: 'smooth', block: 'center' }); return; }
      const v = find(size, color);
      b.disabled = true;
      await addToCart(v?.id, +(qtyIn?.value || 1));
      b.disabled = false;
    }));
    const i0 = refresh(); if (i0 > 0) show(i0);

    // Barra fija de compra en celular
    const mb = $('.mobile-buy'), main = $('.buy-actions');
    if (mb && main && 'IntersectionObserver' in window) {
      new IntersectionObserver(([en]) => mb.classList.toggle('show', !en.isIntersecting && en.boundingClientRect.top < 0)).observe(main);
    }
  }

  // Guía de talles (modal)
  $$('[data-modal]').forEach(b => b.addEventListener('click', e => {
    e.preventDefault();
    const m = document.getElementById(b.dataset.modal);
    if (m) { lastFocus = b; m.classList.add('open'); m.setAttribute('aria-hidden', 'false'); m.querySelector('.close')?.focus(); }
  }));

  // ---------- Filtros del catálogo ----------
  const ff = $('#filters');
  if (ff) {
    $$('[data-filters-toggle]').forEach(b => b.addEventListener('click', () => ff.classList.toggle('open')));
    $$('input[type=checkbox]', ff).forEach(i => i.addEventListener('change', () => ff.requestSubmit()));
  }
  $$('[data-autosubmit]').forEach(s => s.addEventListener('change', () => s.form.requestSubmit()));

  // ---------- Checkout: dirección solo si es envío ----------
  const dm = $$('input[name=delivery_method]');
  if (dm.length) {
    const box = $('[data-address-box]'), shipCell = $('[data-ship]'), totalCell = $('[data-total]');
    const upd = () => {
      const v = $('input[name=delivery_method]:checked')?.value;
      if (box) box.hidden = v !== 'envio';
      if (shipCell) {
        const ship = v === 'envio' ? +shipCell.dataset.cost : 0;
        shipCell.textContent = ship ? shipCell.dataset.costText : 'Gratis';
        totalCell.textContent = v === 'envio' ? totalCell.dataset.withShip : totalCell.dataset.noShip;
      }
    };
    dm.forEach(r => r.addEventListener('change', upd)); upd();
  }

  // ---------- Comprobante de pago: solo para transferencia, y muestra el archivo elegido ----------
  const rb = $('[data-receipt-box]');
  if (rb) {
    const pm = $$('input[name=payment_method]');
    const bank = $('[data-bank-box]');
    const updPm = () => {
      const isTransfer = !(pm.length > 0 && $('input[name=payment_method]:checked')?.value !== 'transferencia');
      rb.hidden = !isTransfer;
      if (bank) bank.hidden = !isTransfer;
    };
    pm.forEach(r => r.addEventListener('change', updPm)); updPm();
  }
  $$('[data-file-drop]').forEach(drop => {
    const input = $('input[type=file]', drop), title = $('b', drop), def = title.textContent;
    input.addEventListener('change', () => {
      const f = input.files[0];
      drop.classList.toggle('has', !!f);
      title.textContent = f ? '✓ ' + f.name : def;
      if (f && f.size > 5 * 1024 * 1024) { notice('Elegí un archivo más liviano: el comprobante puede pesar hasta 5 MB.', 'warning', 'El archivo es muy grande'); input.value = ''; drop.classList.remove('has'); title.textContent = def; }
    });
  });
})();
