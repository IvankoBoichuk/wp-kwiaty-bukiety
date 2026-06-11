@extends('layouts.app')

@section('content')
  @if (function_exists('is_order_received_page') && is_order_received_page())
    @if (have_posts())
      @while (have_posts())
        @php(the_post())
        @php(the_content())
      @endwhile
    @else
      {!! do_shortcode('[woocommerce_checkout]') !!}
    @endif
  @else
    @include('woocommerce.cart.cart')
  @endif
@endsection
