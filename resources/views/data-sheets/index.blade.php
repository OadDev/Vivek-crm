@extends('layouts.app')

@section('title', 'Data Sheets')

@section('content')
<div class="breadcrumb-c"><a href="{{ route('dashboard') }}">Home</a><i class="bi bi-chevron-right"></i><span class="current">Data Sheets</span></div>
<div class="page-header">
  <div>
    <div class="page-title">Data Sheets</div>
    <div class="page-subtitle">Import any other list — courier companies, GST numbers, technical datasheets — from a Google Sheet or Excel file. Manual sync only.</div>
  </div>
  @if (auth()->user()->isAdmin())
  <div class="d-flex gap-2 flex-wrap">
    <button class="btn btn-primary-c btn-sm" data-bs-toggle="modal" data-bs-target="#modalAddDataSheet"><i class="bi bi-plus-lg me-1"></i>Add Data Sheet</button>
  </div>
  @endif
</div>

<div class="row g-3">
  @forelse ($dataSheets as $sheet)
  <div class="col-md-6 col-lg-4">
    <div class="card-c h-100">
      <div class="card-c-body d-flex flex-column">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <h5 class="mb-0">{{ $sheet->name }}</h5>
          @if (auth()->user()->isAdmin())
          <div class="dropdown">
            <button class="btn-icon-sq" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><button type="button" class="dropdown-item js-edit-datasheet"
                data-id="{{ $sheet->id }}" data-name="{{ $sheet->name }}" data-source-type="{{ $sheet->source_type }}"
                data-google-sheet-url="{{ $sheet->google_sheet_url }}" data-excel-original-name="{{ $sheet->excel_original_name }}">Edit</button></li>
              <li>
                <form method="POST" action="{{ route('data-sheets.destroy', $sheet) }}" data-confirm="Delete &quot;{{ $sheet->name }}&quot; and all its imported rows? This cannot be undone.">
                  @csrf @method('DELETE')
                  <button type="submit" class="dropdown-item text-danger">Delete</button>
                </form>
              </li>
            </ul>
          </div>
          @endif
        </div>
        <div class="small text-muted-c mb-2">
          <span class="chip {{ $sheet->source_type === 'google_sheet' ? 'chip-neutral' : 'chip-neutral' }}">{{ $sheet->source_type === 'google_sheet' ? 'Google Sheet' : 'Excel Upload' }}</span>
          <span class="chip chip-neutral">{{ $sheet->rows_count }} row(s)</span>
        </div>
        <div class="small text-muted-c flex-fill">
          @if ($sheet->last_synced_at)
            Last synced (IST) <b class="text-reset">{{ $sheet->last_synced_at->timezone('Asia/Kolkata')->format('d M, h:i A') }}</b>
            <span class="chip {{ $sheet->last_sync_status === 'success' ? 'chip-success' : 'chip-danger' }} ms-1">{{ ucfirst($sheet->last_sync_status ?? '') }}</span>
          @else
            Never synced yet
          @endif
        </div>
        <div class="d-flex gap-2 mt-3">
          <a href="{{ route('data-sheets.show', $sheet) }}" class="btn btn-outline-c btn-sm flex-fill"><i class="bi bi-table me-1"></i>Open</a>
          <form method="POST" action="{{ route('data-sheets.sync-now', $sheet) }}">
            @csrf
            <button type="submit" class="btn btn-primary-c btn-sm" title="Sync Now" data-bs-toggle="tooltip"><i class="bi bi-arrow-repeat"></i></button>
          </form>
        </div>
      </div>
    </div>
  </div>
  @empty
  <div class="col-12">
    <div class="card-c"><div class="card-c-body">
      <div class="empty-state">
        <div class="es-icon"><i class="bi bi-database"></i></div>
        <h6>No data sheets yet</h6>
        <p>{{ auth()->user()->isAdmin() ? 'Add one from a Google Sheet link or an uploaded Excel file.' : 'Ask an admin to add one.' }}</p>
      </div>
    </div></div>
  </div>
  @endforelse
