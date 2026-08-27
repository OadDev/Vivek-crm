@extends('layouts.app')

@section('title', 'Contacts')

@php
if (! function_exists('sortLink')) {
    function sortLink($field, $label, $sort, $dir) {
        $newDir = ($sort === $field && $dir === 'asc') ? 'desc' : 'asc';
        $icon = $sort === $field ? ($dir === 'asc' ? 'bi-arrow-up' : 'bi-arrow-down') : 'bi-arrow-down-up';
        $query = array_merge(request()->query(), ['sort' => $field, 'dir' => $newDir]);
        return '<a href="'.route('contacts.index', $query).'" class="text-decoration-none text-reset d-inline-flex align-items-center gap-1">'.$label.' <i class="bi '.$icon.'" style="font-size:10px;"></i></a>';
    }
}
@endphp

@section('content')
<div class="breadcrumb-c"><a href="{{ route('dashboard') }}">Home</a><i class="bi bi-chevron-right"></i><span class="current">Contacts</span></div>
<div class="page-header">
  <div>
    <div class="page-title">Contacts</div>
    <div class="page-subtitle">Every quotation and lead in one pipeline — one row per Quote No.</div>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    @if (auth()->user()->isAdmin())
    <a href="{{ route('contacts.import.form') }}" class="btn btn-outline-c btn-sm"><i class="bi bi-file-earmark-arrow-up me-1"></i>Import Excel</a>
    <a href="{{ route('contacts.export') }}" class="btn btn-outline-c btn-sm"><i class="bi bi-file-earmark-arrow-down me-1"></i>Export Excel</a>
    <button class="btn btn-primary-c btn-sm" data-bs-toggle="modal" data-bs-target="#modalAddContact"><i class="bi bi-person-plus-fill me-1"></i>Add Contact</button>
    @endif
  </div>
</div>

@if (! auth()->user()->isAdmin() && auth()->user()->sales_man)
<div class="small text-muted-c mb-2"><i class="bi bi-funnel-fill me-1"></i>Showing only leads assigned to Sales Man "<b>{{ auth()->user()->sales_man }}</b>".</div>
@endif

