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
})();
