@php
$allContactsForWa = \App\Models\Contact::whereNotNull('whatsapp')->where('whatsapp', '!=', '')->orderBy('name')->get(['id', 'name', 'company', 'whatsapp']);
$allTemplatesForWa = \App\Models\WhatsappTemplate::orderBy('name')->get(['id', 'name', 'message']);
@endphp

<div class="modal fade" id="modalSendWhatsapp" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Send WhatsApp Message</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Recipient</label>
            <select class="form-select" id="waRecipientSelect">
              <option value="">— Enter number manually —</option>
              @foreach ($allContactsForWa as $c)
                <option value="{{ $c->id }}" data-name="{{ $c->name }}" data-company="{{ $c->company }}" data-number="{{ $c->whatsapp }}">{{ $c->name }} ({{ $c->whatsapp }})</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Phone Number</label>
            <input type="text" class="form-control" id="waManualNumber" placeholder="+91XXXXXXXXXX">
          </div>
          <div class="col-md-12">
            <label class="form-label">Template</label>
            <select class="form-select" id="waTemplateSelect">
              <option value="">— Blank message —</option>
              @foreach ($allTemplatesForWa as $t)
                <option value="{{ $t->id }}" data-message="{{ $t->message }}">{{ $t->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-12">
            <label class="form-label">Message</label>
            <textarea class="form-control" id="waMessageText" rows="6" placeholder="Type your message..."></textarea>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light-c" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-whatsapp-c" id="waSendBtn"><i class="bi bi-whatsapp me-1"></i>Open WhatsApp</button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
(function(){
  function renderWaMessage(){
    var tplSel = document.getElementById('waTemplateSelect');
    var tplOpt = tplSel.options[tplSel.selectedIndex];
    var raw = tplOpt ? (tplOpt.getAttribute('data-message') || '') : '';
    if (!raw) return;
    var recSel = document.getElementById('waRecipientSelect');
    var recOpt = recSel.options[recSel.selectedIndex];
    var name = recOpt ? (recOpt.getAttribute('data-name') || '') : '';
    var company = (recOpt && recOpt.getAttribute('data-company')) || {!! json_encode(config('app.name')) !!};
    var today = new Date().toLocaleDateString('en-GB', {day:'2-digit', month:'short', year:'numeric'});
    var msg = raw
      .split('{name}').join(name || 'there')
      .split('{company}').join(company)
      .split('{employee}').join({!! json_encode(auth()->user()->name) !!})
      .split('{date}').join(today);
    document.getElementById('waMessageText').value = msg;
  }

  window.openWhatsappModal = function(opts){
    opts = opts || {};
    var recSel = document.getElementById('waRecipientSelect');
    recSel.value = opts.contactId || '';
    document.getElementById('waManualNumber').value = opts.number || '';
    document.getElementById('waTemplateSelect').value = opts.templateId || '';
    renderWaMessage();
    new bootstrap.Modal(document.getElementById('modalSendWhatsapp')).show();
  };

  document.getElementById('waTemplateSelect').addEventListener('change', renderWaMessage);
  document.getElementById('waRecipientSelect').addEventListener('change', function(){
    var opt = this.options[this.selectedIndex];
    document.getElementById('waManualNumber').value = (opt && opt.getAttribute('data-number')) || '';
    renderWaMessage();
  });

  document.getElementById('waSendBtn').addEventListener('click', function(){
    var recSel = document.getElementById('waRecipientSelect');
    var recOpt = recSel.options[recSel.selectedIndex];
    var contactId = recSel.value || null;
    var number = document.getElementById('waManualNumber').value.trim();
    var message = document.getElementById('waMessageText').value.trim();

    if (!number) { showToast('Please select a contact or enter a phone number', 'warning'); return; }
    if (!message) { showToast('Please enter a message', 'warning'); return; }

    fetch({!! json_encode(route('whatsapp.send')) !!}, {
      method: 'POST',
      headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.APP_CSRF, 'Accept': 'application/json'},
      body: JSON.stringify({
        template_id: document.getElementById('waTemplateSelect').value || null,
        contact_id: contactId,
        recipient_name: recOpt ? (recOpt.getAttribute('data-name') || 'Manual Entry') : 'Manual Entry',
        recipient_number: number,
        message: message,
      }),
    })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.success) {
          window.open(data.wa_link, '_blank');
          bootstrap.Modal.getInstance(document.getElementById('modalSendWhatsapp')).hide();
          showToast('WhatsApp opened successfully.', 'success');
        } else {
          showToast('Could not send message.', 'danger');
        }
      })
      .catch(function () { showToast('Could not send message.', 'danger'); });
  });
})();
</script>
@endpush
