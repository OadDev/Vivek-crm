(function(){
  'use strict';

  // Theme toggle -----------------------------------------------------------
  function applyTheme(theme){
    document.documentElement.setAttribute('data-theme', theme);
    var icon = document.getElementById('themeIcon');
    if (icon) icon.className = theme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill';
    localStorage.setItem('cms-theme', theme);
  }
  var themeBtn = document.getElementById('themeToggleBtn');
  if (themeBtn) {
    themeBtn.addEventListener('click', function(){
      var current = document.documentElement.getAttribute('data-theme');
      applyTheme(current === 'dark' ? 'light' : 'dark');
    });
  }
  applyTheme(localStorage.getItem('cms-theme') || 'light');

  // Mobile sidebar -----------------------------------------------------------
  var sidebar = document.getElementById('appSidebar');
  var overlay = document.getElementById('sidebarOverlay');
  var burger = document.getElementById('burgerBtn');
  if (burger) burger.addEventListener('click', function(){
    sidebar.classList.add('show'); overlay.classList.add('show');
  });
  if (overlay) overlay.addEventListener('click', function(){
    sidebar.classList.remove('show'); overlay.classList.remove('show');
  });

  // Bootstrap tooltips -------------------------------------------------------
  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el){
    new bootstrap.Tooltip(el);
  });

  // Flash messages -> toasts --------------------------------------------------
  window.showToast = function(message, type){
    type = type || 'primary';
    var iconMap = {success:'bi-check-circle-fill', danger:'bi-x-circle-fill', warning:'bi-exclamation-triangle-fill', primary:'bi-info-circle-fill'};
    var colorMap = {success:'var(--color-success)', danger:'var(--color-danger)', warning:'var(--color-warning)', primary:'var(--color-primary)'};
    var el = document.createElement('div');
    el.className = 'toast align-items-center border-0';
    el.setAttribute('role', 'alert');
    el.innerHTML = '<div class="d-flex"><div class="toast-body d-flex align-items-center gap-2">' +
      '<i class="bi ' + (iconMap[type] || iconMap.primary) + '" style="color:' + (colorMap[type] || colorMap.primary) + ';font-size:16px;"></i>' +
      '<span style="color:var(--text-primary);">' + message + '</span></div>' +
      '<button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button></div>';
    document.getElementById('toastContainer').appendChild(el);
    var t = new bootstrap.Toast(el, {delay: 4000});
    t.show();
    el.addEventListener('hidden.bs.toast', function(){ el.remove(); });
  };

  document.querySelectorAll('.toast-flash').forEach(function(el){
    showToast(el.getAttribute('data-flash-message'), el.getAttribute('data-flash-type'));
  });

  // Confirm-before-submit for delete forms ------------------------------------
  document.querySelectorAll('form[data-confirm]').forEach(function(form){
    form.addEventListener('submit', function(e){
      if (!confirm(form.getAttribute('data-confirm'))) {
        e.preventDefault();
      }
    });
  });

  // WhatsApp buttons (Contacts list + contact profile page) ------------------
  // The href is already the real whatsapp:// link (rendered server-side --
  // see contacts/_row.blade.php etc.) so we can navigate the instant this
  // click fires. Waiting on a fetch() first (as an earlier version of this
  // did) breaks the redirect on real mobile browsers: navigating to a
  // custom scheme like whatsapp:// is only honored while it's still
  // "inside" the original tap/click's user-activation window, which an
  // awaited network request has usually already used up by the time its
  // .then() runs. Logging the click is done separately, in parallel,
  // without being waited on.
  //
  // There's deliberately no "fell back to the web version" logic here
  // anymore: an earlier version tried to detect whether the app opened
  // (via a timeout + visibilitychange) and, if not, redirected this same
  // tab to wa.me. That detection is unreliable on real phones -- it can
  // fire even when the app did open -- and when it misfires it replaces
  // this tab's page with the wa.me page, so returning from WhatsApp lands
  // on that instead of the CRM. Since WhatsApp is expected to be installed
  // for this use case, doing nothing when the app link fails (leaving the
  // user right where they were) is the safer default.
  document.body.addEventListener('click', function (e) {
    var waBtn = e.target.closest('.js-whatsapp-btn');
    if (!waBtn) return;

    e.preventDefault();

    var appLink = waBtn.getAttribute('href');
    var logUrl = waBtn.dataset.logUrl;

    if (logUrl) {
      if (navigator.sendBeacon) {
        var body = new FormData();
        body.append('_token', window.APP_CSRF);
        navigator.sendBeacon(logUrl, body);
      } else {
        fetch(logUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': window.APP_CSRF }, keepalive: true }).catch(function () {});
      }
    }

    window.location.href = appLink;
  });
})();
