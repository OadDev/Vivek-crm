<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Welcome') · Vivek Jain CRM</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<style>
@include('partials.styles')

.guest-shell{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;background:var(--bg-body);}
.guest-card{width:100%;max-width:460px;background:var(--bg-surface);border:1px solid var(--border-color);border-radius:var(--radius-lg);box-shadow:var(--shadow-lg);padding:36px 34px;}
.guest-card.wide{max-width:720px;}
.guest-brand{display:flex;align-items:center;gap:12px;margin-bottom:26px;}
.guest-brand .brand-mark{width:44px;height:44px;border-radius:12px;background:var(--color-primary);display:flex;align-items:center;justify-content:center;color:#fff;font-size:20px;flex-shrink:0;}
.guest-brand .brand-text{font-weight:800;font-size:18px;}
.guest-brand .brand-sub{font-size:12px;color:var(--text-secondary);}
.setup-steps{display:flex;gap:8px;margin-bottom:24px;}
.setup-steps .step{flex:1;height:4px;border-radius:4px;background:var(--border-color);}
.setup-steps .step.active{background:var(--color-primary);}
</style>
</head>
<body>
<div class="guest-shell">
  <div class="guest-card @yield('card-class')">
    <div class="guest-brand">
      <div class="brand-mark"><i class="bi bi-chat-dots-fill"></i></div>
      <div>
        <div class="brand-text">Vivek Jain CRM</div>
        <div class="brand-sub">Communication Management System</div>
      </div>
    </div>

    @if (session('success'))
      <div class="alert alert-success py-2 small">{{ session('success') }}</div>
    @endif

    @yield('content')
  </div>
</div>

<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:2050;" id="toastContainer"></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
