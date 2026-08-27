@extends('layouts.app')

@section('title', $dataSheet->name)

@section('content')
<div class="breadcrumb-c"><a href="{{ route('dashboard') }}">Home</a><i class="bi bi-chevron-right"></i><a href="{{ route('data-sheets.index') }}">Data Sheets</a><i class="bi bi-chevron-right"></i><span class="current">{{ $dataSheet->name }}</span></div>
<div class="page-header">
  <div>
    <div class="page-title">{{ $dataSheet->name }}</div>
    <div class="page-subtitle">
      @if ($dataSheet->last_synced_at)
        Last synced (IST) {{ $dataSheet->last_synced_at->timezone('Asia/Kolkata')->format('d M, h:i A') }}
      @else
        Never synced yet
      @endif
    </div>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <form method="POST" action="{{ route('data-sheets.sync-now', $dataSheet) }}">
      @csrf
      <button type="submit" class="btn btn-primary-c btn-sm"><i class="bi bi-arrow-repeat me-1"></i>Sync Now</button>
    </form>
  </div>
</div>

<div class="card-c">
  <div class="card-c-body">
    <div class="toolbar-c mb-3">
      <div class="toolbar-search position-relative">
        <i class="bi bi-search"></i>
        <input type="text" id="dsSearchInput" class="form-control form-control-sm" placeholder="Search across every column..." value="{{ request('search') }}" autocomplete="off" style="padding-right:26px;">
        <button type="button" id="dsSearchClear" title="Clear" style="display:{{ request('search') ? 'inline-flex' : 'none' }};position:absolute;right:8px;top:50%;transform:translateY(-50%);border:0;background:none;color:var(--text-muted);align-items:center;"><i class="bi bi-x-circle-fill"></i></button>
      </div>
      <div class="ms-auto small text-muted-c">{{ $rows->total() }} row(s)</div>
    </div>

    <div id="dsTableWrap">
      @include('data-sheets._table')
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  var tableWrap = document.getElementById('dsTableWrap');
  var searchInput = document.getElementById('dsSearchInput');
  var clearBtn = document.getElementById('dsSearchClear');

  function loadRows(targetUrl) {
    if (!tableWrap) { window.location.href = targetUrl; return; }
    tableWrap.style.opacity = '0.5';
    fetch(targetUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function (r) { return r.text(); })
      .then(function (html) {
        tableWrap.innerHTML = html;
        tableWrap.style.opacity = '';
        history.replaceState(null, '', targetUrl);
      })
      .catch(function () { window.location.href = targetUrl; });
  }

  if (tableWrap) {
    tableWrap.addEventListener('change', function (e) {
      if (e.target && e.target.id === 'dsPerPageSelect') {
        var u = new URL(window.location.href);
        u.searchParams.set('per_page', e.target.value);
        u.searchParams.delete('page');
        loadRows(u.toString());
      }
    });
  }

  if (searchInput && tableWrap) {
    var debounceTimer;
    function runSearch() {
      var u = new URL(window.location.href);
      if (searchInput.value) { u.searchParams.set('search', searchInput.value); } else { u.searchParams.delete('search'); }
      u.searchParams.delete('page');
      loadRows(u.toString());
    }
    searchInput.addEventListener('input', function () {
      clearBtn.style.display = searchInput.value ? 'inline-flex' : 'none';
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(runSearch, searchInput.value ? 350 : 0);
    });
    clearBtn.addEventListener('click', function () {
      searchInput.value = '';
      clearBtn.style.display = 'none';
      runSearch();
    });
  }
});
</script>
@endpush
