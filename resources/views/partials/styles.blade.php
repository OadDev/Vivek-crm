/* ==========================================================================
   Communication Management System — Frontend Prototype
   Design tokens
   ========================================================================== */
:root{
  --font-sans:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;

  --color-primary:#4F46E5;
  --color-primary-dark:#4338CA;
  --color-primary-light:#EEF2FF;
  --color-success:#16A34A;
  --color-success-light:#F0FDF4;
  --color-warning:#D97706;
  --color-warning-light:#FFFBEB;
  --color-danger:#DC2626;
  --color-danger-light:#FEF2F2;
  --color-info:#0891B2;
  --color-info-light:#ECFEFF;
  --color-whatsapp:#128C7E;
  --color-whatsapp-light:#E7F7F4;

  --bg-body:#F5F6FA;
  --bg-surface:#FFFFFF;
  --bg-surface-2:#FAFBFC;
  --bg-sidebar:#12131C;
  --bg-sidebar-hover:#1D1F2E;
  --bg-sidebar-active:#262A3D;

  --text-primary:#111827;
  --text-secondary:#667085;
  --text-muted:#98A2B3;
  --text-on-dark:#E4E5EA;
  --text-on-dark-muted:#8A8D9E;

  --border-color:#E7E9F0;
  --border-color-strong:#D0D5DD;

  --radius-lg:16px;
  --radius-md:12px;
  --radius-sm:8px;
  --radius-xs:6px;

  --shadow-xs:0 1px 2px rgba(16,24,40,.05);
  --shadow-sm:0 1px 3px rgba(16,24,40,.08),0 1px 2px rgba(16,24,40,.04);
  --shadow-md:0 4px 14px rgba(16,24,40,.08);
  --shadow-lg:0 12px 32px rgba(16,24,40,.14);

  --sidebar-w:264px;
  --topbar-h:68px;
  --transition-fast:.16s cubic-bezier(.4,0,.2,1);
  --transition-med:.28s cubic-bezier(.4,0,.2,1);
}

[data-theme="dark"]{
  --bg-body:#0D0E14;
  --bg-surface:#171923;
  --bg-surface-2:#1D1F2B;
  --text-primary:#F0F1F5;
  --text-secondary:#9AA0B3;
  --text-muted:#6C7286;
  --border-color:#282C3B;
  --border-color-strong:#343850;
  --color-primary-light:#1E1D3D;
  --color-success-light:#0F2419;
  --color-warning-light:#2A2013;
  --color-danger-light:#2A1717;
  --color-info-light:#0E2530;
  --color-whatsapp-light:#0E211D;
  --shadow-sm:0 1px 3px rgba(0,0,0,.35);
  --shadow-md:0 4px 14px rgba(0,0,0,.4);
  --shadow-lg:0 16px 40px rgba(0,0,0,.5);
}

*{box-sizing:border-box;}
html,body{height:100%;}
body{
  font-family:var(--font-sans);
  background:var(--bg-body);
  color:var(--text-primary);
  font-size:14.5px;
  overflow-x:hidden;
  transition:background var(--transition-med),color var(--transition-med);
}
::-webkit-scrollbar{width:8px;height:8px;}
::-webkit-scrollbar-track{background:transparent;}
::-webkit-scrollbar-thumb{background:var(--border-color-strong);border-radius:10px;}
::-webkit-scrollbar-thumb:hover{background:var(--text-muted);}

a{text-decoration:none;color:inherit;}
h1,h2,h3,h4,h5,h6{font-weight:700;letter-spacing:-.01em;color:var(--text-primary);}
small,.text-muted-c{color:var(--text-secondary)!important;}
.fw-600{font-weight:600;}
.fw-700{font-weight:700;}

/* ==========================================================================
   Page loader (top progress bar)
   ========================================================================== */
.page-loader{position:fixed;top:0;left:0;height:3px;width:0;background:var(--color-primary);z-index:2000;transition:width .25s ease,opacity .3s ease;box-shadow:0 0 8px rgba(79,70,229,.6);}
.page-loader.active{width:70%;}
.page-loader.done{width:100%;opacity:0;}

/* ==========================================================================
   Sidebar
   ========================================================================== */
