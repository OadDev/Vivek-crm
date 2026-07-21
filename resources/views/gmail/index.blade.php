@extends('layouts.app')

@section('title', 'Gmail Inbox')

@section('content')
<div class="breadcrumb-c"><a href="{{ route('dashboard') }}">Home</a><i class="bi bi-chevron-right"></i><span class="current">Gmail Inbox</span></div>
<div class="page-header">
  <div>
    <div class="page-title">Gmail Inbox</div>
    <div class="page-subtitle">Manage all customer email conversations in one place.</div>
  </div>
</div>

<div class="gmail-shell {{ $selected ? 'show-preview' : '' }}">
  <div class="gmail-folders">
    @php
      $folderIcons = ['inbox' => 'bi-inbox-fill', 'starred' => 'bi-star-fill', 'sent' => 'bi-send-fill', 'draft' => 'bi-file-earmark-text', 'archive' => 'bi-archive-fill', 'trash' => 'bi-trash-fill'];
      $folderLabels = ['inbox' => 'Inbox', 'starred' => 'Starred', 'sent' => 'Sent', 'draft' => 'Draft', 'archive' => 'Archive', 'trash' => 'Trash'];
    @endphp
    @foreach ($folderLabels as $key => $label)
      <a href="{{ route('gmail.index', ['folder' => $key]) }}" class="gmail-folder-item {{ $folder === $key ? 'active' : '' }}">
        <i class="bi {{ $folderIcons[$key] }}"></i>{{ $label }}<span class="cnt">{{ $folderCounts[$key] ?? 0 }}</span>
      </a>
    @endforeach
  </div>

  <div class="gmail-list-pane">
    <div class="gmail-list-toolbar">
      <form method="GET" action="{{ route('gmail.index') }}" class="mb-2">
        <input type="hidden" name="folder" value="{{ $folder }}">
        <div class="position-relative">
          <i class="bi bi-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);"></i>
          <input type="text" name="search" class="form-control form-control-sm" style="padding-left:32px;" placeholder="Search mail..." value="{{ request('search') }}">
        </div>
      </form>
      <div class="filter-chip-group">
        @foreach (['all' => 'All', 'unread' => 'Unread', 'read' => 'Read', 'starred' => 'Starred', 'archived' => 'Archived'] as $key => $label)
          <a href="{{ route('gmail.index', ['folder' => $folder, 'filter' => $key, 'search' => request('search')]) }}" class="filter-chip-btn {{ $filter === $key ? 'active' : '' }}">{{ $label }}</a>
        @endforeach
      </div>
    </div>
    <div class="gmail-list-scroll">
      @forelse ($conversations as $conv)
        <div class="conv-item {{ !$conv->is_read ? 'unread' : '' }} {{ $selected && $selected->id === $conv->id ? 'selected' : '' }}">
          @if (!$conv->is_read)<span class="unread-dot"></span>@endif
          <a href="{{ route('gmail.index', ['folder' => $folder, 'filter' => $filter, 'search' => request('search'), 'conversation' => $conv->id]) }}"
             class="d-flex flex-fill text-decoration-none text-reset" style="gap:11px;min-width:0;">
            <div class="avatar-circle" style="background:{{ '#'.substr(md5($conv->sender_name), 0, 6) }};width:38px;height:38px;font-size:13px;">
              {{ collect(explode(' ', $conv->sender_name))->map(fn($w) => mb_substr($w, 0, 1))->slice(0, 2)->implode('') }}
            </div>
            <div class="conv-body">
              <div class="conv-top-row">
                <span class="conv-sender">{{ $conv->sender_name }}</span>
                <span class="conv-time">{{ $conv->last_message_at?->diffForHumans(null, true) }}</span>
              </div>
              <div class="conv-subject">{{ $conv->subject }}</div>
              <div class="conv-preview">{{ $conv->preview }}</div>
            </div>
          </a>
          <a href="{{ route('gmail.star.get', $conv) }}" class="conv-star {{ $conv->is_starred ? 'active' : '' }}" style="align-self:flex-start;margin-top:2px;">
            <i class="bi {{ $conv->is_starred ? 'bi-star-fill' : 'bi-star' }}"></i>
          </a>
        </div>
      @empty
        <div class="empty-state">
          <div class="es-icon"><i class="bi bi-inbox"></i></div>
          <h6>No conversations found</h6>
          <p>Try changing filters or search keywords.</p>
        </div>
      @endforelse
    </div>
    <div class="pagination-c px-2 pb-2">
      <span class="p-info">{{ $conversations->total() }} total</span>
      {{ $conversations->links('vendor.pagination.crm') }}
    </div>
  </div>

  <div class="gmail-preview-pane">
    @if ($selected)
      <div class="gmail-preview-header">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
          <div>
            <h5 class="mb-1">{{ $selected->subject }}</h5>
            <div class="small text-muted-c">{{ $selected->messages->count() }} message(s) in this conversation</div>
          </div>
          <div class="d-flex gap-2">
            @if ($selectedContact)
              <a href="{{ route('contacts.show', $selectedContact) }}" class="btn btn-outline-c btn-sm"><i class="bi bi-person-circle me-1"></i>Open Contact</a>
            @else
              <button type="button" class="btn btn-primary-c btn-sm" data-bs-toggle="modal" data-bs-target="#modalCreateContactFromMail"><i class="bi bi-person-plus-fill me-1"></i>Create Contact</button>
            @endif
            <form method="PATCH" action="{{ route('gmail.folder', $selected) }}">
              @csrf @method('PATCH')
              <input type="hidden" name="folder" value="archive">
              <button type="submit" class="btn-icon-sq" title="Archive" data-bs-toggle="tooltip"><i class="bi bi-archive"></i></button>
            </form>
            <form method="PATCH" action="{{ route('gmail.folder', $selected) }}">
              @csrf @method('PATCH')
              <input type="hidden" name="folder" value="trash">
              <button type="submit" class="btn-icon-sq danger" title="Delete" data-bs-toggle="tooltip"><i class="bi bi-trash"></i></button>
            </form>
          </div>
        </div>
      </div>
      <div class="gmail-preview-body">
        @foreach ($selected->messages as $m)
          <div class="thread-message">
            <div class="thread-message-head">
              <div class="avatar-circle" style="background:{{ '#'.substr(md5($m->from_name), 0, 6) }};width:36px;height:36px;font-size:12.5px;">
                {{ collect(explode(' ', $m->direction === 'outgoing' ? 'You' : $m->from_name))->map(fn($w) => mb_substr($w, 0, 1))->slice(0, 2)->implode('') }}
              </div>
              <div class="flex-fill">
                <div class="d-flex justify-content-between">
                  <span class="fw-600" style="font-size:13.4px;">{{ $m->direction === 'outgoing' ? 'You' : $m->from_name }}</span>
                  <span class="small text-muted-c">{{ $m->sent_at->format('d M, h:i A') }}</span>
                </div>
                <div class="small text-muted-c">to {{ $m->to_name }}</div>
              </div>
            </div>
            <div class="thread-message-body">{!! $m->body !!}</div>
          </div>
        @endforeach
      </div>
      <form method="POST" action="{{ route('gmail.reply', $selected) }}" class="reply-box">
        @csrf
        <div class="rich-toolbar">
          <button type="button" title="Bold" data-bs-toggle="tooltip"><i class="bi bi-type-bold"></i></button>
          <button type="button" title="Italic" data-bs-toggle="tooltip"><i class="bi bi-type-italic"></i></button>
          <button type="button" title="Underline" data-bs-toggle="tooltip"><i class="bi bi-type-underline"></i></button>
          <button type="button" title="Bulleted list" data-bs-toggle="tooltip"><i class="bi bi-list-ul"></i></button>
          <button type="button" title="Numbered list" data-bs-toggle="tooltip"><i class="bi bi-list-ol"></i></button>
          <button type="button" title="Insert link" data-bs-toggle="tooltip"><i class="bi bi-link-45deg"></i></button>
          <button type="button" title="Attach file" data-bs-toggle="tooltip"><i class="bi bi-paperclip"></i></button>
        </div>
        <textarea class="form-control reply-textarea" name="body" placeholder="Write your reply..." required></textarea>
        <div class="d-flex justify-content-between align-items-center mt-2 flex-wrap gap-2">
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary-c btn-sm"><i class="bi bi-reply-fill me-1"></i>Reply</button>
            <button type="submit" class="btn btn-outline-c btn-sm"><i class="bi bi-reply-all-fill me-1"></i>Reply All</button>
            <button type="button" class="btn btn-outline-c btn-sm" onclick="showToast('Forwarding requires selecting a new recipient — coming soon.', 'primary')"><i class="bi bi-arrow-right-square-fill me-1"></i>Forward</button>
          </div>
        </div>
      </form>
    @else
      <div class="gmail-preview-empty">
        <i class="bi bi-envelope-open" style="font-size:44px;"></i>
        <div class="fw-600">Select a conversation to preview</div>
        <div class="small">Choose an email from the list to read the full thread.</div>
      </div>
    @endif
  </div>
</div>
@endsection

@push('modals')
@if ($selected && !$selectedContact)
<div class="modal fade" id="modalCreateContactFromMail" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="{{ route('gmail.create-contact', $selected) }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Create Contact from Email</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p class="small text-muted-c">No existing contact matches this sender. Review details and create a new contact.</p>
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Name</label><input type="text" name="name" class="form-control" value="{{ $selected->sender_name }}" required></div>
            <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="{{ $selected->sender_email }}" required></div>
            <div class="col-md-6"><label class="form-label">Company</label><input type="text" name="company" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">WhatsApp Number</label><input type="text" name="whatsapp" class="form-control"></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light-c" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary-c"><i class="bi bi-check2 me-1"></i>Create Contact</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endif
@endpush
