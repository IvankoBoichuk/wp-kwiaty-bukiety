@php
  use App\Blocks\Blocks;
  use App\Media\ImageHelper;

  /* @var array<string, Category> $categories */
  $featuredCategory = $categories[0] ?? null;
  $secondaryCategories = array_slice($categories, 1);
  $productPlaceholderImage = function_exists('wc_placeholder_img_src') ? wc_placeholder_img_src('medium') : '';

  $legacyImage = is_array($media['img'] ?? null) ? $media['img'] : [];
  $mediaType = is_string($media['type'] ?? null) ? $media['type'] : 'img';
  $mediaOrigin = is_string($media['origin'] ?? null) ? $media['origin'] : 'file';
  $mediaFile = is_array($media['file'] ?? null) ? $media['file'] : [];
  $mediaEmbed = is_array($media['embed'] ?? null) ? $media['embed'] : [];

  $heroImageSrc = $media['file']['url'] ?? '';
  $heroImageAlt = 'Hero Image';
  if ($mediaType === 'img') {
    $heroImageAlt = (string) ($mediaFile['alt'] ?? ($mediaFile['title'] ?? 'Hero Image'));
  }
@endphp

<section
  @if (!empty($attributes->anchor)) id="{{ $attributes->anchor }}" @endif
  class="{{ trim((string) ($attributes->className ?? '')) }}"
>
  <div class="relative z-0 w-full overflow-hidden rounded-2xl p-3 pb-1.5">
    @if (!empty($texts->title))
      <h1 class="text-dark-text mb-3 flex flex-col justify-center">{!! Blocks::multilineTitle($texts->title) !!}</h1>
    @endif

    @if (!empty($texts->text))
      <div
        class="text-dark-text [&_a]:underline [&_strong]:font-semibold [&_em]:italic mb-4 max-w-xl text-sm leading-6"
      >
        {!! $texts->text !!}
      </div>
    @endif

    @if (!empty($texts->advantages) && is_array($texts->advantages))
      <ul class="flex flex-wrap gap-1.5">
        @foreach ($texts->advantages as $advantage)
          <li class="border-background bg-accent flex shrink items-center gap-1 rounded-2xl border px-2">
            <span class="text-dark-text text-xs">{{ $advantage }}</span>
          </li>
        @endforeach
      </ul>
    @endif

    @if (!empty($heroImageSrc))
      <img
        src="{{ ImageHelper::resize($media['file']['id'], 200) }}"
        alt="{{ $heroImageAlt }}"
        fetchpriority="high"
        class="absolute top-1/2 right-0 -z-20 h-full w-[50%] -translate-y-1/2 object-cover"
      />
      <div class="absolute inset-0 -z-10 w-full bg-linear-to-r from-[#F9F3EB] from-50% to-[#f9f3eb00] to-70%"></div>
    @endif
  </div>

  <div class="mt-6 grid auto-rows-min grid-cols-2 gap-1.25">
    @if (!empty($featuredCategory->link))
      <div class="relative row-span-2 overflow-hidden rounded-2xl transition hover:brightness-105">
        <img
          src="{{ $featuredCategory->image->src() ?: $productPlaceholderImage }}"
          class="absolute inset-0 size-full object-cover"
          alt="{{ $featuredCategory->image->alt() ?? ($featuredCategory->name ?? '') }}"
        />
        <a
          href="{{ $featuredCategory->link ?? '#' }}"
          target="{{ $featuredCategory->target ?? '_self' }}"
          class="text-background absolute bottom-1 left-1/2 flex w-max max-w-11/12 -translate-x-1/2 items-center rounded-2xl bg-black/60 px-2.5 py-1 text-center text-[14px] leading-4 font-semibold before:absolute before:inset-0"
        >
          {{ $featuredCategory->name ?? '' }}
        </a>
      </div>
    @endif

    @if ($secondaryCategories !== [])
      @foreach ($secondaryCategories as $item)
        @if (!empty($item->link))
          <div
            class="relative flex items-center overflow-hidden rounded-2xl bg-[#F2EDE1] transition-colors hover:bg-[#F2EDE1]/80"
          >
            <img
              src="{{ $item->image->src() }}"
              class="size-12.75 flex-none object-cover"
              alt="{{ $item->image->alt() }}"
            />
            <a
              href="{{ $item->link }}"
              target="{{ $item->target ?? '_self' }}"
              class="text-body-15 text-dark-text px-2.5 before:absolute before:inset-0"
            >
              {{ $item->name ?? '' }}
            </a>
          </div>
        @endif
      @endforeach
    @endif
  </div>
</section>
