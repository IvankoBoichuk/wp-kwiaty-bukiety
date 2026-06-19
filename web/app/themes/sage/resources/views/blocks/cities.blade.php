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
@endphp
<section @id($attributes->anchor) class="{{ trim('text-dark-text '.($attributes->className ?? '')) }}">
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

  @if ($texts->title !== '')
    <div class="mb-6">
      <h2 class="h2-mobile mb-2">{{ $texts->title }}</h2>

      @if ($texts->text !== '')
        <p class="text-body-15">{{ $texts->text }}</p>
      @endif
    </div>
  @endif
  @if (!empty($featuredCities) || $hasMoreCities)
    <ul class="mb-6 flex flex-wrap gap-3" data-cities-list>
      @foreach ($featuredCities as $city)
        @php $widthClass = $widthPattern[$loop->index % count($widthPattern)]; @endphp
        <li class="{{ $widthClass }}">
          <a
            href="{{ get_term_link($city) }}"
            class="text-h4 flex h-full items-center justify-center rounded-2xl bg-[#E5EFDE] px-4 py-4.5 text-center capitalize"
          >
            {{ $city->name }}
          </a>
        </li>
      @endforeach

      @if ($hasMoreCities)
        <li class="basis-full" data-cities-load-more>
          <button
            data-cities-button
            data-args='@json($queryArgs)'
            data-initial-count="{{ count($featuredCities) }}"
            data-rendered-count="0"
            data-total-count="{{ $allCitiesCount }}"
            class="bg-green-dark text-gray-6 border-green-default inline-flex w-full items-center justify-center gap-2 rounded-full border-2 px-8 py-4 text-[13px] font-semibold uppercase transition-all duration-200"
          >
            <span>{{ __('More cities', 'sage-front') }}</span>
          </button>
        </li>
      @endif
    </ul>
  @endif
</section>
