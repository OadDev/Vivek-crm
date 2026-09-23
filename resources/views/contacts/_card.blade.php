{{-- Compact mobile card for one quotation -- shown instead of the desktop
     table row below the md breakpoint (see _table.blade.php). Deliberately
     minimal: no field labels, no avatar/star/priority -- just status,
     quote no., date, company, phone, email, and the same action menu as
     the desktop row, packed tightly per the client's reference design. --}}
@php
  $groupClass = $groupClass ?? '';
  $hidden = $hidden ?? false;
  $groupCount = $groupCount ?? 1;
  $groupId = $groupId ?? null;
  $waTemplate = $waTemplate ?? null;
  $waMessage = $waTemplate ? $waTemplate->render(['name' => $contact->name, 'company' => $contact->company]) : '';
  [$waAppLink, $waWebLink] = $contact->whatsapp
      ? \App\Models\WhatsappMessage::previewLinks($contact->whatsapp, $waMessage)
      : [null, null];
@endphp
<div class="contact-card {{ $groupClass }}" data-href="{{ route('contacts.show', $contact) }}" style="{{ $hidden ? 'display:none;' : '' }}">
  <div class="contact-card-top">
    <div class="d-flex align-items-center gap-2" style="min-width:0;">
      @if ($contact->is_won)
        <span class="chip chip-success"><i class="bi bi-trophy-fill"></i>Won</span>
      @elseif ($contact->is_archived)
        <span class="chip chip-neutral"><i class="bi bi-archive-fill"></i>Archived</span>
      @else
        <span class="chip {{ \App\Models\Contact::statusChipClass($contact->status) }}"><i class="bi bi-circle-fill"></i>{{ \App\Models\Contact::statusOptions()[$contact->status] }}</span>
      @endif
      <span class="fw-600 small">{{ $contact->quote_no ?: '—' }}</span>
      @if ($groupCount > 1)
        <button type="button" class="btn btn-link p-0 border-0" data-group-toggle="group-{{ $groupId }}" title="{{ $groupCount }} quotations for this company" style="color:var(--text-muted);">
          <i class="bi bi-chevron-right group-chevron"></i>
        </button>
        <span class="chip chip-neutral" style="font-size:10px;">{{ $groupCount }}</span>
      @endif
    </div>
    <span class="small text-muted-c flex-shrink-0">{{ $contact->quotation_date?->format('d M Y') ?? '—' }}</span>
  </div>
  <div class="contact-card-company">{{ $contact->company ?: $contact->name }}</div>
  @if ($contact->whatsapp)<div>{{ $contact->whatsapp }}</div>@endif
  @if ($contact->email)<div class="small text-muted-c">{{ $contact->email }}</div>@endif
  <div class="contact-card-actions">
    @if ($contact->whatsapp)
    <a href="{{ $waAppLink }}" class="btn-icon-sq success js-whatsapp-btn" data-web-link="{{ $waWebLink }}" data-log-url="{{ route('contacts.whatsapp', $contact) }}" title="WhatsApp (uses your saved template)"><i class="bi bi-whatsapp"></i></a>
    @endif
    @if ($contact->email)
    <a href="{{ route('gmail.index', ['search' => $contact->email]) }}" class="btn-icon-sq" title="Find in Gmail"><i class="bi bi-envelope-fill"></i></a>
    @endif
    <div class="dropdown">
      <button type="button" class="btn-icon-sq" data-bs-toggle="dropdown" title="More actions"><i class="bi bi-three-dots-vertical"></i></button>
      @include('contacts._action_dropdown', ['contact' => $contact])
    </div>
  </div>
</div>
