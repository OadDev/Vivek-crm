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
    <div class="page-subtitle">All your customer &amp; lead contacts in one directory.</div>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <a href="{{ route('contacts.import.form') }}" class="btn btn-outline-c btn-sm"><i class="bi bi-file-earmark-arrow-up me-1"></i>Import Excel</a>
    <a href="{{ route('contacts.export') }}" class="btn btn-outline-c btn-sm"><i class="bi bi-file-earmark-arrow-down me-1"></i>Export Excel</a>
    <button class="btn btn-primary-c btn-sm" data-bs-toggle="modal" data-bs-target="#modalAddContact"><i class="bi bi-person-plus-fill me-1"></i>Add Contact</button>
  </div>
</div>

{{-- Auto-sync data source card --}}
<div class="card-c mb-3">
  <div class="card-c-body">
    <div class="section-title-row">
      <h5><i class="bi bi-arrow-repeat me-1"></i>Auto-Sync Data Source</h5>
      <button class="btn btn-light-c btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#syncSettingsPanel">Configure <i class="bi bi-chevron-down ms-1"></i></button>
    </div>
    <div class="d-flex flex-wrap gap-3 align-items-center small text-muted-c">
      <span class="chip {{ $syncSetting->is_enabled ? 'chip-success' : 'chip-neutral' }}"><i class="bi bi-circle-fill"></i>{{ $syncSetting->is_enabled ? 'Enabled' : 'Disabled' }}</span>
      <span>Source: <b class="text-reset">{{ $syncSetting->source_type === 'google_sheet' ? 'Google Sheet' : 'Excel Upload' }}</b></span>
      <span>Every <b class="text-reset">{{ $syncSetting->interval_minutes }}</b> min</span>
      @if ($syncSetting->last_synced_at)
        <span>Last synced <b class="text-reset">{{ $syncSetting->last_synced_at->diffForHumans() }}</b></span>
        <span class="chip {{ $syncSetting->last_sync_status === 'success' ? 'chip-success' : 'chip-danger' }}">{{ ucfirst($syncSetting->last_sync_status ?? '') }}</span>
      @else
        <span>Never synced yet</span>
      @endif
      <form method="POST" action="{{ route('contacts.sync-now') }}" class="ms-auto">
        @csrf
        <button type="submit" class="btn btn-primary-c btn-sm"><i class="bi bi-lightning-charge-fill me-1"></i>Sync Now</button>
      </form>
    </div>

    <div class="collapse mt-3" id="syncSettingsPanel">
      <form method="POST" action="{{ route('contacts.sync-settings') }}" enctype="multipart/form-data" class="row g-3 pt-3" style="border-top:1px solid var(--border-color);">
        @csrf
        <div class="col-md-4">
          <label class="form-label">Source Type</label>
          <select class="form-select" name="source_type" id="syncSourceType">
            <option value="excel_upload" {{ $syncSetting->source_type === 'excel_upload' ? 'selected' : '' }}>Uploaded Excel File</option>
            <option value="google_sheet" {{ $syncSetting->source_type === 'google_sheet' ? 'selected' : '' }}>Google Sheet (public link)</option>
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
          Expects columns like Name, Company, Email, WhatsApp, Designation, and a date column (e.g. "Last Contacted") — status is computed automatically from that date (Active &lt; 7 days, Follow-up 7–20 days, Inactive &gt; 20 days).
        </div>
      </form>
    </div>
  </div>
</div>

