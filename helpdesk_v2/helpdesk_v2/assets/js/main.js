// HelpDesk OS — main.js

// ── Mobile Nav Toggle ──
function toggleMobileNav() {
    document.getElementById('mobile-nav').classList.toggle('open');
}
document.addEventListener('click', function(e) {
    const nav = document.getElementById('mobile-nav');
    const ham = document.querySelector('.hamburger');
    if (nav && ham && !nav.contains(e.target) && !ham.contains(e.target)) {
        nav.classList.remove('open');
    }
});

// ── Notification Bell ──
function toggleNotif() {
    const dd = document.getElementById('notif-dropdown');
    if (!dd) return;
    dd.classList.toggle('open');
    if (dd.classList.contains('open')) loadNotifications();
}
document.addEventListener('click', function(e) {
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
                list.innerHTML = '<div class="notif-empty">🎉 No new notifications</div>';
                return;
            }
            list.innerHTML = data.map(n => `
                <div class="notif-item ${n.is_read == 0 ? 'unread' : ''}" onclick="openNotif(${n.id}, '${n.link}')">
                    <span class="notif-icon">${n.icon}</span>
                    <div class="notif-text">
                        <div class="notif-title">${n.title}</div>
                        <div>${n.message}</div>
                        <div class="notif-time">${n.time_ago}</div>
                    </div>
                </div>`).join('');
        })
        .catch(() => {
            const list = document.getElementById('notif-list');
            if (list) list.innerHTML = '<div class="notif-empty">Could not load notifications</div>';
        });
}

function openNotif(id, link) {
    fetch('/Helpdesk/api/notifications.php?action=read&id=' + id);
    updateBadge(-1);
    if (link) window.location.href = link;
}

function markAllRead() {
    fetch('/Helpdesk/api/notifications.php?action=read_all')
        .then(() => {
            loadNotifications();
            const badge = document.getElementById('notif-badge');
            if (badge) { badge.textContent = '0'; badge.classList.add('hidden'); }
        });
}

function updateBadge(delta) {
    const badge = document.getElementById('notif-badge');
    if (!badge) return;
    let count = parseInt(badge.textContent) + delta;
    if (count <= 0) { badge.textContent = '0'; badge.classList.add('hidden'); }
    else { badge.textContent = count; badge.classList.remove('hidden'); }
}

// ── Live Polling — check new notifications every 30 seconds ──
function pollNotifications() {
    fetch('/Helpdesk/api/notifications.php?action=count')
        .then(r => r.json())
        .then(data => {
            const badge = document.getElementById('notif-badge');
            if (!badge) return;
            if (data.count > 0) {
                badge.textContent = data.count;
                badge.classList.remove('hidden');
                // Show toast for new notification
                if (data.count > parseInt(badge.dataset.prev || 0)) showToast(data.latest);
                badge.dataset.prev = data.count;
            } else {
                badge.classList.add('hidden');
            }
        }).catch(() => {});
}

// ── Toast Notification ──
function showToast(message) {
    const existing = document.getElementById('toast-notif');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.id = 'toast-notif';
    toast.style.cssText = `
        position:fixed; bottom:20px; right:20px; z-index:9999;
        background:#1e3a5f; color:#fff; padding:12px 18px;
        border-radius:8px; font-size:13px; max-width:280px;
        box-shadow:0 4px 16px rgba(0,0,0,.2);
        display:flex; align-items:center; gap:10px;
        animation: slideIn .3s ease;
    `;
    toast.innerHTML = `<span style="font-size:18px">🔔</span><span>${message || 'New notification!'}</span><button onclick="this.parentElement.remove()" style="background:none;border:none;color:#fff;cursor:pointer;font-size:16px;margin-left:8px">×</button>`;

    const style = document.createElement('style');
    style.textContent = '@keyframes slideIn { from { transform: translateY(20px); opacity:0 } to { transform: translateY(0); opacity:1 } }';
    document.head.appendChild(style);
    document.body.appendChild(toast);

    setTimeout(() => { if (toast.parentElement) toast.remove(); }, 5000);
}

// Start polling after page load
document.addEventListener('DOMContentLoaded', function() {
    // Auto dismiss alerts
    document.querySelectorAll('.alert').forEach(a => {
        setTimeout(() => { a.style.opacity = '0'; a.style.transition = 'opacity .4s'; setTimeout(() => a.remove(), 400); }, 4000);
    });

    // Start polling if user is logged in
    const badge = document.getElementById('notif-badge');
    if (badge) {
        badge.dataset.prev = badge.textContent;
        setInterval(pollNotifications, 30000);
    }
});
