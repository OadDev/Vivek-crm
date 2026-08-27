{{-- Renders one quotation row. $contact required. $groupCount/$groupId (on
     the primary/most-recent row of a multi-quotation company) render a
     small inline expand arrow; $groupClass/$hidden (on the rest of that
     company's rows) control which rows the arrow shows/hides. Every row
     looks the same either way -- clicking anywhere on it (outside a
     button/link/form) opens the contact's profile. --}}
@php($groupClass = $groupClass ?? '')
@php($hidden = $hidden ?? false)
@php($groupCount = $groupCount ?? 1)
@php($groupId = $groupId ?? null)
<tr class="{{ $groupClass }} contact-row" data-href="{{ route('contacts.show', $contact) }}" style="cursor:pointer;{{ $hidden ? 'display:none;' : '' }}">
  <td>
    <div class="d-flex align-items-center gap-1">
      <form method="POST" action="{{ route('contacts.star', $contact) }}">
        @csrf @method('PATCH')
        <button type="submit" class="btn btn-link p-0 border-0" style="color:{{ $contact->is_starred ? '#F5A623' : 'var(--text-muted)' }};font-size:16px;" title="{{ $contact->is_starred ? 'Unpin' : 'Pin to top' }}">
          <i class="bi {{ $contact->is_starred ? 'bi-star-fill' : 'bi-star' }}"></i>
        </button>
      </form>
      <div class="avatar-circle" style="background:{{ $contact->avatarColor() }};">{{ $contact->initials() }}</div>
    </div>
  </td>
  <td class="fw-600">{{ $contact->quote_no ?: '—' }}</td>
  <td>
    @if ($groupCount > 1)
      <button type="button" class="btn btn-link p-0 border-0 me-1" data-group-toggle="group-{{ $groupId }}" title="{{ $groupCount }} quotations for this company" style="color:var(--text-muted);">
        <i class="bi bi-chevron-right group-chevron"></i>
      </button>
    @endif
    <a href="{{ route('contacts.show', $contact) }}" class="text-reset text-decoration-none fw-600">{{ $contact->company ?: $contact->name }}</a>
    @if ($groupCount > 1)
      <span class="chip chip-neutral ms-1" style="font-size:10.5px;">{{ $groupCount }}</span>
    @endif
  </td>
  <td>{{ $contact->email ?: '—' }}</td>
  <td>{{ $contact->whatsapp ?: '—' }}</td>
  <td>{{ $contact->priority ?: '—' }}</td>
  <td>
    @if ($contact->is_won)
      <span class="chip chip-success"><i class="bi bi-trophy-fill"></i>Won</span>
    @elseif ($contact->is_archived)
      <span class="chip chip-neutral"><i class="bi bi-archive-fill"></i>Archived</span>
    @else
      <span class="chip {{ \App\Models\Contact::statusChipClass($contact->status) }}"><i class="bi bi-circle-fill"></i>{{ \App\Models\Contact::statusOptions()[$contact->status] }}</span>
    @endif
  </td>
  <td class="small text-muted-c">{{ $contact->quotation_date?->format('d M Y') ?? '—' }}</td>
  <td>
    <div class="d-flex gap-1 justify-content-end flex-wrap">
      <button type="button" class="btn-icon-sq js-edit-contact"
        data-id="{{ $contact->id }}" data-quote-no="{{ $contact->quote_no }}" data-quotation-date="{{ optional($contact->quotation_date)->format('Y-m-d') }}"
        data-name="{{ $contact->name }}" data-company="{{ $contact->company }}"
        data-email="{{ $contact->email }}" data-whatsapp="{{ $contact->whatsapp }}" data-designation="{{ $contact->designation }}"
        data-sales-man="{{ $contact->sales_man }}" data-gst-number="{{ $contact->gst_number }}" data-transport="{{ $contact->transport }}"
        data-shipping-address="{{ $contact->shipping_address }}" data-stage="{{ $contact->stage }}" data-priority="{{ $contact->priority }}"
        data-status="{{ $contact->status }}" data-notes="{{ $contact->notes }}"
        title="Edit" data-bs-toggle="tooltip"><i class="bi bi-pencil"></i></button>
      @if ($contact->whatsapp)
      <a href="{{ route('contacts.whatsapp', $contact) }}" target="_blank" class="btn-icon-sq success" title="WhatsApp (uses your saved template)" data-bs-toggle="tooltip"><i class="bi bi-whatsapp"></i></a>
      @endif
      @if ($contact->email)
      <a href="{{ route('gmail.index', ['search' => $contact->email]) }}" class="btn-icon-sq" title="Find in Gmail" data-bs-toggle="tooltip"><i class="bi bi-envelope-fill"></i></a>
      @endif

      <form method="POST" action="{{ route('contacts.remind', $contact) }}">
        <input type="hidden" name="days" value="2">@csrf
        <button type="submit" class="btn btn-outline-c btn-sm" title="Remind me in 2 days" data-bs-toggle="tooltip"><i class="bi bi-alarm"></i> 2d</button>
      </form>
      <form method="POST" action="{{ route('contacts.remind', $contact) }}">
        <input type="hidden" name="days" value="7">@csrf
        <button type="submit" class="btn btn-outline-c btn-sm" title="Remind me in 7 days" data-bs-toggle="tooltip"><i class="bi bi-alarm"></i> 7d</button>
      </form>

      @if ($contact->is_won)
      <form method="POST" action="{{ route('contacts.unwon', $contact) }}">
        @csrf @method('PATCH')
        <button type="submit" class="btn-icon-sq" title="Revert from Won" data-bs-toggle="tooltip"><i class="bi bi-arrow-counterclockwise"></i></button>
      </form>
      @else
      <form method="POST" action="{{ route('contacts.won', $contact) }}">
        @csrf @method('PATCH')
        <button type="submit" class="btn-icon-sq" title="Mark Won" data-bs-toggle="tooltip"><i class="bi bi-trophy"></i></button>
      </form>
      @endif

      @if ($contact->is_archived)
      <form method="POST" action="{{ route('contacts.unarchive', $contact) }}">
        @csrf @method('PATCH')
        <button type="submit" class="btn-icon-sq" title="Restore" data-bs-toggle="tooltip"><i class="bi bi-box-arrow-up"></i></button>
      </form>
      @else
      <form method="POST" action="{{ route('contacts.archive', $contact) }}">
        @csrf @method('PATCH')
        <button type="submit" class="btn-icon-sq" title="Archive" data-bs-toggle="tooltip"><i class="bi bi-archive"></i></button>
      </form>
      @endif
    </div>
  </td>
</tr>