{{-- Auto-sync data source card — status visible to everyone, Configure is admin-only --}}
<div class="card-c mb-3">
  <div class="card-c-body">
    <div class="section-title-row">
      <h5><i class="bi bi-arrow-repeat me-1"></i>Auto-Sync Data Source</h5>
      @if (auth()->user()->isAdmin())
      <button class="btn btn-light-c btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#syncSettingsPanel">Configure <i class="bi bi-chevron-down ms-1"></i></button>
      @endif
    </div>
    <div class="d-flex flex-wrap gap-3 align-items-center small text-muted-c">
      <span class="chip {{ $syncSetting->is_enabled ? 'chip-success' : 'chip-neutral' }}"><i class="bi bi-circle-fill"></i>{{ $syncSetting->is_enabled ? 'Enabled' : 'Disabled' }}</span>
      <span>Source: <b class="text-reset">{{ $syncSetting->source_type === 'google_sheet' ? 'Google Sheet' : 'Excel Upload' }}</b></span>
      <span>Every <b class="text-reset">{{ $syncSetting->interval_minutes }}</b> min</span>
      @if ($syncSetting->last_synced_at)
        <span>Last synced (IST) <b class="text-reset">{{ $syncSetting->last_synced_at->timezone('Asia/Kolkata')->format('d M, h:i A') }}</b></span>
        <span class="chip {{ $syncSetting->last_sync_status === 'success' ? 'chip-success' : 'chip-danger' }}">{{ ucfirst($syncSetting->last_sync_status ?? '') }}</span>
      @else
        <span>Never synced yet</span>
      @endif
      <form method="POST" action="{{ route('contacts.sync-now') }}" class="ms-auto">
        @csrf
        <button type="submit" class="btn btn-primary-c btn-sm"><i class="bi bi-lightning-charge-fill me-1"></i>Sync Now</button>
      </form>
    </div>

    @if (auth()->user()->isAdmin())
    <div class="collapse mt-3" id="syncSettingsPanel">
      <form method="POST" action="{{ route('contacts.sync-settings') }}" enctype="multipart/form-data" class="row g-3 pt-3" style="border-top:1px solid var(--border-color);">
        @csrf
        <div class="col-md-4">
          <label class="form-label">Source Type</label>
          <select class="form-select" name="source_type" id="syncSourceType">
            <option value="google_sheet" {{ $syncSetting->source_type === 'google_sheet' ? 'selected' : '' }}>Google Sheet (public link)</option>
            <option value="excel_upload" {{ $syncSetting->source_type === 'excel_upload' ? 'selected' : '' }}>Uploaded Excel File</option>
          </select>
        </div>
        <div class="col-md-4" id="syncExcelField">
          <label class="form-label">Excel File {{ $syncSetting->excel_original_name ? '(current: '.$syncSetting->excel_original_name.')' : '' }}</label>
          <input type="file" class="form-control" name="sync_file" accept=".xlsx,.xls,.csv">
        </div>
        <div class="col-md-4" id="syncSheetField">
          <label class="form-label">Google Sheet URL</label>
          <input type="url" class="form-control" name="google_sheet_url" value="{{ $syncSetting->google_sheet_url }}" placeholder="https://docs.google.com/spreadsheets/d/...">
        </div>
        <div class="col-md-4">
          <label class="form-label">Check Interval (minutes)</label>
          <input type="number" class="form-control" name="interval_minutes" min="1" max="1440" value="{{ $syncSetting->interval_minutes }}">
        </div>
        <div class="col-md-4 d-flex align-items-end">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_enabled" value="1" id="syncEnabled" {{ $syncSetting->is_enabled ? 'checked' : '' }}>
            <label class="form-check-label small" for="syncEnabled">Enable automatic realtime sync</label>
          </div>
        </div>
        <div class="col-md-4 d-flex align-items-end">
          <button type="submit" class="btn btn-primary-c btn-sm w-100">Save Data Source</button>
        </div>
        <div class="col-12 small text-muted-c">
          Expects columns: Company Name, Date, Phone No., Mail, Sales Man, Address, GST, Transport, Shipping Address,
          Stage, Quote No., Priority — one row per quotation. Status is computed automatically from the Date column
          (Active &lt; 7 days, Follow-up 7 days–2 months, Inactive &gt; 2 months). A lead you archive or delete will
          never be re-added by a later sync, even if it's still in the sheet.
        </div>
      </form>
    </div>
    @endif
  </div>
</div>

