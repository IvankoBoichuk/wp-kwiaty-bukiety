<section
  @if (!empty($attributes->anchor)) id="{{ $attributes->anchor }}" @endif
  class="{{ trim('bg-[#E5EFDE] py-12 lg:py-30 bx-container'.($attributes->className ?? '')) }}"
>
  <div class="flex flex-col gap-y-6 pt-1.5">
    @if (!empty($texts->title))
      <div class="relative mb-6">
        @if (!empty($texts->subtitle))
          <div
            class="font-deco pointer-events-none absolute -top-13 left-28 z-0 text-[69px] leading-15 text-[#E0EAD9] uppercase select-none md:-top-4 md:left-45 md:text-[144px] lg:-top-10 lg:left-50 lg:text-[245px]"
          >
            {{ $texts->subtitle }}
          </div>
        @endif
        <h2 class="h2-mobile md:h2-desktop text-green-default relative z-10">{{ $texts->title }}</h2>
      </div>
    @endif

    <div class="grid auto-rows-auto grid-cols-2 gap-x-3 gap-y-6 md:grid-cols-3 lg:grid-cols-4">
      @foreach ($products ?? [] as $item)
        @include('partials.product-card-grid', ['item' => $item])
      @endforeach
    </div>
    @if (!empty($buttons))
      <div class="flex min-w-max flex-1 flex-wrap justify-center">
        @foreach ($buttons as $button)
          @include('partials.button', $button)
        @endforeach
      </div>
    @endif
  </div>
</section>
