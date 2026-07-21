@extends('layouts.guest')

@section('title', 'Setup')
@section('card-class', 'wide')

@section('content')
<h5 class="fw-700 mb-1">Installation Wizard</h5>
<p class="text-muted-c small mb-4">Connect your MySQL database and create the first administrator account to get started.</p>

<div class="setup-steps">
  <div class="step active" id="stepIndicator1"></div>
  <div class="step" id="stepIndicator2"></div>
</div>

@if ($errors->any())
  <div class="alert alert-danger small">{{ $errors->first() }}</div>
@endif

<form method="POST" action="{{ route('setup.store') }}" id="setupForm">
  @csrf

  <div id="stepDbCredentials">
    <h6 class="fw-700 mb-3"><i class="bi bi-database-fill me-2" style="color:var(--color-primary);"></i>Step 1 — Database Credentials</h6>
    <div class="row g-3">
      <div class="col-md-8">
        <label class="form-label">Database Host</label>
        <input type="text" name="db_host" id="db_host" class="form-control" value="{{ old('db_host', '127.0.0.1') }}" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Port</label>
        <input type="text" name="db_port" id="db_port" class="form-control" value="{{ old('db_port', '3306') }}" required>
      </div>
      <div class="col-md-12">
        <label class="form-label">Database Name</label>
        <input type="text" name="db_database" id="db_database" class="form-control" value="{{ old('db_database') }}" placeholder="vivek_jain_crm" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Username</label>
        <input type="text" name="db_username" id="db_username" class="form-control" value="{{ old('db_username') }}" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Password</label>
        <input type="password" name="db_password" id="db_password" class="form-control" value="{{ old('db_password') }}">
      </div>
    </div>

    <div id="connectionTestResult" class="small mt-3"></div>

    <button type="button" class="btn btn-primary-c w-100 mt-3" id="testConnectionBtn">
      <i class="bi bi-plug-fill me-1"></i>Test Connection &amp; Continue
    </button>
  </div>

  <div id="stepAdminAccount" class="d-none">
    <h6 class="fw-700 mb-3 mt-2"><i class="bi bi-person-badge-fill me-2" style="color:var(--color-primary);"></i>Step 2 — Create Admin Account</h6>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Full Name</label>
        <input type="text" name="admin_name" class="form-control" value="{{ old('admin_name', 'Vivek Jain') }}" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Email Address</label>
        <input type="email" name="admin_email" class="form-control" value="{{ old('admin_email') }}" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Password</label>
        <input type="password" name="admin_password" class="form-control" minlength="8" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Confirm Password</label>
        <input type="password" name="admin_password_confirmation" class="form-control" minlength="8" required>
      </div>
      <div class="col-12">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="load_demo_data" value="1" id="load_demo_data" checked>
          <label class="form-check-label small" for="load_demo_data">Load sample demo data (contacts, products, templates) so I can explore the app immediately</label>
        </div>
      </div>
    </div>

    <div class="d-flex gap-2 mt-3">
      <button type="button" class="btn btn-outline-c" id="backToStep1Btn"><i class="bi bi-arrow-left me-1"></i>Back</button>
      <button type="submit" class="btn btn-primary-c flex-fill"><i class="bi bi-rocket-takeoff-fill me-1"></i>Complete Setup</button>
    </div>
  </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  var testBtn = document.getElementById('testConnectionBtn');
  var resultEl = document.getElementById('connectionTestResult');
  var step1 = document.getElementById('stepDbCredentials');
  var step2 = document.getElementById('stepAdminAccount');
  var ind1 = document.getElementById('stepIndicator1');
  var ind2 = document.getElementById('stepIndicator2');

  testBtn.addEventListener('click', function () {
    resultEl.innerHTML = '<span class="text-muted-c"><span class="spinner-border spinner-border-sm me-1"></span>Testing connection...</span>';
    testBtn.disabled = true;

    fetch('{{ route('setup.test-connection') }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        db_host: document.getElementById('db_host').value,
        db_port: document.getElementById('db_port').value,
        db_database: document.getElementById('db_database').value,
        db_username: document.getElementById('db_username').value,
        db_password: document.getElementById('db_password').value,
      }),
    })
      .then(function (r) { return r.json().then(function (data) { return { ok: r.ok, data: data }; }); })
      .then(function (res) {
        testBtn.disabled = false;
        if (res.ok && res.data.success) {
          resultEl.innerHTML = '<span class="text-success"><i class="bi bi-check-circle-fill me-1"></i>' + res.data.message + '</span>';
          step1.classList.add('d-none');
          step2.classList.remove('d-none');
          ind2.classList.add('active');
        } else {
          resultEl.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle-fill me-1"></i>' + res.data.message + '</span>';
        }
      })
      .catch(function () {
        testBtn.disabled = false;
        resultEl.innerHTML = '<span class="text-danger">Unexpected error while testing the connection.</span>';
      });
  });

  document.getElementById('backToStep1Btn').addEventListener('click', function () {
    step2.classList.add('d-none');
    step1.classList.remove('d-none');
    ind2.classList.remove('active');
  });
});
</script>
@endpush
