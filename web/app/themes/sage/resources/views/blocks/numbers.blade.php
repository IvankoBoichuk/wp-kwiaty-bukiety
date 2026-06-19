@php use App\Blocks\Blocks; @endphp
<section
  @if (!empty($attributes->anchor)) id="{{ $attributes->anchor }}" @endif
  class="{{ trim('text-dark-text '.($attributes->className ?? '')) }}"
  data-counter-section
>
  <div class="mx-auto flex w-full grow flex-col">
    @if ($texts->title)
      <div class="mb-8.5 text-center lg:text-left">
        <h2 class="h2-mobile md:h2-desktop">{{ $texts->title }}</h2>
      </div>
    @endif

    <div class="space-y-4 md:grid md:grid-cols-2 md:gap-8 lg:grid-cols-4">
      @foreach ($list as $item)
        @php
          // TODO: Не працюють іконки
          $icon = is_array($item['icon'] ?? null) ? $item['icon'] : null;
          $iconUrl = is_array($icon) ? (string) ($icon['url'] ?? '') : '';
          $iconAlt = is_array($icon) ? (string) ($icon['alt'] ?? ($icon['title'] ?? ($item['text'] ?? ''))) : '';
          $legacyIcon = is_string($item['icon'] ?? null) ? trim((string) $item['icon']) : '';
          $hasSvgIcon = $legacyIcon !== '' && str_starts_with($legacyIcon, '<svg');
        @endphp
        <div
          class="flex h-max flex-col items-center gap-2 border-b border-[#C7C7C7] pb-4 text-center lg:border-b-0 lg:border-l lg:last:border-r"
        >
          <div
            class="text-green-default text-5xl leading-14 font-medium"
            data-counter="{{ esc_attr($item['number'] ?? '0') }}"
          >
            0
          </div>
          <div
            class="text-body-15 text-gray-1 flex items-center justify-center gap-1 leading-normal font-semibold lg:text-[22px]"
          >
            @if ($iconUrl !== '')
              <div class="flex-none">
                <img src="{{ $iconUrl }}" alt="{{ $iconAlt }}" class="size-5 object-contain" />
              </div>
            @elseif ($hasSvgIcon)
              <div class="flex-none">{!! Blocks::sanitizeSvg($legacyIcon) !!}</div>
            @endif
            {{ $item['text'] ?? '' }}
          </div>
        </div>
      @endforeach
    </div>
  </div>
</section>
