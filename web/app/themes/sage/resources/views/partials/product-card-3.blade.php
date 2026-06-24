@use('App\Catalog\Product')
@php
  /**
   * @var Product $item
   */
@endphp
<div class="swiper-slide product-card-3 flex flex-col items-start justify-center">
  <div class="relative w-full">
    <img
      src="{{ $item->image->src() }}"
      alt="{{ $item->image->alt() }}"
      @if ($item->image->width() > 0) width="{{ $item->image->width() }}" @endif
      @if ($item->image->height() > 0) height="{{ $item->image->height() }}" @endif
      @if ($item->image->srcset() !== '') srcset="{{ $item->image->srcset() }}" @endif
      @if ($item->image->sizes() !== '') sizes="{{ $item->image->sizes() }}" @endif
      class="aspect-15/13 size-full object-cover"
    />

    @include('elements.badges', ['badges' => $item->badges])
  </div>

  <div class="text-dark-text pt-2">
    <a
      href="{{ $item->link }}"
      target="{{ $item->target }}"
      class="text-body-13 truncate font-bold text-wrap uppercase before:absolute before:inset-0 md:text-lg lg:font-semibold"
    >
      {{ $item->name }}
    </a>
    <p class="text-body-13 md:text-body-15">{!! $item->price !!}</p>
  </div>
</div>
