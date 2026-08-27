{{-- Contacts table + pagination. Rendered both as part of the full page and
     as the AJAX response swapped in on search / per-page change, so it must
     not depend on anything only declared in index.blade.php. --}}
@php
if (! function_exists('sortLink')) {
    function sortLink($field, $label, $sort, $dir) {
        $newDir = ($sort === $field && $dir === 'asc') ? 'desc' : 'asc';
        $icon = $sort === $field ? ($dir === 'asc' ? 'bi-arrow-up' : 'bi-arrow-down') : 'bi-arrow-down-up';
        $query = array_merge(request()->query(), ['sort' => $field, 'dir' => $newDir]);
        return '<a href="'.route('contacts.index', $query).'" class="text-decoration-none text-reset d-inline-flex align-items-center gap-1">'.$label.' <i class="bi '.$icon.'" style="font-size:10px;"></i></a>';
    }
}
@endphp
<div class="table-responsive-c">
  <table class="table-c">
    <thead>
      <tr>
        <th></th>
        <th>{!! sortLink('quote_no', 'Quote No.', $sort, $dir) !!}</th>
        <th>{!! sortLink('company', 'Company', $sort, $dir) !!}</th>
        <th>{!! sortLink('email', 'Email', $sort, $dir) !!}</th>
        <th>{!! sortLink('whatsapp', 'Phone / WhatsApp', $sort, $dir) !!}</th>
        <th>{!! sortLink('priority', 'Priority', $sort, $dir) !!}</th>
        <th>{!! sortLink('status', 'Status', $sort, $dir) !!}</th>
        <th>{!! sortLink('quotation_date', 'Quotation Date', $sort, $dir) !!}</th>
        <th class="text-end">Actions</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($contacts as $group)
        @include('contacts._row', ['contact' => $group->primary, 'groupCount' => $group->count, 'groupId' => $group->primary->id])
        @if ($group->count > 1)
          @foreach ($group->others as $other)
            @include('contacts._row', ['contact' => $other, 'groupClass' => 'group-'.$group->primary->id, 'hidden' => true])
          @endforeach
        @endif
      @empty
      <tr><td colspan="9">
        <div class="empty-state">
          <div class="es-icon"><i class="bi bi-person-x"></i></div>
          <h6>No leads found</h6>
          <p>Try adjusting your search or filters, or add a new contact.</p>
        </div>
      </td></tr>
      @endforelse
    </tbody>
  </table>
</div>

<div class="pagination-c">
  <span class="p-info">Showing {{ $contacts->firstItem() ?? 0 }}–{{ $contacts->lastItem() ?? 0 }} of {{ $contacts->total() }}</span>
  <select id="perPageSelect" class="form-select form-select-sm" style="width:auto;">
    @foreach ([10,20,50,100] as $opt)
      <option value="{{ $opt }}" {{ $perPage === $opt ? 'selected' : '' }}>{{ $opt }} / page</option>
    @endforeach
  </select>
  {{ $contacts->onEachSide(1)->links('vendor.pagination.crm') }}
</div>
