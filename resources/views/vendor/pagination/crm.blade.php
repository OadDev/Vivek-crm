@if ($paginator->hasPages())
<div class="p-btns">
  @if ($paginator->onFirstPage())
    <span class="p-btn disabled" style="opacity:.4;"><i class="bi bi-chevron-left"></i></span>
  @else
    <a href="{{ $paginator->previousPageUrl() }}" class="p-btn"><i class="bi bi-chevron-left"></i></a>
  @endif

  @foreach ($elements as $element)
    @if (is_string($element))
      <span class="p-btn disabled" style="opacity:.5;">{{ $element }}</span>
    @endif

    @if (is_array($element))
      @foreach ($element as $page => $url)
        @if ($page == $paginator->currentPage())
          <span class="p-btn active">{{ $page }}</span>
        @else
          <a href="{{ $url }}" class="p-btn">{{ $page }}</a>
        @endif
      @endforeach
    @endif
  @endforeach

  @if ($paginator->hasMorePages())
    <a href="{{ $paginator->nextPageUrl() }}" class="p-btn"><i class="bi bi-chevron-right"></i></a>
  @else
    <span class="p-btn disabled" style="opacity:.4;"><i class="bi bi-chevron-right"></i></span>
  @endif
</div>
@endif
