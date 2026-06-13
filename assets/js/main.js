// HelpDesk OS — main.js v3

// ── Sidebar Toggle (Mobile) ──
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('open');
    document.querySelector('.sidebar-overlay').classList.toggle('show');
}
document.addEventListener('DOMContentLoaded', function () {
    const overlay = document.querySelector('.sidebar-overlay');
    if (overlay) overlay.addEventListener('click', toggleSidebar);
});

// ── Notification Bell ──
function toggleNotif() {
    const dd = document.getElementById('notif-dropdown');
    if (!dd) return;
    dd.classList.toggle('open');
    if (dd.classList.contains('open')) loadNotifications();
}
document.addEventListener('click', function (e) {
    const wrap = document.querySelector('.notif-wrap');
    if (wrap && !wrap.contains(e.target)) {
        const dd = document.getElementById('notif-dropdown');
        if (dd) dd.classList.remove('open');
    }
});

function loadNotifications() {
    fetch('/Helpdesk/api/notifications.php?action=list')
        .then(r => r.json())
        .then(data => {
            const list = document.getElementById('notif-list');
            if (!list) return;
            if (!data.length) {
                list.innerHTML = '<div class="notif-empty">🎉 All caught up!</div>';
                return;
            }
            list.innerHTML = data.slice(0, 8).map(n => `
                <div class="notif-item ${n.is_read == 0 ? 'unread' : ''}" onclick="openNotif(${n.id},'${n.link}')">
                    <span class="ni-icon">${n.icon}</span>
                    <div>
                        <div class="ni-title">${n.title}</div>
                        <div style="color:var(--muted);font-size:11px;margin-top:2px">${n.message}</div>
                        <div class="ni-time">${n.time_ago}</div>
                    </div>
                </div>`).join('');
        })
        .catch(() => {
            const list = document.getElementById('notif-list');
            if (list) list.innerHTML = '<div class="notif-empty">Could not load</div>';
        });
}

function openNotif(id, link) {
    fetch('/Helpdesk/api/notifications.php?action=read&id=' + id);
    const badge = document.getElementById('notif-badge');
    if (badge) {
        let c = parseInt(badge.textContent) - 1;
        if (c <= 0) { badge.textContent = '0'; badge.classList.add('hidden'); }
        else badge.textContent = c;
    }
    if (link) window.location.href = link;
}

function markAllRead() {
    fetch('/Helpdesk/api/notifications.php?action=read_all').then(() => {
        loadNotifications();
        const badge = document.getElementById('notif-badge');
        if (badge) { badge.textContent = '0'; badge.classList.add('hidden'); }
    });
}

// ── Live Polling every 30s ──
let prevCount = 0;
function pollNotifications() {
    fetch('/Helpdesk/api/notifications.php?action=count')
        .then(r => r.json())
        .then(data => {
            const badge = document.getElementById('notif-badge');
            if (!badge) return;
            if (data.count > 0) {
                badge.textContent = data.count;
                badge.classList.remove('hidden');
                if (data.count > prevCount && prevCount > 0) showToast(data.latest || 'New notification!');
            } else {
                badge.classList.add('hidden');
            }
            prevCount = data.count;
        }).catch(() => { });
}

// ── Toast ──
function showToast(msg, type = 'info') {
    const old = document.getElementById('hd-toast');
    if (old) old.remove();
    const icons = { info: '🔔', success: '✅', error: '⚠️', warning: '⚡' };
    const t = document.createElement('div');
    t.id = 'hd-toast';
    t.className = 'toast';
    t.innerHTML = `<span style="font-size:18px">${icons[type] || '🔔'}</span><span style="flex:1">${msg}</span><button class="toast-close" onclick="this.parentElement.remove()">×</button>`;
    document.body.appendChild(t);
    setTimeout(() => { if (t.parentElement) t.remove(); }, 5000);
}

// ── Password Strength ──
function checkStrength(val) {
    const bar = document.getElementById('pwd-bar');
    const hint = document.getElementById('pwd-hint');
    if (!bar) return;
    let s = 0;
    if (val.length >= 6) s++;
    if (val.length >= 10) s++;
    if (/[A-Z]/.test(val)) s++;
    if (/[0-9]/.test(val)) s++;
    if (/[^A-Za-z0-9]/.test(val)) s++;
    const lvl = [
        { w: '0%', c: '#e2e8f0', t: '' },
        { w: '25%', c: '#dc2626', t: 'Weak' },
        { w: '50%', c: '#f97316', t: 'Fair' },
        { w: '75%', c: '#2563eb', t: 'Good' },
        { w: '100%', c: '#16a34a', t: 'Strong ✅' },
    ][Math.min(s, 4)];
    bar.style.width = lvl.w; bar.style.background = lvl.c;
    if (hint) { hint.textContent = lvl.t; hint.style.color = lvl.c; }
}

// ── Init ──
document.addEventListener('DOMContentLoaded', function () {
    // Auto dismiss alerts
    document.querySelectorAll('.alert').forEach(a => {
        setTimeout(() => { a.style.transition = 'opacity .4s'; a.style.opacity = '0'; setTimeout(() => a.remove(), 400); }, 4500);
    });
    // Active nav item
    document.querySelectorAll('.nav-item').forEach(item => {
        if (item.href && window.location.href.includes(item.getAttribute('href'))) {
            item.classList.add('active');
        }
    });
    // Start polling
    const badge = document.getElementById('notif-badge');
    if (badge) {
        prevCount = parseInt(badge.textContent) || 0;
        setInterval(pollNotifications, 30000);
    }
});
