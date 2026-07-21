{{-- Add Product --}}
<div class="modal fade" id="modalAddProduct" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="{{ route('products.store') }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Add Product</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-4"><label class="form-label">Product Code *</label><input type="text" name="code" class="form-control" placeholder="e.g. PRD-1001" required></div>
            <div class="col-md-8"><label class="form-label">Product Name *</label><input type="text" name="name" class="form-control" placeholder="e.g. Stainless Steel Hinge" required></div>
            <div class="col-md-4"><label class="form-label">Size</label><input type="text" name="size" class="form-control" placeholder="e.g. 4 inch"></div>
            <div class="col-md-4"><label class="form-label">Weight</label><input type="text" name="weight" class="form-control" placeholder="e.g. 250g"></div>
            <div class="col-md-4">
              <label class="form-label">Unit</label>
              <select class="form-select" name="unit"><option>PCS</option><option>KG</option><option>BOX</option><option>SET</option></select>
            </div>
            <div class="col-md-6"><label class="form-label">Rate (₹)</label><input type="number" step="0.01" name="rate" class="form-control" placeholder="e.g. 145.00" required></div>
            <div class="col-md-6"><label class="form-label">Specification</label><input type="text" name="specification" class="form-control" placeholder="e.g. SS304, Matte Finish"></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light-c" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary-c"><i class="bi bi-check2 me-1"></i>Save Product</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Edit Product --}}
<div class="modal fade" id="modalEditProduct" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" id="formEditProduct">
        @csrf @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title">Edit Product</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-4"><label class="form-label">Product Code *</label><input type="text" name="code" class="form-control" required></div>
            <div class="col-md-8"><label class="form-label">Product Name *</label><input type="text" name="name" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label">Size</label><input type="text" name="size" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">Weight</label><input type="text" name="weight" class="form-control"></div>
            <div class="col-md-4">
              <label class="form-label">Unit</label>
              <select class="form-select" name="unit"><option>PCS</option><option>KG</option><option>BOX</option><option>SET</option></select>
            </div>
            <div class="col-md-6"><label class="form-label">Rate (₹)</label><input type="number" step="0.01" name="rate" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label">Specification</label><input type="text" name="specification" class="form-control"></div>
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

{{-- Standard Reference Tables (quick-lookup popup, one accordion item per table) --}}
<div class="modal fade" id="modalReferenceTables" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content" style="max-height:88vh;">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-lightning-charge-fill me-1" style="color:var(--color-warning);"></i>Standard Reference Tables</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="overflow-y:auto;">
        <p class="small text-muted-c">Quick-lookup reference tables (ASTM/EN copper tube standards, etc.) while filling in product specifications. Each table's data can be replaced by pasting a fresh range copied directly from Excel or Google Sheets.</p>

        <div class="accordion" id="referenceTablesAccordion">
          @forelse ($referenceTables as $rt)
          <div class="accordion-item" style="border-color:var(--border-color);background:var(--bg-surface);">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#rtBody{{ $rt->id }}" style="background:var(--bg-surface-2);color:var(--text-primary);">
                <span class="fw-600">{{ $rt->title }}</span>
              </button>
            </h2>
            <div id="rtBody{{ $rt->id }}" class="accordion-collapse collapse" data-bs-parent="#referenceTablesAccordion">
              <div class="accordion-body">
                @if ($rt->description)
                  <p class="small text-muted-c">{{ $rt->description }}</p>
                @endif
                <div class="table-responsive-c mb-3" style="max-height:320px;overflow-y:auto;">
                  <table class="table-c">
                    <thead>
                      <tr>@foreach ($rt->headers as $h)<th>{{ $h }}</th>@endforeach</tr>
                    </thead>
                    <tbody>
                      @foreach ($rt->rows as $row)
                      <tr>@foreach ($row as $cell)<td>{{ $cell }}</td>@endforeach</tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>

                <div class="d-flex gap-2 mb-2">
                  <button type="button" class="btn btn-outline-c btn-sm js-toggle-edit-rt" data-target="rtEdit{{ $rt->id }}"><i class="bi bi-clipboard-check me-1"></i>Replace via Paste</button>
                  <form method="POST" action="{{ route('reference-tables.destroy', $rt) }}" data-confirm="Remove table &quot;{{ $rt->title }}&quot;?">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline-c btn-sm text-danger"><i class="bi bi-trash me-1"></i>Delete Table</button>
                  </form>
                </div>

                <form method="POST" action="{{ route('reference-tables.update', $rt) }}" id="rtEdit{{ $rt->id }}" class="d-none" style="background:var(--bg-surface-2);border-radius:var(--radius-sm);padding:12px;">
                  @csrf @method('PUT')
                  <div class="row g-2">
                    <div class="col-md-6"><label class="form-label">Title</label><input type="text" name="title" class="form-control form-control-sm" value="{{ $rt->title }}" required></div>
                    <div class="col-md-6"><label class="form-label">Description</label><input type="text" name="description" class="form-control form-control-sm" value="{{ $rt->description }}"></div>
                    <div class="col-12">
                      <label class="form-label">Paste new data (optional — select the range in Google Sheets/Excel including the header row, copy, and paste here to replace all rows)</label>
                      <textarea name="pasted_data" class="form-control form-control-sm" rows="4" placeholder="Paste tab-separated data here to replace this table's rows..."></textarea>
                    </div>
                    <div class="col-12"><button type="submit" class="btn btn-primary-c btn-sm">Save Table</button></div>
                  </div>
                </form>
              </div>
            </div>
          </div>
          @empty
          <div class="empty-state"><div class="es-icon"><i class="bi bi-table"></i></div><h6>No reference tables yet</h6><p>Add one below by pasting data copied from Excel or Google Sheets.</p></div>
          @endforelse
        </div>

        <hr>
        <h6 class="fw-700">Add New Reference Table</h6>
        <form method="POST" action="{{ route('reference-tables.store') }}" class="row g-2">
          @csrf
          <div class="col-md-6"><label class="form-label">Title *</label><input type="text" name="title" class="form-control form-control-sm" placeholder="e.g. ASTM B88 Type K — Dimensions (inches)" required></div>
          <div class="col-md-6"><label class="form-label">Description</label><input type="text" name="description" class="form-control form-control-sm" placeholder="Optional short note"></div>
          <div class="col-12">
            <label class="form-label">Paste data from Excel/Google Sheets *</label>
            <textarea name="pasted_data" class="form-control form-control-sm" rows="5" placeholder="Select the range including the header row in your sheet, copy (Ctrl/Cmd+C), and paste it here..." required></textarea>
          </div>
          <div class="col-12"><button type="submit" class="btn btn-primary-c btn-sm"><i class="bi bi-plus-lg me-1"></i>Add Table</button></div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light-c" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('.js-toggle-edit-rt').forEach(function (btn) {
  btn.addEventListener('click', function () {
    document.getElementById(btn.dataset.target).classList.toggle('d-none');
  });
});
</script>
@endpush
