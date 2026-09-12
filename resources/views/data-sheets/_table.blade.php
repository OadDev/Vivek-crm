{{-- Dynamic table for one data sheet's rows. Columns come from the sheet's
     own headers, so this can't hardcode column names -- rendered both as
     part of the full page and as the AJAX response swapped in on search /
     sort / per-page change. --}}
@php
if (! function_exists('dataSheetSortLink')) {
    function dataSheetSortLink($column, $sort, $dir, $dataSheet) {
        $newDir = ($sort === $column && $dir === 'asc') ? 'desc' : 'asc';
        $icon = $sort === $column ? ($dir === 'asc' ? 'bi-arrow-up' : 'bi-arrow-down') : 'bi-arrow-down-up';
        $query = array_merge(request()->query(), ['sort' => $column, 'dir' => $newDir]);
        return '<a href="'.route('data-sheets.show', array_merge(['dataSheet' => $dataSheet], $query)).'" class="text-decoration-none text-reset d-inline-flex align-items-center gap-1">'.e($column).' <i class="bi '.$icon.'" style="font-size:10px;"></i></a>';
    }
}
@endphp
<div class="table-responsive-c">
  <table class="table-c">
    <thead>
      <tr>
        @foreach ($headers as $h)
          <th>{!! dataSheetSortLink($h, $sort, $dir, $dataSheet) !!}</th>
        @endforeach
      </tr>
    </thead>
    <tbody>
      @forelse ($rows as $row)
        <tr>
          @foreach ($headers as $h)
            <td data-label="{{ $h }}">{{ $row->data[$h] ?? '—' }}</td>
          @endforeach
        </tr>
      @empty
        <tr><td colspan="{{ max(count($headers), 1) }}" class="td-plain">
          <div class="empty-state">
            <div class="es-icon"><i class="bi bi-search"></i></div>
            <h6>No rows found</h6>
            <p>Try a different search term, or Sync Now if this sheet hasn't been imported yet.</p>
          </div>
        </td></tr>
      @endforelse
    </tbody>
  </table>
</div>

<div class="pagination-c">
  <span class="p-info">Showing {{ $rows->firstItem() ?? 0 }}–{{ $rows->lastItem() ?? 0 }} of {{ $rows->total() }}</span>
  <select id="dsPerPageSelect" class="form-select form-select-sm" style="width:auto;">
    @foreach ([10,20,50,100] as $opt)
      <option value="{{ $opt }}" {{ $perPage === $opt ? 'selected' : '' }}>{{ $opt }} / page</option>
    @endforeach
  </select>
  {{ $rows->onEachSide(1)->links('vendor.pagination.crm') }}
</div>
