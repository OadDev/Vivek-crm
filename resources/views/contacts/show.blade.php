@extends('layouts.app')

@section('title', $contact->name)

@section('content')
<div class="breadcrumb-c"><a href="{{ route('dashboard') }}">Home</a><i class="bi bi-chevron-right"></i><a href="{{ route('contacts.index') }}">Contacts</a><i class="bi bi-chevron-right"></i><span class="current">{{ $contact->name }}</span></div>
<div class="page-header">
  <div>
    <div class="page-title">{{ $contact->name }}</div>
    <div class="page-subtitle">Complete 360° view of the customer relationship.</div>
  </div>
  <a href="{{ route('contacts.index') }}" class="btn btn-outline-c btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to Contacts</a>
</div>

<div class="row g-3">
  <div class="col-lg-4">
    <div class="card-c">
      <div class="card-c-body text-center">
        <div class="avatar-circle avatar-lg mx-auto mb-3" style="background:{{ $contact->avatarColor() }};">{{ $contact->initials() }}</div>
        <h5 class="mb-0">{{ $contact->name }}</h5>
        <div class="text-muted-c small mb-2">{{ $contact->designation ?: 'No designation' }} {{ $contact->company ? '· '.$contact->company : '' }}</div>
        <span class="chip {{ \App\Models\Contact::statusChipClass($contact->status) }}">{{ \App\Models\Contact::statusOptions()[$contact->status] }}</span>

        <div class="d-flex justify-content-center gap-2 mt-4">
          <a href="mailto:{{ $contact->email }}" class="btn-icon-sq" title="Email" data-bs-toggle="tooltip"><i class="bi bi-envelope-fill"></i></a>
          @if ($contact->whatsapp)
          <button type="button" class="btn-icon-sq success" title="WhatsApp" data-bs-toggle="tooltip"
            onclick="openWhatsappModal({contactId:'{{ $contact->id }}', number:'{{ $contact->whatsapp }}'})"><i class="bi bi-whatsapp"></i></button>
          @endif
          <button type="button" class="btn-icon-sq" title="Edit" data-bs-toggle="modal" data-bs-target="#modalQuickEditContact"><i class="bi bi-pencil-fill"></i></button>
        </div>

        <hr class="my-4">
        <div class="text-start small">
          <div class="d-flex justify-content-between py-2 border-bottom" style="border-color:var(--border-color)!important;"><span class="text-muted-c">Company</span><span class="fw-600">{{ $contact->company ?: '—' }}</span></div>
          <div class="d-flex justify-content-between py-2 border-bottom" style="border-color:var(--border-color)!important;"><span class="text-muted-c">Email</span><span class="fw-600">{{ $contact->email }}</span></div>
          <div class="d-flex justify-content-between py-2"><span class="text-muted-c">WhatsApp</span><span class="fw-600">{{ $contact->whatsapp ?: '—' }}</span></div>
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
              <div class="col-md-6"><label class="form-label">Name</label><input class="form-control" value="{{ $contact->name }}" disabled></div>
              <div class="col-md-6"><label class="form-label">Company</label><input class="form-control" value="{{ $contact->company }}" disabled></div>
              <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" value="{{ $contact->email }}" disabled></div>
              <div class="col-md-6"><label class="form-label">WhatsApp</label><input class="form-control" value="{{ $contact->whatsapp }}" disabled></div>
              <div class="col-md-6"><label class="form-label">Designation</label><input class="form-control" value="{{ $contact->designation }}" disabled></div>
              <div class="col-md-6"><label class="form-label">Status</label><input class="form-control" value="{{ \App\Models\Contact::statusOptions()[$contact->status] }}" disabled></div>
              <div class="col-md-6"><label class="form-label">Last Contacted</label><input class="form-control" value="{{ optional($contact->last_contacted_at)->format('d M Y') ?? '—' }}" disabled></div>
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
              <div class="empty-state"><div class="es-icon"><i class="bi bi-envelope"></i></div><h6>No Gmail conversations yet</h6><p>Emails exchanged with this contact will appear here.</p></div>
            @endforelse
          </div>

          <div class="tab-pane fade" id="tabWhatsapp">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
              <div>
                @if ($contact->whatsappMessages->first())
                  <div class="fw-600">Last Message Sent</div>
                  <div class="small text-muted-c">{{ $contact->whatsappMessages->first()->sent_at->diffForHumans() }} · Template used: <b>{{ $contact->whatsappMessages->first()->template?->name ?? 'Custom message' }}</b></div>
                @else
                  <div class="fw-600">No WhatsApp messages yet</div>
                  <div class="small text-muted-c">Send your first message to this contact below.</div>
                @endif
              </div>
              @if ($contact->whatsapp)
              <button type="button" class="btn btn-whatsapp-c btn-sm" onclick="openWhatsappModal({contactId:'{{ $contact->id }}', number:'{{ $contact->whatsapp }}'})"><i class="bi bi-whatsapp me-1"></i>Send WhatsApp</button>
              @endif
            </div>
            @if ($contact->whatsappMessages->first())
              <div class="wa-bubble" style="max-width:460px;">{{ $contact->whatsappMessages->first()->message }}</div>
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
              <input type="hidden" name="last_contacted_at" value="{{ optional($contact->last_contacted_at)->format('Y-m-d') }}">
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
@include('partials.whatsapp-send-modal')

{{-- Quick edit modal (prefilled server-side since this is a single-contact page) --}}
<div class="modal fade" id="modalQuickEditContact" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="{{ route('contacts.update', $contact) }}">
        @csrf @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title">Edit Contact</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Full Name *</label><input type="text" name="name" class="form-control" value="{{ $contact->name }}" required></div>
            <div class="col-md-6"><label class="form-label">Company</label><input type="text" name="company" class="form-control" value="{{ $contact->company }}"></div>
            <div class="col-md-6"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" value="{{ $contact->email }}" required></div>
            <div class="col-md-6"><label class="form-label">WhatsApp Number</label><input type="text" name="whatsapp" class="form-control" value="{{ $contact->whatsapp }}"></div>
            <div class="col-md-6"><label class="form-label">Designation</label><input type="text" name="designation" class="form-control" value="{{ $contact->designation }}"></div>
            <div class="col-md-6">
              <label class="form-label">Status</label>
              <select class="form-select" name="status">
                @foreach (\App\Models\Contact::statusOptions() as $key => $label)
                  <option value="{{ $key }}" {{ $contact->status === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6"><label class="form-label">Last Contacted Date</label><input type="date" name="last_contacted_at" class="form-control" value="{{ optional($contact->last_contacted_at)->format('Y-m-d') }}"></div>
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