<div class="card-c">
  <div class="card-c-body">
    <div class="filter-chip-group mb-3">
      <a href="{{ route('contacts.index', array_merge(request()->except(['view','page']), ['view' => 'pipeline'])) }}" class="filter-chip-btn {{ $view === 'pipeline' ? 'active' : '' }}"><i class="bi bi-kanban me-1"></i>Pipeline <span class="cnt">{{ $counts['pipeline'] }}</span></a>
      <a href="{{ route('contacts.index', array_merge(request()->except(['view','page']), ['view' => 'won'])) }}" class="filter-chip-btn {{ $view === 'won' ? 'active' : '' }}"><i class="bi bi-trophy-fill me-1"></i>Won <span class="cnt">{{ $counts['won'] }}</span></a>
      <a href="{{ route('contacts.index', array_merge(request()->except(['view','page']), ['view' => 'archived'])) }}" class="filter-chip-btn {{ $view === 'archived' ? 'active' : '' }}"><i class="bi bi-archive-fill me-1"></i>Archived <span class="cnt">{{ $counts['archived'] }}</span></a>
    </div>

    <form method="GET" action="{{ route('contacts.index') }}" id="contactsFilterForm">
      <input type="hidden" name="view" value="{{ $view }}">
      <div class="toolbar-c">
        <div class="toolbar-search position-relative">
          <i class="bi bi-search"></i>
          <input type="text" name="search" id="contactsSearchInput" class="form-control form-control-sm" placeholder="Search name, company, email, WhatsApp, quote no..." value="{{ request('search') }}" autocomplete="off" style="padding-right:26px;">
          <button type="button" id="contactsSearchClear" title="Clear" style="display:{{ request('search') ? 'inline-flex' : 'none' }};position:absolute;right:8px;top:50%;transform:translateY(-50%);border:0;background:none;color:var(--text-muted);align-items:center;"><i class="bi bi-x-circle-fill"></i></button>
        </div>
        <div class="filter-chip-group">
          <a href="{{ route('contacts.index', array_merge(request()->except(['filter_status','page']))) }}" class="filter-chip-btn {{ !request('filter_status') ? 'active' : '' }}">All <span class="cnt">{{ $counts['all_statuses'] }}</span></a>
          @foreach (\App\Models\Contact::statusOptions() as $key => $label)
            <a href="{{ route('contacts.index', array_merge(request()->except('page'), ['filter_status' => $key])) }}" class="filter-chip-btn {{ request('filter_status') === $key ? 'active' : '' }}">{{ $label }} <span class="cnt">{{ $statusCounts[$key] ?? 0 }}</span></a>
          @endforeach
        </div>
        <button type="button" class="btn btn-light-c btn-sm" data-bs-toggle="collapse" data-bs-target="#advancedFiltersPanel"><i class="bi bi-sliders me-1"></i>Custom Filters</button>
        <div class="ms-auto small text-muted-c">{{ $contacts->total() }} lead(s)</div>
      </div>

      <div class="collapse {{ request()->hasAny(['filter_name','filter_company','filter_email','filter_whatsapp','filter_designation','date_from','date_to','starred_only']) ? 'show' : '' }} mb-3" id="advancedFiltersPanel">
        <div class="row g-2 p-3" style="background:var(--bg-surface-2);border-radius:var(--radius-md);">
          <div class="col-md-3"><label class="form-label">Name</label><input type="text" name="filter_name" class="form-control form-control-sm" value="{{ request('filter_name') }}"></div>
          <div class="col-md-3"><label class="form-label">Company</label><input type="text" name="filter_company" class="form-control form-control-sm" value="{{ request('filter_company') }}"></div>
          <div class="col-md-3"><label class="form-label">Email</label><input type="text" name="filter_email" class="form-control form-control-sm" value="{{ request('filter_email') }}"></div>
          <div class="col-md-3"><label class="form-label">WhatsApp / Phone</label><input type="text" name="filter_whatsapp" class="form-control form-control-sm" value="{{ request('filter_whatsapp') }}"></div>
          <div class="col-md-3"><label class="form-label">Designation</label><input type="text" name="filter_designation" class="form-control form-control-sm" value="{{ request('filter_designation') }}"></div>
          <div class="col-md-3"><label class="form-label">Quotation Date From</label><input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}"></div>
          <div class="col-md-3"><label class="form-label">Quotation Date To</label><input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}"></div>
          <div class="col-md-3 d-flex align-items-end">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="starred_only" value="1" id="starredOnly" {{ request('starred_only') ? 'checked' : '' }}>
              <label class="form-check-label small" for="starredOnly">Starred / pinned only</label>
            </div>
          </div>
          <div class="col-12 d-flex gap-2">
            <button type="submit" class="btn btn-primary-c btn-sm">Apply Filters</button>
            <a href="{{ route('contacts.index', ['view' => $view]) }}" class="btn btn-light-c btn-sm">Clear All</a>
          </div>
        </div>
      </div>
    </form>

    <div id="contactsTableWrap">
      @include('contacts._table')
    </div>
  </div>
</div>
@endsection

@push('modals')
@include('contacts._modals')
@endpush

@push('scripts')
<script>
(function () {
  // Per-page choice persists across visits (localStorage) -- on a fresh
  // navigation with no explicit per_page in the URL, redirect once to the
  // last-remembered size instead of always resetting to the 20 default.
  var PP_KEY = 'contacts_per_page';
  var url = new URL(window.location.href);
  if (url.searchParams.has('per_page')) {
    localStorage.setItem(PP_KEY, url.searchParams.get('per_page'));
  } else {
    var saved = localStorage.getItem(PP_KEY);
    if (saved && saved !== '20') {
      url.searchParams.set('per_page', saved);
      window.location.replace(url.toString());
    }
  }
})();

