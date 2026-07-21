@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">Dashboard</div>
    <div class="page-subtitle">Welcome back, {{ explode(' ', auth()->user()->name)[0] }} 👋 — here's what's happening today.</div>
  </div>
  <div class="d-flex gap-2">
    <a href="{{ route('contacts.export') }}" class="btn btn-outline-c btn-sm"><i class="bi bi-download me-1"></i>Export Report</a>
    <a href="{{ route('dashboard') }}" class="btn btn-primary-c btn-sm"><i class="bi bi-arrow-clockwise me-1"></i>Refresh</a>
  </div>
</div>

@php
$statCards = [
  ['label' => 'Total Contacts', 'value' => $stats['totalContacts'], 'icon' => 'bi-person-lines-fill', 'color' => 'primary'],
  ['label' => 'Unread Emails', 'value' => $stats['unreadEmails'], 'icon' => 'bi-envelope-exclamation-fill', 'color' => 'danger'],
  ['label' => 'Gmail Conversations', 'value' => $stats['totalConversations'], 'icon' => 'bi-chat-left-text-fill', 'color' => 'info'],
  ['label' => 'Emails Replied Today', 'value' => $stats['repliedToday'], 'icon' => 'bi-reply-all-fill', 'color' => 'success'],
  ['label' => 'WhatsApp Sent Today', 'value' => $stats['whatsappToday'], 'icon' => 'bi-whatsapp', 'color' => 'success'],
  ['label' => 'Products', 'value' => $stats['totalProducts'], 'icon' => 'bi-box-seam-fill', 'color' => 'warning'],
];
@endphp

<div class="row g-3">
  @foreach ($statCards as $s)
  <div class="col-sm-6 col-lg-4 col-xl-2">
    <div class="card-c hoverable stat-card h-100">
      <div class="stat-icon" style="background:var(--color-{{ $s['color'] }}-light);color:var(--color-{{ $s['color'] }});"><i class="bi {{ $s['icon'] }}"></i></div>
      <div>
        <div class="stat-value">{{ $s['value'] }}</div>
        <div class="stat-label">{{ $s['label'] }}</div>
      </div>
    </div>
  </div>
  @endforeach
</div>

<div class="section-title-row mt-4">
  <h5>Quick Actions</h5>
</div>
@php
$actions = [
  ['label' => 'Import Contacts', 'sub' => 'Bulk upload via Excel', 'icon' => 'bi-file-earmark-arrow-up-fill', 'color' => 'primary', 'url' => route('contacts.import.form')],
  ['label' => 'Open Gmail Inbox', 'sub' => 'View conversations', 'icon' => 'bi-envelope-open-fill', 'color' => 'info', 'url' => route('gmail.index')],
  ['label' => 'Add Contact', 'sub' => 'Create a new record', 'icon' => 'bi-person-plus-fill', 'color' => 'success', 'url' => route('contacts.index')],
  ['label' => 'Add Product', 'sub' => 'Expand your catalog', 'icon' => 'bi-box-seam-fill', 'color' => 'warning', 'url' => route('products.index')],
  ['label' => 'Send WhatsApp', 'sub' => 'Use a saved template', 'icon' => 'bi-whatsapp', 'color' => 'success', 'url' => route('whatsapp.index')],
];
@endphp
<div class="row g-3">
  @foreach ($actions as $a)
  <div class="col-sm-6 col-lg-4" style="flex:1 1 190px;">
    <a href="{{ $a['url'] }}" class="card-c hoverable quick-action-card w-100 d-block text-decoration-none">
      <div class="qa-icon" style="background:var(--color-{{ $a['color'] }}-light);color:var(--color-{{ $a['color'] }});"><i class="bi {{ $a['icon'] }}"></i></div>
      <div class="qa-label">{{ $a['label'] }}</div>
      <div class="qa-sub">{{ $a['sub'] }}</div>
    </a>
  </div>
  @endforeach
</div>