<div class="card-c">
  <div class="card-c-body">
    <form method="GET" action="{{ route('contacts.index') }}">
      <div class="toolbar-c">
        <div class="toolbar-search">
          <i class="bi bi-search"></i>
          <input type="text" name="search" class="form-control form-control-sm" placeholder="Search contacts..." value="{{ request('search') }}">
        </div>
        <div class="filter-chip-group">
          <a href="{{ route('contacts.index', array_merge(request()->except(['filter_status','page']))) }}" class="filter-chip-btn {{ !request('filter_status') ? 'active' : '' }}">All</a>
          @foreach (\App\Models\Contact::statusOptions() as $key => $label)
            <a href="{{ route('contacts.index', array_merge(request()->except('page'), ['filter_status' => $key])) }}" class="filter-chip-btn {{ request('filter_status') === $key ? 'active' : '' }}">{{ $label }}</a>
          @endforeach
        </div>
        <button type="button" class="btn btn-light-c btn-sm" data-bs-toggle="collapse" data-bs-target="#advancedFiltersPanel"><i class="bi bi-sliders me-1"></i>Custom Filters</button>
        <div class="ms-auto small text-muted-c">{{ $contacts->total() }} contact(s)</div>
      </div>

      <div class="collapse {{ request()->hasAny(['filter_name','filter_company','filter_email','filter_whatsapp','filter_designation','date_from','date_to','starred_only']) ? 'show' : '' }} mb-3" id="advancedFiltersPanel">
        <div class="row g-2 p-3" style="background:var(--bg-surface-2);border-radius:var(--radius-md);">
          <div class="col-md-3"><label class="form-label">Name</label><input type="text" name="filter_name" class="form-control form-control-sm" value="{{ request('filter_name') }}"></div>
          <div class="col-md-3"><label class="form-label">Company</label><input type="text" name="filter_company" class="form-control form-control-sm" value="{{ request('filter_company') }}"></div>
          <div class="col-md-3"><label class="form-label">Email</label><input type="text" name="filter_email" class="form-control form-control-sm" value="{{ request('filter_email') }}"></div>
          <div class="col-md-3"><label class="form-label">WhatsApp</label><input type="text" name="filter_whatsapp" class="form-control form-control-sm" value="{{ request('filter_whatsapp') }}"></div>
          <div class="col-md-3"><label class="form-label">Designation</label><input type="text" name="filter_designation" class="form-control form-control-sm" value="{{ request('filter_designation') }}"></div>
          <div class="col-md-3"><label class="form-label">Last Contacted From</label><input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}"></div>
          <div class="col-md-3"><label class="form-label">Last Contacted To</label><input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}"></div>
          <div class="col-md-3 d-flex align-items-end">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="starred_only" value="1" id="starredOnly" {{ request('starred_only') ? 'checked' : '' }}>
              <label class="form-check-label small" for="starredOnly">Starred / pinned only</label>
            </div>
          </div>
          <div class="col-12 d-flex gap-2">
            <button type="submit" class="btn btn-primary-c btn-sm">Apply Filters</button>
            <a href="{{ route('contacts.index') }}" class="btn btn-light-c btn-sm">Clear All</a>
          </div>
        </div>
      </div>
    </form>

    <div class="table-responsive-c">
      <table class="table-c">
        <thead>
          <tr>
            <th></th>
            <th>{!! sortLink('name', 'Name', $sort, $dir) !!}</th>
            <th>{!! sortLink('company', 'Company', $sort, $dir) !!}</th>
            <th>Email</th>
            <th>WhatsApp</th>
            <th>Designation</th>
            <th>{!! sortLink('status', 'Status', $sort, $dir) !!}</th>
            <th>{!! sortLink('last_contacted_at', 'Last Contacted', $sort, $dir) !!}</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($contacts as $contact)
          <tr>
            <td>
              <div class="d-flex align-items-center gap-1">
                <form method="POST" action="{{ route('contacts.star', $contact) }}">
                  @csrf @method('PATCH')
                  <button type="submit" class="btn btn-link p-0 border-0" style="color:{{ $contact->is_starred ? '#F5A623' : 'var(--text-muted)' }};font-size:16px;" title="{{ $contact->is_starred ? 'Unpin' : 'Pin to top' }}">
                    <i class="bi {{ $contact->is_starred ? 'bi-star-fill' : 'bi-star' }}"></i>
                  </button>
                </form>
                <div class="avatar-circle" style="background:{{ $contact->avatarColor() }};">{{ $contact->initials() }}</div>
              </div>
            </td>
            <td><a href="{{ route('contacts.show', $contact) }}" class="fw-600 text-reset text-decoration-none">{{ $contact->name }}</a></td>
            <td>{{ $contact->company ?: '—' }}</td>
            <td>{{ $contact->email }}</td>
            <td>{{ $contact->whatsapp ?: '—' }}</td>
            <td>{{ $contact->designation ?: '—' }}</td>
            <td><span class="chip {{ \App\Models\Contact::statusChipClass($contact->status) }}"><i class="bi bi-circle-fill"></i>{{ \App\Models\Contact::statusOptions()[$contact->status] }}</span></td>
            <td class="small text-muted-c">{{ $contact->last_contacted_at?->format('d M Y') ?? '—' }}</td>
            <td>
              <div class="d-flex gap-1 justify-content-end">
                <a href="{{ route('contacts.show', $contact) }}" class="btn-icon-sq" title="View" data-bs-toggle="tooltip"><i class="bi bi-eye"></i></a>
                <button type="button" class="btn-icon-sq js-edit-contact"
                  data-id="{{ $contact->id }}" data-name="{{ $contact->name }}" data-company="{{ $contact->company }}"
                  data-email="{{ $contact->email }}" data-whatsapp="{{ $contact->whatsapp }}" data-designation="{{ $contact->designation }}"
                  data-status="{{ $contact->status }}" data-notes="{{ $contact->notes }}"
                  data-last-contacted="{{ optional($contact->last_contacted_at)->format('Y-m-d') }}"
                  title="Edit" data-bs-toggle="tooltip"><i class="bi bi-pencil"></i></button>
                @if ($contact->whatsapp)
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $contact->whatsapp) }}" target="_blank" class="btn-icon-sq success" title="WhatsApp" data-bs-toggle="tooltip"><i class="bi bi-whatsapp"></i></a>
                @endif
                <a href="mailto:{{ $contact->email }}" class="btn-icon-sq" title="Email" data-bs-toggle="tooltip"><i class="bi bi-envelope"></i></a>
                <form method="POST" action="{{ route('contacts.destroy', $contact) }}" data-confirm="Delete {{ $contact->name }}? This cannot be undone.">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn-icon-sq danger" title="Delete" data-bs-toggle="tooltip"><i class="bi bi-trash"></i></button>
                </form>
              </div>
            </td>
          </tr>
          @empty
          <tr><td colspan="9">
            <div class="empty-state">
              <div class="es-icon"><i class="bi bi-person-x"></i></div>
              <h6>No contacts found</h6>
              <p>Try adjusting your search or filters, or add a new contact.</p>
            </div>
          </td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="pagination-c">
      <span class="p-info">Showing {{ $contacts->firstItem() ?? 0 }}–{{ $contacts->lastItem() ?? 0 }} of {{ $contacts->total() }}</span>
      {{ $contacts->onEachSide(1)->links('vendor.pagination.crm') }}
    </div>
  </div>
