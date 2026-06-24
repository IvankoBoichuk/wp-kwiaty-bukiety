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

<div @class($class)>
  @if ($label)
    @include('elements.label-product-setting')
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
