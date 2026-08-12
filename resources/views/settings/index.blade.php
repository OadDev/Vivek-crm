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
            <div class="col-md-6"><label class="form-label">Email Address</label><input type="email" name="email" class="form-control" value="{{ auth()->user()->email }}" @disabled(! auth()->user()->isAdmin())></div>
            <div class="col-md-6"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="{{ auth()->user()->phone }}" @disabled(! auth()->user()->isAdmin())></div>
            <div class="col-md-6"><label class="form-label">Role</label><input type="text" class="form-control" value="{{ ucfirst(auth()->user()->role) }}" disabled></div>
          </div>
          @unless (auth()->user()->isAdmin())
            <p class="small text-muted-c mt-2 mb-0">Only your name can be changed here — ask your admin to update your email or phone.</p>
          @endunless
          <button type="submit" class="btn btn-primary-c btn-sm mt-3">Save Changes</button>
        </form>
      </div>
    </div>
  </div>

  @if (auth()->user()->isAdmin())
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
        <p class="small text-muted-c mt-2 mb-0">Only admins can change passwords — for a User, reset theirs from Team Accounts.</p>
      </div>
    </div>
  </div>
  @else
  <div class="col-lg-6">
    <div class="card-c h-100">
      <div class="card-c-body">
        <div class="d-flex align-items-center gap-2 mb-3">
          <div class="settings-card-icon" style="background:var(--color-danger-light);color:var(--color-danger);"><i class="bi bi-shield-lock-fill"></i></div>
          <h5 class="mb-0">Password</h5>
        </div>
        <p class="small text-muted-c mb-0">Only an admin can change your password. Ask them to reset it from Team Accounts if you need a new one.</p>
      </div>
    </div>
  </div>
  @endif

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

  {{-- Email Signature — everyone --}}
  <div class="col-lg-6">
    <div class="card-c h-100">
      <div class="card-c-body">
        <div class="d-flex align-items-center gap-2 mb-3">
          <div class="settings-card-icon" style="background:var(--color-info-light);color:var(--color-info);"><i class="bi bi-signpost-split-fill"></i></div>
          <h5 class="mb-0">Email Signature</h5>
        </div>
        <form method="POST" action="{{ route('settings.signature') }}">
          @csrf @method('PUT')
          <label class="form-label">HTML Signature</label>
          <textarea class="form-control" name="html_signature" rows="5" placeholder="e.g. &lt;b&gt;{{ auth()->user()->name }}&lt;/b&gt;&lt;br&gt;{{ config('app.name') }}">{{ auth()->user()->html_signature }}</textarea>
          <div class="small text-muted-c mt-1">Appended automatically to every Gmail reply you send.</div>
          <button type="submit" class="btn btn-primary-c btn-sm mt-3">Save Signature</button>
        </form>
      </div>
    </div>
  </div>

  @if (auth()->user()->isAdmin())
  {{-- Team Accounts --}}
  <div class="col-lg-6">
    <div class="card-c h-100">
      <div class="card-c-body d-flex flex-column">
        <div class="d-flex align-items-center gap-2 mb-3">
          <div class="settings-card-icon" style="background:var(--color-primary-light);color:var(--color-primary);"><i class="bi bi-people-fill"></i></div>
          <h5 class="mb-0">Team Accounts</h5>
        </div>
        <p class="small text-muted-c">Add teammates, set who's an Admin vs a restricted User, assign a Sales Man name, and reset passwords.</p>
        <a href="{{ route('users.index') }}" class="btn btn-outline-c btn-sm mt-auto align-self-start"><i class="bi bi-person-plus-fill me-1"></i>Manage Team Accounts</a>
      </div>
    </div>
  </div>

  {{-- Shared Google OAuth Client — admin only --}}
  <div class="col-lg-6">
    <div class="card-c h-100">
      <div class="card-c-body">
        <div class="d-flex align-items-center gap-2 mb-3">
          <div class="settings-card-icon" style="background:var(--color-warning-light);color:var(--color-warning);"><i class="bi bi-google"></i></div>
          <h5 class="mb-0 flex-grow-1">Google OAuth Client</h5>
          <button type="button" class="btn-icon-sq" title="Setup instructions" data-bs-toggle="modal" data-bs-target="#modalGmailInstructions"><i class="bi bi-info-circle"></i></button>
        </div>
        <p class="small text-muted-c">One shared Google Cloud app every teammate's own "Connect Gmail" (below, in their own Settings) authorizes against. Set this up once.</p>
        <form method="POST" action="{{ route('settings.gmail.credentials') }}">
          @csrf
          <div class="row g-2">
            <div class="col-md-6">
              <label class="form-label small mb-1">Client ID</label>
              <input type="text" name="client_id" class="form-control form-control-sm" value="{{ old('client_id', $gmailSharedClient->client_id) }}" placeholder="xxxxxxxx.apps.googleusercontent.com" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small mb-1">Client Secret</label>
              <input type="password" name="client_secret" class="form-control form-control-sm" placeholder="{{ $gmailSharedClient->client_secret ? '•••••••• (saved — leave blank to keep)' : 'GOCSPX-...' }}">
            </div>
          </div>
          <button type="submit" class="btn btn-outline-c btn-sm mt-2"><i class="bi bi-key-fill me-1"></i>Save Credentials</button>
          <span class="small text-muted-c ms-2">Stored encrypted in the database — not in a file.</span>
        </form>
      </div>
    </div>
  </div>
  @endif

  {{-- Gmail — everyone connects their own --}}
  <div class="col-lg-6">
    <div class="card-c h-100">
      <div class="card-c-body">
        <div class="d-flex align-items-center gap-2 mb-3">
          <div class="settings-card-icon" style="background:var(--color-warning-light);color:var(--color-warning);"><i class="bi bi-envelope-at-fill"></i></div>
          <h5 class="mb-0 flex-grow-1">My Gmail</h5>
          @unless (auth()->user()->isAdmin())
          <button type="button" class="btn-icon-sq" title="Setup instructions" data-bs-toggle="modal" data-bs-target="#modalGmailInstructions"><i class="bi bi-info-circle"></i></button>
          @endunless
        </div>
        <div class="d-flex align-items-center justify-content-between p-3 mb-3" style="background:var(--bg-surface-2);border-radius:var(--radius-md);">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-google" style="font-size:20px;color:var(--color-danger);"></i>
            <div>
              <div class="fw-600 small">{{ $gmailAccount->email ?: 'No account connected' }}</div>
              <div class="chip {{ $gmailAccount->isConnected() ? 'chip-success' : 'chip-danger' }}"><i class="bi bi-circle-fill"></i>{{ $gmailAccount->isConnected() ? 'Connected' : 'Disconnected' }}</div>
            </div>
          </div>
        </div>

        @if ($gmailAccount->isConnected())
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <form method="POST" action="{{ route('settings.gmail.sync-now') }}" class="d-inline">
              @csrf
              <button type="submit" class="btn btn-outline-c btn-sm"><i class="bi bi-arrow-repeat me-1"></i>Sync Now</button>
            </form>
            <form method="POST" action="{{ route('settings.gmail.disconnect') }}" class="d-inline">
              @csrf
              <button type="submit" class="btn btn-danger-c btn-sm"><i class="bi bi-x-circle me-1"></i>Disconnect Gmail</button>
            </form>
          </div>
          <p class="small text-muted-c mt-2 mb-0">
            @if ($gmailAccount->last_synced_at)
              Last synced {{ $gmailAccount->last_synced_at->diffForHumans() }} — {{ $gmailAccount->last_sync_message }}
            @else
              Never synced yet. New inbox mail is also pulled automatically every few minutes.
            @endif
          </p>
        @elseif ($gmailAccount->hasCredentials())
          <a href="{{ route('settings.gmail.connect') }}" class="btn btn-outline-c btn-sm">
            <i class="bi bi-plug-fill me-1"></i>Connect Gmail
          </a>
          <p class="small text-muted-c mt-2 mb-0">Redirects to Google's real sign-in to authorize inbox read + send access for your own inbox.</p>
        @else
          <button type="button" class="btn btn-outline-c btn-sm" disabled>
            <i class="bi bi-plug-fill me-1"></i>Connect Gmail
          </button>
          <p class="small text-muted-c mt-2 mb-0">Ask your admin to save the Google OAuth Client ID/Secret first.</p>
        @endif
      </div>
    </div>
  </div>

  {{-- Leads Data Source status — everyone sees it, configure is admin-only --}}
  <div class="col-12">
    <div class="card-c">
      <div class="card-c-body">
        <div class="d-flex align-items-center gap-2 mb-3">
          <div class="settings-card-icon" style="background:var(--color-info-light);color:var(--color-info);"><i class="bi bi-google"></i></div>
          <h5 class="mb-0">Leads Data Source</h5>
          <span class="chip {{ $syncSetting->is_enabled ? 'chip-success' : 'chip-neutral' }} ms-auto"><i class="bi bi-circle-fill"></i>{{ $syncSetting->is_enabled ? 'Enabled' : 'Disabled' }}</span>
        </div>

        @if (auth()->user()->isAdmin())
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
        @endif

        <div class="d-flex flex-wrap gap-3 align-items-center small text-muted-c mt-3 pt-3" style="border-top:1px solid var(--border-color);">
          @if ($syncSetting->last_synced_at)
            <span>Last synced (IST) <b class="text-reset">{{ $syncSetting->last_synced_at->timezone('Asia/Kolkata')->format('d M Y, h:i A') }}</b> — {{ $syncSetting->last_synced_at->diffForHumans() }}</span>
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
        @if ($syncSetting->is_enabled && (! $syncSetting->last_synced_at || $syncSetting->last_synced_at->lt(now()->subMinutes(max(30, $syncSetting->interval_minutes * 3)))))
        <div class="small mt-2" style="color:var(--color-warning);">
          <i class="bi bi-exclamation-triangle-fill me-1"></i>Auto-sync looks stalled — this usually means the hosting cron job for <code>php artisan schedule:run</code> isn't set up. Ask your admin to check hPanel's Cron Jobs.
        </div>
        @endif
      </div>
    </div>
  </div>

  {{-- WhatsApp — my own template + defaults --}}
  <div class="col-12">
    <div class="card-c">
      <div class="card-c-body">
        <div class="d-flex align-items-center gap-2 mb-3">
          <div class="settings-card-icon" style="background:var(--color-whatsapp-light);color:var(--color-whatsapp);"><i class="bi bi-whatsapp"></i></div>
          <h5 class="mb-0">My WhatsApp Templates</h5>
        </div>

        <form method="POST" action="{{ route('settings.whatsapp-personal') }}" class="row g-3 mb-4 pb-4" style="border-bottom:1px solid var(--border-color);">
          @csrf @method('PUT')
          <div class="col-md-8">
            <label class="form-label">My Default Template</label>
            <select class="form-select" name="whatsapp_default_template_id">
              <option value="">— Use company default —</option>
              @if ($myTemplates->isNotEmpty())
                <optgroup label="My templates">
                  @foreach ($myTemplates as $t)
                    <option value="{{ $t->id }}" {{ (string) auth()->user()->whatsapp_default_template_id === (string) $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                  @endforeach
                </optgroup>
              @endif
              @if ($companyTemplates->isNotEmpty())
                <optgroup label="Company templates">
                  @foreach ($companyTemplates as $t)
                    <option value="{{ $t->id }}" {{ (string) auth()->user()->whatsapp_default_template_id === (string) $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                  @endforeach
                </optgroup>
              @endif
            </select>
            <div class="small text-muted-c mt-1">Used by the WhatsApp button on every contact — no picker, one click. Falls back to the company default below if you leave this unset.</div>
          </div>
          <div class="col-md-4 d-flex align-items-end"><button type="submit" class="btn btn-primary-c btn-sm">Save My Default</button></div>
        </form>

        <div class="d-flex align-items-center justify-content-between mb-2">
          <div class="fw-600 small">My Templates</div>
          <button type="button" class="btn btn-outline-c btn-sm" data-bs-toggle="modal" data-bs-target="#modalAddTemplate"><i class="bi bi-plus-lg me-1"></i>Add My Template</button>
        </div>
        <div class="table-responsive-c mb-2">
          <table class="table-c">
            <thead><tr><th>Name</th><th>Message</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
              @forelse ($myTemplates as $t)
              <tr>
                <td class="fw-600" style="white-space:nowrap;">{{ $t->name }}</td>
                <td class="small text-muted-c">{{ \Illuminate\Support\Str::limit($t->message, 90) }}</td>
                <td>
                  <div class="d-flex gap-1 justify-content-end">
                    <button type="button" class="btn-icon-sq js-edit-template" data-id="{{ $t->id }}" data-name="{{ $t->name }}" data-message="{{ $t->message }}" title="Edit" data-bs-toggle="tooltip"><i class="bi bi-pencil"></i></button>
                    <form method="POST" action="{{ route('whatsapp.destroy', $t) }}" data-confirm="Delete template '{{ $t->name }}'?">
                      @csrf @method('DELETE')
                      <button type="submit" class="btn-icon-sq danger" title="Delete" data-bs-toggle="tooltip"><i class="bi bi-trash"></i></button>
                    </form>
                  </div>
                </td>
              </tr>
              @empty
              <tr><td colspan="3" class="text-center small text-muted-c py-3">No personal templates yet — add one above.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <p class="small text-muted-c mb-0">Placeholders: <code>{name}</code> <code>{company}</code> <code>{employee}</code> <code>{date}</code></p>

        @if (auth()->user()->isAdmin())
        <hr class="my-4">
        <div class="d-flex align-items-center gap-2 mb-3">
          <h6 class="mb-0">Company Settings (Admin)</h6>
        </div>
        <form method="POST" action="{{ route('settings.whatsapp') }}" class="row g-3 mb-4 pb-4" style="border-bottom:1px solid var(--border-color);">
          @csrf @method('PUT')
          <div class="col-md-6">
            <label class="form-label">Sender Number</label>
            <input type="text" name="whatsapp_sender_number" class="form-control" value="{{ $settings['whatsapp_sender_number'] }}" placeholder="+91 90000 12345">
            <div class="small text-muted-c mt-1">
              Informational only — WhatsApp links always send from whichever WhatsApp account is logged into the
              device that clicks the button, so this field can't change who a message is sent "from".
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label">Company Default Template</label>
            <select class="form-select" name="whatsapp_default_template_id">
              <option value="">— None (blank message) —</option>
              @foreach ($companyTemplates as $t)
                <option value="{{ $t->id }}" {{ (string) $settings['whatsapp_default_template_id'] === (string) $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
              @endforeach
            </select>
            <div class="small text-muted-c mt-1">Fallback used for any teammate who hasn't set their own default above.</div>
          </div>
          <div class="col-12"><button type="submit" class="btn btn-primary-c btn-sm">Save Company Settings</button></div>
        </form>

        <div class="d-flex align-items-center justify-content-between mb-2">
          <div class="fw-600 small">Company Templates (visible to everyone)</div>
          <button type="button" class="btn btn-outline-c btn-sm" data-bs-toggle="modal" data-bs-target="#modalAddCompanyTemplate"><i class="bi bi-plus-lg me-1"></i>Add Company Template</button>
        </div>
        <div class="table-responsive-c">
          <table class="table-c">
            <thead><tr><th>Name</th><th>Message</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
              @forelse ($companyTemplates as $t)
              <tr>
                <td class="fw-600" style="white-space:nowrap;">{{ $t->name }}</td>
                <td class="small text-muted-c">{{ \Illuminate\Support\Str::limit($t->message, 90) }}</td>
                <td>
                  <div class="d-flex gap-1 justify-content-end">
                    <button type="button" class="btn-icon-sq js-edit-template" data-id="{{ $t->id }}" data-name="{{ $t->name }}" data-message="{{ $t->message }}" title="Edit" data-bs-toggle="tooltip"><i class="bi bi-pencil"></i></button>
                    <form method="POST" action="{{ route('whatsapp.destroy', $t) }}" data-confirm="Delete template '{{ $t->name }}'?">
                      @csrf @method('DELETE')
                      <button type="submit" class="btn-icon-sq danger" title="Delete" data-bs-toggle="tooltip"><i class="bi bi-trash"></i></button>
                    </form>
                  </div>
                </td>
              </tr>
              @empty
              <tr><td colspan="3" class="text-center small text-muted-c py-3">No company templates yet — add one above.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @endif
      </div>
    </div>
  </div>

  @if (auth()->user()->isAdmin())
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
  @endif
</div>

@include('settings._gmail_instructions_modal')

@push('modals')
<div class="modal fade" id="modalAddTemplate" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="{{ route('whatsapp.store') }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Add My WhatsApp Template</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label">Name *</label><input type="text" name="name" class="form-control" required></div>
          <div class="mb-2"><label class="form-label">Message *</label><textarea class="form-control" name="message" rows="5" required></textarea></div>
          <p class="small text-muted-c mb-0">Placeholders: <code>{name}</code> <code>{company}</code> <code>{employee}</code> <code>{date}</code></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light-c" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary-c"><i class="bi bi-check2 me-1"></i>Save Template</button>
        </div>
      </form>
    </div>
  </div>
</div>

@if (auth()->user()->isAdmin())
<div class="modal fade" id="modalAddCompanyTemplate" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="{{ route('whatsapp.store') }}">
        @csrf
        <input type="hidden" name="is_shared" value="1">
        <div class="modal-header">
          <h5 class="modal-title">Add Company WhatsApp Template</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label">Name *</label><input type="text" name="name" class="form-control" required></div>
          <div class="mb-2"><label class="form-label">Message *</label><textarea class="form-control" name="message" rows="5" required></textarea></div>
          <p class="small text-muted-c mb-0">Placeholders: <code>{name}</code> <code>{company}</code> <code>{employee}</code> <code>{date}</code></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light-c" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary-c"><i class="bi bi-check2 me-1"></i>Save Template</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endif

<div class="modal fade" id="modalEditTemplate" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" id="formEditTemplate">
        @csrf @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title">Edit WhatsApp Template</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label">Name *</label><input type="text" name="name" class="form-control" required></div>
          <div class="mb-2"><label class="form-label">Message *</label><textarea class="form-control" name="message" rows="5" required></textarea></div>
          <p class="small text-muted-c mb-0">Placeholders: <code>{name}</code> <code>{company}</code> <code>{employee}</code> <code>{date}</code></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light-c" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary-c"><i class="bi bi-check2 me-1"></i>Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endpush
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  function toggleSyncFields() {
    var sel = document.getElementById('settingsSyncSourceType');
    if (!sel) return;
    var type = sel.value;
    document.getElementById('settingsSyncSheetField').style.display = type === 'google_sheet' ? '' : 'none';
    document.getElementById('settingsSyncExcelField').style.display = type === 'excel_upload' ? '' : 'none';
  }
  var sel = document.getElementById('settingsSyncSourceType');
  if (sel) { sel.addEventListener('change', toggleSyncFields); toggleSyncFields(); }

  document.querySelectorAll('.js-edit-template').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var form = document.getElementById('formEditTemplate');
      form.action = '{{ url('whatsapp') }}/' + btn.dataset.id;
      form.querySelector('[name=name]').value = btn.dataset.name || '';
      form.querySelector('[name=message]').value = btn.dataset.message || '';
      new bootstrap.Modal(document.getElementById('modalEditTemplate')).show();
    });
  });
});
</script>
@endpush
