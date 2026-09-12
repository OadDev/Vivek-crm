@extends('layouts.app')

@section('title', 'Team Accounts')

@section('content')
<div class="breadcrumb-c"><a href="{{ route('dashboard') }}">Home</a><i class="bi bi-chevron-right"></i><a href="{{ route('settings.index') }}">Settings</a><i class="bi bi-chevron-right"></i><span class="current">Team Accounts</span></div>
<div class="page-header">
  <div>
    <div class="page-title">Team Accounts</div>
    <div class="page-subtitle">Admins get full access; Users get day-to-day CRM access only (view/edit contacts, WhatsApp, Gmail replies) — no Settings, imports/export, Add Contact, or account management. Set a Sales Man name to restrict a user to only their own leads.</div>
  </div>
  <button class="btn btn-primary-c btn-sm" data-bs-toggle="modal" data-bs-target="#modalAddUser"><i class="bi bi-person-plus-fill me-1"></i>Add Account</button>
</div>

<div class="card-c">
  <div class="card-c-body">
    <div class="table-responsive-c">
      <table class="table-c">
        <thead>
          <tr><th>Name</th><th>Email</th><th>Role</th><th>Sales Man</th><th class="text-end">Actions</th></tr>
        </thead>
        <tbody>
          @foreach ($users as $u)
          <tr>
            <td class="fw-600" data-label="Name">{{ $u->name }}{{ $u->id === auth()->id() ? ' (you)' : '' }}</td>
            <td data-label="Email">{{ $u->email }}</td>
            <td data-label="Role"><span class="chip {{ $u->isAdmin() ? 'chip-success' : 'chip-neutral' }}">{{ ucfirst($u->role) }}</span></td>
            <td data-label="Sales Man">{{ $u->sales_man ?: '—' }}</td>
            <td class="td-plain">
              <div class="d-flex gap-1 justify-content-end">
                <button type="button" class="btn-icon-sq js-edit-user"
                  data-id="{{ $u->id }}" data-name="{{ $u->name }}" data-email="{{ $u->email }}" data-role="{{ $u->role }}" data-sales-man="{{ $u->sales_man }}"
                  title="Edit" data-bs-toggle="tooltip"><i class="bi bi-pencil"></i></button>
                <form method="POST" action="{{ route('users.reset-password', $u) }}" data-confirm="Reset the password for {{ $u->name }}? A new temporary password will be shown once.">
                  @csrf
                  <button type="submit" class="btn-icon-sq" title="Reset Password" data-bs-toggle="tooltip"><i class="bi bi-key"></i></button>
                </form>
                @if ($u->id !== auth()->id())
                <form method="POST" action="{{ route('users.destroy', $u) }}" data-confirm="Remove {{ $u->name }}'s account? This cannot be undone.">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn-icon-sq danger" title="Remove" data-bs-toggle="tooltip"><i class="bi bi-trash"></i></button>
                </form>
                @endif
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection

@push('modals')
<div class="modal fade" id="modalAddUser" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="{{ route('users.store') }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Add Account</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12"><label class="form-label">Name *</label><input type="text" name="name" class="form-control" required></div>
            <div class="col-12"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" required></div>
            <div class="col-12">
              <label class="form-label">Role</label>
              <select class="form-select" name="role">
                <option value="user">User (restricted)</option>
                <option value="admin">Admin (full access)</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Sales Man Name <span class="text-muted-c">(optional)</span></label>
              <input type="text" name="sales_man" class="form-control" placeholder="Must match the Sales Man column in imported leads">
              <div class="small text-muted-c mt-1">If set, this User only sees leads where Sales Man matches exactly. Leave blank to see all leads.</div>
            </div>
            <div class="col-12">
              <label class="form-label">Password</label>
              <input type="text" name="password" class="form-control" placeholder="Leave blank to auto-generate">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light-c" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary-c"><i class="bi bi-check2 me-1"></i>Create Account</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="modalEditUser" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" id="formEditUser">
        @csrf @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title">Edit Account</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12"><label class="form-label">Name *</label><input type="text" name="name" class="form-control" required></div>
            <div class="col-12"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" required></div>
            <div class="col-12">
              <label class="form-label">Role</label>
              <select class="form-select" name="role">
                <option value="user">User (restricted)</option>
                <option value="admin">Admin (full access)</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Sales Man Name <span class="text-muted-c">(optional)</span></label>
              <input type="text" name="sales_man" class="form-control" placeholder="Must match the Sales Man column in imported leads">
              <div class="small text-muted-c mt-1">If set, this User only sees leads where Sales Man matches exactly. Leave blank to see all leads.</div>
            </div>
          </div>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.js-edit-user').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var form = document.getElementById('formEditUser');
      form.action = '{{ url('users') }}/' + btn.dataset.id;
      form.querySelector('[name=name]').value = btn.dataset.name || '';
      form.querySelector('[name=email]').value = btn.dataset.email || '';
      form.querySelector('[name=role]').value = btn.dataset.role || 'user';
      form.querySelector('[name=sales_man]').value = btn.dataset.salesMan || '';
      new bootstrap.Modal(document.getElementById('modalEditUser')).show();
    });
  });
});
</script>
@endpush
