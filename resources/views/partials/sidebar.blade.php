<div class="sidebar-overlay" id="sidebarOverlay"></div>
<aside class="app-sidebar" id="appSidebar">
  <div class="sidebar-brand">
    <div class="brand-mark"><i class="bi bi-chat-dots-fill"></i></div>
    <div>
      <div class="brand-text">Vivek Jain CRM</div>
      <div class="brand-sub">Communication Management</div>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-section-label">Main</div>
    <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
      <i class="bi bi-grid-1x2-fill"></i><span>Dashboard</span>
    </a>
    <a href="{{ route('gmail.index') }}" class="sidebar-link {{ request()->routeIs('gmail.*') ? 'active' : '' }}">
      <i class="bi bi-envelope-fill"></i><span>Gmail Inbox</span>
      @php($unread = \App\Models\EmailConversation::where('folder','inbox')->where('is_read', false)->count())
      @if($unread > 0)<span class="badge-count">{{ $unread }}</span>@endif
    </a>
    <a href="{{ route('contacts.index') }}" class="sidebar-link {{ request()->routeIs('contacts.*') ? 'active' : '' }}">
      <i class="bi bi-person-lines-fill"></i><span>Contacts</span>
    </a>
    <a href="{{ route('whatsapp.index') }}" class="sidebar-link {{ request()->routeIs('whatsapp.*') ? 'active' : '' }}">
      <i class="bi bi-whatsapp"></i><span>WhatsApp Templates</span>
    </a>
    <a href="{{ route('products.index') }}" class="sidebar-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
      <i class="bi bi-box-seam-fill"></i><span>Product Master</span>
    </a>

    <div class="nav-section-label">Account</div>
    <a href="{{ route('settings.index') }}" class="sidebar-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
      <i class="bi bi-gear-fill"></i><span>Settings</span>
    </a>
    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="sidebar-link logout-link border-0 bg-transparent w-100 text-start">
        <i class="bi bi-box-arrow-right"></i><span>Logout</span>
      </button>
    </form>
  </nav>

  <div class="sidebar-footer">
    <div class="sidebar-user">
      <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=4F46E5&color=fff&bold=true" class="avatar-sm" alt="User">
      <div>
        <div class="u-name">{{ auth()->user()->name }}</div>
        <div class="u-role">{{ ucfirst(auth()->user()->role) }}</div>
      </div>
    </div>
  </div>
</aside>