<div class="row g-3 mt-1">
  <div class="col-lg-7">
    <div class="card-c h-100">
      <div class="card-c-body">
        <div class="section-title-row"><h5>Weekly Communication Volume</h5><span class="chip chip-neutral">Last 7 days</span></div>
        <div class="bar-chart">
          @foreach ($weekly as $d)
          <div class="bar-col">
            <div class="bar" style="height:{{ $d['percent'] }}%;" title="{{ $d['value'] }} messages"></div>
            <div class="bar-lbl">{{ $d['label'] }}</div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="card-c h-100">
      <div class="card-c-body">
        <div class="section-title-row"><h5>Conversations by Status</h5></div>
        <div class="donut-wrap">
          <div class="donut" style="background:conic-gradient(var(--color-primary) 0% {{ round($statusBreakdown['replied']/$statusTotal*100) }}%, var(--color-success) {{ round($statusBreakdown['replied']/$statusTotal*100) }}% {{ round(($statusBreakdown['replied']+$statusBreakdown['open'])/$statusTotal*100) }}%, var(--color-warning) {{ round(($statusBreakdown['replied']+$statusBreakdown['open'])/$statusTotal*100) }}% {{ round(($statusBreakdown['replied']+$statusBreakdown['open']+$statusBreakdown['pending'])/$statusTotal*100) }}%, var(--color-danger) {{ round(($statusBreakdown['replied']+$statusBreakdown['open']+$statusBreakdown['pending'])/$statusTotal*100) }}% 100%);">
            <div class="donut-center"><b>{{ $statusTotal }}</b><span>Total</span></div>
          </div>
          <div>
            <div class="legend-item"><span class="legend-dot" style="background:var(--color-primary);"></span>Replied <b class="ms-1">{{ $statusBreakdown['replied'] }}</b></div>
            <div class="legend-item"><span class="legend-dot" style="background:var(--color-success);"></span>Open <b class="ms-1">{{ $statusBreakdown['open'] }}</b></div>
            <div class="legend-item"><span class="legend-dot" style="background:var(--color-warning);"></span>Pending <b class="ms-1">{{ $statusBreakdown['pending'] }}</b></div>
            <div class="legend-item"><span class="legend-dot" style="background:var(--color-danger);"></span>Overdue <b class="ms-1">{{ $statusBreakdown['overdue'] }}</b></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mt-1">
  <div class="col-lg-7">
    <div class="card-c">
      <div class="card-c-body">
        <div class="section-title-row"><h5>Recent Activity</h5></div>
        @forelse ($activities as $act)
          <div class="timeline-item">
            <div class="timeline-dot" style="background:var(--color-{{ $act->color }}-light);color:var(--color-{{ $act->color }});"><i class="bi {{ $act->icon }}"></i></div>
            <div>
              <div class="timeline-text">{!! $act->description !!}</div>
              <div class="timeline-time">{{ $act->created_at->diffForHumans() }}</div>
            </div>
          </div>
        @empty
          <div class="empty-state"><div class="es-icon"><i class="bi bi-clock-history"></i></div><h6>No recent activity</h6><p>Actions across the app will show up here.</p></div>
        @endforelse
      </div>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="card-c">
      <div class="card-c-body">
        <div class="section-title-row"><h5>Products Snapshot</h5></div>
        @forelse ($products as $p)
          <div class="d-flex align-items-center justify-content-between py-2" style="border-bottom:1px solid var(--border-color);">
            <div>
              <div class="fw-600" style="font-size:13px;">{{ $p->name }}</div>
              <div class="small text-muted-c">{{ $p->code }} · {{ $p->unit }}</div>
            </div>
            <div class="fw-700" style="font-size:13px;">₹{{ number_format($p->rate, 2) }}</div>
          </div>
        @empty
          <div class="empty-state"><div class="es-icon"><i class="bi bi-box"></i></div><h6>No products yet</h6><p>Add products from Product Master.</p></div>
        @endforelse
      </div>
    </div>
  </div>
</div>
@endsection
