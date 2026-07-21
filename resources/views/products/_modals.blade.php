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

{{-- Standardized Copper Reference (quick-lookup popup) --}}
<div class="modal fade" id="modalCopperStandards" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-lightning-charge-fill me-1" style="color:var(--color-warning);"></i>Standard Copper Conductor Reference</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="small text-muted-c">Indicative standard values for quick lookup while filling in product specifications. Edit or add rows to match your exact spec sheet.</p>
        <div class="table-responsive-c mb-3">
          <table class="table-c">
            <thead>
              <tr><th>Size</th><th>Cross-section (sqmm)</th><th>Weight (kg/km)</th><th>Current Rating (A)</th><th>Standard</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
              @forelse ($copperStandards as $cs)
              <tr>
                <td class="fw-600">{{ $cs->size_designation }}</td>
                <td>{{ $cs->cross_section_sqmm }}</td>
                <td>{{ $cs->weight_per_km_kg }}</td>
                <td>{{ $cs->current_rating_amps }}</td>
                <td class="small text-muted-c">{{ $cs->standard_reference }}</td>
                <td>
                  <div class="d-flex gap-1 justify-content-end">
                    <button type="button" class="btn-icon-sq js-edit-copper"
                      data-id="{{ $cs->id }}" data-size="{{ $cs->size_designation }}" data-csa="{{ $cs->cross_section_sqmm }}"
                      data-weight="{{ $cs->weight_per_km_kg }}" data-amps="{{ $cs->current_rating_amps }}" data-ref="{{ $cs->standard_reference }}"
                      title="Edit"><i class="bi bi-pencil"></i></button>
                    <form method="POST" action="{{ route('copper-standards.destroy', $cs) }}" data-confirm="Remove this reference row?">
                      @csrf @method('DELETE')
                      <button type="submit" class="btn-icon-sq danger" title="Delete"><i class="bi bi-trash"></i></button>
                    </form>
                  </div>
                </td>
              </tr>
              @empty
              <tr><td colspan="6" class="text-center small text-muted-c py-4">No reference rows yet.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <form method="POST" action="{{ route('copper-standards.store') }}" id="formAddCopper" class="row g-2 align-items-end">
          @csrf
          <div class="col"><label class="form-label">Size</label><input type="text" name="size_designation" class="form-control form-control-sm" placeholder="e.g. 2.5 sqmm" required></div>
          <div class="col"><label class="form-label">Cross-section</label><input type="text" name="cross_section_sqmm" class="form-control form-control-sm"></div>
          <div class="col"><label class="form-label">Weight kg/km</label><input type="text" name="weight_per_km_kg" class="form-control form-control-sm"></div>
          <div class="col"><label class="form-label">Current (A)</label><input type="text" name="current_rating_amps" class="form-control form-control-sm"></div>
          <div class="col"><label class="form-label">Standard</label><input type="text" name="standard_reference" class="form-control form-control-sm" placeholder="e.g. IS 8130"></div>
          <div class="col-auto"><button type="submit" class="btn btn-primary-c btn-sm"><i class="bi bi-plus-lg"></i></button></div>
        </form>

        <form method="POST" id="formEditCopper" class="row g-2 align-items-end mt-2 d-none" style="background:var(--color-primary-light);border-radius:var(--radius-sm);padding:10px;">
          @csrf @method('PUT')
          <div class="col-12 small fw-600 mb-1">Editing row — update values and save</div>
          <div class="col"><input type="text" name="size_designation" class="form-control form-control-sm" required></div>
          <div class="col"><input type="text" name="cross_section_sqmm" class="form-control form-control-sm"></div>
          <div class="col"><input type="text" name="weight_per_km_kg" class="form-control form-control-sm"></div>
          <div class="col"><input type="text" name="current_rating_amps" class="form-control form-control-sm"></div>
          <div class="col"><input type="text" name="standard_reference" class="form-control form-control-sm"></div>
          <div class="col-auto d-flex gap-1">
            <button type="submit" class="btn btn-primary-c btn-sm"><i class="bi bi-check2"></i></button>
            <button type="button" class="btn btn-light-c btn-sm" id="cancelEditCopperBtn"><i class="bi bi-x"></i></button>
          </div>
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
document.querySelectorAll('.js-edit-copper').forEach(function (btn) {
  btn.addEventListener('click', function () {
    var form = document.getElementById('formEditCopper');
    form.action = '{{ url('copper-standards') }}/' + btn.dataset.id;
    form.querySelector('[name=size_designation]').value = btn.dataset.size || '';
    form.querySelector('[name=cross_section_sqmm]').value = btn.dataset.csa || '';
    form.querySelector('[name=weight_per_km_kg]').value = btn.dataset.weight || '';
    form.querySelector('[name=current_rating_amps]').value = btn.dataset.amps || '';
    form.querySelector('[name=standard_reference]').value = btn.dataset.ref || '';
    form.classList.remove('d-none');
    document.getElementById('formAddCopper').classList.add('d-none');
  });
});
document.getElementById('cancelEditCopperBtn').addEventListener('click', function () {
  document.getElementById('formEditCopper').classList.add('d-none');
  document.getElementById('formAddCopper').classList.remove('d-none');
});
</script>
@endpush
