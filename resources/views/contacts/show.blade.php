@extends('layouts.app')

@section('title', $contact->company ?: $contact->name)

@section('content')
<div class="breadcrumb-c"><a href="{{ route('dashboard') }}">Home</a><i class="bi bi-chevron-right"></i><a href="{{ route('contacts.index') }}">Contacts</a><i class="bi bi-chevron-right"></i><span class="current">{{ $contact->company ?: $contact->name }}</span></div>
<div class="page-header">
  <div>
    <div class="page-title">{{ $contact->company ?: $contact->name }}{{ $contact->quote_no ? ' · '.$contact->quote_no : '' }}</div>
    <div class="page-subtitle">Complete 360° view of the customer relationship.</div>
  </div>
  <a href="{{ route('contacts.index') }}" class="btn btn-outline-c btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to Contacts</a>
</div>

<div class="row g-3">
  <div class="col-lg-4">
    <div class="card-c">
      <div class="card-c-body text-center">
        <div class="avatar-circle avatar-lg mx-auto mb-3" style="background:{{ $contact->avatarColor() }};">{{ $contact->initials() }}</div>
        <h5 class="mb-0">{{ $contact->company ?: $contact->name }}</h5>
        <div class="text-muted-c small mb-2">{{ $contact->designation ?: 'No designation' }} {{ $contact->name && $contact->name !== $contact->company ? '· '.$contact->name : '' }}</div>
        @if ($contact->is_won)
          <span class="chip chip-success"><i class="bi bi-trophy-fill"></i>Won</span>
        @elseif ($contact->is_archived)
          <span class="chip chip-neutral"><i class="bi bi-archive-fill"></i>Archived</span>
        @else
          <span class="chip {{ \App\Models\Contact::statusChipClass($contact->status) }}">{{ \App\Models\Contact::statusOptions()[$contact->status] }}</span>
        @endif

        <div class="d-flex justify-content-center gap-2 mt-4">
          @if ($contact->email)
          <a href="mailto:{{ $contact->email }}" class="btn-icon-sq" title="Email" data-bs-toggle="tooltip"><i class="bi bi-envelope-fill"></i></a>
          @endif
          @if ($contact->whatsapp)
          <a href="{{ route('contacts.whatsapp', $contact) }}" target="_blank" class="btn-icon-sq success" title="WhatsApp (uses your saved template)" data-bs-toggle="tooltip"><i class="bi bi-whatsapp"></i></a>
          @endif
          <button type="button" class="btn-icon-sq" title="Edit" data-bs-toggle="modal" data-bs-target="#modalQuickEditContact"><i class="bi bi-pencil-fill"></i></button>
        </div>

        <div class="d-flex justify-content-center gap-2 mt-2">
          @if ($contact->is_won)
          <form method="POST" action="{{ route('contacts.unwon', $contact) }}"><button class="btn btn-outline-c btn-sm" type="submit">@csrf @method('PATCH') Revert from Won</button></form>
          @else
          <form method="POST" action="{{ route('contacts.won', $contact) }}"><button class="btn btn-outline-c btn-sm" type="submit">@csrf @method('PATCH') <i class="bi bi-trophy me-1"></i>Mark Won</button></form>
          @endif
          @if ($contact->is_archived)
          <form method="POST" action="{{ route('contacts.unarchive', $contact) }}"><button class="btn btn-outline-c btn-sm" type="submit">@csrf @method('PATCH') Restore</button></form>
          @else
          <form method="POST" action="{{ route('contacts.archive', $contact) }}"><button class="btn btn-outline-c btn-sm" type="submit">@csrf @method('PATCH') <i class="bi bi-archive me-1"></i>Archive</button></form>
          @endif
        </div>

        <hr class="my-4">
        <div class="text-start small">
          <div class="d-flex justify-content-between py-2 border-bottom" style="border-color:var(--border-color)!important;"><span class="text-muted-c">Quote No.</span><span class="fw-600">{{ $contact->quote_no ?: '—' }}</span></div>
          <div class="d-flex justify-content-between py-2 border-bottom" style="border-color:var(--border-color)!important;"><span class="text-muted-c">Email</span><span class="fw-600">{{ $contact->email ?: '—' }}</span></div>
          <div class="d-flex justify-content-between py-2 border-bottom" style="border-color:var(--border-color)!important;"><span class="text-muted-c">WhatsApp</span><span class="fw-600">{{ $contact->whatsapp ?: '—' }}</span></div>
          <div class="d-flex justify-content-between py-2"><span class="text-muted-c">Sales Man</span><span class="fw-600">{{ $contact->sales_man ?: '—' }}</span></div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="card-c">
      <div class="card-c-body">
        <ul class="nav nav-tabs-c mb-3" id="cpTabs" role="tablist">
          <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabInformation" type="button">Information</button></li>
          <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabGmail" type="button">Gmail Conversations</button></li>
          <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabWhatsapp" type="button">WhatsApp</button></li>
          <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabNotes" type="button">Notes</button></li>
        </ul>

        <div class="tab-content">
          <div class="tab-pane fade show active" id="tabInformation">
            <div class="row g-3">
              <div class="col-md-6"><label class="form-label">Company</label><input class="form-control" value="{{ $contact->company }}" disabled></div>
              <div class="col-md-6"><label class="form-label">Contact Person</label><input class="form-control" value="{{ $contact->name }}" disabled></div>
              <div class="col-md-6"><label class="form-label">Quote No.</label><input class="form-control" value="{{ $contact->quote_no }}" disabled></div>
              <div class="col-md-6"><label class="form-label">Quotation Date</label><input class="form-control" value="{{ optional($contact->quotation_date)->format('d M Y') ?? '—' }}" disabled></div>
              <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" value="{{ $contact->email }}" disabled></div>
              <div class="col-md-6"><label class="form-label">WhatsApp</label><input class="form-control" value="{{ $contact->whatsapp }}" disabled></div>
              <div class="col-md-6"><label class="form-label">Designation</label><input class="form-control" value="{{ $contact->designation }}" disabled></div>
              <div class="col-md-6"><label class="form-label">Sales Man</label><input class="form-control" value="{{ $contact->sales_man }}" disabled></div>
              <div class="col-md-6"><label class="form-label">GST No.</label><input class="form-control" value="{{ $contact->gst_number }}" disabled></div>
              <div class="col-md-6"><label class="form-label">Transport</label><input class="form-control" value="{{ $contact->transport }}" disabled></div>
              <div class="col-md-6"><label class="form-label">Stage</label><input class="form-control" value="{{ $contact->stage }}" disabled></div>
              <div class="col-md-6"><label class="form-label">Priority</label><input class="form-control" value="{{ $contact->priority }}" disabled></div>
              <div class="col-12"><label class="form-label">Shipping Address</label><textarea class="form-control" disabled rows="2">{{ $contact->shipping_address }}</textarea></div>
              <div class="col-md-6"><label class="form-label">Status</label><input class="form-control" value="{{ \App\Models\Contact::statusOptions()[$contact->status] }}" disabled></div>
              <div class="col-md-6"><label class="form-label">Source</label><input class="form-control" value="{{ ucfirst(str_replace('_', ' ', $contact->source)) }}" disabled></div>
            </div>
          </div>

          <div class="tab-pane fade" id="tabGmail">
            @forelse ($contact->emailConversations as $t)
              <a href="{{ route('gmail.index', ['folder' => $t->folder === 'inbox' ? 'inbox' : $t->folder, 'conversation' => $t->id]) }}" class="conv-item text-decoration-none text-reset d-flex" style="border-radius:var(--radius-sm);border:1px solid var(--border-color);margin-bottom:8px;">
                <div class="avatar-circle" style="background:{{ $contact->avatarColor() }};width:38px;height:38px;font-size:13px;">{{ $contact->initials() }}</div>
                <div class="conv-body">
                  <div class="conv-top-row"><span class="conv-sender">{{ $t->subject }}</span><span class="conv-time">{{ $t->last_message_at?->diffForHumans() }}</span></div>
                  <div class="conv-preview">{{ $t->preview }}</div>
                </div>
              </a>
            @empty
              <div class="empty-state">
                <div class="es-icon"><i class="bi bi-envelope"></i></div>
                <h6>No Gmail conversations yet</h6>
                <p>Emails exchanged with this contact will appear here.</p>
                @if ($contact->email)
                <a href="{{ route('gmail.index', ['search' => $contact->email]) }}" class="btn btn-outline-c btn-sm mt-2">Search Gmail for {{ $contact->email }}</a>
                @endif
              </div>
            @endforelse
          </div>

          <div class="tab-pane fade" id="tabWhatsapp">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
              <div>
                @if ($contact->whatsappMessages->first())
                  <div class="fw-600">Last Message Sent</div>
                  <div class="small text-muted-c">{{ $contact->whatsappMessages->first()->sent_at->diffForHumans() }} · Template used: <b>{{ $contact->whatsappMessages->first()->template?->name ?? 'None (default template not set in Settings)' }}</b></div>
                @else
                  <div class="fw-600">No WhatsApp messages yet</div>
                  <div class="small text-muted-c">Uses the default template saved in Settings → WhatsApp.</div>
                @endif
              </div>
              @if ($contact->whatsapp)
              <a href="{{ route('contacts.whatsapp', $contact) }}" target="_blank" class="btn btn-whatsapp-c btn-sm"><i class="bi bi-whatsapp me-1"></i>Send WhatsApp</a>
              @endif
            </div>
            @if ($contact->whatsappMessages->first())
              <div class="wa-bubble" style="max-width:460px;">{{ $contact->whatsappMessages->first()->message ?: '(blank message — no default template was set when this was sent)' }}</div>
            @endif
          </div>

          <div class="tab-pane fade" id="tabNotes">
            <form method="POST" action="{{ route('contacts.update', $contact) }}">
              @csrf @method('PUT')
              <input type="hidden" name="name" value="{{ $contact->name }}">
              <input type="hidden" name="email" value="{{ $contact->email }}">
              <input type="hidden" name="status" value="{{ $contact->status }}">
              <input type="hidden" name="company" value="{{ $contact->company }}">
              <input type="hidden" name="whatsapp" value="{{ $contact->whatsapp }}">
              <input type="hidden" name="designation" value="{{ $contact->designation }}">
              <input type="hidden" name="quote_no" value="{{ $contact->quote_no }}">
              <input type="hidden" name="quotation_date" value="{{ optional($contact->quotation_date)->format('Y-m-d') }}">
              <label class="form-label">Internal Notes</label>
              <textarea class="form-control" name="notes" rows="10">{{ $contact->notes }}</textarea>
              <button type="submit" class="btn btn-primary-c btn-sm mt-3"><i class="bi bi-check2 me-1"></i>Save Notes</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('modals')
