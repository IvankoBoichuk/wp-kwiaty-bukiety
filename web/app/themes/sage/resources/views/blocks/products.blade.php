<section @id($attributes->anchor) @class(array_filter(['relative text-dark-text', $attributes->className ?? null]))>
  <div>
    @if (!empty($texts->title) || !empty($texts->subtitle))
      <div class="mb-4.5">
        @if (!empty($texts->title))
          <h2 class="text-h2 mb-1">{{ $texts->title }}</h2>
        @endif

        @if (!empty($texts->subtitle))
          <p class="text-body-15">{{ $texts->subtitle }}</p>
        @endif
      </div>
    @endif

    <div class="events-swiper swiper">
      <div class="swiper-wrapper mb-3">
        @foreach ($products ?? [] as $item)
          @include('partials.product-card-slider', ['item' => $item])
        @endforeach
      </div>

      <div class="swiper-pagination relative! m-0 flex justify-center [--swiper-pagination-bottom:0]"></div>
    </div>
  </div>
</section>
