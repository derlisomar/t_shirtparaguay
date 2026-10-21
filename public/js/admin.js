/* t_shirtparaguay · panel de administración */
(function () {
  'use strict';
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));
  const csrf = document.querySelector('meta[name=csrf-token]').content;
  const fmt = n => (+n || 0).toLocaleString('es-PY').replace(/,/g, '.');
  const num = s => +String(s || '').replace(/\D/g, '') || 0;
  const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const toast = (m, e) => window.toast ? window.toast(m, e) : alert(m);

  // Montos con separador de miles mientras se escribe
  $$('[data-money]').forEach(i => i.addEventListener('input', () => {
    const n = num(i.value); i.value = n ? fmt(n) : '';
  }));

  // Botones de borrar (envían un formulario DELETE)
  document.addEventListener('click', e => {
    const b = e.target.closest('[data-delete]'); if (!b) return;
    e.preventDefault();
    if (b.dataset.confirm && !confirm(b.dataset.confirm)) return;
    const f = document.createElement('form');
    f.method = 'post'; f.action = b.dataset.delete;
    f.innerHTML = `<input type="hidden" name="_token" value="${csrf}"><input type="hidden" name="_method" value="DELETE">`;
    document.body.appendChild(f); f.submit();
  });

  // Formularios que piden confirmación antes de enviarse
  $$('form[data-confirm]').forEach(f => f.addEventListener('submit', e => { if (!confirm(f.dataset.confirm)) e.preventDefault(); }));

  // ---------- Pedido: ajustar cantidades o quitar prendas ----------
  const itemsForm = $('[data-items-form]');
  if (itemsForm) {
    const inputs = $$('input[name^="qty["]', itemsForm);
    const cur = 'Gs. ';
    const upd = () => {
      let sub = 0, changed = false;
      inputs.forEach(i => {
        const q = Math.max(0, Math.min(999, parseInt(i.value, 10) || 0));
        const line = i.closest('[data-item]');
        sub += q * +i.dataset.price;
        changed = changed || q !== +i.dataset.orig;
        line.classList.toggle('removed', q === 0);
        $('[data-line-total]', line).textContent = cur + fmt(q * +i.dataset.price);
        $('[data-remove]', line).textContent = q === 0 ? 'Volver a agregar' : 'Quitar';
      });
      $('[data-new-sub]', itemsForm).textContent = cur + fmt(sub);
      $('[data-items-save]', itemsForm).hidden = !changed;
    };
    itemsForm.addEventListener('click', e => {
      const line = e.target.closest('[data-item]'); if (!line) return;
      const i = $('input[name^="qty["]', line);
      const step = e.target.closest('[data-q]');
      if (step) i.value = Math.max(0, (parseInt(i.value, 10) || 0) + +step.dataset.q);
      if (e.target.closest('[data-remove]')) i.value = (parseInt(i.value, 10) || 0) === 0 ? (+i.dataset.orig || 1) : 0;
      if (step || e.target.closest('[data-remove]')) upd();
    });
    inputs.forEach(i => i.addEventListener('input', upd));
    $('[data-items-reset]', itemsForm).addEventListener('click', () => { inputs.forEach(i => { i.value = i.dataset.orig; }); upd(); });
    itemsForm.addEventListener('submit', e => {
      const removed = inputs.filter(i => (parseInt(i.value, 10) || 0) === 0 && +i.dataset.orig > 0).length;
      if (removed && !confirm(`Vas a quitar ${removed === 1 ? 'una prenda' : removed + ' prendas'} del pedido. ¿Seguimos?`)) e.preventDefault();
    });
  }

  // ---------- Formulario de producto: grilla talle × color ----------
  const grid = $('[data-stock-grid]');
  if (grid) {
    const stock = JSON.parse(grid.dataset.stock || '{}');
    const sizesBox = $('[data-pick=sizes]'), colorsBox = $('[data-pick=colors]');
    const read = () => $$('input', grid).forEach(i => {
      const s = i.dataset.s, c = i.dataset.c;
      stock[s] = stock[s] || {};
      if (i.value === '') delete stock[s][c]; else stock[s][c] = i.value;
    });
    function draw() {
      read();
      const sizes = $$('input:checked', sizesBox).map(i => ({ id: i.value, name: i.dataset.name }));
      let colors = $$('input:checked', colorsBox).map(i => ({ id: i.value, name: i.dataset.name, hex: i.dataset.hex }));
      if (!colors.length) colors = [{ id: '0', name: 'Sin color' }];
      if (!sizes.length) { grid.innerHTML = '<p class="help">Elegí al menos un talle.</p>'; return; }
      let h = '<table><tr><th></th>' + sizes.map(s => `<th>${esc(s.name)}</th>`).join('') + '<th></th></tr>';
      colors.forEach(c => {
        h += `<tr><th class="c">${c.hex ? `<span style="display:inline-block;width:12px;height:12px;border-radius:9px;background:${c.hex};margin-right:6px;box-shadow:inset 0 0 0 1px rgba(0,0,0,.15)"></span>` : ''}${esc(c.name)}</th>`;
        sizes.forEach(s => {
          const v = stock[s.id]?.[c.id];
          h += `<td><input type="number" min="0" name="stock[${s.id}][${c.id}]" data-s="${s.id}" data-c="${c.id}" value="${v ?? ''}" placeholder="—" aria-label="${esc(s.name)} ${esc(c.name)}"></td>`;
        });
        h += `<td><button type="button" class="btn sm soft" style="height:34px" data-fill="${c.id}" title="Poner la misma cantidad en todos los talles">Llenar</button></td></tr>`;
      });
      grid.innerHTML = h + '</table>';
    }
    grid.addEventListener('click', e => {
      const b = e.target.closest('[data-fill]'); if (!b) return;
      const q = prompt('¿Cuántas unidades en cada talle?', '5'); if (q === null) return;
      $$(`input[data-c="${b.dataset.fill}"]`, grid).forEach(i => i.value = Math.max(0, parseInt(q) || 0));
    });
    // Si el producto no tiene variantes, la grilla se llena con stock 0 al elegir talles/colores nuevos
    $$('input', sizesBox).concat($$('input', colorsBox)).forEach(i => i.addEventListener('change', draw));
    draw();
  }

  // Fotos nuevas: vista previa + color de cada una
  const photos = $('[data-photos]');
  if (photos) {
    const out = $('[data-new-photos]'), drop = $('[data-drop]');
    const colorOpts = $('select[name^=image_color]')?.innerHTML
      || '<option value="">Todos los colores</option>' + $$('[data-pick=colors] input').map(i => `<option value="${i.value}">${esc(i.dataset.name)}</option>`).join('');
    function preview() {
      out.innerHTML = '';
      Array.from(photos.files).forEach((f, i) => {
        const d = document.createElement('div'); d.className = 'im';
        d.innerHTML = `<img alt=""><select name="photo_color[${i}]" aria-label="Color de la foto">${colorOpts}</select>`;
        d.querySelector('select').value = '';
        d.querySelector('img').src = URL.createObjectURL(f);
        out.appendChild(d);
      });
    }
    photos.addEventListener('change', preview);
    ['dragenter', 'dragover'].forEach(ev => drop.addEventListener(ev, e => { e.preventDefault(); drop.classList.add('over'); }));
    ['dragleave', 'drop'].forEach(ev => drop.addEventListener(ev, e => { e.preventDefault(); drop.classList.remove('over'); }));
    drop.addEventListener('drop', e => { photos.files = e.dataTransfer.files; preview(); });
  }

  // ---------- Slider: vista previa en vivo y orden ----------
  const sf = $('[data-slide-form]');
  if (sf) {
    const prev = $('[data-prev]');
    const glow = $('[data-prev-glow]');
    $$('[data-bind]', sf).forEach(i => i.addEventListener('input', () => {
      const k = i.dataset.bind;
      if (k === 'title') $('[data-prev-title]').textContent = i.value || 'Título del slide';
      if (k === 'text') $('[data-prev-text]').textContent = i.value;
      if (k === 'bg') prev.style.background = i.value;
      if (k === 'acc') glow.style.background = `radial-gradient(45% 60% at 70% 55%, ${i.value}66, transparent 70%)`;
    }));
    $('[data-prev-file]')?.addEventListener('change', e => {
      const f = e.target.files[0]; if (!f) return;
      const img = $('[data-prev-img]'); img.src = URL.createObjectURL(f); img.hidden = false;
    });
  }
  const sortable = $('[data-sortable]');
  if (sortable) {
    let drag = null;
    sortable.addEventListener('dragstart', e => { drag = e.target.closest('.slide-row'); drag?.classList.add('dragging'); });
    sortable.addEventListener('dragend', async () => {
      drag?.classList.remove('dragging'); drag = null;
      const order = $$('.slide-row', sortable).map(r => r.dataset.id);
      await fetch(sortable.dataset.sortable, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json', Accept: 'application/json' }, body: JSON.stringify({ order }) });
      toast('Orden guardado');
    });
    sortable.addEventListener('dragover', e => {
      e.preventDefault();
      const after = $$('.slide-row:not(.dragging)', sortable).find(r => e.clientY < r.getBoundingClientRect().top + r.offsetHeight / 2);
      if (drag) after ? sortable.insertBefore(drag, after) : sortable.appendChild(drag);
    });
  }

  // ---------- Punto de venta ----------
  const pos = $('[data-pos]');
  if (pos) {
    const cur = pos.dataset.currency;
    const money = n => cur + ' ' + fmt(n);
    let products = [], cart = [], cat = '', customer = null;
    const gridEl = $('[data-pos-grid]'), linesEl = $('[data-pos-lines]');

    async function load() {
      const q = $('[data-pos-q]').value.trim();
      const r = await fetch(`${pos.dataset.search}?q=${encodeURIComponent(q)}&categoria=${cat}`, { headers: { Accept: 'application/json' } });
      products = await r.json();
      gridEl.innerHTML = products.length ? products.map((p, i) => {
        const st = p.variants.reduce((a, v) => a + v.stock, 0);
        return `<button type="button" class="pos-item" data-i="${i}" ${st ? '' : 'style="opacity:.5"'}><img src="${p.image}" alt="" loading="lazy"><b>${esc(p.name)}</b><span>${p.price_text} · ${st} en stock</span>${p.wholesale_price ? `<span>May. ${money(p.wholesale_price)}${p.wholesale_min ? ' desde ' + p.wholesale_min : ''}</span>` : ''}</button>`;
      }).join('') : '<p class="help">No hay prendas con ese nombre.</p>';
    }
    let qt; $('[data-pos-q]').addEventListener('input', () => { clearTimeout(qt); qt = setTimeout(load, 200); });
    $$('[data-pos-cat]').forEach(b => b.addEventListener('click', () => {
      $$('[data-pos-cat]').forEach(x => x.classList.toggle('on', x === b)); cat = b.dataset.posCat; load();
    }));

    const modal = $('#pos-variant');
    const openModal = m => { m.classList.add('open'); m.setAttribute('aria-hidden', 'false'); };
    const closeModal = m => { m.classList.remove('open'); m.setAttribute('aria-hidden', 'true'); };
    gridEl.addEventListener('click', e => {
      const b = e.target.closest('.pos-item'); if (!b) return;
      const p = products[+b.dataset.i];
      const byColor = {};
      p.variants.forEach(v => { (byColor[v.color || 'Único'] = byColor[v.color || 'Único'] || { hex: v.hex, list: [] }).list.push(v); });
      $('[data-variant-body]').innerHTML = `<h2 style="font-size:24px;margin-bottom:4px">${esc(p.name)}</h2><p class="help" style="margin-bottom:16px">${p.price_text} · Elegí talle y color</p><div class="var-pick">` +
        Object.entries(byColor).map(([c, g]) => `<div><div style="font-weight:600;margin-bottom:8px">${g.hex ? `<span style="display:inline-block;width:12px;height:12px;border-radius:9px;background:${g.hex};margin-right:6px"></span>` : ''}${esc(c)}</div><div class="row">` +
          g.list.map(v => {
            const inCart = cart.filter(l => l.id === v.id).reduce((a, l) => a + l.qty, 0);
            const left = v.stock - inCart;
            return `<button type="button" data-v="${v.id}" ${left > 0 ? '' : 'disabled'}>${esc(v.size)}<small>${left > 0 ? left + ' u.' : 'agotado'}</small></button>`;
          }).join('') + '</div></div>').join('') + '</div>';
      modal.dataset.p = b.dataset.i;
      openModal(modal);
    });
    modal.addEventListener('click', e => {
      const b = e.target.closest('[data-v]');
      if (b) {
        const p = products[+modal.dataset.p];
        const v = p.variants.find(x => x.id === +b.dataset.v);
        const ex = cart.find(l => l.id === v.id);
        if (ex) ex.qty++; else cart.push({ id: v.id, pid: p.id, name: p.name, label: [v.size, v.color].filter(Boolean).join(' · '), price: p.price, base: p.price, ws: p.wholesale_price, wsMin: p.wholesale_min, qty: 1, stock: v.stock });
        closeModal(modal); render(); toast('Agregado');
      }
      if (e.target.closest('[data-close]')) closeModal(modal);
    });

    // Precio mayorista: si se marca para toda la venta, si el cliente es mayorista
    // o si se llega a la cantidad mínima del producto (sumando talles y colores)
    function applyPrices() {
      const all = $('[data-pos-wholesale]').checked;
      const perProduct = {};
      cart.forEach(l => { perProduct[l.pid] = (perProduct[l.pid] || 0) + l.qty; });
      cart.forEach(l => {
        l.isWs = !!l.ws && (all || (l.wsMin > 0 && perProduct[l.pid] >= l.wsMin));
        l.price = l.isWs ? l.ws : l.base;
      });
    }
    function totals() {
      applyPrices();
      const sub = cart.reduce((a, l) => a + l.price * l.qty, 0);
      const disc = Math.min(sub, num($('[data-pos-discount]').value));
      return { sub, disc, total: sub - disc };
    }
    function render() {
      applyPrices();
      linesEl.innerHTML = cart.length ? cart.map((l, i) => `<div class="tline"><div><b>${esc(l.name)}</b><br><span>${esc(l.label)} · ${money(l.price)}${l.isWs ? ' · <b style="color:var(--ok)">mayorista</b>' : ''}</span></div><div style="text-align:right"><b>${money(l.price * l.qty)}</b><div class="q" style="margin-top:6px"><button type="button" data-dec="${i}" aria-label="Uno menos">−</button><span>${l.qty}</span><button type="button" data-inc="${i}" aria-label="Uno más" ${l.qty >= l.stock ? 'disabled' : ''}>+</button></div></div></div>`).join('') : '<p class="help">Tocá una prenda para agregarla.</p>';
      const t = totals();
      $('[data-pos-sub]').textContent = money(t.sub);
      $('[data-pos-disc]').textContent = t.disc ? '-' + money(t.disc) : money(0);
      $('[data-pos-total]').textContent = money(t.total);
      const cashOn = $('input[name=pos_pay]:checked')?.value === 'efectivo';
      $('[data-cash-box]').style.visibility = cashOn ? 'visible' : 'hidden';
      const cash = num($('[data-pos-cash]').value);
      $('[data-change-row]').hidden = !(cashOn && cash);
      $('[data-pos-change]').textContent = money(Math.max(0, cash - t.total));
      const over = customer?.purchase_limit && t.total > customer.purchase_limit;
      $('[data-pos-limit]').hidden = !over;
      if (over) $('[data-pos-limit]').textContent = `Supera el límite por compra del cliente (${money(customer.purchase_limit)}).`;
      $('[data-pos-confirm]').disabled = !cart.length || over;
      $('[data-pos-confirm]').textContent = cart.length ? 'Cobrar ' + money(t.total) : 'Cobrar';
    }
    linesEl.addEventListener('click', e => {
      const d = e.target.closest('[data-dec]'), u = e.target.closest('[data-inc]');
      if (d) { const l = cart[+d.dataset.dec]; l.qty--; if (l.qty <= 0) cart.splice(+d.dataset.dec, 1); }
      if (u) { const l = cart[+u.dataset.inc]; if (l.qty < l.stock) l.qty++; }
      render();
    });
    $$('[data-pos-discount], [data-pos-cash]').forEach(i => i.addEventListener('input', render));
    $$('input[name=pos_pay]').forEach(i => i.addEventListener('change', render));
    $('[data-pos-wholesale]').addEventListener('change', render);
    $('[data-pos-clear]').addEventListener('click', () => { if (!cart.length || confirm('¿Vaciar la venta?')) { cart = []; render(); } });

    // Cliente
    const ci = $('[data-pos-customer]'), cr = $('[data-pos-cust-res]');
    let ct;
    ci.addEventListener('input', () => {
      customer = null; render(); clearTimeout(ct);
      ct = setTimeout(async () => {
        if (ci.value.trim().length < 2) { cr.innerHTML = ''; return; }
        const r = await fetch(`${pos.dataset.customers}?q=${encodeURIComponent(ci.value)}`, { headers: { Accept: 'application/json' } });
        const list = await r.json();
        cr.innerHTML = list.map((c, i) => `<button type="button" data-c="${i}"><b>${esc(c.name)}</b>${c.is_wholesale ? ' <span class="pill acc">Mayorista</span>' : ''}${c.purchase_limit ? ` <span class="pill warn">Límite ${money(c.purchase_limit)}</span>` : ''} · ${esc(c.email || '')} ${esc(c.phone || '')}</button>`).join('');
        cr.onclick = e => {
          const b = e.target.closest('[data-c]'); if (!b) return;
          customer = list[+b.dataset.c]; ci.value = customer.name;
          $('[data-pos-phone]').value = customer.phone || ''; $('[data-pos-doc]').value = customer.document || '';
          if (customer.is_wholesale) { $('[data-pos-wholesale]').checked = true; toast('Cliente mayorista: se aplica el precio mayorista'); }
          if (customer.purchase_limit) toast('Este cliente tiene un límite por compra de ' + money(customer.purchase_limit));
          cr.innerHTML = ''; render();
        };
      }, 220);
    });

    $('[data-pos-confirm]').addEventListener('click', async () => {
      const btn = $('[data-pos-confirm]'); btn.disabled = true;
      const t = totals();
      const body = {
        items: cart.map(l => ({ variant_id: l.id, qty: l.qty })),
        payment_method: $('input[name=pos_pay]:checked').value,
        customer_id: customer?.id || null,
        customer_name: customer ? null : ci.value.trim() || null,
        customer_phone: $('[data-pos-phone]').value.trim() || null,
        customer_document: $('[data-pos-doc]').value.trim() || null,
        discount: t.disc,
        cash_received: num($('[data-pos-cash]').value) || null,
        wholesale: $('[data-pos-wholesale]').checked,
      };
      try {
        const r = await fetch(pos.dataset.store, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json', Accept: 'application/json' }, body: JSON.stringify(body) });
        const d = await r.json();
        if (!r.ok) throw new Error(d.message || 'No se pudo registrar la venta.');
        $('[data-done-text]').innerHTML = `Comprobante <b>${esc(d.number)}</b> por <b>${esc(d.total)}</b>. El stock ya se descontó.`;
        $('[data-done-ticket]').href = d.ticket;
        openModal($('#pos-done'));
        cart = []; customer = null; ci.value = ''; $('[data-pos-phone]').value = ''; $('[data-pos-doc]').value = '';
        $('[data-pos-discount]').value = ''; $('[data-pos-cash]').value = ''; $('[data-pos-wholesale]').checked = false;
        render(); load();
      } catch (err) { toast(err.message, true); btn.disabled = false; }
    });
    $('#pos-done').addEventListener('click', e => { if (e.target.closest('[data-close]')) closeModal($('#pos-done')); });

    load(); render();
  }
})();
