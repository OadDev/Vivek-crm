@extends('layouts.app')

@section('title', 'Product Master')

@section('content')
<div class="breadcrumb-c"><a href="{{ route('dashboard') }}">Home</a><i class="bi bi-chevron-right"></i><span class="current">Product Master</span></div>
<div class="page-header">
  <div>
    <div class="page-title">Product Master</div>
    <div class="page-subtitle">Manage your product catalog and specifications.</div>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <form method="POST" action="{{ route('products.import') }}" enctype="multipart/form-data" id="productImportForm" class="d-none">
      @csrf
      <input type="file" name="file" id="productImportFile" accept=".xlsx,.xls,.csv">
    </form>
    <button type="button" class="btn btn-outline-c btn-sm" onclick="document.getElementById('productImportFile').click()"><i class="bi bi-file-earmark-arrow-up me-1"></i>Import Excel</button>
    <a href="{{ route('products.export') }}" class="btn btn-outline-c btn-sm"><i class="bi bi-file-earmark-arrow-down me-1"></i>Export Excel</a>
    <button class="btn btn-primary-c btn-sm" data-bs-toggle="modal" data-bs-target="#modalAddProduct"><i class="bi bi-plus-lg me-1"></i>Add Product</button>
  </div>
</div>

<div class="card-c">
  <div class="card-c-body">
    <form method="GET" action="{{ route('products.index') }}">
      <div class="toolbar-c">
        <div class="toolbar-search">
          <i class="bi bi-search"></i>
          <input type="text" name="search" class="form-control form-control-sm" placeholder="Search products..." value="{{ request('search') }}">
        </div>
        <div class="filter-chip-group">
          <a href="{{ route('products.index') }}" class="filter-chip-btn {{ !request('unit') || request('unit') === 'all' ? 'active' : '' }}">All Units</a>
          @foreach (['PCS','KG','BOX','SET'] as $u)
            <a href="{{ route('products.index', array_merge(request()->except('page'), ['unit' => $u])) }}" class="filter-chip-btn {{ request('unit') === $u ? 'active' : '' }}">{{ $u }}</a>
          @endforeach
        </div>
        <div class="ms-auto small text-muted-c">{{ $products->total() }} product(s)</div>
      </div>
    </form>

    <div class="table-responsive-c">
      <table class="table-c">
        <thead>
          <tr>
            <th>Code</th>
            <th>
              Product Name
              <button type="button" class="btn-icon-sq" style="width:22px;height:22px;font-size:11px;" data-bs-toggle="modal" data-bs-target="#modalCopperStandards" title="Standard Copper Reference"><i class="bi bi-info-circle"></i></button>
            </th>
            <th>Size</th><th>Weight</th><th>Unit</th><th>Rate</th><th>Specification</th><th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($products as $p)
          <tr>
            <td class="fw-600">{{ $p->code }}</td>
            <td>{{ $p->name }}</td>
            <td>{{ $p->size ?: '—' }}</td>
            <td>{{ $p->weight ?: '—' }}</td>
            <td><span class="chip chip-info">{{ $p->unit }}</span></td>
            <td class="fw-600">₹{{ number_format($p->rate, 2) }}</td>
            <td class="small text-muted-c">{{ $p->specification ?: '—' }}</td>
            <td>
              <div class="d-flex gap-1 justify-content-end">
                <button type="button" class="btn-icon-sq js-edit-product"
                  data-id="{{ $p->id }}" data-code="{{ $p->code }}" data-name="{{ $p->name }}" data-size="{{ $p->size }}"
                  data-weight="{{ $p->weight }}" data-unit="{{ $p->unit }}" data-rate="{{ $p->rate }}" data-spec="{{ $p->specification }}"
                  title="Edit" data-bs-toggle="tooltip"><i class="bi bi-pencil"></i></button>
                <form method="POST" action="{{ route('products.destroy', $p) }}" data-confirm="Delete {{ $p->name }}?">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn-icon-sq danger" title="Delete" data-bs-toggle="tooltip"><i class="bi bi-trash"></i></button>
                </form>
              </div>
            </td>
          </tr>
          @empty
          <tr><td colspan="8">
            <div class="empty-state"><div class="es-icon"><i class="bi bi-box"></i></div><h6>No products found</h6><p>Try adjusting your search or filters, or add a new product.</p></div>
          </td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="pagination-c">
      <span class="p-info">Showing {{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }} of {{ $products->total() }}</span>
      {{ $products->onEachSide(1)->links('vendor.pagination.crm') }}
    </div>
  </div>
</div>
@endsection

@push('modals')
@include('products._modals')
@endpush

@push('scripts')
<script>
document.getElementById('productImportFile').addEventListener('change', function () {
  if (this.files.length) document.getElementById('productImportForm').submit();
});

document.querySelectorAll('.js-edit-product').forEach(function (btn) {
  btn.addEventListener('click', function () {
    var form = document.getElementById('formEditProduct');
    form.action = '{{ url('products') }}/' + btn.dataset.id;
    form.querySelector('[name=code]').value = btn.dataset.code || '';
    form.querySelector('[name=name]').value = btn.dataset.name || '';
    form.querySelector('[name=size]').value = btn.dataset.size || '';
    form.querySelector('[name=weight]').value = btn.dataset.weight || '';
    form.querySelector('[name=unit]').value = btn.dataset.unit || 'PCS';
    form.querySelector('[name=rate]').value = btn.dataset.rate || 0;
    form.querySelector('[name=specification]').value = btn.dataset.spec || '';
    new bootstrap.Modal(document.getElementById('modalEditProduct')).show();
  });
});
</script>
@endpush
