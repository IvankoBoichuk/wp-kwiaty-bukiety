@php
  /**
   * @var WC_Order|false $order
   */
  $deliveryDateRaw = (string) $order->get_meta('delivery_date');
  $deliveryTimeRaw = (string) $order->get_meta('delivery_time');

  $deliveryDate = $deliveryDateRaw !== '' ? strtotime($deliveryDateRaw) : false;
  $deliveryTimeLabel =
    $deliveryTimeRaw !== '' ? $deliveryTimeRaw : ($deliveryDate ? date_i18n('H:i', $deliveryDate) : '');
  $orderNumber = $order instanceof WC_Order ? $order->get_order_number() : '';
@endphp

<div class="woocommerce-order">
  @if ($order instanceof WC_Order)
    @php(do_action('woocommerce_before_thankyou', $order->get_id()))
    @if ($order->has_status('failed'))
      <div class="mx-auto flex w-full max-w-md flex-col gap-4 px-3 py-8">
        <p class="text-dark-text text-center text-[22px] leading-7 font-semibold">
          {{ __('Payment failed', 'woocommerce') }}
        </p>
        <p class="text-center text-[15px] leading-6 text-[#426E59]">
          {{
            __(
              'Please try to pay for the order again or choose a different payment method.',
              'sage-front',
            )
          }}
        </p>
        <div class="flex justify-center gap-3">
          @include('partials.button',
            [
              'text' => __('Pay for the order', 'woocommerce'),
              'link' => $order->get_checkout_payment_url(),
              'variant' => 'green',
              'size' => 'lg',
              'target' => '_self',
              'showIcon' => false
            ])
        </div>
      </div>
    @else
      <section>
        <img
          src="{{ get_template_directory_uri() . '/resources/images/flowers-on-the-table.jpg' }}"
          alt="{{ esc_attr__('Order Confirmation', 'sage-front') }}"
          class="aspect-394/226 w-full object-cover"
        />

        <div class="px-3 py-8 text-center">
          <h1 class="text-h2 text-dark-text mb-4">{{ __('Order Successful!', 'sage-front') }}</h1>
          <p class="mb-6 text-[#426E59]">
            {{
              implode(' ', [
                __('Thank you for choosing us for your flower delivery.', 'sage-front'),
                __('Your order has been successfully placed, and a confirmation has been sent to your email address.', 'sage-front'),
              ])
            }}
          </p>
          @if ($deliveryDate || $deliveryTimeLabel !== '')
            <div class="mb-6 flex flex-col gap-1">
              <div class="text-3.5 text-green-easy text-center font-normal">
                {{ __('Expected delivery', 'sage-front') }}
              </div>
              <div class="text-body-15 text-dark-text flex justify-center gap-3 font-bold">
                @if ($deliveryDate)
                  <span> {{ date_i18n('d.m.Y', $deliveryDate) }} </span>
                @endif

                @if ($deliveryTimeLabel !== '')
                  <span class="time"> {{ $deliveryTimeLabel }} </span>
                @endif
              </div>
            </div>
          @endif

          <div class="mt-auto">
            @include('partials.button',
              [
                'text' => __('Back to Home Page', 'sage-front'),
                'link' => home_url('/'),
                'variant' => 'border',
                'size' => 'lg',
                'target' => '_self',
                'showIcon' => false
              ])
          </div>
        </div>
      </section>
    @endif
    @php(do_action('woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id()))
    @php(do_action('woocommerce_thankyou', $order->get_id()))
  @else
    <section class="bg-background">
      <div class="mx-auto flex w-full max-w-md flex-col gap-4 px-3 py-8 text-center">
        <h2 class="text-h2 text-dark-text">{{ __('Thank You for Your Order', 'sage-front') }}</h2>
        <p class="text-3.5 font-normal text-[#426E59]">{{
          __(
            'Your order confirmation has been saved.',
            'sage-front',
          )
        }}</p>
        <div class="mx-auto">
          @include('partials.button',
            [
              'text' => __('Back to Home Page', 'sage-front'),
              'link' => home_url('/'),
              'variant' => 'border',
              'size' => 'lg',
              'target' => '_self',
              'showIcon' => false
            ])
        </div>
      </div>
    </section>
  @endif
</div>
