{{-- Create Template --}}
<div class="modal fade" id="modalAddTemplate" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="{{ route('whatsapp.store') }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Create Template</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <label class="form-label">Template Name *</label>
          <input type="text" name="name" class="form-control mb-3" placeholder="e.g. Order Confirmation" required>

          <label class="form-label">Available Placeholders</label>
          <div class="d-flex gap-2 flex-wrap mb-2">
            <button type="button" class="placeholder-btn js-insert-ph" data-target="addTemplateMessage" data-ph="{name}">{name}</button>
            <button type="button" class="placeholder-btn js-insert-ph" data-target="addTemplateMessage" data-ph="{company}">{company}</button>
            <button type="button" class="placeholder-btn js-insert-ph" data-target="addTemplateMessage" data-ph="{employee}">{employee}</button>
            <button type="button" class="placeholder-btn js-insert-ph" data-target="addTemplateMessage" data-ph="{date}">{date}</button>
          </div>

          <label class="form-label">Message *</label>
          <textarea class="form-control" id="addTemplateMessage" name="message" rows="7" placeholder="Hi {name}, thank you for reaching out to {company}..." required></textarea>
          <div class="form-text">Click a placeholder above to insert it at the cursor position.</div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light-c" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary-c"><i class="bi bi-check2 me-1"></i>Save Template</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Edit Template --}}
<div class="modal fade" id="modalEditTemplate" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" id="formEditTemplate">
        @csrf @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title">Edit Template</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <label class="form-label">Template Name *</label>
          <input type="text" name="name" class="form-control mb-3" required>

          <label class="form-label">Available Placeholders</label>
          <div class="d-flex gap-2 flex-wrap mb-2">
            <button type="button" class="placeholder-btn js-insert-ph" data-target="editTemplateMessage" data-ph="{name}">{name}</button>
            <button type="button" class="placeholder-btn js-insert-ph" data-target="editTemplateMessage" data-ph="{company}">{company}</button>
            <button type="button" class="placeholder-btn js-insert-ph" data-target="editTemplateMessage" data-ph="{employee}">{employee}</button>
            <button type="button" class="placeholder-btn js-insert-ph" data-target="editTemplateMessage" data-ph="{date}">{date}</button>
          </div>

          <label class="form-label">Message *</label>
          <textarea class="form-control" id="editTemplateMessage" name="message" rows="7" required></textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light-c" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary-c"><i class="bi bi-check2 me-1"></i>Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('.js-insert-ph').forEach(function (btn) {
  btn.addEventListener('click', function () {
    var ta = document.getElementById(btn.dataset.target);
    var ph = btn.dataset.ph;
    var start = ta.selectionStart || ta.value.length;
    var end = ta.selectionEnd || ta.value.length;
    ta.value = ta.value.slice(0, start) + ph + ta.value.slice(end);
    ta.focus();
    ta.selectionStart = ta.selectionEnd = start + ph.length;
  });
});
</script>
@endpush
