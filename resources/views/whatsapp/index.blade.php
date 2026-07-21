@extends('layouts.app')

@section('title', 'WhatsApp Templates')

@section('content')
<div class="breadcrumb-c"><a href="{{ route('dashboard') }}">Home</a><i class="bi bi-chevron-right"></i><span class="current">WhatsApp Templates</span></div>
<div class="page-header">
  <div>
    <div class="page-title">WhatsApp Templates</div>
    <div class="page-subtitle">Create reusable message templates with dynamic placeholders.</div>
  </div>
  <button class="btn btn-primary-c btn-sm" id="createTemplateBtn" data-bs-toggle="modal" data-bs-target="#modalAddTemplate"><i class="bi bi-plus-lg me-1"></i>Create Template</button>
</div>

<form method="GET" action="{{ route('whatsapp.index') }}" class="toolbar-c">
  <div class="toolbar-search">
    <i class="bi bi-search"></i>
    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search templates..." value="{{ request('search') }}">
  </div>
</form>

<div class="row g-3">
  @forelse ($templates as $t)
  <div class="col-sm-6 col-lg-4">
    <div class="card-c hoverable template-card h-100">
      <div class="d-flex justify-content-between align-items-start">
        <div class="tpl-icon"><i class="bi bi-whatsapp"></i></div>
        <div class="d-flex gap-1">
          <button type="button" class="btn-icon-sq js-edit-template" data-id="{{ $t->id }}" data-name="{{ $t->name }}" data-message="{{ $t->message }}" title="Edit" data-bs-toggle="tooltip"><i class="bi bi-pencil"></i></button>
          <form method="POST" action="{{ route('whatsapp.destroy', $t) }}" data-confirm="Delete template &quot;{{ $t->name }}&quot;?">
            @csrf @method('DELETE')
            <button type="submit" class="btn-icon-sq danger" title="Delete" data-bs-toggle="tooltip"><i class="bi bi-trash"></i></button>
          </form>
        </div>
      </div>
      <div class="tpl-name">{{ $t->name }}</div>
      <div class="tpl-preview text-trunc-2">{{ $t->render(['name' => 'Priya']) }}</div>
      <button type="button" class="btn btn-whatsapp-c btn-sm mt-auto js-use-template" data-id="{{ $t->id }}"><i class="bi bi-send-fill me-1"></i>Use Template</button>
    </div>
  </div>
  @empty
  <div class="col-12">
    <div class="empty-state"><div class="es-icon"><i class="bi bi-whatsapp"></i></div><h6>No templates found</h6><p>Try a different search or create a new template.</p></div>
  </div>
  @endforelse
</div>
@endsection

@push('modals')
@include('whatsapp._modals')
@include('partials.whatsapp-send-modal')
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.js-edit-template').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var form = document.getElementById('formEditTemplate');
      form.action = '{{ url('whatsapp') }}/' + btn.dataset.id;
      form.querySelector('[name=name]').value = btn.dataset.name || '';
      form.querySelector('[name=message]').value = btn.dataset.message || '';
      new bootstrap.Modal(document.getElementById('modalEditTemplate')).show();
    });
  });

  document.querySelectorAll('.js-use-template').forEach(function (btn) {
    btn.addEventListener('click', function () {
      openWhatsappModal({ templateId: btn.dataset.id });
    });
  });
});
</script>
@endpush
