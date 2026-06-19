<section @id($attributes->anchor) class="{{ trim('text-dark-text '.($attributes->className ?? '')) }}">
  @if (!empty($texts->title))
    <div class="mb-6">
      <h2 class="h2-mobile">{{ $texts->title }}</h2>
    </div>
  @endif

  @if (!empty($list) && is_array($list))
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
      @foreach ($list as $item)
        <div class="text-green-dark flex flex-col gap-2 bg-[#F6EDDB] px-2 py-3">
          <div class="flex items-center gap-2.5">
            @if (!empty($item['icon']))
              <img
                src="{{ esc_url((string) $item['icon']) }}"
                alt="{{ esc_attr($item['title'] ?? '') }}"
                width="20"
                height="20"
                class="size-5 flex-none"
              />
            @endif
            <h3 class="text-h3">{{ $item['title'] ?? '' }}</h3>
          </div>
          <p class="text-body-15">{{ $item['text'] ?? '' }}</p>
        </div>
      @endforeach
    </div>
  @endif
</section>
