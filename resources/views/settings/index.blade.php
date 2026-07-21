@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div class="breadcrumb-c"><a href="{{ route('dashboard') }}">Home</a><i class="bi bi-chevron-right"></i><span class="current">Settings</span></div>
<div class="page-header">
  <div>
    <div class="page-title">Settings</div>
    <div class="page-subtitle">Manage your account, integrations and preferences.</div>
  </div>
</div>

<div class="row g-3">
  {{-- User Profile --}}
  <div class="col-lg-6">
    <div class="card-c h-100">
      <div class="card-c-body">
        <div class="d-flex align-items-center gap-2 mb-3">
          <div class="settings-card-icon" style="background:var(--color-primary-light);color:var(--color-primary);"><i class="bi bi-person-fill"></i></div>
          <h5 class="mb-0">User Profile</h5>
        </div>
        <div class="d-flex align-items-center gap-3 mb-3">
          <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=4F46E5&color=fff&bold=true" class="avatar-circle avatar-md">
          <div class="small text-muted-c">Avatar is generated automatically from your name.</div>
        </div>
        <form method="POST" action="{{ route('settings.profile') }}">
          @csrf @method('PUT')
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Full Name</label><input type="text" name="name" class="form-control" value="{{ auth()->user()->name }}"></div>
            <div class="col-md-6"><label class="form-label">Email Address</label><input type="email" name="email" class="form-control" value="{{ auth()->user()->email }}"></div>
            <div class="col-md-6"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="{{ auth()->user()->phone }}"></div>
            <div class="col-md-6"><label class="form-label">Role</label><input type="text" class="form-control" value="{{ ucfirst(auth()->user()->role) }}" disabled></div>
          </div>
          <button type="submit" class="btn btn-primary-c btn-sm mt-3">Save Changes</button>
        </form>
      </div>
    </div>
  </div>

  {{-- Change Password --}}
  <div class="col-lg-6">
    <div class="card-c h-100">
      <div class="card-c-body">
        <div class="d-flex align-items-center gap-2 mb-3">
          <div class="settings-card-icon" style="background:var(--color-danger-light);color:var(--color-danger);"><i class="bi bi-shield-lock-fill"></i></div>
          <h5 class="mb-0">Change Password</h5>
        </div>
        <form method="POST" action="{{ route('settings.password') }}">
          @csrf @method('PUT')
          <div class="mb-3"><label class="form-label">Current Password</label><input type="password" name="current_password" class="form-control" placeholder="••••••••" required></div>
          <div class="mb-3"><label class="form-label">New Password</label><input type="password" name="password" class="form-control" placeholder="••••••••" required minlength="8"></div>
          <div class="mb-3"><label class="form-label">Confirm New Password</label><input type="password" name="password_confirmation" class="form-control" placeholder="••••••••" required minlength="8"></div>
          <button type="submit" class="btn btn-primary-c btn-sm">Update Password</button>
        </form>
      </div>
    </div>
  </div>

  {{-- Theme --}}
  <div class="col-lg-6">
    <div class="card-c h-100">
      <div class="card-c-body">
        <div class="d-flex align-items-center gap-2 mb-3">
          <div class="settings-card-icon" style="background:var(--color-info-light);color:var(--color-info);"><i class="bi bi-palette-fill"></i></div>
          <h5 class="mb-0">Theme</h5>
        </div>
        <p class="small text-muted-c">Choose how the interface looks across the app.</p>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-outline-c btn-sm" onclick="document.documentElement.setAttribute('data-theme','light'); localStorage.setItem('cms-theme','light'); document.getElementById('themeIcon').className='bi bi-moon-stars-fill';"><i class="bi bi-sun-fill me-1"></i>Light</button>
          <button type="button" class="btn btn-outline-c btn-sm" onclick="document.documentElement.setAttribute('data-theme','dark'); localStorage.setItem('cms-theme','dark'); document.getElementById('themeIcon').className='bi bi-sun-fill';"><i class="bi bi-moon-stars-fill me-1"></i>Dark</button>
        </div>
      </div>
    </div>
  </div>

  {{-- Gmail Integration --}}
  <div class="col-lg-6">
    <div class="card-c h-100">
      <div class="card-c-body">
        <div class="d-flex align-items-center gap-2 mb-3">
          <div class="settings-card-icon" style="background:var(--color-warning-light);color:var(--color-warning);"><i class="bi bi-envelope-at-fill"></i></div>
          <h5 class="mb-0">Gmail Integration</h5>
        </div>
        <div class="d-flex align-items-center justify-content-between p-3 mb-3" style="background:var(--bg-surface-2);border-radius:var(--radius-md);">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-google" style="font-size:20px;color:var(--color-danger);"></i>
            <div>
              <div class="fw-600 small">{{ $settings['gmail_account'] ?: 'No account connected' }}</div>
              <div class="chip {{ $settings['gmail_connected'] ? 'chip-success' : 'chip-danger' }}"><i class="bi bi-circle-fill"></i>{{ $settings['gmail_connected'] ? 'Connected' : 'Disconnected' }}</div>
            </div>
          </div>
        </div>
        <form method="POST" action="{{ route('settings.gmail') }}" class="d-inline">
          @csrf
          <input type="hidden" name="action" value="{{ $settings['gmail_connected'] ? 'disconnect' : 'connect' }}">
          <button type="submit" class="btn {{ $settings['gmail_connected'] ? 'btn-danger-c' : 'btn-outline-c' }} btn-sm">
            <i class="bi {{ $settings['gmail_connected'] ? 'bi-x-circle' : 'bi-plug-fill' }} me-1"></i>{{ $settings['gmail_connected'] ? 'Disconnect Gmail' : 'Connect Gmail' }}
          </button>
        </form>
        <p class="small text-muted-c mt-2 mb-0">Full Gmail OAuth sync is a future integration; this toggle reflects connection state in the UI today.</p>
      </div>
    </div>
  </div>

  {{-- Leads Data Source (Google Sheets / Excel) --}}
  <div class="col-12">
    <div class="card-c">
      <div class="card-c-body">
        <div class="d-flex align-items-center gap-2 mb-3">
          <div class="settings-card-icon" style="background:var(--color-info-light);color:var(--color-info);"><i class="bi bi-google"></i></div>
          <h5 class="mb-0">Leads Data Source</h5>
          <span class="chip {{ $syncSetting->is_enabled ? 'chip-success' : 'chip-neutral' }} ms-auto"><i class="bi bi-circle-fill"></i>{{ $syncSetting->is_enabled ? 'Enabled' : 'Disabled' }}</span>
        </div>
        <p class="small text-muted-c">Link a Google Sheet (shared as "Anyone with the link") or upload an Excel file so new leads/contacts flow in automatically. Same source used by the Contacts page — editing it here updates it there too.</p>

        <form method="POST" action="{{ route('contacts.sync-settings') }}" enctype="multipart/form-data" class="row g-3">
          @csrf
          <div class="col-md-3">
            <label class="form-label">Source Type</label>
            <select class="form-select" name="source_type" id="settingsSyncSourceType">
              <option value="google_sheet" {{ $syncSetting->source_type === 'google_sheet' ? 'selected' : '' }}>Google Sheet (link)</option>
              <option value="excel_upload" {{ $syncSetting->source_type === 'excel_upload' ? 'selected' : '' }}>Uploaded Excel File</option>
            </select>
          </div>
          <div class="col-md-5" id="settingsSyncSheetField">
            <label class="form-label">Google Sheet URL</label>
            <input type="url" class="form-control" name="google_sheet_url" value="{{ $syncSetting->google_sheet_url }}" placeholder="https://docs.google.com/spreadsheets/d/...">
          </div>
          <div class="col-md-5" id="settingsSyncExcelField">
            <label class="form-label">Excel File {{ $syncSetting->excel_original_name ? '(current: '.$syncSetting->excel_original_name.')' : '' }}</label>
            <input type="file" class="form-control" name="sync_file" accept=".xlsx,.xls,.csv">
          </div>
          <div class="col-md-2">
            <label class="form-label">Interval (min)</label>
            <input type="number" class="form-control" name="interval_minutes" min="1" max="1440" value="{{ $syncSetting->interval_minutes }}">
          </div>
          <div class="col-md-3 d-flex align-items-end">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" name="is_enabled" value="1" id="settingsSyncEnabled" {{ $syncSetting->is_enabled ? 'checked' : '' }}>
              <label class="form-check-label small" for="settingsSyncEnabled">Enable automatic sync</label>
            </div>
          </div>
          <div class="col-12 d-flex gap-2">
            <button type="submit" class="btn btn-primary-c btn-sm">Save Data Source</button>
          </div>
        </form>

        <div class="d-flex flex-wrap gap-3 align-items-center small text-muted-c mt-3 pt-3" style="border-top:1px solid var(--border-color);">
          @if ($syncSetting->last_synced_at)
            <span>Last synced <b class="text-reset">{{ $syncSetting->last_synced_at->diffForHumans() }}</b></span>
            <span class="chip {{ $syncSetting->last_sync_status === 'success' ? 'chip-success' : 'chip-danger' }}">{{ ucfirst($syncSetting->last_sync_status ?? '') }}</span>
            @if ($syncSetting->last_sync_message)<span>{{ $syncSetting->last_sync_message }}</span>@endif
          @else
            <span>Never synced yet</span>
          @endif
          <form method="POST" action="{{ route('contacts.sync-now') }}" class="ms-auto">
            @csrf
            <button type="submit" class="btn btn-outline-c btn-sm"><i class="bi bi-lightning-charge-fill me-1"></i>Sync Now</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  {{-- WhatsApp Settings --}}
  <div class="col-lg-6">
    <div class="card-c h-100">
      <div class="card-c-body">
        <div class="d-flex align-items-center gap-2 mb-3">
          <div class="settings-card-icon" style="background:var(--color-whatsapp-light);color:var(--color-whatsapp);"><i class="bi bi-whatsapp"></i></div>
          <h5 class="mb-0">WhatsApp Settings</h5>
        </div>
        <form method="POST" action="{{ route('settings.whatsapp') }}">
          @csrf @method('PUT')
          <div class="mb-3"><label class="form-label">Sender Number</label><input type="text" name="whatsapp_sender_number" class="form-control" value="{{ $settings['whatsapp_sender_number'] }}" placeholder="+91 90000 12345"></div>
          <div class="mb-3">
            <label class="form-label">Default Template</label>
            <select class="form-select" name="whatsapp_default_template_id">
              <option value="">— None —</option>
              @foreach ($templates as $t)
                <option value="{{ $t->id }}" {{ (string) $settings['whatsapp_default_template_id'] === (string) $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
              @endforeach
            </select>
          </div>
          <button type="submit" class="btn btn-primary-c btn-sm">Save Settings</button>
        </form>
      </div>
    </div>
  </div>

  {{-- System Preferences --}}
  <div class="col-lg-6">
    <div class="card-c h-100">
      <div class="card-c-body">
        <div class="d-flex align-items-center gap-2 mb-3">
          <div class="settings-card-icon" style="background:var(--color-success-light);color:var(--color-success);"><i class="bi bi-sliders"></i></div>
          <h5 class="mb-0">System Preferences</h5>
        </div>
        <form method="POST" action="{{ route('settings.preferences') }}">
          @csrf @method('PUT')
          <div class="form-check form-switch mb-2">
            <input class="form-check-input" type="checkbox" name="pref_email_notifications" value="1" id="prefEmailNotif" {{ $settings['pref_email_notifications'] ? 'checked' : '' }}>
            <label class="form-check-label small" for="prefEmailNotif">Email notifications</label>
          </div>
          <div class="form-check form-switch mb-2">
            <input class="form-check-input" type="checkbox" name="pref_whatsapp_alerts" value="1" id="prefWaNotif" {{ $settings['pref_whatsapp_alerts'] ? 'checked' : '' }}>
            <label class="form-check-label small" for="prefWaNotif">WhatsApp delivery alerts</label>
          </div>
          <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" name="pref_auto_archive" value="1" id="prefAutoArchive" {{ $settings['pref_auto_archive'] ? 'checked' : '' }}>
            <label class="form-check-label small" for="prefAutoArchive">Auto-archive replied emails</label>
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Language</label>
              <select class="form-select" name="language">
                @foreach (['English','Hindi','Marathi'] as $lang)
                  <option {{ $settings['language'] === $lang ? 'selected' : '' }}>{{ $lang }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Timezone</label>
              <select class="form-select" name="timezone">
                @foreach (['Asia/Kolkata (IST)','UTC'] as $tz)
                  <option {{ $settings['timezone'] === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <button type="submit" class="btn btn-primary-c btn-sm mt-3">Save Preferences</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  function toggleSyncFields() {
    var type = document.getElementById('settingsSyncSourceType').value;
    document.getElementById('settingsSyncSheetField').style.display = type === 'google_sheet' ? '' : 'none';
    document.getElementById('settingsSyncExcelField').style.display = type === 'excel_upload' ? '' : 'none';
  }
  var sel = document.getElementById('settingsSyncSourceType');
  if (sel) { sel.addEventListener('change', toggleSyncFields); toggleSyncFields(); }
});
</script>
@endpush
