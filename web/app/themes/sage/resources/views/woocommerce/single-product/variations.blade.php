<div class="flex flex-wrap gap-2" id="product-variations">
  @foreach ($sage_product->getVariations() as $variation)
    <button
      type="button"
      class="group bg-background [&.active]:border-green-easy [&.active]:bg-green-easy flex flex-1 flex-col items-center justify-center gap-1 rounded-xl border border-[#E0E0D7] px-3 py-2 text-nowrap transition-all"
      data-variation-id="{{ $variation['variation_id'] }}"
      aria-pressed="false"
    >
      <div class="text-gray-2 group-[.active]:text-background flex items-center gap-1.5">
        <span class="text-[14px] leading-tight font-medium"> {{ $variation['name'] }} </span>
        <span class="text-[15px] leading-tight font-bold"> {!! $variation['price_html'] !!} </span>
      </div>
      <span class="text-gray-3 group-[.active]:text-background text-sm">
        {!! $variation['variation_description'] !!}
      </span>
    </button>
  @endforeach
</div>
