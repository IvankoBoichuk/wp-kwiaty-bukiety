@extends('layouts.app')

@section('content')
  {{-- <main id="product" class="flex flex-col grow gap-12 pb-12 md:pt-4 lg:gap-25 lg:pt-8 lg:pb-25 bg-[#FCF9F6]"> --}}
  {{-- </main> --}}
  @php
    global $product;
    if (!isset($singleProductContentView)) {
      if (!($product instanceof \WC_Product)) {
        $product = wc_get_product(get_the_ID());
      }
      $productData = new \App\Catalog\ProductData($product);

      $singleProductContentView =
        $product instanceof \WC_Product && $productData->isFuneral()
          ? 'woocommerce.content-single-product-funeral'
          : 'woocommerce.content-single-product';
    }

    do_action('woocommerce_before_main_content');
  @endphp
  @while (have_posts())
    @php
      the_post();
    @endphp
    @include($singleProductContentView)
  @endwhile
  @php
    do_action('woocommerce_after_main_content');
  @endphp
@endsection