document.addEventListener('DOMContentLoaded', function () {
  function toggleSyncFields() {
    var sel = document.getElementById('syncSourceType');
    if (!sel) return;
    var type = sel.value;
    document.getElementById('syncExcelField').style.display = type === 'excel_upload' ? '' : 'none';
    document.getElementById('syncSheetField').style.display = type === 'google_sheet' ? '' : 'none';
  }
  var sel = document.getElementById('syncSourceType');
  if (sel) { sel.addEventListener('change', toggleSyncFields); toggleSyncFields(); }

  var tableWrap = document.getElementById('contactsTableWrap');
  var searchInput = document.getElementById('contactsSearchInput');
  var clearBtn = document.getElementById('contactsSearchClear');

  function openEditModal(btn) {
    var form = document.getElementById('formEditContact');
    form.action = '{{ url('contacts') }}/' + btn.dataset.id;
    var fields = ['quoteNo:quote_no', 'quotationDate:quotation_date', 'name:name', 'company:company', 'email:email',
      'whatsapp:whatsapp', 'designation:designation', 'salesMan:sales_man', 'gstNumber:gst_number', 'transport:transport',
      'shippingAddress:shipping_address', 'stage:stage', 'priority:priority', 'status:status', 'notes:notes'];
    fields.forEach(function (pair) {
      var parts = pair.split(':');
      var input = form.querySelector('[name=' + parts[1] + ']');
      if (input) input.value = btn.dataset[parts[0]] || '';
    });
    new bootstrap.Modal(document.getElementById('modalEditContact')).show();
  }

  // Loads a contacts.index URL's table+pagination fragment via AJAX and
  // swaps it into #contactsTableWrap -- used by search and the per-page
  // selector so neither one reloads the whole page.
  function loadContacts(targetUrl) {
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
    // Delegated listeners on the wrap itself survive every AJAX swap --
    // no rebinding needed after the innerHTML is replaced.
    tableWrap.addEventListener('click', function (e) {
      var toggleBtn = e.target.closest('[data-group-toggle]');
      if (toggleBtn) {
        var groupClass = toggleBtn.dataset.groupToggle;
        var rows = tableWrap.querySelectorAll('.' + groupClass);
        var chevron = toggleBtn.querySelector('.group-chevron');
        var willShow = rows.length && rows[0].style.display === 'none';
        rows.forEach(function (r) { r.style.display = willShow ? '' : 'none'; });
        if (chevron) chevron.className = 'bi group-chevron ' + (willShow ? 'bi-chevron-down' : 'bi-chevron-right');
        return;
      }

      var editBtn = e.target.closest('.js-edit-contact');
      if (editBtn) { openEditModal(editBtn); return; }

      // Whole-row click opens the contact's profile, except when the click
      // landed on an actual control (link/button/form field) inside it.
      var row = e.target.closest('tr[data-href]');
      if (row && !e.target.closest('a, button, form, input, select')) {
        window.location.href = row.dataset.href;
      }
    });

    tableWrap.addEventListener('change', function (e) {
      if (e.target && e.target.id === 'perPageSelect') {
        var u = new URL(window.location.href);
        u.searchParams.set('per_page', e.target.value);
        u.searchParams.delete('page');
        localStorage.setItem('contacts_per_page', e.target.value);
        loadContacts(u.toString());
      }
    });
  }

  // Progressive search: fetch-and-swap while typing (debounced), no full
  // page reload.
  if (searchInput && tableWrap) {
    var debounceTimer;
    function runSearch() {
      var u = new URL(window.location.href);
      if (searchInput.value) { u.searchParams.set('search', searchInput.value); } else { u.searchParams.delete('search'); }
      u.searchParams.delete('page');
      loadContacts(u.toString());
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
