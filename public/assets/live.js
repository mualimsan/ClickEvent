// Pembaruan data real-time: cek /api/live tiap beberapa detik, lalu jalankan handler
// halaman untuk bagian data yang berubah (att, souv, inv, evt, emp, stock, alloc, users).
window.Live = (function () {
  const INTERVAL = 3000;
  const handlers = [];
  let reloadKeys = null, pendingReload = false, last = null, lastInteract = 0, timer = null, busy = false;

  ['keydown', 'pointerdown', 'input'].forEach(ev => addEventListener(ev, () => { lastInteract = Date.now(); }, true));

  function on(keys, fn) { handlers.push({ keys, fn }); }
  function reloadOn(keys) { reloadKeys = keys; }

  // Aman untuk reload kalau pengguna tidak sedang mengisi/memilih sesuatu.
  function idle() {
    const a = document.activeElement;
    if (a && /^(INPUT|TEXTAREA|SELECT)$/.test(a.tagName)) return false;
    if (document.querySelector('.modal.show, .dropdown-menu.show')) return false;
    if (document.querySelector('main input[type=checkbox]:checked')) return false;
    for (const el of document.querySelectorAll('main input:not([type=hidden]):not([type=checkbox]):not([type=radio]), main textarea')) if (el.value !== el.defaultValue) return false;
    for (const s of document.querySelectorAll('main select')) { const d = [...s.options].findIndex(o => o.defaultSelected); if (s.selectedIndex !== (d < 0 ? 0 : d)) return false; }
    return Date.now() - lastInteract > 8000;
  }
  function reload() {
    try { sessionStorage.setItem('liveScroll', JSON.stringify({ u: location.href, y: scrollY })); } catch (e) {}
    location.reload();
  }
  // Ganti elemen [data-live="nama"] dengan versi terbaru dari server tanpa reload halaman.
  async function swap(names) {
    const r = await fetch(location.href, { headers: { 'X-Live': '1' }, cache: 'no-store' });
    if (r.redirected && new URL(r.url).pathname.endsWith('/login')) { location.href = (window.BASE||'') + '/login'; return; }
    const doc = new DOMParser().parseFromString(await r.text(), 'text/html');
    names.forEach(n => {
      const fresh = doc.querySelector('[data-live="' + n + '"]'), old = document.querySelector('[data-live="' + n + '"]');
      if (fresh && old) { const node = document.importNode(fresh, true); node.classList.add('live-swapped'); old.replaceWith(node); }
    });
  }
  function notice(msg) {
    if (document.getElementById('liveNotice')) return;
    const bar = document.createElement('div');
    bar.id = 'liveNotice'; bar.className = 'live-notice';
    bar.innerHTML = '<i class="bi bi-arrow-repeat"></i><span></span><button type="button">Muat ulang</button><button type="button" class="ln-x" aria-label="Tutup">&times;</button>';
    bar.querySelector('span').textContent = msg;
    bar.querySelector('button').onclick = reload;
    bar.querySelector('.ln-x').onclick = () => bar.remove();
    document.body.appendChild(bar);
  }

  async function tick() {
    if (busy || document.visibilityState !== 'visible') return;
    busy = true;
    try {
      const r = await fetch((window.BASE||'')+'/api/live', { cache: 'no-store', headers: { Accept: 'application/json' } });
      if (r.redirected && new URL(r.url).pathname.endsWith('/login')) { location.href = (window.BASE||'') + '/login'; return; }
      if (!r.ok) return;
      const v = await r.json();
      if (last) {
        const changed = Object.keys(v).filter(k => v[k] !== last[k]);
        if (changed.length) {
          for (const h of handlers) if (h.keys.some(k => changed.includes(k))) { try { await h.fn(changed); } catch (e) {} }
          if (reloadKeys && reloadKeys.some(k => changed.includes(k))) pendingReload = true;
        }
      }
      last = v;
      if (pendingReload && idle()) reload();
    } catch (e) {} finally { busy = false; }
  }
  function start() {
    try {
      const s = JSON.parse(sessionStorage.getItem('liveScroll') || 'null');
      sessionStorage.removeItem('liveScroll');
      if (s && s.u === location.href) scrollTo(0, s.y);
    } catch (e) {}
    tick();
    timer = setInterval(tick, INTERVAL);
    document.addEventListener('visibilitychange', () => { if (document.visibilityState === 'visible') tick(); });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start); else start();
  return { on, reloadOn, swap, notice, reload };
})();
