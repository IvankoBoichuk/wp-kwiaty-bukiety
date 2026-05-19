@use('App\Catalog\Product')
@use('App\Blocks\Blocks')
@php
  /**
   * @var Product $item
   */
@endphp
<div class="swiper-slide flex flex-col justify-center items-start">
  <div class="relative w-full">
    <img
      src="{{ $item->image->src() }}"
      alt="{{ $item->image->alt() }}"
      @if ($item->image->width() > 0) width="{{ $item->image->width() }}" @endif
      @if ($item->image->height() > 0) height="{{ $item->image->height() }}" @endif
      @if ($item->image->srcset() !== '') srcset="{{ $item->image->srcset() }}" @endif
      @if ($item->image->sizes() !== '') sizes="{{ $item->image->sizes() }}" @endif
      class="size-full object-cover aspect-15/13"
    />

    @if (! empty($item->badges))
      <div class="absolute top-1.25 left-1.25 flex flex-wrap gap-1.5">
        @foreach ($item->badges as $badge)
          <span class="{{ Blocks::badgeClasses((string) $badge) }}">{{ $badge }}</span>
        @endforeach
      </div>
    @endif
  </div>

  <div class="text-dark-text pt-2">
    <a
      href="{{ $item->link }}"
      target="{{ $item->target }}"
      class="text-body-13 truncate font-bold text-wrap uppercase before:absolute before:inset-0"
    >
      {{ $item->name }}
    </a>
    <p class="text-body-15">{!! $item->price !!}</p>
  </div>
</div>