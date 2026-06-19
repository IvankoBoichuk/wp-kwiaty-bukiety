@php($id = uniqid())
<section
  @id($attributes->anchor)
  @class(array_filter(['relative text-dark-text mx-0', $attributes->className ?? null]))
>
  @if (!empty($texts->title) || !empty($texts->subtitle))
    <div class="mx-container mb-4.5 md:mb-6 lg:mb-16">
      @if (!empty($texts->title))
        <h2 class="h2-mobile md:h2-desktop mb-1 md:mb-0">{{ $texts->title }}</h2>
      @endif

      @if (!empty($texts->subtitle))
        <p class="text-body-15 md:text-body-16">{{ $texts->subtitle }}</p>
      @endif
    </div>
  @endif

  <div class="overflow-x-hidden">
    <div class="events-swiper swiper bx-container! overflow-visible!" id="events-swiper-{{ $id }}">
      <div class="swiper-wrapper mb-3">
        @foreach ($products ?? [] as $item)
          @include('partials.product-card-slider', ['item' => $item])
        @endforeach
      </div>
      <div class="mx-auto flex w-max items-center justify-center gap-5">
        <button
          id="events-swiper-{{ $id }}-prev"
          class="[&.swiper-button-disabled]:opacity-50 [&.swiper-button-disabled]:pointer-events-none flex size-10 flex-none cursor-pointer items-center justify-center rounded-xl border-2 border-[#C7C7C7] hover:border-[#B19BC5]"
        >
          <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12.5 15L7.5 10L12.5 5" stroke="#B19BC5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </button>
        <div class="swiper-pagination relative! m-0 flex justify-center [--swiper-pagination-bottom:0]"></div>
        <button
          id="events-swiper-{{ $id }}-next"
          class="[&.swiper-button-disabled]:opacity-50 [&.swiper-button-disabled]:pointer-events-none flex size-10 flex-none cursor-pointer items-center justify-center rounded-xl border-2 border-[#C7C7C7] hover:border-[#B19BC5]"
        >
          <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M7.5 15L12.5 10L7.5 5" stroke="#B19BC5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </button>
      </div>
    </div>
  </div>
</section>
