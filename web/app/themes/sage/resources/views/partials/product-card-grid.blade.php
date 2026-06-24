<div class="swiper-slide flex flex-col items-start justify-center">
  <div class="relative w-full">
    <img
      src="{{ esc_url($item->image->src()) }}"
      alt="{{ esc_attr($item->image->alt()) }}"
      @if ($item->image->width() > 0) width="{{ $item->image->width() }}" @endif
      @if ($item->image->height() > 0) height="{{ $item->image->height() }}" @endif
      @if ($item->image->srcset() !== '') srcset="{{ esc_attr($item->image->srcset()) }}" @endif
      @if ($item->image->sizes() !== '') sizes="{{ esc_attr($item->image->sizes()) }}" @endif
      class="aspect-15/13 size-full object-cover"
    />

    @include('elements.badges', ['badges' => $item->badges])
  </div>

  <div class="text-dark-text pt-2">
    <a
      href="{{ esc_url($item->link) }}"
      target="{{ esc_attr($item->target) }}"
      class="truncate text-sm font-bold text-wrap uppercase before:absolute before:inset-0 md:text-lg lg:font-semibold"
    >
      {{ $item->name }}
    </a>
    <p class="text-body-13 md:text-body-15">{!! $item->price !!}</p>
  </div>
</div>
