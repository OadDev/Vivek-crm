@extends('layouts.app')

@section('title', 'Import Contacts')

@section('content')
<div class="breadcrumb-c"><a href="{{ route('dashboard') }}">Home</a><i class="bi bi-chevron-right"></i><a href="{{ route('contacts.index') }}">Contacts</a><i class="bi bi-chevron-right"></i><span class="current">Import</span></div>
<div class="page-header">
  <div>
    <div class="page-title">Import Contacts</div>
    <div class="page-subtitle">Upload an Excel (.xlsx/.xls) or CSV file to bulk-add or update contacts.</div>
  </div>
  <a href="{{ route('contacts.index') }}" class="btn btn-outline-c btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to Contacts</a>
</div>

<div class="card-c" style="max-width:560px;">
  <div class="card-c-body">
    <form method="POST" action="{{ route('contacts.import') }}" enctype="multipart/form-data">
      @csrf
      <div class="text-center py-4">
        <div class="mx-auto mb-3 d-flex align-items-center justify-content-center" style="width:64px;height:64px;border-radius:50%;background:var(--color-primary-light);color:var(--color-primary);font-size:28px;">
          <i class="bi bi-file-earmark-spreadsheet-fill"></i>
        </div>
        <p class="fw-600 mb-1">Select your .xlsx, .xls or .csv file</p>
        <p class="small text-muted-c">Recognized columns: Name, Company, Email, WhatsApp, Designation, Status (optional), Last Contacted / Date, Notes.</p>
        <input type="file" name="file" class="form-control mt-2" accept=".xlsx,.xls,.csv" required>
        @error('file')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
      </div>
      <button type="submit" class="btn btn-primary-c w-100"><i class="bi bi-upload me-1"></i>Import Contacts</button>
    </form>
  </div>
</div>
@endsection
