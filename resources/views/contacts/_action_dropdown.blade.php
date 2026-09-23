{{-- Shared "more actions" dropdown menu content for one contact, used by
     both the desktop table row and the mobile compact card. Include inside
     a Bootstrap .dropdown wrapper with $contact in scope. --}}
<ul class="dropdown-menu dropdown-menu-end">
  <li><button type="button" class="dropdown-item js-edit-contact"
    data-id="{{ $contact->id }}" data-quote-no="{{ $contact->quote_no }}" data-quotation-date="{{ optional($contact->quotation_date)->format('Y-m-d') }}"
    data-name="{{ $contact->name }}" data-company="{{ $contact->company }}"
    data-email="{{ $contact->email }}" data-whatsapp="{{ $contact->whatsapp }}" data-designation="{{ $contact->designation }}"
    data-sales-man="{{ $contact->sales_man }}" data-gst-number="{{ $contact->gst_number }}" data-transport="{{ $contact->transport }}"
    data-shipping-address="{{ $contact->shipping_address }}" data-stage="{{ $contact->stage }}" data-priority="{{ $contact->priority }}"
    data-status="{{ $contact->status }}" data-notes="{{ $contact->notes }}"><i class="bi bi-pencil me-2"></i>Edit</button></li>
  <li>
    <form method="POST" action="{{ route('contacts.remind', $contact) }}">
      <input type="hidden" name="days" value="2">@csrf
      <button type="submit" class="dropdown-item"><i class="bi bi-alarm me-2"></i>Remind me in 2 days</button>
    </form>
  </li>
  <li>
    <form method="POST" action="{{ route('contacts.remind', $contact) }}">
      <input type="hidden" name="days" value="7">@csrf
      <button type="submit" class="dropdown-item"><i class="bi bi-alarm me-2"></i>Remind me in 7 days</button>
    </form>
  </li>
  <li><hr class="dropdown-divider"></li>
  @if ($contact->is_won)
  <li>
    <form method="POST" action="{{ route('contacts.unwon', $contact) }}">
      @csrf @method('PATCH')
      <button type="submit" class="dropdown-item"><i class="bi bi-arrow-counterclockwise me-2"></i>Revert from Won</button>
    </form>
  </li>
  @else
  <li>
    <form method="POST" action="{{ route('contacts.won', $contact) }}">
      @csrf @method('PATCH')
      <button type="submit" class="dropdown-item"><i class="bi bi-trophy me-2"></i>Mark Won</button>
    </form>
  </li>
  @endif
  @if ($contact->is_archived)
  <li>
    <form method="POST" action="{{ route('contacts.unarchive', $contact) }}">
      @csrf @method('PATCH')
      <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-up me-2"></i>Restore</button>
    </form>
  </li>
  @else
  <li>
    <form method="POST" action="{{ route('contacts.archive', $contact) }}">
      @csrf @method('PATCH')
      <button type="submit" class="dropdown-item"><i class="bi bi-archive me-2"></i>Archive</button>
    </form>
  </li>
  @endif
</ul>
