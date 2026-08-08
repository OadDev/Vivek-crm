{{-- Add Contact --}}
<div class="modal fade" id="modalAddContact" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="{{ route('contacts.store') }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Add Contact / Quotation</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Company Name *</label><input type="text" name="company" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label">Contact Person</label><input type="text" name="name" class="form-control" placeholder="Defaults to company name"></div>
            <div class="col-md-6"><label class="form-label">Quote No.</label><input type="text" name="quote_no" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">Quotation Date</label><input type="date" name="quotation_date" class="form-control" value="{{ now()->format('Y-m-d') }}"></div>
            <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">Phone / WhatsApp Number</label><input type="text" name="whatsapp" class="form-control" placeholder="+91XXXXXXXXXX"></div>
            <div class="col-md-6"><label class="form-label">Sales Man</label><input type="text" name="sales_man" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">Designation</label><input type="text" name="designation" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">GST No.</label><input type="text" name="gst_number" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">Transport</label><input type="text" name="transport" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">Stage</label><input type="text" name="stage" class="form-control" placeholder="Quote / Proforma / ..."></div>
            <div class="col-md-6"><label class="form-label">Priority</label><input type="text" name="priority" class="form-control"></div>
            <div class="col-12"><label class="form-label">Shipping Address</label><textarea class="form-control" name="shipping_address" rows="2"></textarea></div>
            <div class="col-md-6">
              <label class="form-label">Status</label>
              <select class="form-select" name="status">
                @foreach (\App\Models\Contact::statusOptions() as $key => $label)
                  <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
              </select>
            </div>
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
          <h5 class="modal-title">Edit Contact / Quotation</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Company Name *</label><input type="text" name="company" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label">Contact Person</label><input type="text" name="name" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">Quote No.</label><input type="text" name="quote_no" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">Quotation Date</label><input type="date" name="quotation_date" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">Phone / WhatsApp Number</label><input type="text" name="whatsapp" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">Sales Man</label><input type="text" name="sales_man" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">Designation</label><input type="text" name="designation" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">GST No.</label><input type="text" name="gst_number" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">Transport</label><input type="text" name="transport" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">Stage</label><input type="text" name="stage" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">Priority</label><input type="text" name="priority" class="form-control"></div>
            <div class="col-12"><label class="form-label">Shipping Address</label><textarea class="form-control" name="shipping_address" rows="2"></textarea></div>
            <div class="col-md-6">
              <label class="form-label">Status</label>
              <select class="form-select" name="status">
                @foreach (\App\Models\Contact::statusOptions() as $key => $label)
                  <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
              </select>
            </div>
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
