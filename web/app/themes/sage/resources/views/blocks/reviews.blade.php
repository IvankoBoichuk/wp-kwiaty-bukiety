<section
  @if (!empty($attributes->anchor)) id="{{ $attributes->anchor }}" @endif
  class="{{ trim('text-dark-text '.($attributes->className ?? '')) }}"
>
  @if (!empty($texts->title))
    <div class="relative mb-6">
      @if (!empty($texts->subtitle))
        <div
          class="font-deco pointer-events-none absolute -bottom-2 left-18 z-0 text-[37px] leading-15 text-[#E0E0D7] uppercase select-none"
        >
          {{ $texts->subtitle }}
        </div>
      @endif
      <h2 class="text-h2 relative z-10">{{ $texts->title }}</h2>
    </div>
  @endif

  @if (!empty($reviews) && is_array($reviews))
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
      @foreach ($reviews as $review)
        @include('partials.review-card', ['review' => $review])
      @endforeach
    </div>
  @endif
</section>
