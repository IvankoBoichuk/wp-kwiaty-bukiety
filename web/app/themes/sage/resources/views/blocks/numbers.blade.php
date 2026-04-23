@php use App\Blocks\Blocks; @endphp

<section
  @if (!empty($attributes->anchor)) id="{{ $attributes->anchor }}" @endif
  class="{{ trim('text-dark-text '.($attributes->className ?? '')) }}"
  data-counter-section
>
  @if ($texts->title)
    <div class="mb-8 text-center">
      <h2 class="text-h2">{{ $texts->title }}</h2>
    </div>
  @endif

  @if ($list !== [])
    <div class="space-y-4">
      @foreach ($list as $item)
        @php
          $icon = is_array($item['icon'] ?? null) ? $item['icon'] : null;
          $iconUrl = is_array($icon) ? (string) ($icon['url'] ?? '') : '';
          $iconAlt = is_array($icon) ? (string) ($icon['alt'] ?? ($icon['title'] ?? ($item['text'] ?? ''))) : '';
          $legacyIcon = is_string($item['icon'] ?? null) ? trim((string) $item['icon']) : '';
          $hasSvgIcon = $legacyIcon !== '' && str_starts_with($legacyIcon, '<svg');
        @endphp
        <div class="flex flex-col items-center gap-2 border-b border-[#C7C7C7] pb-4 text-center">
          <div
            class="text-green-default w-full text-5xl leading-14 font-medium"
            data-counter="{{ esc_attr($item['number'] ?? '0') }}"
          >
            0
          </div>
          <div class="text-body-15 text-gray-1 flex items-center justify-center gap-1 font-semibold">
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
  @endif
</section>
