<section @id($attributes->anchor) class="{{ trim('text-dark-text '.($attributes->className ?? '')) }}">
  @if (!empty($texts->title))
    <div class="mb-6">
      <h2 class="h2-mobile md:h2-desktop">{{ $texts->title }}</h2>
    </div>
  @endif

  @if (!empty($list) && is_array($list))
    <div class="grid grid-cols-1 gap-3 lg:grid-cols-6">
      @foreach ($list as $item)
        <div
          class="text-green-dark flex flex-col gap-2 rounded-sm bg-[#F6EDDB] px-5 py-4 lg:col-span-2 lg:p-8 nth-4:lg:col-span-3 nth-5:lg:col-span-3"
        >
          <div class="flex items-center gap-2.5">
            @if (!empty($item['icon']))
              <img
                src="{{ esc_url((string) $item['icon']) }}"
                alt="{{ esc_attr($item['title'] ?? '') }}"
                width="20"
                height="20"
                class="size-5 flex-none lg:size-6"
              />
            @endif
            <h3 class="h3-mobile lg:h4-desktop">{{ $item['title'] ?? '' }}</h3>
          </div>
          <p class="text-body-15 text-gray-600">{{ $item['text'] ?? '' }}</p>
        </div>
      @endforeach
    </div>
  @endif
</section>
