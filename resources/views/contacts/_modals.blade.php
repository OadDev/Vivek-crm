{{-- Add Contact --}}
<div class="modal fade" id="modalAddContact" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="{{ route('contacts.store') }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Add Contact</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Full Name *</label><input type="text" name="name" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label">Company</label><input type="text" name="company" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label">WhatsApp Number</label><input type="text" name="whatsapp" class="form-control" placeholder="+91XXXXXXXXXX"></div>
            <div class="col-md-6"><label class="form-label">Designation</label><input type="text" name="designation" class="form-control"></div>
            <div class="col-md-6">
              <label class="form-label">Status</label>
              <select class="form-select" name="status">
                @foreach (\App\Models\Contact::statusOptions() as $key => $label)
                  <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6"><label class="form-label">Last Contacted Date</label><input type="date" name="last_contacted_at" class="form-control" value="{{ now()->format('Y-m-d') }}"></div>
            <div class="col-12"><label class="form-label">Notes</label><textarea class="form-control" name="notes" rows="3"></textarea></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light-c" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary-c"><i class="bi bi-check2 me-1"></i>Save Contact</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Edit Contact (fields populated by JS from the clicked row) --}}
<div class="modal fade" id="modalEditContact" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" id="formEditContact">
        @csrf
        @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title">Edit Contact</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Full Name *</label><input type="text" name="name" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label">Company</label><input type="text" name="company" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label">WhatsApp Number</label><input type="text" name="whatsapp" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">Designation</label><input type="text" name="designation" class="form-control"></div>
            <div class="col-md-6">
              <label class="form-label">Status</label>
              <select class="form-select" name="status">
                @foreach (\App\Models\Contact::statusOptions() as $key => $label)
                  <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6"><label class="form-label">Last Contacted Date</label><input type="date" name="last_contacted_at" class="form-control"></div>
            <div class="col-12"><label class="form-label">Notes</label><textarea class="form-control" name="notes" rows="3"></textarea></div>
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
