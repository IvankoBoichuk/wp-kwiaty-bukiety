@props([
  'name' => null,
  'value' => null,
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

  <div class="grid grid-cols-2 gap-1.5">
    @foreach ($options as $optionValue => $optionLabel)
      @php
        $optionId = ($id ?: $name ?: 'radio') . '-' . sanitize_title((string) $optionValue);
        $isChecked = (string) $value !== '' ? (string) $value === (string) $optionValue : $loop->first;
      @endphp
      <label
        for="{{ $optionId }}"
        class="text-green-default flex min-h-11 cursor-pointer items-center justify-center rounded-xl border border-[#C7C7C7] bg-white px-4 py-2 text-center text-[16px] leading-6 transition-colors has-checked:border-[#6E8F7E] has-checked:bg-[#6E8F7E] has-checked:text-white"
      >
        <input
          id="{{ $optionId }}"
          type="radio"
          @if ($name) name="{{ $name }}" @endif
          value="{{ $optionValue }}"
          class="sr-only"
          @checked($isChecked)
        />
        <span>{{ $optionLabel }}</span>
      </label>
    @endforeach
  </div>
</div>
