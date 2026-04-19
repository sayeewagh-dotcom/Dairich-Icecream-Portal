/* ============================================
   DAIRICH — Admin Portal Shared JS
   admin-portal.js
   ============================================ */

/* ── Auth guard + topbar injector ── */
function adminInit(activePage) {
  const token = localStorage.getItem('dairich_admin_token');
  const name  = localStorage.getItem('dairich_admin_name') || 'Admin';
  if (!token) { window.location.href = 'admin_login.html'; return; }

  const nav = document.getElementById('adminNav');
  if (!nav) return;

  const pages = [
    { id:'dashboard',    label:'Dashboard',    href:'admin_dashboard.html' },
    { id:'distributors', label:'Distributors', href:'admin_distributors.html' },
    { id:'orders',       label:'Orders',       href:'admin_orders.html' },
    { id:'enquiries',    label:'Enquiries',    href:'admin_enquiries.html' },
    { id:'products',     label:'Products',     href:'admin_products.html' },
    { id:'feedback',     label:'Feedback',     href:'admin_feedback.html' },
  ];

  nav.innerHTML = `
    <a href="admin_dashboard.html" class="pbar-logo" style="color:var(--navy);">Dairich <span>Admin</span></a>
    <ul class="pbar-nav">
      ${pages.map(p=>`<li><a href="${p.href}" ${p.id===activePage?'class="active"':''}>${p.label}</a></li>`).join('')}
    </ul>
    <div class="pbar-right">
      <div class="pbar-user">
        <div class="pbar-avatar" style="background:var(--navy);">${name.charAt(0).toUpperCase()}</div>
        <span>${name}</span>
      </div>
      <a href="#" class="pbar-logout" id="adminLogoutBtn">Logout</a>
    </div>
  `;

  document.getElementById('adminLogoutBtn').addEventListener('click', async function(e) {
    e.preventDefault();
    await fetch('api/admin/alogout.php', { method:'POST', headers:{'Authorization':'Bearer '+token} });
    localStorage.removeItem('dairich_admin_token');
    localStorage.removeItem('dairich_admin_name');
    localStorage.removeItem('dairich_admin_role');
    window.location.href = 'admin_login.html';
  });

  return token;
}

/* ── Status pill helper ── */
function pill(status) {
  const map = {
    pending:'pill-pending', confirmed:'pill-confirmed', dispatched:'pill-dispatched',
    delivered:'pill-delivered', cancelled:'pill-cancelled', processing:'pill-confirmed',
    new:'pill-pending', reviewed:'pill-confirmed', contacted:'pill-dispatched', closed:'pill-delivered',
    in_transit:'pill-confirmed', out_for_delivery:'pill-dispatched', failed:'pill-cancelled'
  };
  return `<span class="pill ${map[status]||'pill-pending'}">${status||'—'}</span>`;
}

/* ── Toast notification ── */
function toast(msg, type='ok') {
  const t = document.createElement('div');
  t.className = `alert alert-${type==='ok'?'ok':'err'}`;
  t.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:9999;min-width:300px;animation:fadeIn 0.3s;';
  t.innerHTML = (type==='ok'?'✅ ':'⚠️ ') + msg;
  document.body.appendChild(t);
  setTimeout(() => t.remove(), 3500);
}

/* ── Confirm dialog ── */
function confirmAction(msg) {
  return window.confirm(msg);
}
