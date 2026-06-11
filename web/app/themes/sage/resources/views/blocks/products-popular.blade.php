<section
  @if (!empty($attributes->anchor)) id="{{ $attributes->anchor }}" @endif
  class="{{ trim('bg-[#E5EFDE] py-12 bx-container'.($attributes->className ?? '')) }}"
>
  <div class="flex flex-col gap-y-6 pt-1.5">
    @if (!empty($texts->title))
      <div class="relative mb-6">
        @if (!empty($texts->subtitle))
          <div
            class="font-deco pointer-events-none absolute -bottom-2 left-18 z-0 text-[37px] leading-15 text-[#E0E0D7] uppercase select-none"
          >
            {{ $texts->subtitle }}
          </div>
        @endif
        <h2 class="text-h2 text-green-default relative z-10">{{ $texts->title }}</h2>
      </div>
    @endif

    <div class="grid auto-rows-auto grid-cols-2 gap-x-2.75 gap-y-6 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
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