@include('contacts._modals')

{{-- Quick edit modal (prefilled server-side since this is a single-contact page) --}}
<div class="modal fade" id="modalQuickEditContact" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="{{ route('contacts.update', $contact) }}">
        @csrf @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title">Edit Contact / Quotation</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Company Name *</label><input type="text" name="company" class="form-control" value="{{ $contact->company }}" required></div>
            <div class="col-md-6"><label class="form-label">Contact Person</label><input type="text" name="name" class="form-control" value="{{ $contact->name }}"></div>
            <div class="col-md-6"><label class="form-label">Quote No.</label><input type="text" name="quote_no" class="form-control" value="{{ $contact->quote_no }}"></div>
            <div class="col-md-6"><label class="form-label">Quotation Date</label><input type="date" name="quotation_date" class="form-control" value="{{ optional($contact->quotation_date)->format('Y-m-d') }}"></div>
            <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="{{ $contact->email }}"></div>
            <div class="col-md-6"><label class="form-label">WhatsApp Number</label><input type="text" name="whatsapp" class="form-control" value="{{ $contact->whatsapp }}"></div>
            <div class="col-md-6"><label class="form-label">Sales Man</label><input type="text" name="sales_man" class="form-control" value="{{ $contact->sales_man }}"></div>
            <div class="col-md-6"><label class="form-label">Designation</label><input type="text" name="designation" class="form-control" value="{{ $contact->designation }}"></div>
            <div class="col-md-6"><label class="form-label">GST No.</label><input type="text" name="gst_number" class="form-control" value="{{ $contact->gst_number }}"></div>
            <div class="col-md-6"><label class="form-label">Transport</label><input type="text" name="transport" class="form-control" value="{{ $contact->transport }}"></div>
            <div class="col-md-6"><label class="form-label">Stage</label><input type="text" name="stage" class="form-control" value="{{ $contact->stage }}"></div>
            <div class="col-md-6"><label class="form-label">Priority</label><input type="text" name="priority" class="form-control" value="{{ $contact->priority }}"></div>
            <div class="col-12"><label class="form-label">Shipping Address</label><textarea class="form-control" name="shipping_address" rows="2">{{ $contact->shipping_address }}</textarea></div>
            <div class="col-md-6">
              <label class="form-label">Status</label>
              <select class="form-select" name="status">
                @foreach (\App\Models\Contact::statusOptions() as $key => $label)
                  <option value="{{ $key }}" {{ $contact->status === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-12"><label class="form-label">Notes</label><textarea class="form-control" name="notes" rows="3">{{ $contact->notes }}</textarea></div>
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