</div>
@endsection

@push('modals')
@include('contacts._modals')
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  function toggleSyncFields() {
    var type = document.getElementById('syncSourceType').value;
    document.getElementById('syncExcelField').style.display = type === 'excel_upload' ? '' : 'none';
    document.getElementById('syncSheetField').style.display = type === 'google_sheet' ? '' : 'none';
  }
  var sel = document.getElementById('syncSourceType');
  if (sel) { sel.addEventListener('change', toggleSyncFields); toggleSyncFields(); }

  document.querySelectorAll('.js-edit-contact').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var form = document.getElementById('formEditContact');
      form.action = '{{ url('contacts') }}/' + btn.dataset.id;
      form.querySelector('[name=name]').value = btn.dataset.name || '';
      form.querySelector('[name=company]').value = btn.dataset.company || '';
      form.querySelector('[name=email]').value = btn.dataset.email || '';
      form.querySelector('[name=whatsapp]').value = btn.dataset.whatsapp || '';
      form.querySelector('[name=designation]').value = btn.dataset.designation || '';
      form.querySelector('[name=status]').value = btn.dataset.status || 'active';
      form.querySelector('[name=notes]').value = btn.dataset.notes || '';
      form.querySelector('[name=last_contacted_at]').value = btn.dataset.lastContacted || '';
      new bootstrap.Modal(document.getElementById('modalEditContact')).show();
    });
  });
});
</script>
@endpush
