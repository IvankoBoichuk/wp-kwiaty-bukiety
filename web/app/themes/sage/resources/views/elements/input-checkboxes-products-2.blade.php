@props([
  'name' => 'addition_ids',
  'value' => [],
  'id' => null,
  'class' => '',
  'label' => null,
  'icon' => null,
  'items' => []
])

@php
  $id = $id ?: $name;
  $inputName = $name && str_ends_with($name, '[]') ? $name : ($name ? $name . '[]' : 'addition_ids[]');
  $selectedValues = array_map(
    'strval',
    array_values(array_filter(is_array($value) ? $value : [$value], fn($item) => $item !== null && $item !== '')),
  );

  $products = [];

  foreach ($items as $item) {
    if ($item instanceof \App\Catalog\Product) {
      $products[] = $item;
      continue;
    }

    if ($item instanceof \WC_Product) {
      $products[] = \App\Catalog\Product::fromWooCommerce($item);
      continue;
    }

    $productId = absint($item);

    if ($productId <= 0) {
      continue;
    }

    try {
      $products[] = \App\Catalog\Product::fromID($productId);
    } catch (\InvalidArgumentException $exception) {
      continue;
    }
  }
@endphp

<div @class(['flex flex-col gap-3', $class])>
  @if ($label)
    <div class="text-green-default flex items-center gap-2">
      @if ($icon)
        <svg width="20" height="20" aria-hidden="true">
          <use href="{{ get_template_directory_uri() . '/resources/icon/sprite-base.svg' }}#{{ $icon }}"></use>
        </svg>
      @endif
      <span class="text-body-14">{{ $label }}</span>
    </div>
  @endif

  <div class="grid grid-cols-2 gap-3">
    @foreach ($products as $item)
      @php
        $optionId = ($id ?: $name ?: 'addition-checkboxes') . '-' . $item->id;
        $isChecked = in_array((string) $item->id, $selectedValues, true);
      @endphp
      <label
        for="{{ $optionId }}"
        class="text-gray-1 group has-checked:border-green-easy has-checked:bg-green-easy flex cursor-pointer flex-col overflow-hidden rounded-xl border border-[#E0E0D7] bg-white transition-colors has-checked:text-white"
      >
        <input
          id="{{ $optionId }}"
          type="checkbox"
          name="{{ $inputName }}"
          value="{{ $item->id }}"
          class="addition-checkbox sr-only"
          data-addition-id="{{ $item->id }}"
          data-addition-price="{{ $item->product->get_price() }}"
          data-addition-name="{{ $item->name }}"
          @checked($isChecked)
        />

        <img
          src="{{ $item->image->src() }}"
          alt="{{ $item->image->alt() }}"
          @if ($item->image->width() > 0) width="{{ $item->image->width() }}" @endif
          @if ($item->image->height() > 0) height="{{ $item->image->height() }}" @endif
          class="h-38.5 w-full object-cover"
        />

        <div class="flex flex-1 flex-col justify-between gap-2 p-2">
          <h3 class="text-body-15 mb-2 font-semibold">{{ $item->name }}</h3>

          <div class="flex items-center justify-between gap-2">
            <span class="pl-1 text-[16px] font-bold">{!! $item->price !!}</span>

            <span
              class="text-green-default flex items-center justify-center rounded-xl border border-current bg-[#A5AB5F]/25 p-2 transition-all group-has-checked:text-white"
            >
              <svg class="group-has-checked:hidden" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M10 4.16663V15.8333" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M15.8335 10H4.16683" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
              </svg>

              <svg class="hidden group-has-checked:block" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M15.8335 10H4.16683" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </span>
          </div>
        </div>
      </label>
    @endforeach
  </div>
</div>
