@php
  $imageSrc = '';
  $imageAlt = 'Як це працює';

  if ($media->type === 'img') {
    $imageSrc = $media->origin === 'embed' ? $media->embed['url'] : $media->file['url'];
    $imageAlt = $media->file['alt'] ?? ($media->file['title'] ?? $imageAlt);
  }
@endphp

<section
  @if (!empty($attributes->anchor)) id="{{ $attributes->anchor }}" @endif
  class="{{ trim((string) ($attributes->className ?? '')) }}"
>
  <div class="grid auto-rows-auto grid-cols-12 gap-6">
    @if (!empty($texts->title))
      <h2 class="h2-mobile text-green-default col-span-7">{{ $texts->title }}</h2>
    @endif

    @if ($imageSrc !== '')
      <div class="relative col-span-5 row-span-3 h-full">
        <img
          src="{{ esc_url($imageSrc) }}"
          alt="{{ esc_attr($imageAlt) }}"
          class="absolute inset-0 size-full object-cover"
        />
      </div>
    @endif

    @foreach ($list as $item)
      <div
        class="{{ $loop->index < 2 ? 'col-span-7' : 'col-span-full' }} flex flex-col gap-3 border-b border-[#C7C7C7] pb-4 pl-1.5 text-green-dark"
      >
        <h3>
          <span class="mr-2 text-[16px]">0{{ $loop->iteration }}</span
          ><span class="text-h3">{{ $item['title'] ?? '' }}</span>
        </h3>
        <p class="text-body-13">{{ $item['text'] ?? '' }}</p>
      </div>
    @endforeach
  </div>
</section>
