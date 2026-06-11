@props([
  'name' => null,
  'value' => null,
  'min' => null,
  'max' => null,
  'id' => null,
  'class' => '',
  'label' => null,
  'icon' => null,
  'options' => []
])

@php
  $id = $id ?: $name;
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

  <div class="relative">
    <select
      @if ($id) id="{{ $id }}" @endif
      @if ($name) name="{{ $name }}" @endif
      class="focus:border-green-easy h-12 w-full appearance-none rounded-xl border border-[#C7C7C7] bg-white px-3 py-3 pr-11 text-[16px] leading-6 text-[#404844] transition-colors outline-none"
    >
      @if (!empty($options))
        @foreach ($options as $optionValue => $optionLabel)
          <option value="{{ $optionValue }}" @if ($value == $optionValue) selected @endif>{{ $optionLabel }}</option>
        @endforeach
      @endif
    </select>

    <span class="pointer-events-none absolute inset-y-0 right-2 flex items-center text-[#969998]" aria-hidden="true">
      <svg width="20" height="20" aria-hidden="true">
        <use href="{{ get_template_directory_uri() . '/resources/icon/sprite-base.svg' }}#chevron-down"></use>
      </svg>
    </span>
  </div>
</div>
