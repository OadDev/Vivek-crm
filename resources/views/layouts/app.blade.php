<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Dashboard') · {{ config('app.name') }}</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<style>
@include('partials.styles')
</style>
@stack('styles')
</head>
<body>

<div class="page-loader" id="pageLoader"></div>

@include('partials.sidebar')

<header class="app-topbar">
  <button class="topbar-burger" id="burgerBtn" aria-label="Toggle sidebar"><i class="bi bi-list"></i></button>

  <form class="topbar-search" method="GET" action="{{ route('contacts.index') }}">
    <i class="bi bi-search"></i>
    <input type="text" name="search" placeholder="Search contacts, emails, products..." autocomplete="off">
  </form>

  <div class="topbar-spacer"></div>

  <div class="topbar-actions">
    <button type="button" class="icon-btn theme-toggle-btn" id="themeToggleBtn" title="Toggle theme">
      <i class="bi bi-moon-stars-fill" id="themeIcon"></i>
    </button>

    <div class="dropdown">
      <button class="icon-btn" data-bs-toggle="dropdown" title="Notifications">
        <i class="bi bi-bell-fill"></i><span class="dot-badge"></span>
      </button>
      <div class="dropdown-menu dropdown-menu-end" style="width:320px;">
        <div class="d-flex justify-content-between align-items-center px-2 pb-2">
          <strong style="font-size:13.5px;">Notifications</strong>
        </div>
        @forelse ($globalRecentActivities ?? [] as $act)
          <div class="notif-item">
            <div class="notif-dot" style="background:var(--color-{{ $act->color }});"></div>
            <div><div style="font-size:12.8px;font-weight:600;">{!! $act->description !!}</div><div style="font-size:11.4px;color:var(--text-muted);">{{ $act->created_at->diffForHumans() }}</div></div>
          </div>
        @empty
          <div class="px-2 py-3 small text-muted-c">No notifications yet.</div>
        @endforelse
      </div>
    </div>

    <div class="dropdown">
      <div class="profile-chip" data-bs-toggle="dropdown">
        <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=4F46E5&color=fff&bold=true" alt="profile">
        <div class="p-text">
          <div class="p-name">{{ auth()->user()->name }}</div>
          <div class="p-role">{{ ucfirst(auth()->user()->role) }}</div>
        </div>
        <i class="bi bi-chevron-down small"></i>
      </div>
      <div class="dropdown-menu dropdown-menu-end" style="width:220px;">
        <a class="dropdown-item" href="{{ route('settings.index') }}"><i class="bi bi-person-circle"></i> My Profile</a>
        <a class="dropdown-item" href="{{ route('settings.index') }}"><i class="bi bi-gear"></i> Settings</a>
        <hr class="my-2">
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="dropdown-item text-danger border-0 bg-transparent w-100 text-start"><i class="bi bi-box-arrow-right"></i> Logout</button>
        </form>
      </div>
    </div>
  </div>
</header>

<main class="app-main">
  <div class="container-fluid main-inner">
    @if (session('success'))
      <div class="toast-flash" data-flash-type="success" data-flash-message="{{ session('success') }}"></div>
    @endif
    @if (session('error'))
      <div class="toast-flash" data-flash-type="danger" data-flash-message="{{ session('error') }}"></div>
    @endif
    @if ($errors->any())
      <div class="toast-flash" data-flash-type="danger" data-flash-message="{{ $errors->first() }}"></div>
    @endif

    @yield('content')
  </div>
</main>

@stack('modals')

<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:2050;" id="toastContainer"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
window.APP_CSRF = @json(csrf_token());
</script>
<script>
@include('partials.app-js')
</script>
@stack('scripts')
</body>
</html>