</div>
@endsection

@if (auth()->user()->isAdmin())
@push('modals')
<div class="modal fade" id="modalAddDataSheet" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="{{ route('data-sheets.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Add Data Sheet</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" placeholder="e.g. Courier Companies" required></div>
          <div class="mb-3">
            <label class="form-label">Source</label>
            <select class="form-select" name="source_type" id="addSourceType">
              <option value="google_sheet">Google Sheet (public link)</option>
              <option value="excel_upload">Uploaded Excel / CSV File</option>
            </select>
          </div>
          <div class="mb-3" id="addSheetField"><label class="form-label">Google Sheet URL</label><input type="url" class="form-control" name="google_sheet_url" placeholder="https://docs.google.com/spreadsheets/d/..."></div>
          <div class="mb-3" id="addExcelField" style="display:none;"><label class="form-label">Excel / CSV File</label><input type="file" class="form-control" name="sync_file" accept=".xlsx,.xls,.csv"></div>
          <div class="small text-muted-c">First row must be column headers. This imports immediately, and only re-imports when you click Sync Now afterward.</div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light-c" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary-c"><i class="bi bi-check2 me-1"></i>Add &amp; Import</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="modalEditDataSheet" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" id="formEditDataSheet" enctype="multipart/form-data">
        @csrf @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title">Edit Data Sheet</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" id="editName" required></div>
          <div class="mb-3">
            <label class="form-label">Source</label>
            <select class="form-select" name="source_type" id="editSourceType">
              <option value="google_sheet">Google Sheet (public link)</option>
              <option value="excel_upload">Uploaded Excel / CSV File</option>
            </select>
          </div>
          <div class="mb-3" id="editSheetField"><label class="form-label">Google Sheet URL</label><input type="url" class="form-control" name="google_sheet_url" id="editGoogleSheetUrl"></div>
          <div class="mb-3" id="editExcelField" style="display:none;"><label class="form-label">Excel / CSV File <span id="editExcelCurrent" class="text-muted-c"></span></label><input type="file" class="form-control" name="sync_file" accept=".xlsx,.xls,.csv"></div>
          <div class="small text-muted-c">Saving here only updates the source config — click Sync Now on the card to re-import.</div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light-c" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary-c">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  function toggleFields(sel, sheetField, excelField) {
    var isExcel = sel.value === 'excel_upload';
    sheetField.style.display = isExcel ? 'none' : '';
    excelField.style.display = isExcel ? '' : 'none';
  }

  var addSel = document.getElementById('addSourceType');
  var addSheetField = document.getElementById('addSheetField');
  var addExcelField = document.getElementById('addExcelField');
  addSel.addEventListener('change', function () { toggleFields(addSel, addSheetField, addExcelField); });

  var editSel = document.getElementById('editSourceType');
  var editSheetField = document.getElementById('editSheetField');
  var editExcelField = document.getElementById('editExcelField');
  editSel.addEventListener('change', function () { toggleFields(editSel, editSheetField, editExcelField); });

  document.querySelectorAll('.js-edit-datasheet').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var form = document.getElementById('formEditDataSheet');
      form.action = '{{ url('data-sheets') }}/' + btn.dataset.id;
      document.getElementById('editName').value = btn.dataset.name || '';
      document.getElementById('editGoogleSheetUrl').value = btn.dataset.googleSheetUrl || '';
      document.getElementById('editExcelCurrent').textContent = btn.dataset.excelOriginalName ? '(current: ' + btn.dataset.excelOriginalName + ')' : '';
      editSel.value = btn.dataset.sourceType;
      toggleFields(editSel, editSheetField, editExcelField);
      new bootstrap.Modal(document.getElementById('modalEditDataSheet')).show();
    });
  });
});
</script>
@endpush
@endif
