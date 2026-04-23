@php
  use App\Blocks\Blocks;
@endphp

<div
  class="swiper-slide border-background bg-background shadow-100 flex flex-col items-start justify-center border"
>
  <div class="relative w-full">
    <img
      src="{{ esc_url($item->image->src()) }}"
      alt="{{ esc_attr($item->image->alt()) }}"
      @if ($item->image->width() > 0) width="{{ $item->image->width() }}" @endif
      @if ($item->image->height() > 0) height="{{ $item->image->height() }}" @endif
      @if ($item->image->srcset() !== '') srcset="{{ esc_attr($item->image->srcset()) }}" @endif
      @if ($item->image->sizes() !== '') sizes="{{ esc_attr($item->image->sizes()) }}" @endif
      class="aspect-square size-full object-cover"
    />

    @if ($item->badges !== [])
      <div class="absolute top-1.25 left-1.25 flex flex-wrap gap-1.5">
        @foreach ($item->badges as $badge)
          <span
            class="{{ Blocks::badgeClasses((string) $badge) }}"
            >{{ $badge }}</span
          >
        @endforeach
      </div>
    @endif
  </div>

  <div class="text-dark-text px-1 pt-2 pb-3">
    <a
      href="{{ $item->link }}"
      target="{{ $item->target }}"
      class="truncate text-sm font-bold text-wrap uppercase before:absolute before:inset-0"
    >
      {{ $item->name }}
    </a>
    <p>{!! $item->price !!}</p>
  </div>
</div>
