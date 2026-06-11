@props([
  'name' => null,
  'value' => null,
  'id' => null,
  'class' => '',
  'label' => null,
  'icon' => null,
  'type' => 'text',
  'placeholder' => null,
  'autocomplete' => null
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
    <input
      type="{{ $type }}"
      @if ($id) id="{{ $id }}" @endif
      @if ($name) name="{{ $name }}" @endif
      @if ($value !== null) value="{{ $value }}" @endif
      @if ($placeholder !== null) placeholder="{{ $placeholder }}" @endif
      @if ($autocomplete !== null) autocomplete="{{ $autocomplete }}" @endif
      class="focus:border-green-easy h-12 w-full rounded-xl border border-[#C7C7C7] bg-white px-3 py-3 pr-11 text-[16px] leading-6 text-[#404844] transition-colors outline-none"
    />
  </div>
</div>
