@php
  use App\Blocks\Blocks;

  global $product;

  if (!($product instanceof \WC_Product)) {
    $product = wc_get_product(get_the_ID());
  }

  $productData = $productView ?? null;

  $orderOptionsView = $orderOptionsView ?? 'woocommerce.single-product.order-options';
@endphp

@if ($product instanceof \WC_Product && $productData)
  @php
    do_action('woocommerce_before_single_product');
  @endphp
  @if (post_password_required())
    {!! get_the_password_form() !!}
  @else
    <section class="bg-background">
      <div class="relative w-full">
        <div class="swiper product-gallery-swiper">
          <div class="swiper-wrapper">
            @foreach ($productData['gallery'] as $image)
              <div class="swiper-slide">
                <a href="{{ esc_url($image['src']) }}" class="lightgallery-item">
                  <div class="relative w-full">
                    <img
                      src="{{ esc_url($image['src']) }}"
                      alt="{{ esc_attr($image['alt'] ?: $productData['title']) }}"
                      @if (($image['width'] ?? 0) > 0) width="{{ (int) $image['width'] }}" @endif
                      @if (($image['height'] ?? 0) > 0) height="{{ (int) $image['height'] }}" @endif
                      @if (!empty($image['srcset'])) srcset="{{ esc_attr($image['srcset']) }}" @endif
                      @if (!empty($image['sizes'])) sizes="{{ esc_attr($image['sizes']) }}" @endif
                      class="aspect-394/274 h-full w-full object-cover"
                    />
                  </div>
                </a>
              </div>
            @endforeach
          </div>

          @if (!empty($productData['badges']))
            <div class="absolute top-2 right-2 z-10 flex flex-wrap gap-1.5">
              @foreach ($productData['badges'] as $badge)
                <span class="{{ Blocks::badgeClasses((string) $badge) }}">{{ $badge }}</span>
              @endforeach
            </div>
          @endif
        </div>

        <button
          type="button"
          class="product-gallery-prev absolute top-1/2 left-3 z-10 -translate-y-1/2 cursor-pointer"
          aria-label="{{ esc_attr__('Previous image', 'sage-front') }}"
        >
          <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M14 0.5C6.54416 0.5 0.5 6.54416 0.5 14C0.5 21.4558 6.54416 27.5 14 27.5C21.4558 27.5 27.5 21.4558 27.5 14C27.5 6.54416 21.4558 0.5 14 0.5Z" fill="white" fill-opacity="0.25" />
            <path d="M14 0.5C6.54416 0.5 0.5 6.54416 0.5 14C0.5 21.4558 6.54416 27.5 14 27.5C21.4558 27.5 27.5 21.4558 27.5 14C27.5 6.54416 21.4558 0.5 14 0.5Z" stroke="#FCF9F6" />
            <path d="M16.5 19L11.5 14L16.5 9" stroke="#FCF9F6" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </button>
        <button
          type="button"
          class="product-gallery-next absolute top-1/2 right-3 z-10 -translate-y-1/2 cursor-pointer"
          aria-label="{{ esc_attr__('Next image', 'sage-front') }}"
        >
          <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="0.5" y="0.5" width="27" height="27" rx="13.5" fill="white" fill-opacity="0.25" />
            <rect x="0.5" y="0.5" width="27" height="27" rx="13.5" stroke="#FCF9F6" />
            <path d="M11.5 19L16.5 14L11.5 9" stroke="#FCF9F6" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </button>
      </div>

      <div class="px-3">
        <h1 class="text-green-default mb-5 py-1.25 text-[20px] leading-5.75 font-semibold uppercase">
          {{ $productData['title'] }}
        </h1>

        {{-- Variations --}}
        @if ($productData['isVariable'] && !empty($productData['availableVariations']))
          <div class="mb-8">
            @include('woocommerce.single-product.variations', ['variations' => $productData['availableVariations']])
          </div>
        @endif

        {{-- Order Options --}}
        <div class="mb-12">
          @include($orderOptionsView)
        </div>

        {{-- Cross-sell products --}}
        @if (!empty($productData['additions']))
          <div class="mb-12">
            @include('woocommerce.single-product.cross-sell-products', ['additions' => $productData['additions']])
          </div>
        @endif

        {{-- Product Description --}}
        @if ($productData['description'])
          <div class="mb-12">
            @include('woocommerce.single-product.description', ['description' => $productData['description']])
          </div>
        @endif

        {{-- Products Reviews --}}
        @if (!empty($productData['reviews']))
          <div class="mb-12">
            @include('woocommerce.single-product.reviews', ['reviews' => $productData['reviews']])
          </div>
        @endif
      </div>
    </section>
    @if (!empty($relatedProducts))
      <section class="bx-container bg-[#E5EFDE] py-12">
        <h2 class="h2-mobile text-green-default mb-6">{{ __('Similar products', 'sage-front') }}</h2>
        <div class="grid auto-rows-auto grid-cols-2 gap-x-2.75 gap-y-6 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
          @foreach ($relatedProducts as $item)
            @include('partials.product-card-3', ['item' => $item])
          @endforeach
        </div>
      </section>
    @endif
    @php(do_action('woocommerce_after_single_product'))
  @endif
@endif

@push('scripts')
  @if ($productData && $productData['isVariable'] && !empty($productData['availableVariations']))
    <script>
      window.productVariations = @json($productData['availableVariations']);
    </script>
  @endif
@endpush
