@php
  /** @var WP_Term_Query $cities */
  $featuredCities = $cities->get_terms();
  $featuredCityIds = array_values(
    array_filter(array_map(fn($city) => (int) $city->term_id, is_array($featuredCities) ? $featuredCities : [])),
  );
  $allCitiesCount = (int) get_terms([
    'taxonomy' => 'product_cat',
    'hide_empty' => false,
    'name__like' => 'Kwiaciarnia',
    'exclude' => $featuredCityIds,
    'fields' => 'count',
  ]);

  $hasMoreCities = $allCitiesCount > 0;
  $queryArgs = [
    'taxonomy' => 'product_cat',
    'hide_empty' => false,
    'name__like' => 'Kwiaciarnia',
    'orderby' => 'name',
    'order' => 'ASC',
    'exclude' => $featuredCityIds,
    'number' => 6,
    'offset' => 0,
  ];

  $imageSrc = '';
  $imageAlt = 'Як це працює';

  if ($media->type === 'img') {
    $imageSrc = $media->origin === 'embed' ? $media->embed['url'] : $media->file['url'];
    $imageAlt = $media->file['alt'] ?? ($media->file['title'] ?? $imageAlt);
  }
@endphp

@php
  $widthPattern = [
    'flex-[1_1_28%]',
    'flex-[1_1_35%]',
    'flex-[1_1_25%]',
    'flex-[1_1_30%]',
    'flex-[1_1_32%]',
    'flex-[1_1_27%]',
    'flex-[1_1_33%]',
    'flex-[1_1_29%]',
    'flex-[1_1_26%]',
  ];
@endphp

<section
  @id($attributes->anchor)
  class="{{ trim('text-dark-text bg-[#E5EFDE] py-10 bx-container'.($attributes->className ?? '')) }}"
>
  <div class="mx-auto flex w-full grow flex-col">
    <div class="grid grid-cols-1 gap-8 lg:grid-cols-12 lg:gap-12">
      <div class="flex flex-col justify-center lg:col-span-7 lg:pt-20 lg:pb-7">
        @if ($texts->title !== '')
          <div class="mb-6">
            <h2 class="h2-mobile md:h2-desktop mb-2">{{ $texts->title }}</h2>

            @if ($texts->text !== '')
              <p class="text-body-15 md:text-body-16">{{ $texts->text }}</p>
            @endif
          </div>
        @endif

        <ul class="mb-6 flex flex-wrap gap-3" data-cities-list>
          @foreach ($featuredCities as $city)
            @php $widthClass = $widthPattern[$loop->index % count($widthPattern)]; @endphp
            <li class="{{ $widthClass }}">
              <a
                href="{{ get_term_link($city) }}"
                class="bg-green-easy text-h4 flex h-full items-center justify-center rounded-2xl px-4 py-4.5 text-center text-white lg:px-20"
              >
                {{ $city->name }}
              </a>
            </li>
          @endforeach
          @if ($hasMoreCities)
            <li class="flex min-w-max flex-1 justify-center lg:w-auto lg:flex-1" data-cities-load-more>
              <button
                data-cities-button
                data-args='@json($queryArgs)'
                data-initial-count="{{ count($featuredCities) }}"
                data-rendered-count="0"
                data-total-count="{{ $allCitiesCount }}"
                class="bg-green-dark text-gray-6 border-green-default inline-flex w-full items-center justify-center gap-2 rounded-full border-2 px-8 py-4 text-[13px] leading-[auto] font-semibold tracking-normal uppercase transition-all duration-200"
              >
                <span>{{ __('More cities', 'sage-front') }}</span>
              </button>
            </li>
          @endif
        </ul>
      </div>
      {{-- TODO: Додати картинку на бекенді --}}
      @if ($imageSrc !== '')
        <div class="relative -my-10 -mr-30 hidden overflow-hidden lg:col-span-5 lg:block">
          <div class="absolute inset-y-0 left-0 w-[50vw]">
            <img src="{{ esc_url($imageSrc) }}" alt="{{ esc_attr($imageAlt) }}" class="h-full w-full object-cover" />
          </div>
        </div>
      @endif
    </div>
  </div>
</section>
