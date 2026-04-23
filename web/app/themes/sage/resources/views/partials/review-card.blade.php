@php
  $rating = (float) ($review['rating'] ?? 0);
  $fullStars = (int) floor($rating);
  $decimal = $rating - $fullStars;
  $hasPartialStar = $decimal >= 0.1;
  $partialStarFill = (int) round($decimal * 100);
  $totalEmptyStars = $hasPartialStar ? (5 - $fullStars - 1) : (5 - $fullStars);
  $gradientId = wp_unique_id('review-star-');
@endphp

<div class="flex flex-col gap-3 bg-[#E5EFDE] p-4">
  <div class="flex items-center justify-between">
    <div class="flex items-center gap-2 text-[13px] leading-3.75 font-semibold">
      <div class="text-green-easy uppercase">{{ $review['name'] ?? '' }}</div>
      <div class="text-gray-3">{{ $review['location'] ?? '' }}</div>
    </div>

    <div class="flex items-center gap-0.5">
      @for ($index = 0; $index < $fullStars; $index++)
        <svg width="14" height="13" viewBox="0 0 14 13" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M6.58333 0.75L8.38583 4.40167L12.4167 4.99083L9.5 7.83167L10.1883 11.845L6.58333 9.94917L2.97833 11.845L3.66667 7.83167L0.75 4.99083L4.78083 4.40167L6.58333 0.75Z" fill="#F2994A" stroke="#F2994A" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      @endfor

      @if ($hasPartialStar)
        <svg width="14" height="13" viewBox="0 0 14 13" fill="none" xmlns="http://www.w3.org/2000/svg">
          <defs>
            <linearGradient id="{{ $gradientId }}">
              <stop offset="{{ $partialStarFill }}%" stop-color="#F2994A" />
              <stop offset="{{ $partialStarFill }}%" stop-color="transparent" />
            </linearGradient>
          </defs>
          <path
            fill="url(#{{ $gradientId }})"
            d="M6.58333 0.75L8.38583 4.40167L12.4167 4.99083L9.5 7.83167L10.1883 11.845L6.58333 9.94917L2.97833 11.845L3.66667 7.83167L0.75 4.99083L4.78083 4.40167L6.58333 0.75Z"
            stroke="#F2994A"
            stroke-width="1.5"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
        </svg>
      @endif

      @for ($index = 0; $index < $totalEmptyStars; $index++)
        <svg width="14" height="13" viewBox="0 0 14 13" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M6.58333 0.75L8.38583 4.40167L12.4167 4.99083L9.5 7.83167L10.1883 11.845L6.58333 9.94917L2.97833 11.845L3.66667 7.83167L0.75 4.99083L4.78083 4.40167L6.58333 0.75Z" stroke="#F2994A" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      @endfor
    </div>
  </div>

  @if (! empty($review['text']))
    <p class="text-[14px] font-medium">&quot;{{ $review['text'] }}&quot;</p>
  @endif
</div>
