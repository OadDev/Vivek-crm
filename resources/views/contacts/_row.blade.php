{{-- Renders one quotation row. $contact required. $groupCount/$groupId (on
     the primary/most-recent row of a multi-quotation company) render a
     small inline expand arrow; $groupClass/$hidden (on the rest of that
     company's rows) control which rows the arrow shows/hides. Every row
     looks the same either way -- clicking anywhere on it (outside a
     button/link/form) opens the contact's profile. --}}
@php
  $groupClass = $groupClass ?? '';
  $hidden = $hidden ?? false;
  $groupCount = $groupCount ?? 1;
  $groupId = $groupId ?? null;
  $waTemplate = $waTemplate ?? null;
  $waMessage = $waTemplate ? $waTemplate->render(['name' => $contact->name, 'company' => $contact->company]) : '';
  $waAppLink = $contact->whatsapp
      ? \App\Models\WhatsappMessage::previewLinks($contact->whatsapp, $waMessage)[0]
      : null;
@endphp
<tr class="{{ $groupClass }} contact-row" data-href="{{ route('contacts.show', $contact) }}" style="cursor:pointer;{{ $hidden ? 'display:none;' : '' }}">
  <td class="td-plain">
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
  <td class="fw-600" data-label="Quote No.">{{ $contact->quote_no ?: '—' }}</td>
  <td data-label="Company">
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
  <td data-label="Email">{{ $contact->email ?: '—' }}</td>
  <td data-label="Phone / WhatsApp">{{ $contact->whatsapp ?: '—' }}</td>
  <td data-label="Priority">{{ $contact->priority ?: '—' }}</td>
  <td data-label="Status">
    @if ($contact->is_won)
      <span class="chip chip-success"><i class="bi bi-trophy-fill"></i>Won</span>
    @elseif ($contact->is_archived)
      <span class="chip chip-neutral"><i class="bi bi-archive-fill"></i>Archived</span>
    @else
      <span class="chip {{ \App\Models\Contact::statusChipClass($contact->status) }}"><i class="bi bi-circle-fill"></i>{{ \App\Models\Contact::statusOptions()[$contact->status] }}</span>
    @endif
  </td>
  <td class="small text-muted-c" data-label="Quotation Date">{{ $contact->quotation_date?->format('d M Y') ?? '—' }}</td>
  <td class="td-plain">
    <div class="d-flex gap-1 justify-content-end align-items-center" style="flex-wrap:nowrap;">
      @if ($contact->whatsapp)
      <a href="{{ $waAppLink }}" class="btn-icon-sq success js-whatsapp-btn" data-log-url="{{ route('contacts.whatsapp', $contact) }}" title="WhatsApp (uses your saved template)" data-bs-toggle="tooltip"><i class="bi bi-whatsapp"></i></a>
      @endif
      @if ($contact->email)
      <a href="{{ route('gmail.index', ['search' => $contact->email]) }}" class="btn-icon-sq" title="Find in Gmail" data-bs-toggle="tooltip"><i class="bi bi-envelope-fill"></i></a>
      @endif
      <div class="dropdown">
        <button type="button" class="btn-icon-sq" data-bs-toggle="dropdown" title="More actions"><i class="bi bi-three-dots-vertical"></i></button>
        @include('contacts._action_dropdown', ['contact' => $contact])
      </div>
    </div>
  </td>
</tr>
