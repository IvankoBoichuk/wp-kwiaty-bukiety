@php
  use App\Blocks\Blocks;
  $iconSize = Blocks::buttonIconSize($size);
@endphp

@if (!empty($text) && !empty($link))
  <a
    href="{{ $link }}"
    target="{{ $target ?? '_self' }}"
    class="{{ Blocks::buttonClasses($variant, $size, $showIcon) }}"
  >
    <span>{{ $text }}</span>
  </a>
@endif
