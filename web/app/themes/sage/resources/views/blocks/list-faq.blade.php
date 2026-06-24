@php use App\Blocks\Blocks; @endphp

<section @id($attributes->anchor) class="{{ trim('text-dark-text '.($attributes->className ?? '')) }}">
  @if (!empty($texts->title))
    <div class="mb-8 lg:mb-16">
      <h2 class="h2-mobile md:h2-desktop">{{ $texts->title }}</h2>
    </div>
  @endif

  @if (!empty($list) && is_array($list))
    <div x-data="accordion" class="space-y-4 divide-y divide-[#E0E0D7] lg:space-y-7 lg:border-b lg:border-[#E0E0D7]">
      @foreach ($list as $item)
        <div class="overflow-hidden pb-3 last:max-lg:pb-0 lg:pb-7">
          <button
            @click="toggle('faq-{{ $loop->index }}')"
            type="button"
            :aria-expanded="isActive('faq-{{ $loop->index }}')"
            class="flex w-full items-center justify-between text-left"
          >
            <span class="text-body-16 font-bold max-lg:hidden">{{ sprintf('%02d', $loop->iteration) }}</span>
            <span
              class="h3-mobile md:h4-desktop md:text-green-dark text-green-default w-full pr-6.5 lg:ml-auto lg:max-w-202.5 lg:pr-45"
            >
              {{ $item['title'] ?? '' }}
            </span>
            <div class="relative size-6 shrink-0">
              <!-- Horizontal line (always visible) -->
              <span
                class="bg-dark-text absolute top-1/2 left-1/2 h-0.5 w-4 -translate-x-1/2 -translate-y-1/2 transition-transform duration-300"
                :class="{ 'rotate-0': !isActive('faq-{{ $loop->index }}'), 'rotate-180': isActive('faq-{{ $loop->index }}') }"
              ></span>
              <!-- Vertical line (rotates to horizontal when open) -->
              <span
                class="bg-dark-text absolute top-1/2 left-1/2 h-0.5 w-4 origin-center -translate-x-1/2 -translate-y-1/2 transition-transform duration-300"
                :class="{ 'rotate-90': !isActive('faq-{{ $loop->index }}'), 'rotate-0': isActive('faq-{{ $loop->index }}') }"
              ></span>
            </div>
          </button>
          <div x-show="isActive('faq-{{ $loop->index }}')" x-collapse>
            <div
              class="text-body-13 text-green-dark md:text-body-16 ml-auto w-full pt-2 md:pt-4 lg:max-w-208.5 lg:pr-51"
            >
              {{ $item['text'] ?? '' }}
            </div>
          </div>
        </div>
      @endforeach
    </div>
  @endif

  {!! Blocks::faqSchema($list) !!}
</section>