.app-sidebar{
  position:fixed;top:0;left:0;bottom:0;width:var(--sidebar-w);
  background:var(--bg-sidebar);
  display:flex;flex-direction:column;
  z-index:1050;
  transition:transform var(--transition-med);
}
.sidebar-brand{
  display:flex;align-items:center;gap:10px;
  padding:20px 22px;
  border-bottom:1px solid rgba(255,255,255,.06);
  flex-shrink:0;
}
.sidebar-brand .brand-mark{
  width:36px;height:36px;border-radius:10px;background:var(--color-primary);
  display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:16px;flex-shrink:0;
}
.sidebar-brand .brand-text{color:#fff;font-weight:700;font-size:15.5px;line-height:1.2;}
.sidebar-brand .brand-sub{color:var(--text-on-dark-muted);font-size:11px;font-weight:500;}

.sidebar-nav{flex:1;overflow-y:auto;padding:14px 12px;}
.sidebar-nav .nav-section-label{
  color:var(--text-on-dark-muted);font-size:10.5px;font-weight:700;letter-spacing:.08em;
  text-transform:uppercase;padding:14px 12px 8px;
}
.sidebar-link{
  display:flex;align-items:center;gap:12px;
  padding:10px 12px;border-radius:var(--radius-sm);
  color:var(--text-on-dark);font-weight:500;font-size:14px;
  margin-bottom:2px;cursor:pointer;position:relative;
  transition:background var(--transition-fast),color var(--transition-fast),transform var(--transition-fast);
  border:1px solid transparent;
}
.sidebar-link i{font-size:17px;width:20px;text-align:center;color:var(--text-on-dark-muted);transition:color var(--transition-fast);}
.sidebar-link:hover{background:var(--bg-sidebar-hover);transform:translateX(2px);}
.sidebar-link:hover i{color:#fff;}
.sidebar-link.active{background:var(--bg-sidebar-active);color:#fff;border-color:rgba(79,70,229,.35);}
.sidebar-link.active i{color:var(--color-primary);}
.sidebar-link.active::before{
  content:'';position:absolute;left:-12px;top:8px;bottom:8px;width:3px;border-radius:3px;background:var(--color-primary);
}
.sidebar-link .badge-count{
  margin-left:auto;background:var(--color-primary);color:#fff;font-size:10.5px;font-weight:700;
  border-radius:20px;padding:1px 8px;
}
.sidebar-link.logout-link{color:#FCA5A5;}
.sidebar-link.logout-link i{color:#FCA5A5;}
.sidebar-link.logout-link:hover{background:rgba(220,38,38,.15);}

.sidebar-footer{padding:14px 18px;border-top:1px solid rgba(255,255,255,.06);flex-shrink:0;}
.sidebar-user{display:flex;align-items:center;gap:10px;}
.sidebar-user .avatar-sm{width:36px;height:36px;border-radius:50%;}
.sidebar-user .u-name{color:#fff;font-size:13.5px;font-weight:600;}
.sidebar-user .u-role{color:var(--text-on-dark-muted);font-size:11.5px;}

.sidebar-overlay{
  position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1040;display:none;
  backdrop-filter:blur(2px);
}
.sidebar-overlay.show{display:block;animation:fadeIn .2s ease;}

/* ==========================================================================
   Topbar
   ========================================================================== */
.app-topbar{
  position:fixed;top:0;right:0;left:var(--sidebar-w);height:var(--topbar-h);
  background:var(--bg-surface);border-bottom:1px solid var(--border-color);
  display:flex;align-items:center;gap:14px;padding:0 22px;z-index:1030;
  transition:left var(--transition-med),background var(--transition-med),border-color var(--transition-med);
}
.topbar-burger{
  display:none;background:var(--bg-surface-2);border:1px solid var(--border-color);
  width:38px;height:38px;border-radius:var(--radius-sm);align-items:center;justify-content:center;
  color:var(--text-primary);font-size:18px;flex-shrink:0;cursor:pointer;
}
.topbar-search{
  flex:1;max-width:440px;position:relative;
}
.topbar-search input{
  width:100%;background:var(--bg-surface-2);border:1px solid var(--border-color);
  border-radius:var(--radius-md);padding:9px 14px 9px 38px;font-size:13.5px;color:var(--text-primary);
  transition:border-color var(--transition-fast),box-shadow var(--transition-fast),background var(--transition-fast);
}
.topbar-search input:focus{outline:none;border-color:var(--color-primary);box-shadow:0 0 0 3px var(--color-primary-light);background:var(--bg-surface);}
.topbar-search i{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:15px;}
.topbar-search kbd{
  position:absolute;right:10px;top:50%;transform:translateY(-50%);font-size:10.5px;color:var(--text-muted);
  background:var(--bg-body);border:1px solid var(--border-color);border-radius:5px;padding:1px 6px;
}
.topbar-spacer{flex:1;}
.topbar-actions{display:flex;align-items:center;gap:8px;}
.icon-btn{
  width:40px;height:40px;border-radius:50%;border:1px solid var(--border-color);background:var(--bg-surface);
  color:var(--text-secondary);display:flex;align-items:center;justify-content:center;font-size:17px;position:relative;
  cursor:pointer;transition:all var(--transition-fast);
}
.icon-btn:hover{background:var(--color-primary-light);color:var(--color-primary);border-color:var(--color-primary);transform:translateY(-1px);}
.icon-btn .dot-badge{
  position:absolute;top:6px;right:7px;width:8px;height:8px;border-radius:50%;background:var(--color-danger);
  border:2px solid var(--bg-surface);
}
.theme-toggle-btn i{transition:transform .4s ease;}
.theme-toggle-btn:active i{transform:rotate(180deg);}

.profile-chip{
  display:flex;align-items:center;gap:9px;padding:5px 10px 5px 5px;border-radius:50px;
  border:1px solid var(--border-color);background:var(--bg-surface);cursor:pointer;transition:all var(--transition-fast);
}
.profile-chip:hover{background:var(--bg-surface-2);border-color:var(--border-color-strong);}
.profile-chip img{width:32px;height:32px;border-radius:50%;}
.profile-chip .p-name{font-size:13px;font-weight:600;line-height:1.1;}
.profile-chip .p-role{font-size:11px;color:var(--text-secondary);}

/* ==========================================================================
   Main layout
   ========================================================================== */
.app-main{margin-left:var(--sidebar-w);padding-top:var(--topbar-h);min-height:100vh;transition:margin-left var(--transition-med);}
.main-inner{padding:26px 28px 60px;max-width:1520px;}

.page-section{display:none;animation:fadeSlideUp .35s ease both;}
.page-section.active{display:block;}
@keyframes fadeSlideUp{from{opacity:0;transform:translateY(10px);}to{opacity:1;transform:translateY(0);}}
@keyframes fadeIn{from{opacity:0;}to{opacity:1;}}

.page-header{display:flex;flex-wrap:wrap;align-items:flex-end;justify-content:space-between;gap:14px;margin-bottom:22px;}
.page-title{font-size:23px;font-weight:800;margin:0;}
.page-subtitle{color:var(--text-secondary);font-size:13.5px;margin-top:2px;}
.breadcrumb-c{display:flex;align-items:center;gap:6px;font-size:12.5px;color:var(--text-muted);margin-bottom:6px;}
.breadcrumb-c i{font-size:10px;}
.breadcrumb-c a{color:var(--text-secondary);font-weight:500;}
.breadcrumb-c a:hover{color:var(--color-primary);}
.breadcrumb-c .current{color:var(--text-primary);font-weight:600;}

/* ==========================================================================
   Cards & surfaces
   ========================================================================== */
.card-c{
  background:var(--bg-surface);border:1px solid var(--border-color);border-radius:var(--radius-lg);
  box-shadow:var(--shadow-xs);transition:box-shadow var(--transition-fast),transform var(--transition-fast),border-color var(--transition-med),background var(--transition-med);
}
.card-c.hoverable:hover{box-shadow:var(--shadow-md);transform:translateY(-3px);border-color:var(--border-color-strong);}
.card-c .card-c-body{padding:20px 22px;}

.stat-card{padding:20px 20px;display:flex;flex-direction:column;gap:14px;position:relative;overflow:hidden;}
.stat-card .stat-icon{
  width:44px;height:44px;border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;
}
.stat-card .stat-value{font-size:26px;font-weight:800;line-height:1;}
.stat-card .stat-label{font-size:12.8px;color:var(--text-secondary);font-weight:500;margin-top:4px;}
.stat-card .stat-trend{font-size:11.5px;font-weight:700;display:inline-flex;align-items:center;gap:3px;padding:2px 8px;border-radius:20px;width:fit-content;}
.stat-trend.up{color:var(--color-success);background:var(--color-success-light);}
.stat-trend.down{color:var(--color-danger);background:var(--color-danger-light);}

.section-title-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;}
.section-title-row h5{font-size:15.5px;margin:0;font-weight:700;}

/* quick action */
.quick-action-card{
  display:flex;flex-direction:column;align-items:flex-start;gap:10px;padding:18px 16px;cursor:pointer;
}
.quick-action-card .qa-icon{
  width:42px;height:42px;border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;font-size:19px;
}
.quick-action-card .qa-label{font-weight:600;font-size:13.6px;}
.quick-action-card .qa-sub{font-size:11.6px;color:var(--text-secondary);}

/* timeline */
.timeline-item{display:flex;gap:14px;padding:13px 0;border-bottom:1px dashed var(--border-color);}
.timeline-item:last-child{border-bottom:none;}
.timeline-dot{
  width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0;
}
.timeline-text{font-size:13.4px;font-weight:500;}
.timeline-time{font-size:11.6px;color:var(--text-muted);margin-top:2px;}

/* charts (pure css) */
.bar-chart{display:flex;align-items:flex-end;gap:10px;height:170px;padding-top:10px;}
.bar-chart .bar-col{flex:1;display:flex;flex-direction:column;align-items:center;gap:8px;height:100%;justify-content:flex-end;}
.bar-chart .bar{
  width:100%;max-width:30px;border-radius:6px 6px 3px 3px;background:var(--color-primary);
  transition:transform .3s ease,opacity .3s ease;position:relative;
}
.bar-chart .bar:hover{opacity:.85;transform:scaleY(1.02);}
.bar-chart .bar-lbl{font-size:11px;color:var(--text-muted);font-weight:600;}
.donut-wrap{display:flex;align-items:center;gap:22px;flex-wrap:wrap;justify-content:center;}
.donut{
  width:140px;height:140px;border-radius:50%;position:relative;flex-shrink:0;
  background:conic-gradient(var(--color-primary) 0% 42%, var(--color-success) 42% 68%, var(--color-warning) 68% 86%, var(--color-danger) 86% 100%);
}
.donut::after{content:'';position:absolute;inset:18px;border-radius:50%;background:var(--bg-surface);}
.donut-center{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;}
.donut-center b{font-size:20px;}
.donut-center span{font-size:10.5px;color:var(--text-secondary);}
.legend-item{display:flex;align-items:center;gap:8px;font-size:12.6px;margin-bottom:8px;}
.legend-dot{width:10px;height:10px;border-radius:3px;flex-shrink:0;}

/* ==========================================================================
   Buttons
   ========================================================================== */
.btn{border-radius:var(--radius-sm);font-weight:600;font-size:13.4px;padding:.55rem 1rem;transition:all var(--transition-fast);}
.btn-sm{padding:.36rem .75rem;font-size:12.6px;border-radius:var(--radius-xs);}
.btn-primary-c{background:var(--color-primary);border:1px solid var(--color-primary);color:#fff;}
.btn-primary-c:hover{background:var(--color-primary-dark);border-color:var(--color-primary-dark);color:#fff;transform:translateY(-1px);box-shadow:var(--shadow-sm);}
.btn-outline-c{background:transparent;border:1px solid var(--border-color-strong);color:var(--text-primary);}
.btn-outline-c:hover{background:var(--bg-surface-2);border-color:var(--text-secondary);}
.btn-light-c{background:var(--bg-surface-2);border:1px solid var(--border-color);color:var(--text-primary);}
.btn-light-c:hover{background:var(--border-color);}
.btn-success-c{background:var(--color-success);border:1px solid var(--color-success);color:#fff;}
.btn-success-c:hover{filter:brightness(.92);color:#fff;}
.btn-danger-c{background:var(--color-danger);border:1px solid var(--color-danger);color:#fff;}
.btn-danger-c:hover{filter:brightness(.92);color:#fff;}
.btn-whatsapp-c{background:var(--color-whatsapp);border:1px solid var(--color-whatsapp);color:#fff;}
.btn-whatsapp-c:hover{filter:brightness(.93);color:#fff;}
.btn-icon-sq{
  width:33px;height:33px;padding:0;display:inline-flex;align-items:center;justify-content:center;border-radius:var(--radius-xs);
  border:1px solid var(--border-color);background:var(--bg-surface);color:var(--text-secondary);
}
.btn-icon-sq:hover{background:var(--color-primary-light);color:var(--color-primary);border-color:var(--color-primary);}
.btn-icon-sq.danger:hover{background:var(--color-danger-light);color:var(--color-danger);border-color:var(--color-danger);}
.btn-icon-sq.success:hover{background:var(--color-success-light);color:var(--color-success);border-color:var(--color-success);}

/* ==========================================================================
   Forms / floating labels
   ========================================================================== */
.form-control,.form-select{
  border-radius:var(--radius-sm);border:1px solid var(--border-color-strong);font-size:13.6px;padding:.62rem .85rem;
  background:var(--bg-surface);color:var(--text-primary);
}
.form-control:focus,.form-select:focus{border-color:var(--color-primary);box-shadow:0 0 0 3px var(--color-primary-light);background:var(--bg-surface);color:var(--text-primary);}
.form-floating>label{color:var(--text-muted);font-size:13.4px;}
.form-floating>.form-control{padding-top:1.4rem;}
.form-label{font-size:12.8px;font-weight:600;color:var(--text-secondary);margin-bottom:5px;}
.input-group-text{background:var(--bg-surface-2);border-color:var(--border-color-strong);color:var(--text-muted);}

/* ==========================================================================
   Tables
   ========================================================================== */
.table-c{width:100%;border-collapse:separate;border-spacing:0;font-size:13.4px;}
.table-c thead th{
  background:var(--bg-surface-2);color:var(--text-secondary);font-weight:700;font-size:11.3px;
  text-transform:uppercase;letter-spacing:.04em;padding:12px 16px;border-bottom:1px solid var(--border-color);white-space:nowrap;
}
.table-c thead th:first-child{border-top-left-radius:var(--radius-md);}
.table-c thead th:last-child{border-top-right-radius:var(--radius-md);}
.table-c tbody td{padding:13px 16px;border-bottom:1px solid var(--border-color);vertical-align:middle;color:var(--text-primary);}
.table-c tbody tr{transition:background var(--transition-fast);}
.table-c tbody tr:hover{background:var(--bg-surface-2);}
.table-c tbody tr:last-child td{border-bottom:none;}
.table-responsive-c{overflow-x:auto;border-radius:var(--radius-md);border:1px solid var(--border-color);}
.avatar-circle{
  width:36px;height:36px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;
  font-weight:700;font-size:13px;color:#fff;flex-shrink:0;
}
.avatar-lg{width:88px;height:88px;font-size:28px;}
.avatar-md{width:52px;height:52px;font-size:17px;}

/* chips / badges */
.chip{display:inline-flex;align-items:center;gap:5px;font-size:11.3px;font-weight:700;padding:3px 10px;border-radius:20px;white-space:nowrap;}
.chip i{font-size:8px;}
.chip-success{background:var(--color-success-light);color:var(--color-success);}
.chip-warning{background:var(--color-warning-light);color:var(--color-warning);}
.chip-danger{background:var(--color-danger-light);color:var(--color-danger);}
.chip-info{background:var(--color-info-light);color:var(--color-info);}
.chip-neutral{background:var(--bg-surface-2);color:var(--text-secondary);border:1px solid var(--border-color);}
.chip-primary{background:var(--color-primary-light);color:var(--color-primary);}

/* ==========================================================================
   Toolbars / filters
   ========================================================================== */
.toolbar-c{display:flex;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:18px;}
.toolbar-search{position:relative;flex:1;min-width:200px;max-width:320px;}
.toolbar-search input{padding-left:36px;}
.toolbar-search i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);}
.filter-chip-group{display:flex;gap:6px;flex-wrap:wrap;}
.filter-chip-btn{
  border:1px solid var(--border-color-strong);background:var(--bg-surface);color:var(--text-secondary);font-size:12.4px;font-weight:600;
  padding:6px 13px;border-radius:20px;cursor:pointer;transition:all var(--transition-fast);
}
.filter-chip-btn:hover{border-color:var(--color-primary);color:var(--color-primary);}
.filter-chip-btn.active{background:var(--color-primary);border-color:var(--color-primary);color:#fff;}

/* ==========================================================================
   Gmail module
   ========================================================================== */
.gmail-shell{display:grid;grid-template-columns:200px 360px 1fr;gap:0;border:1px solid var(--border-color);border-radius:var(--radius-lg);overflow:hidden;background:var(--bg-surface);box-shadow:var(--shadow-xs);height:calc(100vh - var(--topbar-h) - 150px);min-height:560px;}
.gmail-folders{border-right:1px solid var(--border-color);background:var(--bg-surface-2);padding:16px 10px;overflow-y:auto;}
.gmail-folder-item{
  display:flex;align-items:center;gap:10px;padding:9px 12px;border-radius:var(--radius-sm);font-size:13.4px;font-weight:500;
  cursor:pointer;margin-bottom:2px;color:var(--text-secondary);transition:all var(--transition-fast);
}
.gmail-folder-item i{font-size:16px;width:18px;}
.gmail-folder-item:hover{background:var(--bg-surface);color:var(--text-primary);}
.gmail-folder-item.active{background:var(--color-primary-light);color:var(--color-primary);font-weight:700;}
.gmail-folder-item .cnt{margin-left:auto;font-size:11px;color:var(--text-muted);font-weight:700;}
.gmail-folder-item.active .cnt{color:var(--color-primary);}
.compose-btn-c{width:100%;margin-bottom:14px;}

.gmail-list-pane{border-right:1px solid var(--border-color);display:flex;flex-direction:column;overflow:hidden;}
.gmail-list-toolbar{padding:12px 14px;border-bottom:1px solid var(--border-color);flex-shrink:0;}
.gmail-list-scroll{flex:1;overflow-y:auto;}
.conv-item{
  display:flex;gap:11px;padding:13px 14px;border-bottom:1px solid var(--border-color);cursor:pointer;position:relative;
  transition:background var(--transition-fast);
}
.conv-item:hover{background:var(--bg-surface-2);}
.conv-item.selected{background:var(--color-primary-light);}
.conv-item.unread .conv-sender,.conv-item.unread .conv-subject{font-weight:700;color:var(--text-primary);}
.conv-item .conv-body{flex:1;min-width:0;}
.conv-top-row{display:flex;justify-content:space-between;align-items:center;gap:6px;}
.conv-sender{font-size:13.3px;font-weight:600;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.conv-time{font-size:11px;color:var(--text-muted);flex-shrink:0;}
.conv-subject{font-size:12.8px;color:var(--text-primary);margin-top:1px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.conv-preview{font-size:12.2px;color:var(--text-muted);margin-top:1px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.conv-item .unread-dot{width:8px;height:8px;border-radius:50%;background:var(--color-primary);position:absolute;left:4px;top:22px;}
.conv-star{color:var(--text-muted);font-size:15px;cursor:pointer;flex-shrink:0;}
.conv-star.active{color:#F5A623;}
.conv-star:hover{color:#F5A623;}

.gmail-preview-pane{display:flex;flex-direction:column;overflow:hidden;}
.gmail-preview-header{padding:16px 22px;border-bottom:1px solid var(--border-color);flex-shrink:0;}
.gmail-preview-body{flex:1;overflow-y:auto;padding:18px 22px;}
.gmail-preview-empty{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;color:var(--text-muted);gap:10px;}
.thread-message{border:1px solid var(--border-color);border-radius:var(--radius-md);margin-bottom:14px;overflow:hidden;}
.thread-message-head{display:flex;gap:12px;padding:14px 16px;background:var(--bg-surface-2);cursor:pointer;}
.thread-message-body{padding:16px;font-size:13.6px;line-height:1.7;color:var(--text-primary);border-top:1px solid var(--border-color);}
.reply-box{border-top:1px solid var(--border-color);padding:14px 22px 18px;flex-shrink:0;}
.rich-toolbar{display:flex;gap:2px;padding:6px;border:1px solid var(--border-color-strong);border-bottom:none;border-radius:var(--radius-sm) var(--radius-sm) 0 0;background:var(--bg-surface-2);flex-wrap:wrap;}
.rich-toolbar button{
  width:30px;height:30px;border:none;background:transparent;border-radius:6px;color:var(--text-secondary);font-size:14px;cursor:pointer;
}
.rich-toolbar button:hover{background:var(--border-color);color:var(--text-primary);}
.reply-textarea{border-radius:0 0 var(--radius-sm) var(--radius-sm);border-top:none;min-height:100px;resize:vertical;}

/* ==========================================================================
   Modals
   ========================================================================== */
.modal-content{border-radius:var(--radius-lg);border:none;box-shadow:var(--shadow-lg);background:var(--bg-surface);color:var(--text-primary);}
.modal-header{border-bottom:1px solid var(--border-color);padding:18px 24px;}
.modal-title{font-weight:700;font-size:16.5px;}
.modal-body{padding:22px 24px;}
.modal-footer{border-top:1px solid var(--border-color);padding:16px 24px;}
.btn-close{filter:var(--btnclose-filter,none);}
[data-theme="dark"] .btn-close{filter:invert(1) grayscale(1) brightness(1.8);}

/* ==========================================================================
   Toasts
   ========================================================================== */
.toast{border-radius:var(--radius-md);border:1px solid var(--border-color);box-shadow:var(--shadow-md);background:var(--bg-surface);}
.toast-header{background:transparent;border-bottom:1px solid var(--border-color);color:var(--text-primary);border-radius:var(--radius-md) var(--radius-md) 0 0;}
.toast-body{color:var(--text-secondary);font-size:13px;}

/* ==========================================================================
   Nav tabs (contact profile)
   ========================================================================== */
.nav-tabs-c{border-bottom:1px solid var(--border-color);gap:4px;}
.nav-tabs-c .nav-link{
  border:none;color:var(--text-secondary);font-weight:600;font-size:13.6px;padding:10px 16px;border-radius:var(--radius-sm) var(--radius-sm) 0 0;
}
.nav-tabs-c .nav-link:hover{color:var(--color-primary);background:var(--color-primary-light);}
.nav-tabs-c .nav-link.active{color:var(--color-primary);background:var(--color-primary-light);border-bottom:2px solid var(--color-primary);}

/* ==========================================================================
   Skeleton loaders
   ========================================================================== */
.skeleton{background:linear-gradient(90deg,var(--border-color) 25%,var(--bg-surface-2) 37%,var(--border-color) 63%);background-size:400% 100%;animation:skeleton-loading 1.4s ease infinite;border-radius:8px;}
@keyframes skeleton-loading{0%{background-position:100% 50%;}100%{background-position:0 50%;}}

/* ==========================================================================
   Empty states
   ========================================================================== */
.empty-state{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:56px 20px;text-align:center;color:var(--text-muted);}
.empty-state .es-icon{width:64px;height:64px;border-radius:50%;background:var(--bg-surface-2);display:flex;align-items:center;justify-content:center;font-size:26px;margin-bottom:14px;color:var(--text-muted);}
.empty-state h6{color:var(--text-primary);font-weight:700;margin-bottom:4px;}
.empty-state p{font-size:12.8px;max-width:320px;}

/* ==========================================================================
   Pagination
   ========================================================================== */
.pagination-c{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:14px 4px 0;flex-wrap:wrap;}
.pagination-c .p-info{font-size:12.4px;color:var(--text-secondary);}
.pagination-c .p-btns{display:flex;gap:4px;}
.pagination-c .p-btn{
  width:32px;height:32px;border-radius:var(--radius-xs);border:1px solid var(--border-color);background:var(--bg-surface);
  color:var(--text-secondary);font-size:12.6px;font-weight:600;display:flex;align-items:center;justify-content:center;cursor:pointer;
}
.pagination-c .p-btn:hover{border-color:var(--color-primary);color:var(--color-primary);}
.pagination-c .p-btn.active{background:var(--color-primary);border-color:var(--color-primary);color:#fff;}

/* ==========================================================================
   WhatsApp templates
   ========================================================================== */
.template-card{padding:18px 18px;display:flex;flex-direction:column;gap:10px;}
.template-card .tpl-icon{width:38px;height:38px;border-radius:10px;background:var(--color-whatsapp-light);color:var(--color-whatsapp);display:flex;align-items:center;justify-content:center;font-size:18px;}
.template-card .tpl-name{font-weight:700;font-size:14.5px;}
.template-card .tpl-preview{font-size:12.6px;color:var(--text-secondary);line-height:1.55;min-height:56px;}
.wa-bubble{background:var(--color-whatsapp-light);border-radius:12px 12px 12px 3px;padding:12px 14px;font-size:13.2px;line-height:1.6;color:var(--text-primary);position:relative;}
.wa-phone-frame{background:#0b0b0e;border-radius:28px;padding:10px;width:260px;margin:0 auto;box-shadow:var(--shadow-lg);}
.wa-phone-screen{background:#E5DDD5;border-radius:20px;min-height:360px;padding:14px 10px;position:relative;overflow:hidden;}
[data-theme="dark"] .wa-phone-screen{background:#0b141a;}

/* ==========================================================================
   Settings
   ========================================================================== */
.settings-card-icon{width:40px;height:40px;border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;}
.form-switch .form-check-input{width:2.4em;height:1.3em;}
.form-check-input:checked{background-color:var(--color-primary);border-color:var(--color-primary);}

/* ==========================================================================
   Misc utils
   ========================================================================== */
.divider-v{width:1px;background:var(--border-color);align-self:stretch;}
.tooltip-inner{font-size:11.5px;border-radius:6px;}
.cursor-pointer{cursor:pointer;}
.text-trunc-2{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
.placeholder-btn{font-size:11px;font-weight:700;padding:3px 9px;border-radius:20px;border:1px dashed var(--color-primary);color:var(--color-primary);background:var(--color-primary-light);cursor:pointer;}
.placeholder-btn:hover{background:var(--color-primary);color:#fff;}
.dropdown-menu{border-radius:var(--radius-md);border:1px solid var(--border-color);box-shadow:var(--shadow-md);padding:8px;background:var(--bg-surface);}
.dropdown-item{border-radius:var(--radius-xs);font-size:13.2px;padding:8px 10px;color:var(--text-primary);}
.dropdown-item:hover{background:var(--color-primary-light);color:var(--color-primary);}
.dropdown-item i{width:18px;}
.notif-item{display:flex;gap:10px;padding:10px 8px;border-radius:var(--radius-sm);}
.notif-item:hover{background:var(--bg-surface-2);}
.notif-dot{width:8px;height:8px;border-radius:50%;background:var(--color-primary);flex-shrink:0;margin-top:6px;}

/* ==========================================================================
   Responsive
   ========================================================================== */
@media (max-width:1200px){
  .gmail-shell{grid-template-columns:170px 300px 1fr;}
}
@media (max-width:991px){
  .app-sidebar{transform:translateX(-100%);}
  .app-sidebar.show{transform:translateX(0);box-shadow:var(--shadow-lg);}
  .app-main{margin-left:0;}
  .app-topbar{left:0;}
  .topbar-burger{display:flex;}
  .gmail-shell{grid-template-columns:1fr;height:auto;}
  .gmail-folders{display:none;}
  .gmail-list-pane,.gmail-preview-pane{border-right:none;}
  .gmail-shell.show-preview .gmail-list-pane{display:none;}
  .gmail-shell:not(.show-preview) .gmail-preview-pane{display:none;}
}
@media (max-width:767px){
  .main-inner{padding:18px 14px 50px;}
  .topbar-search{max-width:none;}
  .profile-chip .p-text{display:none;}
  .page-title{font-size:19px;}
}
